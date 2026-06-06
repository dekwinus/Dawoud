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

        $data = $request->validate([
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'date' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'change' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment = \DB::transaction(function () use ($request, $data) {
            $user = Auth::user();
            $view_records = $user->hasRecordView();
            $isAdmin = (int) $user->role_id === 1;
            $sale = Sale::lockForUpdate()->findOrFail($data['sale_id']);

            if (! $view_records) {
                $this->authorizeForUser($request->user('api'), 'check_record', $sale);
            }

            $due = max(0, (float) $sale->GrandTotal - (float) $sale->paid_amount);
            if ((float) $data['montant'] > $due) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'montant' => 'Payment amount cannot exceed the remaining balance.',
                ]);
            }

            $paymentMethod = PaymentMethod::find($data['payment_method_id']);
            $accountId = $paymentMethod?->account_id ?: ($data['account_id'] ?? null);
            $status = $isAdmin ? 'approved' : 'pending';

            $payment = PaymentSale::create([
                'sale_id' => $sale->id,
                'Ref' => $this->getNumberOrder(),
                'date' => $data['date'],
                'account_id' => $accountId,
                'payment_method_id' => $data['payment_method_id'],
                'montant' => $data['montant'],
                'change' => $data['change'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'user_id' => $user->id,
                'approved_by' => $isAdmin ? $user->id : null,
                'approved_at' => $isAdmin ? Carbon::now() : null,
            ]);

            if ($isAdmin) {
                if ($accountId) {
                    Account::whereKey($accountId)->lockForUpdate()->increment('balance', $data['montant']);
                }
                $this->applyPaymentDelta($sale, (float) $data['montant']);
            }

            return $payment;
        }, 10);

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully',
            'status' => $payment->status,
        ], 201);
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

        $data = $request->validate([
            'date' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'change' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($id, $request, $data) {
            $user = Auth::user();
            $view_records = $user->hasRecordView();
            $payment = PaymentSale::lockForUpdate()->findOrFail($id);

            if (! $view_records) {
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $sale = Sale::lockForUpdate()->findOrFail($payment->sale_id);
            $wasReflected = $this->paymentAffectsLedger($payment, $sale);
            $available = max(
                0,
                (float) $sale->GrandTotal - (float) $sale->paid_amount
                    + ($wasReflected ? (float) $payment->montant : 0)
            );

            if ((float) $data['montant'] > $available + 0.0001) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'montant' => 'Payment amount cannot exceed the remaining balance.',
                ]);
            }

            if ($wasReflected) {
                if ($payment->account_id) {
                    Account::whereKey($payment->account_id)
                        ->lockForUpdate()
                        ->decrement('balance', $payment->montant);
                }
                $this->applyPaymentDelta($sale, -((float) $payment->montant));
            }

            $paymentMethod = PaymentMethod::find($data['payment_method_id']);
            $accountId = $paymentMethod?->account_id ?: ($data['account_id'] ?? null);
            $payment->update([
                'date' => $data['date'],
                'payment_method_id' => $data['payment_method_id'],
                'account_id' => $accountId,
                'montant' => $data['montant'],
                'change' => $data['change'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($wasReflected) {
                if ($accountId) {
                    Account::whereKey($accountId)
                        ->lockForUpdate()
                        ->increment('balance', $data['montant']);
                }
                $this->applyPaymentDelta($sale, (float) $data['montant']);
            }
        }, 10);

        return response()->json(['success' => true, 'message' => 'Payment Update successfully'], 200);
    }

    // ----------- Delete Payment Sales --------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', PaymentSale::class);

        DB::transaction(function () use ($id, $request) {
            $user = Auth::user();
            $view_records = $user->hasRecordView();
            $payment = PaymentSale::lockForUpdate()->findOrFail($id);

            if (! $view_records) {
                $this->authorizeForUser($request->user('api'), 'check_record', $payment);
            }

            $sale = Sale::lockForUpdate()->findOrFail($payment->sale_id);
            $wasReflected = $this->paymentAffectsLedger($payment, $sale);
            $payment->delete();

            if ($wasReflected && $payment->account_id) {
                Account::whereKey($payment->account_id)
                    ->lockForUpdate()
                    ->decrement('balance', $payment->montant);
            }

            if ($wasReflected) {
                $this->applyPaymentDelta($sale, -((float) $payment->montant));
            }
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
                $code = $prefix.'_'.$inMsg;
            } else {
                // Find the max numeric suffix among all Refs that match the INV/SL_XXXX pattern
                $maxRef = DB::table('payment_sales')
                    ->where('Ref', 'LIKE', 'INV/SL_%')
                    ->latest('id')
                    ->value('Ref');
                if ($maxRef) {
                    $parts = explode('_', $maxRef);
                    $num = (int) end($parts) + 1;
                    $code = 'INV/SL_'.$num;
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

        \DB::transaction(function () use ($id) {
            $payment = PaymentSale::with('sale')->lockForUpdate()->findOrFail($id);
            if ($payment->status === 'approved') {
                return;
            }
            if ($payment->status === 'rejected') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => 'Rejected payments cannot be approved.',
                ]);
            }

            $sale = Sale::lockForUpdate()->findOrFail($payment->sale_id);
            $alreadyReflected = $this->paymentAffectsLedger($payment, $sale);

            if (! $alreadyReflected) {
                $due = max(0, (float) $sale->GrandTotal - (float) $sale->paid_amount);
                if ((float) $payment->montant > $due + 0.0001) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'montant' => 'Payment amount cannot exceed the remaining balance.',
                    ]);
                }
            }

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
            if (! $alreadyReflected && $payment->account_id) {
                Account::whereKey($payment->account_id)
                    ->lockForUpdate()
                    ->increment('balance', $payment->montant);
            }

            if (! $alreadyReflected) {
                $this->applyPaymentDelta($sale, (float) $payment->montant);
            }
        });

        return response()->json(['success' => true]);
    }

    // ------------- Reject Payment --------------\\

    public function reject(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'Reports_payments_Sales', PaymentSale::class);

        \DB::transaction(function () use ($id) {
            $payment = PaymentSale::with('sale')->lockForUpdate()->findOrFail($id);
            if ($payment->status === 'rejected') {
                return;
            }

            $previousStatus = $payment->status;
            $sale = Sale::lockForUpdate()->findOrFail($payment->sale_id);
            $wasReflected = $this->paymentAffectsLedger($payment, $sale);

            $payment->status = 'rejected';
            $payment->approved_by = Auth::user()->id;
            $payment->approved_at = Carbon::now();
            $payment->save();

            // Log status change
            \App\Models\PaymentStatusLog::create([
                'payment_sale_id' => $payment->id,
                'previous_status' => $previousStatus,
                'new_status' => 'rejected',
                'changed_by' => Auth::user()->id,
                'changed_at' => Carbon::now(),
            ]);

            if ($wasReflected && $payment->account_id) {
                Account::whereKey($payment->account_id)
                    ->lockForUpdate()
                    ->decrement('balance', $payment->montant);
            }

            if ($wasReflected) {
                $this->applyPaymentDelta($sale, -((float) $payment->montant));
            }
        });

        return response()->json(['success' => true]);
    }

    protected function paymentAffectsLedger(PaymentSale $payment, Sale $sale): bool
    {
        if ($payment->status === 'approved') {
            return true;
        }

        if ($payment->status === 'rejected') {
            return false;
        }

        // Legacy payments were migrated to "pending" after already updating the ledger.
        $nonRejectedTotal = (float) PaymentSale::where('sale_id', $sale->id)
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->where('status', '!=', 'rejected')
                    ->orWhereNull('status');
            })
            ->sum('montant');

        return (float) $sale->paid_amount + 0.0001
            >= min((float) $sale->GrandTotal, $nonRejectedTotal);
    }

    protected function applyPaymentDelta(Sale $sale, float $delta): void
    {
        $totalPaid = min(
            (float) $sale->GrandTotal,
            max(0, (float) $sale->paid_amount + $delta)
        );
        $due = max(0, (float) $sale->GrandTotal - $totalPaid);
        $paymentStatus = $due <= 0
            ? 'paid'
            : ($totalPaid > 0 ? 'partial' : 'unpaid');

        $sale->update([
            'paid_amount' => $totalPaid,
            'payment_statut' => $paymentStatus,
        ]);
    }
}
