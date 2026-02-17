<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <title>Sale Invoice - {{$sale['Ref']}}</title>
      <style>
         @page { margin: 0; size: A5; }
         @font-face {
             font-family: 'Cairo';
             src: url("{{ public_path('fonts/Cairo-Regular.ttf') }}") format("truetype");
             font-weight: normal;
             font-style: normal;
         }
         @font-face {
             font-family: 'Cairo';
             src: url("{{ public_path('fonts/Cairo-Bold.ttf') }}") format("truetype");
             font-weight: bold;
             font-style: normal;
         }
         body { margin: 0; padding: 5px; font-family: 'Cairo', sans-serif; font-size: 9px; line-height: 1.2; }
         * { box-sizing: border-box; }
         .text-center { text-align: center; }
         .text-right { text-align: right; }
         .text-left { text-align: left; }
         .font-bold { font-weight: bold; }
         table { width: 100%; border-collapse: collapse; }
         th, td { border: 1px solid #000; padding: 2px 4px; }
         .no-border { border: none; }
      </style>
   </head>
   <body>
      <div id="print_Invoice">
         <div class="invoice-print" style="direction: rtl; text-align: right; color: #000;">
            
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 2px; border-bottom: 1px solid #000; padding-bottom: 2px;">
               <h1 style="margin: 0; font-weight: 900; font-size: 14px; color: #000; letter-spacing: 1px;">{{$setting['CompanyName']}}</h1>
            </div>

            <!-- Compact Details Row: Invoice # and Sales Name ONLY -->
            <div style="border-bottom: 1px solid #000; padding: 2px 0; margin-bottom: 5px;">
               <table style="width: 100%; border: none;">
                   <tr style="border: none;">
                       <td style="border: none; text-align: right; width: 50%;">
                           <span style="font-weight: bold;">رقم الفاتورة:</span> {{$sale['Ref']}} <br>
                           <span style="font-weight: bold;">التاريخ:</span> {{$sale['date']}}
                       </td>
                       <td style="border: none; text-align: left; width: 50%;">
                           <span style="font-weight: bold;">المبيعات:</span> {{$sale['seller_name']}}
                       </td>
                   </tr>
               </table>
            </div>

            <!-- Products Table -->
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 5px;">
               <thead>
                  <tr style="background-color: #f0f0f0;">
                     <th style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: 900; width: 15%; font-size: 9px;">الكمية</th>
                     <th style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: 900; width: 55%; font-size: 9px;">المادة</th>
                     <th style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: 900; width: 15%; font-size: 9px;">س.الإفرادي</th>
                     <th style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: 900; width: 15%; font-size: 9px;">الإجمالي</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($details as $detail)
                  <tr>
                     <td style="border: 1px solid #000; padding: 2px; text-align: center; vertical-align: middle; font-weight: bold; font-family: sans-serif;">
                        {{number_format((float)$detail['quantity'], 2, '.', '')}} {{$detail['unitSale']}}
                     </td>
                     <td style="border: 1px solid #000; padding: 2px; text-align: right; vertical-align: middle; font-weight: 800;">
                        {{$detail['name']}}
                     </td>
                     <td style="border: 1px solid #000; padding: 2px; text-align: center; vertical-align: middle; font-weight: bold; font-family: sans-serif;">
                        {{number_format((float)($detail['total'] / ($detail['quantity'] != 0 ? $detail['quantity'] : 1)), 2, '.', '')}}
                     </td>
                     <td style="border: 1px solid #000; padding: 2px; text-align: center; vertical-align: middle; font-weight: 900; font-family: sans-serif;">
                        {{number_format((float)$detail['total'], 2, '.', '')}}
                     </td>
                  </tr>
                  @endforeach
               </tbody>
            </table>

            <!-- Totals Section (One Row) -->
            <div style="border: 1px solid #000; padding: 2px; margin-bottom: 0;">
                <table style="width: 100%; border: none;">
                    <tr style="border: none;">
                        <td style="border: none; text-align: center; width: 33%;">
                            <span style="font-weight: bold;">الاجمالي:</span> {{number_format($sale['GrandTotal'], 2, '.', '')}}
                        </td>
                        <td style="border: none; text-align: center; width: 33%;">
                            <span style="font-weight: bold;">المدفوع:</span> {{number_format($sale['paid_amount'], 2, '.', '')}}
                        </td>
                        <td style="border: none; text-align: center; width: 33%;">
                            <span style="font-weight: bold;">المتبقي:</span> {{number_format($sale['GrandTotal'] - $sale['paid_amount'], 2, '.', '')}}
                        </td>
                    </tr>
                </table>
            </div>

         </div>
      </div>
   </body>
</html>
