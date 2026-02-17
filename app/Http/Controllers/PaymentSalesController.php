<?php

namespace App\Http\Controllers;

use App\Mail\CustomEmail;
use App\Models\Account;
use App\Models\Client;
use App\Models\EmailMessage;
use App\Models\PaymentMethod;
use App\Models\PaymentSale;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\SaleDocument;
use App\Models\SaleReturn;
use App\Models\Shipment;
use App\Models\Unit;
use App\Models\product_warehouse;
use App\Models\sms_gateway;
use App\Models\SMSMessage;
use App\utils\helpers;
use ArPHP\I18N\Arabic;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client as Client_guzzle;
use GuzzleHttp\Client as Client_termi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use PDF;
use Twilio\Rest\Client as Client_Twilio;

class PaymentSalesController extends BaseController
{
    // ------------- Get All Payments Sales --------------\\

    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_payments_Sales', PaymentSale::class);

        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers;
        $user = Auth::user();
        // New way: Check user's record_view field (user-level boolean)
        // Backward compatibility: If record_view is null, fall back to role permission check
        $view_records = $user->hasRecordView();
        // Filter fields With Params to retriever
        $param = [0 => 'like', 1 => '=', 2 => '='];
        $columns = [0 => 'Ref', 1 => 'sale_id', 2 => 'payment_method_id'];
        $data = [];

        // Check If User Has Permission View  All Records
        $Payments = PaymentSale::with('sale.client', 'account', 'user')
            ->where('deleted_at', '=', null)
            ->whereBetween('date', [$request->from, $request->to])
            ->where(function ($query) use ($view_records) {
                if (! $view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            })
        // Multiple Filter
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('client_id'), function ($query) use ($request) {
                    return $query->whereHas('sale.client', function ($q) use ($request) {
                        $q->where('id', '=', $request->client_id);
                    });
                });
            })
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('status'), function ($query) use ($request) {
                    return $query->where('status', '=', $request->status);
                });
            });
        $Filtred = $helpers->filter($Payments, $columns, $param, $request)
        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('Ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('date', 'LIKE', "%{$request->search}%")
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('sale', function ($q) use ($request) {
                                $q->where('Ref', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('payment_method', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('sale.client', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });
                });
            });

        $totalRows = $Filtred->count();
        if ($perPage == '-1') {
            $perPage = $totalRows;
        }
        $Payments = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($Payments as $Payment) {

            $item['date'] = $Payment->date;
            $item['Ref'] = $Payment->Ref;
            $item['Ref_Sale'] = $Payment['sale']->Ref;
            $item['sale_id'] = $Payment['sale']->id;
            $item['client_name'] = $Payment['sale']['client']->name;
            $item['payment_method'] = $Payment['payment_method']->name;
            $item['montant'] = $Payment->montant;
            $item['account_name'] = $Payment['account'] ? $Payment['account']->account_name : '---';
            $item['user_name'] = $Payment['user'] ? $Payment['user']->username : '---';
            $item['status'] = $Payment->status;
            $data[] = $item;
        }

        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);
        $sales = Sale::get(['Ref', 'id']);
        $payment_methods = PaymentMethod::where('deleted_at', '=', null)->get(['id', 'name', 'account_id']);

        return response()->json([
            'totalRows' => $totalRows,
            'payments' => $data,
            'sales' => $sales,
            'clients' => $clients,
            'payment_methods' => $payment_methods,
        ]);

    }

    // ----------- Store new Payment Sale --------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', PaymentSale::class);

        \DB::transaction(function () use ($request) {
            $helpers = new helpers;
            $user = Auth::user();
            // New way: Check user's record_view field (user-level boolean)
            // Backward compatibility: If record_view is null, fall back to role permission check
            $view_records = $user->hasRecordView();
            $sale = Sale::findOrFail($request['sale_id']);

            // Check If User Has Permission view All Records
            if (! $view_records) {
                // Check If User->id === sale->id
                $this->authorizeForUser($request->user('api'), 'check_record', $sale);
            }

            try {

                $total_paid = $sale->paid_amount + $request['montant'];
                $due = $sale->GrandTotal - $total_paid;

                if ($due === 0.0 || $due < 0.0) {
                    $payment_statut = 'paid';
                } elseif ($due !== $sale->GrandTotal) {
                    $payment_statut = 'partial';
                } elseif ($due === $sale->GrandTotal) {
                    $payment_statut = 'unpaid';
                }

                if ($request['montant'] > 0) {
                    // All payment methods are now handled the same way; no online Stripe charge is performed here.
                    PaymentSale::create([
                        'sale_id' => $sale->id,
                        'Ref' => app('App\Http\Controllers\PaymentSalesController')->getNumberOrder(),
                        'date' => $request['date'],
                        'account_id' => $request['account_id'] ? $request['account_id'] : null,
                        'payment_method_id' => $request['payment_method_id'],
                        'montant' => $request['montant'],
                        'change' => $request['change'],
                        'notes' => $request['notes'],
                        'status' => 'pending',
                        'user_id' => Auth::user()->id,
                    ]);

                    $account = Account::where('id', $request['account_id'])->exists();
 
                     if ($account) {
                         // Account exists, perform the update
                         $account = Account::find($request['account_id']);
                         $account->update([
                             'balance' => $account->balance + $request['montant'],
                         ]);
                     }

                    $total_paid = PaymentSale::where('sale_id', $sale->id)
                        ->where('deleted_at', null)
                        ->sum('montant');

                    $due = $sale->GrandTotal - $total_paid;

                    if ($due === 0.0 || $due < 0.0) {
                        $payment_statut = 'paid';
                    } elseif ($due !== $sale->GrandTotal) {
                        $payment_statut = 'partial';
                    } elseif ($due === $sale->GrandTotal) {
                        $payment_statut = 'unpaid';
                    }

                    $sale->update([
                        'paid_amount' => $total_paid,
                        'payment_statut' => $payment_statut,
                    ]);
                }

            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Create successfully'], 200);
    }

    // ------------ function show -----------\\

    public function show($id)
    {
        //

    }

    // ----------- Update Payments Sale --------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', PaymentSale::class);

        \DB::transaction(function () use ($id, $request) {
            $helpers = new helpers;
            $user = Auth::user();
            // New way: Check user's record_view field (user-level boolean)
            // Backward compatibility: If record_view is null, fall back to role permission check
            $view_records = $user->hasRecordView();
            $payment = PaymentSale::findOrFail($id);

            // Check If User Has Permission view All Records
            if (! $view_records) {
                // Check If User->id === payment->id
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $sale = Sale::find($payment->sale_id);
            $old_total_paid = $sale->paid_amount - $payment->montant;
            $new_total_paid = $old_total_paid + $request['montant'];

            $due = $sale->GrandTotal - $new_total_paid;
            if ($due === 0.0 || $due < 0.0) {
                $payment_statut = 'paid';
            } elseif ($due !== $sale->GrandTotal) {
                $payment_statut = 'partial';
            } elseif ($due === $sale->GrandTotal) {
                $payment_statut = 'unpaid';
            }

            // delete old balance
             if ($payment->account_id) {
                 $account = Account::where('id', $payment->account_id)->exists();
 
                 if ($account) {
                     // Account exists, perform the update
                     $account = Account::find($payment->account_id);
                     $account->update([
                         'balance' => $account->balance - $payment->montant,
                     ]);
                 }
             }

            try {
                if ($payment->payment_method_id != 1 && $payment->payment_method_id != '1') {

                    $payment->update([
                        'date' => $request['date'],
                        'payment_method_id' => $request['payment_method_id'],
                        'account_id' => $request['account_id'] ? $request['account_id'] : null,
                        'montant' => $request['montant'],
                        'change' => $request['change'],
                        'notes' => $request['notes'],
                    ]);

                    // update new account
                     if ($request['account_id']) {
                         $new_account = Account::where('id', $request['account_id'])->exists();
 
                         if ($new_account) {
                             // Account exists, perform the update
                             $new_account = Account::find($request['account_id']);
                             $new_account->update([
                                 'balance' => $new_account->balance + $request['montant'],
                             ]);
                         }
                     }

                    $new_total_paid = PaymentSale::where('sale_id', $sale->id)
                        ->where('deleted_at', null)
                        ->sum('montant');

                    $due = $sale->GrandTotal - $new_total_paid;
                    
                    if ($due === 0.0 || $due < 0.0) {
                        $payment_statut = 'paid';
                    } elseif ($due !== $sale->GrandTotal) {
                        $payment_statut = 'partial';
                    } elseif ($due === $sale->GrandTotal) {
                        $payment_statut = 'unpaid';
                    }

                    $sale->update([
                        'paid_amount' => $new_total_paid,
                        'payment_statut' => $payment_statut,
                    ]);

                }

            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }

        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Update successfully'], 200);
    }

    // ----------- Delete Payment Sales --------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', PaymentSale::class);

        \DB::transaction(function () use ($id, $request) {
            $user = Auth::user();
            // New way: Check user's record_view field (user-level boolean)
            // Backward compatibility: If record_view is null, fall back to role permission check
            $view_records = $user->hasRecordView();
            $payment = PaymentSale::findOrFail($id);

            // Check If User Has Permission view All Records
            if (! $view_records) {
                // Check If User->id === payment->id
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $sale = Sale::find($payment->sale_id);
            $total_paid = $sale->paid_amount - $payment->montant;
            $due = $sale->GrandTotal - $total_paid;

            if ($due === 0.0 || $due < 0.0) {
                $payment_statut = 'paid';
            } elseif ($due !== $sale->GrandTotal) {
                $payment_statut = 'partial';
            } elseif ($due === $sale->GrandTotal) {
                $payment_statut = 'unpaid';
            }

            PaymentSale::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            if ($payment->account_id) {
                 $account = Account::where('id', $payment->account_id)->exists();
 
                 if ($account) {
                     // Account exists, perform the update
                     $account = Account::find($payment->account_id);
                     $account->update([
                         'balance' => $account->balance - $payment->montant,
                     ]);
                 }
             }

            $total_paid = PaymentSale::where('sale_id', $sale->id)
                ->where('deleted_at', null)
                ->sum('montant');

            $due = $sale->GrandTotal - $total_paid;

            if ($due === 0.0 || $due < 0.0) {
                $payment_statut = 'paid';
            } elseif ($due !== $sale->GrandTotal) {
                $payment_statut = 'partial';
            } elseif ($due === $sale->GrandTotal) {
                $payment_statut = 'unpaid';
            }

            $sale->update([
                'paid_amount' => $total_paid,
                'payment_statut' => $payment_statut,
            ]);

        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Delete successfully'], 200);

    }

    // ----------- Reference order Payment Sales --------------\\

    public function getNumberOrder()
    {
        $last = DB::table('payment_sales')->latest('id')->first();

        if ($last) {
            $item = $last->Ref;
            $nwMsg = explode('_', $item);
            // Guard: if the Ref doesn't follow the expected "PREFIX_NUMBER" format,
            // fall back to querying the max numeric suffix from all conforming Refs.
            if (count($nwMsg) >= 2 && is_numeric($nwMsg[count($nwMsg) - 1])) {
                $inMsg = (int) $nwMsg[count($nwMsg) - 1] + 1;
                $prefix = implode('_', array_slice($nwMsg, 0, -1));
                $code = $prefix . '_' . $inMsg;
            } else {
                // Find the max numeric suffix among all Refs that match the INV/SL_XXXX pattern
                $maxRef = DB::table('payment_sales')
                    ->where('Ref', 'LIKE', 'INV/SL_%')
                    ->latest('id')
                    ->value('Ref');
                if ($maxRef) {
                    $parts = explode('_', $maxRef);
                    $num = (int) end($parts) + 1;
                    $code = 'INV/SL_' . $num;
                } else {
                    $code = 'INV/SL_1111';
                }
            }
        } else {
            $code = 'INV/SL_1111';
        }

        return $code;
    }

    // ----------- Payment Sale PDF --------------\\

    public function payment_sale(Request $request, $id)
    {
        $payment = PaymentSale::with('sale', 'sale.client')->findOrFail($id);

        $payment_data['sale_Ref'] = $payment['sale']->Ref;
        $payment_data['client_name'] = $payment['sale']['client']->name;
        $payment_data['client_phone'] = $payment['sale']['client']->phone;
        $payment_data['client_adr'] = $payment['sale']['client']->adresse;
        $payment_data['client_email'] = $payment['sale']['client']->email;
        $payment_data['montant'] = $payment->montant;
        $payment_data['Ref'] = $payment->Ref;
        $payment_data['date'] = $payment->date;
        $payment_data['payment_method'] = $payment['payment_method']->name;

        $helpers = new helpers;
        $settings = Setting::where('deleted_at', '=', null)->first();
        $symbol = $helpers->Get_Currency_Code();

        $Html = view('pdf.payment_sale', [
            'symbol' => $symbol,
            'setting' => $settings,
            'payment' => $payment_data,
        ])->render();

        $arabic = new Arabic;
        $p = $arabic->arIdentify($Html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($Html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $Html = substr_replace($Html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        $pdf = PDF::loadHTML($Html);

        return $pdf->download('Payment_Sale.pdf');

    }

    // ------------- Send Payment Sale on Email -----------\\

    public function SendEmail(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', PaymentSale::class);
        // PaymentSale
        $payment = PaymentSale::with('sale.client')->findOrFail($request->id);

        $helpers = new helpers;
        $currency = $helpers->Get_Currency();

        // settings
        $settings = Setting::where('deleted_at', '=', null)->first();

        // the custom msg of payment_received
        $emailMessage = EmailMessage::where('name', 'payment_received')->first();

        if ($emailMessage) {
            $message_body = $emailMessage->body;
            $message_subject = $emailMessage->subject;
        } else {
            $message_body = '';
            $message_subject = '';
        }

        $payment_number = $payment->Ref;

        $total_amount = $currency.' '.number_format($payment->montant, 2, '.', ',');

        $contact_name = $payment['sale']['client']->name;
        $business_name = $settings->CompanyName;

        // receiver email
        $receiver_email = $payment['sale']['client']->email;

        // replace the text with tags
        $message_body = str_replace('{contact_name}', $contact_name, $message_body);
        $message_body = str_replace('{business_name}', $business_name, $message_body);
        $message_body = str_replace('{payment_number}', $payment_number, $message_body);
        $message_body = str_replace('{total_amount}', $total_amount, $message_body);

        $email['subject'] = $message_subject;
        $email['body'] = $message_body;
        $email['company_name'] = $business_name;

        $this->Set_config_mail();

        Mail::to($receiver_email)->send(new CustomEmail($email));

        return response()->json(['message' => 'Email sent successfully'], 200);

        // return $mail;
    }

    // -------------------Sms Notifications -----------------\\

    public function Send_SMS(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', PaymentSale::class);

        // PaymentSale
        $payment = PaymentSale::with('sale.client')->findOrFail($request->id);

        // settings
        $settings = Setting::where('deleted_at', '=', null)->first();

        $default_sms_gateway = sms_gateway::where('id', $settings->sms_gateway)
            ->where('deleted_at', '=', null)->first();

        $helpers = new helpers;
        $currency = $helpers->Get_Currency();

        // the custom msg of payment_received
        $smsMessage = SMSMessage::where('name', 'payment_received')->first();

        if ($smsMessage) {
            $message_text = $smsMessage->text;
        } else {
            $message_text = '';
        }

        $payment_number = $payment->Ref;

        $total_amount = $currency.' '.number_format($payment->montant, 2, '.', ',');

        $contact_name = $payment['sale']['client']->name;
        $business_name = $settings->CompanyName;

        // receiver phone
        $receiverNumber = $payment['sale']['client']->phone;

        // replace the text with tags
        $message_text = str_replace('{contact_name}', $contact_name, $message_text);
        $message_text = str_replace('{business_name}', $business_name, $message_text);
        $message_text = str_replace('{payment_number}', $payment_number, $message_text);
        $message_text = str_replace('{total_amount}', $total_amount, $message_text);

        // twilio
        if ($default_sms_gateway->title == 'twilio') {
            try {

                $account_sid = env('TWILIO_SID');
                $auth_token = env('TWILIO_TOKEN');
                $twilio_number = env('TWILIO_FROM');

                $client = new Client_Twilio($account_sid, $auth_token);
                $client->messages->create($receiverNumber, [
                    'from' => $twilio_number,
                    'body' => $message_text]);

            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }
        // termii
        elseif ($default_sms_gateway->title == 'termii') {

            $client = new Client_termi;
            $url = 'https://api.ng.termii.com/api/sms/send';

            $payload = [
                'to' => $receiverNumber,
                'from' => env('TERMI_SENDER'),
                'sms' => $message_text,
                'type' => 'plain',
                'channel' => 'generic',
                'api_key' => env('TERMI_KEY'),
            ];

            try {
                $response = $client->post($url, [
                    'json' => $payload,
                ]);

                $result = json_decode($response->getBody(), true);

                return response()->json($result);
            } catch (\Exception $e) {
                Log::error('Termii SMS Error: '.$e->getMessage());

                return response()->json(['status' => 'error', 'message' => 'Failed to send SMS'], 500);
            }

        }
        // ---- infobip
        elseif ($default_sms_gateway->title == 'infobip') {

            $BASE_URL = env('base_url');
            $API_KEY = env('api_key');
            $SENDER = env('sender_from');

            $configuration = (new Configuration)
                ->setHost($BASE_URL)
                ->setApiKeyPrefix('Authorization', 'App')
                ->setApiKey('Authorization', $API_KEY);

            $client = new Client_guzzle;

            $sendSmsApi = new SendSMSApi($client, $configuration);
            $destination = (new SmsDestination)->setTo($receiverNumber);
            $message = (new SmsTextualMessage)
                ->setFrom($SENDER)
                ->setText($message_text)
                ->setDestinations([$destination]);

            $request = (new SmsAdvancedTextualRequest)->setMessages([$message]);

            try {
                $smsResponse = $sendSmsApi->sendSmsMessage($request);
                echo 'Response body: '.$smsResponse;
            } catch (Throwable $apiException) {
                echo 'HTTP Code: '.$apiException->getCode()."\n";
            }

        }

        return response()->json(['success' => true]);
    }



    // ------------- Payment Status History --------------\\

    public function statusHistory(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_payments_Sales', PaymentSale::class);
        $logs = \App\Models\PaymentStatusLog::with('payment.sale', 'user')
            ->orderBy('id', 'desc')
            ->limit($request->get('limit', 100))
            ->get()
            ->map(function ($log) {
                return [
                    'date' => optional($log->changed_at)->toDateTimeString(),
                    'status' => $log->new_status,
                    'sale_ref' => optional(optional($log->payment)->sale)->Ref,
                    'user_name' => optional($log->user)->username,
                ];
            });
        return response()->json(['logs' => $logs]);
    }

    // ------------- Approve Payment --------------\\

    public function approve(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_payments_Sales', PaymentSale::class);
        
        $payment = PaymentSale::findOrFail($id);
        if ($payment->status === 'approved') {
            return response()->json(['success' => true]);
        }

        \DB::transaction(function () use ($payment) {
            $payment->status = 'approved';
            $payment->approved_by = Auth::user()->id;
            $payment->approved_at = Carbon::now();
            $payment->save();
    
            // Log status change
            \App\Models\PaymentStatusLog::create([
                'payment_sale_id' => $payment->id,
                'previous_status' => 'pending',
                'new_status' => 'approved',
                'changed_by' => Auth::user()->id,
                'changed_at' => Carbon::now(),
            ]);
    
            // Update account balance
            if ($payment->account_id) {
                $account = Account::find($payment->account_id);
                if ($account) {
                    $account->update(['balance' => $account->balance + $payment->montant]);
                }
            }
    
            // Update sale status
            $sale = $payment->sale;
            $totalPaid = PaymentSale::where('sale_id', $sale->id)
                ->where('deleted_at', null)
                ->where('status', '!=', 'rejected') // Exclude rejected payments
                ->sum('montant');
            
            $due = $sale->GrandTotal - $totalPaid;
            
            if ($due <= 0) {
                $sale->update([
                    'paid_amount' => $totalPaid,
                    'payment_statut' => 'paid',
                    'has_payment_deadline' => false,
                    'payment_deadline' => null,
                    'deadline_passed' => false,
                ]);
            } else {
                $payment_statut = $due < $sale->GrandTotal ? 'partial' : 'unpaid';
                $sale->update([
                    'paid_amount' => $totalPaid,
                    'payment_statut' => $payment_statut,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    // ------------- Reject Payment --------------\\

    public function reject(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_payments_Sales', PaymentSale::class);
        
        $payment = PaymentSale::findOrFail($id);
        if ($payment->status === 'rejected') {
            return response()->json(['success' => true]);
        }

        \DB::transaction(function () use ($payment) {
            $payment->status = 'rejected';
            $payment->approved_by = Auth::user()->id; // Or rejected_by if you add that column
            $payment->approved_at = Carbon::now();
            $payment->save();
    
            // Log status change
            \App\Models\PaymentStatusLog::create([
                'payment_sale_id' => $payment->id,
                'previous_status' => 'pending',
                'new_status' => 'rejected',
                'changed_by' => Auth::user()->id,
                'changed_at' => Carbon::now(),
            ]);
    
            // Reverse account balance if it was credited (though pending payments shouldn't credit accounts generally, 
            // but if your logic credits on creation, then reverse here. 
            // YOUR CURRENT STORE LOGIC CREDITS ACCOUNT IMMEDIATELY. 
            // SO WE MUST REVERSE IT IF REJECTED.)
            if ($payment->account_id) {
                $account = Account::find($payment->account_id);
                if ($account) {
                    $account->update(['balance' => $account->balance - $payment->montant]);
                }
            }
    
            $sale = $payment->sale;
            $totalPaid = PaymentSale::where('sale_id', $sale->id)
                ->where('deleted_at', null)
                ->where('status', '!=', 'rejected')
                ->sum('montant');
            
            // If no valid payments remain, DELETE THE SALE
            if ($totalPaid == 0) {
                // 1. Check Returns (Skip deletion if returns exist)
                if (SaleReturn::where('sale_id', $sale->id)->whereNull('deleted_at')->exists()) {
                     // Log or just skip? 
                     // We'll skip deleting the sale, but still update status.
                } else {
                    // 2. Restore Stock (if completed)
                    if ($sale->statut === 'completed') {
                        foreach ($sale->details as $d) {
                            $product = $d->product;
                            if (! $product || ($product->type ?? null) === 'is_service') {
                                continue;
                            }

                            $unit = $d->sale_unit_id ? Unit::find($d->sale_unit_id) : optional($product->unitSale);
                            $qty = (float) $d->quantity;
                            $opv = max((float) ($unit->operator_value ?? 1), 1);
                            $addQ = ($unit && $unit->operator === '/') ? ($qty / $opv) : ($qty * $opv);

                            $pw = product_warehouse::whereNull('deleted_at')
                                ->where('warehouse_id', $sale->warehouse_id)
                                ->where('product_id', $d->product_id)
                                ->when($d->product_variant_id, fn ($q) => $q->where('product_variant_id', $d->product_variant_id))
                                ->first();

                            if ($pw && $unit) {
                                $pw->qte = max(0, $pw->qte + $addQ);
                                $pw->save();
                            }
                        }
                    }

                    // 3. Reverse client points
                    $client = Client::find($sale->client_id);
                    if ($client) {
                        $used_points = (int) ($sale->used_points ?? 0);
                        $earned_points = (int) ($sale->earned_points ?? 0);

                        if ($used_points > 0) {
                            $client->increment('points', $used_points);
                        }
                        if ($earned_points > 0) {
                            $new_balance = max(0, (int) $client->points - $earned_points);
                            $client->update(['points' => $new_balance]);
                        }
                    }

                    // 4. Delete Shipment
                    if ($ship = Shipment::where('sale_id', $sale->id)->first()) {
                        $ship->delete();
                    }

                    // 5. Delete Details
                    $sale->details()->delete();

                    // 6. Delete Payments
                    $payments = PaymentSale::where('sale_id', $sale->id)->get();
                    foreach ($payments as $p) {
                         // Account balance already reversed if needed (by reject logic or if handled here)
                         // For "this" payment, balance is reversed above.
                         // For other payments (if any - unlikely since totalPaid=0), balance reversal logic is tricky.
                         // Usually we assume if totalPaid=0, they are all rejected/deleted/0-amount.
                         // If we delete them, we just clean up.
                         $p->delete();
                    }

                    // 7. Soft-delete the sale
                    $sale->update([
                        'deleted_at' => Carbon::now(),
                        'shipping_status' => null,
                        'statut' => 'deleted', // Update status to reflect deletion logic
                    ]);
                    
                    return; // Exit as sale is deleted
                }
            }

            $due = $sale->GrandTotal - $totalPaid;
            
            if ($due <= 0) {
                $sale->update([
                    'paid_amount' => $totalPaid,
                    'payment_statut' => 'paid',
                    'has_payment_deadline' => false,
                    'payment_deadline' => null,
                    'deadline_passed' => false,
                ]);
            } else {
                $payment_statut = $due < $sale->GrandTotal ? 'partial' : 'unpaid';
                $sale->update([
                    'paid_amount' => $totalPaid,
                    'payment_statut' => $payment_statut,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

}
