<template>
  <div class="main-content">
    <breadcumb class="no-print" :page="$t('SaleDetail')" :folder="$t('Sales')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3 no-print"></div>

    <b-card v-if="!isLoading" class="print-card">
      <b-row class="no-print">
        <b-col md="12" class="mb-4">
          <div class="action-buttons-wrapper">
            <!-- Navigation Actions Group -->
            <div class="button-group navigation-actions">
              <router-link
                :to="{ name: 'index_sales' }"
                class="action-btn btn-back"
                title="Back"
              >
                <i class="i-Left"></i>
                <span>{{$t('Back')}}</span>
              </router-link>
            </div>

            <!-- Primary Actions Group -->
            <div class="button-group primary-actions">
              <router-link
                v-if="currentUserPermissions && currentUserPermissions.includes('Sales_edit') && sale.sale_has_return == 'no'"
                title="Edit"
                class="action-btn btn-edit"
                :to="{ name:'edit_sale', params: { id: $route.params.id } }"
              >
                <i class="i-Edit"></i>
                <span>{{$t('EditSale')}}</span>
              </router-link>

              <button
                v-if="currentUserPermissions && currentUserPermissions.includes('Sales_delete') && sale.sale_has_return == 'no'"
                @click="Delete_Sale()"
                class="action-btn btn-delete"
                title="Delete"
              >
                <i class="i-Close-Window"></i>
                <span>{{$t('Del')}}</span>
              </button>
            </div>

            <!-- Communication Actions Group -->
            <div class="button-group communication-actions">
              <button @click="Send_Email()" class="action-btn btn-email" title="Send Email">
                <i class="i-Envelope-2"></i>
                <span>{{$t('Email')}}</span>
              </button>
              <button @click="Sale_SMS()" class="action-btn btn-sms" title="Send SMS">
                <i class="i-Speach-Bubble"></i>
                <span>SMS</span>
              </button>
            </div>

            <!-- Export & Print Actions Group -->
            <div class="button-group export-actions">
              <button @click="Sale_PDF()" class="action-btn btn-pdf" title="Download PDF">
                <i class="i-File-TXT"></i>
                <span>PDF</span>
              </button>
              <button @click="print()" class="action-btn btn-print" title="Print">
                <i class="i-Billing"></i>
                <span>{{$t('print')}}</span>
              </button>
            </div>
          </div>
        </b-col>
      </b-row>
      <div id="invoice-POS">
        <div class="invoice">
          <div class="invoice-print">
            <div style="width:100%; margin:0 auto; font-family: 'Cairo', sans-serif; direction: rtl; text-align: right; color: #000; font-size: 10px; line-height: 1.2;">
            
              <!-- Header -->
              <div style="text-align: center; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 5px; padding-top: 5px;">
                <h1 style="margin: 0; font-weight: 900; font-size: 24px; color: #000; letter-spacing: 1px;">{{company.CompanyName}}</h1>
                <h2 style="margin: 10px 0 0 0; font-size: 18px; font-weight: 800; color: #000; text-transform: uppercase;">بيان مبيعات</h2> 
              </div>

              <!-- Top Meta Data Box -->
              <div style="border: 1px solid #000; margin-bottom: 10px;">
              
                <!-- Row 2: Date -->
                <div style="border-bottom: 1px solid #000; padding: 0; display: flex;">
                    <div style="width: 30%; padding: 4px 8px; text-align: center; font-weight: 900; border-left: 1px solid #000; background: #f0f0f0;">
                       التاريخ
                    </div>
                    <div style="width: 70%; padding: 4px 8px; text-align: center; font-family: sans-serif; font-weight: bold; font-size: 11px;">
                        {{formatDisplayDate(sale.date)}}
                    </div>
                </div>

                <!-- Row 3: Customer -->
                <div style="padding: 0; display: flex;">
                    <div style="width: 30%; padding: 4px 8px; text-align: center; font-weight: 900; border-left: 1px solid #000; background: #f0f0f0;">
                       اسم العميل
                    </div>
                    <div style="width: 70%; padding: 4px 8px; text-align: center; font-weight: 800; font-size: 12px;">
                        {{sale.client_name}}
                    </div>
                </div>
              </div>

              <!-- Products Table -->
              <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-bottom: 10px;">
                <thead>
                  <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #000; padding: 5px 4px; text-align: center; font-weight: 900; width: 10%; font-size: 11px;">الكمية</th>
                    <th style="border: 1px solid #000; padding: 5px 4px; text-align: center; font-weight: 900; width: 55%; font-size: 11px;">اسم المنتج</th>
                    <th style="border: 1px solid #000; padding: 5px 4px; text-align: center; font-weight: 900; width: 10%; font-size: 11px;">الوحدة</th>
                    <th style="border: 1px solid #000; padding: 5px 4px; text-align: center; font-weight: 900; width: 12%; font-size: 11px;">السعر</th>
                    <th style="border: 1px solid #000; padding: 5px 4px; text-align: center; font-weight: 900; width: 13%; font-size: 11px;">الإجمالي</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(detail, index) in details" :key="index">
                    <td style="border: 1px solid #000; padding: 8px 5px; text-align: center; vertical-align: middle; font-weight: bold; font-family: sans-serif;">
                        {{formatNumber(detail.quantity, 2)}}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px 10px; text-align: right; vertical-align: middle; font-weight: 800;">
                      {{detail.name}}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px 5px; text-align: center; vertical-align: middle; font-weight: bold;">
                        {{detail.unit_sale}}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px 5px; text-align: center; vertical-align: middle; font-weight: bold; font-family: sans-serif;">
                        {{formatPriceDisplay(detail.Net_price, 2)}}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px 5px; text-align: center; vertical-align: middle; font-weight: 900; font-family: sans-serif; background-color: #fafafa;">
                        {{formatPriceDisplay(detail.total, 2)}}
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Totals Section -->
              <div style="border: 1px solid #000; margin-bottom: 10px;">
                  <div style="display: flex;">
                      <!-- Right Side: Text Amount -->
                      <div style="width: 55%; display: flex; align-items: center; justify-content: center; text-align: center; padding: 10px; border-left: 1px solid #000;">
                          <div style="border: 1px dashed #000; padding: 5px; width: 100%;">
                              <div style="margin-bottom: 2px; font-weight: 900; font-size: 14px;">فقط {{formatPriceWithSymbol(currentUser.currency, sale.GrandTotal, 2)}}</div>
                              <div style="font-weight: 800; font-size: 12px;">لا غير</div>
                          </div>
                      </div>

                      <!-- Left Side: Totals -->
                      <div style="width: 45%;">
                          <!-- Grand Total -->
                          <div style="display: flex; border-bottom: 1px solid #000;">
                              <div style="width: 60%; padding: 4px; text-align: center; font-weight: 900; background: #f0f0f0; border-left: 1px solid #000;">المجموع الكلي</div>
                              <div style="width: 40%; padding: 4px; text-align: center; font-weight: 900; font-family: sans-serif;">{{formatNumber(sale.GrandTotal, 2)}}</div>
                          </div>
                          <!-- Discount -->
                          <div style="display: flex; border-bottom: 1px solid #000;">
                              <div style="width: 60%; padding: 4px; text-align: center; font-weight: 900; background: #f0f0f0; border-left: 1px solid #000;">خصم</div>
                              <div style="width: 40%; padding: 4px; text-align: center; font-weight: bold; font-family: sans-serif;">{{formatNumber(sale.discount, 2)}}</div>
                          </div>
                          <!-- Paid -->
                          <div style="display: flex; border-bottom: 1px solid #000;">
                              <div style="width: 60%; padding: 4px; text-align: center; font-weight: 900; background: #f0f0f0; border-left: 1px solid #000;">مدفوع</div>
                              <div style="width: 40%; padding: 4px; text-align: center; font-weight: 900; font-family: sans-serif; color: #000;">{{formatNumber(sale.paid_amount, 2)}}</div>
                          </div>
                          <!-- Due -->
                          <div style="display: flex; background-color: #f9f9f9;">
                              <div style="width: 60%; padding: 6px; text-align: center; font-weight: 900; background: #000; color: #fff; border-left: 1px solid #000;">متبقي</div>
                              <div style="width: 40%; padding: 6px; text-align: center; font-weight: 900; font-family: sans-serif; font-size: 12px;">{{formatNumber(sale.due, 2)}}</div>
                          </div>
                      </div>
                  </div>
              </div>
              
              <!-- Footer Barcode & References -->
              <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
                   <div style="text-align: right;">
                       <div style="font-size: 13px; font-weight: 800; margin-bottom: 5px;">المبيعات: {{sale.created_by}}</div>
                       <div style="font-size: 11px; font-family: sans-serif; color: #333;">{{sale.Ref}}</div>
                   </div>
                   <div style="text-align: center;">
                        <barcode
                         v-if="sale.Ref"
                         :value="sale.Ref"
                         :format="'CODE128'"
                         :textmargin="0"
                         :fontSize="14"
                         :height="35"
                         :width="1.2"
                         :displayValue="false"
                       ></barcode>
                       <div style="font-size: 11px; font-family: sans-serif; font-weight: bold; margin-top: 2px;">{{sale.Ref}}</div>
                   </div>
              </div>

              <!-- Footer Message -->
              <div style="border-top: 2px solid #000; padding-top: 5px; text-align: center;">
                 <div style="font-weight: 900; font-size: 12px; margin-bottom: 10px;">شكراً لزيارتكم .. نرجو تشريفنا مرة أخرى</div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </b-card>
  </div>
</template>

<script>

import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";
import VueBarcode from "vue-barcode";
import {
  formatPriceDisplay as formatPriceDisplayHelper,
  getPriceFormatSetting
} from "../../../../utils/priceFormat";
import Util from "../../../../utils/index";

export default {
  components: {
    barcode: VueBarcode,
  },
  computed: {
    ...mapGetters(["currentUserPermissions", "currentUser"]),

    // Sum of line totals before order-level tax/discount/shipping
    invoiceSubtotal() {
      try {
        const details = Array.isArray(this.details) ? this.details : [];
        return details.reduce((sum, d) => {
          const n = Number(d && d.total != null ? d.total : 0);
          return sum + (Number.isFinite(n) ? n : 0);
        }, 0);
      } catch (e) {
        return 0;
      }
    },

    currentReceiptPaperSizeClass() {
      // Default to 80mm if not set
      const size = (this.pos_settings && this.pos_settings.receipt_paper_size) || 80;
      return `receipt-${size}`;
    },

    // Manual discount amount only (excluding discount from points)
    manualSaleDiscountAmount() {
      try {
        const sale = this.sale || {};
        const discMethod = String(sale.discount_Method || '2');
        const discVal = Number(sale.discount || 0);
        // Use actual order subtotal from line totals
        const subtotal = Number(this.invoiceSubtotal || 0);
        if (!Number.isFinite(subtotal) || subtotal <= 0) {
          return 0;
        }

        if (discMethod === '1') {
          // Percentage discount: use subtotal * %
          return parseFloat((subtotal * (discVal / 100)).toFixed(2));
        }
        // Fixed discount
        return parseFloat(Math.min(discVal, subtotal).toFixed(2));
      } catch (e) {
        return 0;
      }
    },
  },
  metaInfo: {
    title: "Detail Sale"
  },

  data() {
    return {
      isLoading: true,
      sale: {},
      details: [],
      pos_settings: {},
      variants: [],
      company: {},
      email: {
        to: "",
        subject: "",
        message: "",
        client_name: "",
        Sale_Ref: ""
      },
      // Optional price format key for frontend display (loaded from system settings/localStorage)
      price_format_key: null
    };
  },

  methods: {
   

    //----------------------------------- Invoice Sale PDF  -------------------------\\
    Sale_PDF() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
     
       axios
        .get(`sale_pdf/${id}`, {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "Sale_" + this.sale.Ref + ".pdf");
          document.body.appendChild(link);
          link.click();
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

     //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //------------------------------Formetted Numbers -------------------------\\
    formatNumber(number, dec) {
      const value = (typeof number === "string"
        ? number
        : number.toString()
      ).split(".");
      if (dec <= 0) return value[0];
      let formated = value[1] || "";
      if (formated.length > dec)
        return `${value[0]}.${formated.substr(0, dec)}`;
      while (formated.length < dec) formated += "0";
      return `${value[0]}.${formated}`;
    },

    // Price formatting for display only (does NOT affect calculations or stored values)
    // Uses the global/system price_format setting when available; otherwise falls back
    // to the existing formatNumber helper to preserve current behavior.
    formatPriceDisplay(number, dec) {
      try {
        const decimals = Number.isInteger(dec) ? dec : 0;
        const key = this.price_format_key || getPriceFormatSetting({ store: this.$store });
        if (key) {
          this.price_format_key = key;
        }
        const effectiveKey = key || null;
        return formatPriceDisplayHelper(number, decimals, effectiveKey);
      } catch (e) {
        return this.formatNumber(number, dec);
      }
    },

    formatPriceWithSymbol(symbol, number, dec) {
      const safeSymbol = symbol || "";
      const value = this.formatPriceDisplay(number, dec);
      return safeSymbol ? `${safeSymbol} ${value}` : value;
    },

    //------------------------------ Format Display Date -------------------------\\
    formatDisplayDate(value) {
      const dateFormat = this.$store.getters.getDateFormat || Util.getDateFormat(this.$store);
      return Util.formatDisplayDate(value, dateFormat);
    },

    //------------------------------ Get Status Badge Class -------------------------\\
    getStatusBadgeClass(status) {
      const statusLower = (status || '').toLowerCase();
      const statusClasses = {
        'completed': 'invoice-status-badge invoice-status-completed',
        'paid': 'invoice-status-badge invoice-status-completed',
        'pending': 'invoice-status-badge invoice-status-pending',
        'unpaid': 'invoice-status-badge invoice-status-pending',
        'partial': 'invoice-status-badge invoice-status-partial',
      };
      return statusClasses[statusLower] || 'invoice-status-badge invoice-status-default';
    },

    //------------------------------ Get Payment Badge Class -------------------------\\
    getPaymentBadgeClass(paymentStatus) {
      const paymentLower = (paymentStatus || '').toLowerCase();
      const paymentClasses = {
        'paid': 'invoice-status-badge invoice-status-completed',
        'pending': 'invoice-status-badge invoice-status-pending',
        'unpaid': 'invoice-status-badge invoice-status-pending',
        'partial': 'invoice-status-badge invoice-status-partial',
      };
      return paymentClasses[paymentLower] || 'invoice-status-badge invoice-status-default';
    },

    //------------------------------ Print -------------------------\\
    print() {
      // Clone the element to ensure we have the outer wrapper ID
      const element = document.getElementById("invoice-POS");
      const divContents = element.outerHTML;
      
      const a = window.open("", "", "height=500, width=500");
      a.document.write('<html><head><title>Print Invoice</title>');
      a.document.write('<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">');
      a.document.write('<link rel="stylesheet" href="/css/pos_print.css">');
      a.document.write('</head>');
      a.document.write('<body class="' + this.currentReceiptPaperSizeClass + '" >');
      a.document.write(divContents);
      a.document.write("</body></html>");
      a.document.close();
      
      setTimeout(() => {
         a.print();
      }, 1000);
    },



    Send_Email() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .post("sales_send_email", {
          id: id,
        })
        .then(response => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast(
            "success",
            this.$t("SendEmail"),
            this.$t("Success")
          );
        })
        .catch(error => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast("danger", this.$t("SMTPIncorrect"), this.$t("Failed"));
        });
    },

    //---------SMS notification
     Sale_SMS() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .post("sales_send_sms", {
          id: id,
        })
        .then(response => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast(
            "success",
            this.$t("Send_SMS"),
            this.$t("Success")
          );
        })
        .catch(error => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast("danger", this.$t("sms_config_invalid"), this.$t("Failed"));
        });
    },

    //----------------------------------- Get Details Sale ------------------------------\\
    Get_Details() {
      let id = this.$route.params.id;
      axios
        .get(`sales/${id}`)
        .then(response => {
          this.sale = response.data.sale;
          this.details = response.data.details;
          this.company = response.data.company;
          this.pos_settings = response.data.pos_settings;
          this.isLoading = false;
        })
        .catch(response => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //------------------------------------------ DELETE Sale ------------------------------\\
    Delete_Sale() {
      let id = this.$route.params.id;
      this.$swal({
        title: this.$t("Delete_Title"),
        text: this.$t("Delete_Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete_cancelButtonText"),
        confirmButtonText: this.$t("Delete_confirmButtonText")
      }).then(result => {
        if (result.value) {
          axios
            .delete("sales/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete_Deleted"),
                this.$t("Deleted_in_successfully"),
                "success"
              );
              this.$router.push({ name: "index_sales" });
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete_Failed"),
                this.$t("Delete_Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    }
  }, //end Methods

  //----------------------------- Created function-------------------

  created: function() {
    this.Get_Details();
  }
};
</script>

<style scoped>
.main-content {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

.action-buttons-wrapper {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
  padding: 1rem 0;
}

.button-group {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 500;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  text-decoration: none;
  position: relative;
  overflow: hidden;
}

.action-btn i {
  font-size: 1rem;
  line-height: 1;
}

.action-btn span {
  white-space: nowrap;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Back Button */
.btn-back {
  background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
  color: #ffffff;
}

.btn-back:hover {
  background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
  color: #ffffff;
}

/* Edit Button */
.btn-edit {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: #ffffff;
}

.btn-edit:hover {
  background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
  color: #ffffff;
}

/* Delete Button */
.btn-delete {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  color: #ffffff;
}

.btn-delete:hover {
  background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
  color: #ffffff;
}

/* Email Button */
.btn-email {
  background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
  color: #ffffff;
}

.btn-email:hover {
  background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
  color: #ffffff;
}

/* SMS Button */
.btn-sms {
  background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
  color: #ffffff;
}

.btn-sms:hover {
  background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
  color: #ffffff;
}

/* PDF Button */
.btn-pdf {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  color: #ffffff;
}

.btn-pdf:hover {
  background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
  color: #ffffff;
}

/* Print Button */
.btn-print {
  background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
  color: #212529;
}

.btn-print:hover {
  background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
  color: #212529;
}

/* Products - Desktop/Default Styles */
.invoice-products-mobile {
  display: none;
}

.invoice-products-desktop {
  display: table;
}

/* Responsive Design - Tablet */
@media (max-width: 1024px) and (min-width: 769px) {
  .invoice-print {
    padding: 12px 15px;
    font-size: 8.5pt;
  }

  .invoice-header-table {
    margin-bottom: 10px;
  }

  .invoice-logo {
    max-height: 50px;
    max-width: 150px;
  }

  .invoice-main-title {
    font-size: 16pt;
  }

  /* Bill To / From - Stack vertically on tablet */
  .invoice-billto-table {
    display: block;
    margin-bottom: 15px;
  }

  .invoice-billto-table tr {
    display: block;
  }

  .invoice-billto-cell {
    display: block;
    width: 100% !important;
    margin-bottom: 12px;
  }

  .invoice-spacer-cell {
    display: none;
  }

  /* Show mobile cards on tablet, hide desktop table */
  .invoice-products-desktop {
    display: none;
  }

  .invoice-products-mobile {
    display: block;
    margin-bottom: 15px;
  }

  .invoice-products-mobile-header {
    font-size: 10pt;
    padding: 10px 14px;
  }

  .invoice-product-card {
    padding: 14px;
  }

  .invoice-product-card-name {
    font-size: 10pt;
  }

  .invoice-product-card-total {
    font-size: 11pt;
  }

  .invoice-product-card-row {
    font-size: 8pt;
  }

  .invoice-product-card-detail-label {
    font-size: 7.5pt;
  }

  .invoice-product-card-detail-value {
    font-size: 9pt;
  }

  /* Summary - Full width on tablet for better visibility */
  .invoice-summary-wrapper {
    display: block;
    margin-bottom: 15px;
  }

  .invoice-summary-wrapper tr {
    display: block;
  }

  .invoice-summary-spacer {
    display: none;
  }

  .invoice-summary-cell {
    display: block;
    width: 100% !important;
  }

  .invoice-summary-table {
    width: 100%;
  }

  .invoice-summary-label,
  .invoice-summary-value,
  .invoice-summary-discount-value {
    padding: 6px 12px;
    font-size: 8pt;
  }

  .invoice-summary-value,
  .invoice-summary-discount-value {
    text-align: right !important;
  }

  .invoice-summary-total-label,
  .invoice-summary-total-value {
    padding: 8px 12px;
    font-size: 10pt;
  }

  .invoice-summary-total-value {
    text-align: right !important;
  }

  .invoice-summary-paid-label,
  .invoice-summary-paid-value,
  .invoice-summary-due-label,
  .invoice-summary-due-value {
    padding: 6px 12px;
    font-size: 8.5pt;
  }

  .invoice-summary-paid-value,
  .invoice-summary-due-value {
    text-align: right !important;
  }
}

/* Responsive Design - Mobile and Tablet */
@media (max-width: 768px) {
  .print-card {
    padding: 0.5rem;
  }

  .print-card .card-body {
    padding: 0.75rem;
  }

  .action-buttons-wrapper {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
    padding: 0.75rem 0;
  }

  .button-group {
    flex-direction: row;
    flex-wrap: wrap;
    width: 100%;
    gap: 0.5rem;
  }

  .action-btn {
    flex: 1 1 auto;
    min-width: calc(50% - 0.25rem);
    justify-content: center;
    padding: 0.625rem 0.875rem;
    font-size: 0.8125rem;
  }

  .action-btn span {
    display: none;
  }

  .action-btn i {
    font-size: 1.1rem;
  }

  /* Invoice responsive styles */
  .invoice {
    padding: 10px;
    border-radius: 0;
    overflow-x: hidden;
    width: 100%;
    max-width: 100%;
  }

  .invoice-print {
    padding: 10px 12px;
    font-size: 8pt;
  }

  /* Header - Stack vertically on mobile */
  .invoice-header-table {
    display: block;
    margin-bottom: 15px;
  }

  .invoice-header-table tr {
    display: block;
  }

  .invoice-logo-cell {
    display: block;
    width: 100%;
    text-align: center;
    margin-bottom: 10px;
  }

  .invoice-logo {
    max-height: 50px;
    max-width: 150px;
  }

  .invoice-title-cell {
    display: block;
    width: 100%;
    text-align: center;
  }

  .invoice-main-title {
    font-size: 14pt;
    margin-bottom: 8px;
  }

  .invoice-ref-badge {
    font-size: 9pt;
    padding: 4px 10px;
    margin-bottom: 10px;
  }

  .invoice-meta-table {
    width: 100%;
    margin: 10px auto 0;
    max-width: 300px;
  }

  .invoice-meta-label,
  .invoice-meta-value {
    text-align: center;
    padding: 4px;
    font-size: 7.5pt;
  }

  /* Bill To / From - Stack vertically on mobile */
  .invoice-billto-table {
    display: block;
    margin-bottom: 15px;
  }

  .invoice-billto-table tr {
    display: block;
  }

  .invoice-billto-cell {
    display: block;
    width: 100% !important;
    margin-bottom: 12px;
  }

  .invoice-spacer-cell {
    display: none;
  }

  .invoice-box-header {
    font-size: 8.5pt;
    padding: 6px 10px;
  }

  .invoice-box-content {
    padding: 10px;
  }

  .invoice-box-name {
    font-size: 9.5pt;
  }

  .invoice-box-details {
    font-size: 7pt;
  }

  /* Products - Hide desktop table, show mobile cards */
  .invoice-products-desktop {
    display: none;
  }

  .invoice-products-mobile {
    display: block;
    margin-bottom: 15px;
  }

  .invoice-products-mobile-header {
    background: #1a56db;
    color: #ffffff;
    padding: 8px 12px;
    font-size: 9pt;
    font-weight: bold;
    text-transform: uppercase;
    border-radius: 4px 4px 0 0;
    margin-bottom: 0;
  }

  .invoice-product-card {
    border: 1px solid #e5e7eb;
    border-top: none;
    background: #ffffff;
    padding: 12px;
  }

  .invoice-product-card:nth-child(even) {
    background: #f9fafb;
  }

  .invoice-product-card:last-child {
    border-radius: 0 0 4px 4px;
  }

  .invoice-product-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5e7eb;
  }

  .invoice-product-card-name {
    font-size: 9pt;
    font-weight: 600;
    color: #1f2937;
    flex: 1;
    margin-right: 10px;
  }

  .invoice-product-card-total {
    font-size: 10pt;
    font-weight: bold;
    color: #1a56db;
    white-space: nowrap;
  }

  .invoice-product-card-body {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .invoice-product-card-row {
    display: flex;
    font-size: 7.5pt;
    color: #6b7280;
  }

  .invoice-product-card-label {
    font-weight: 600;
    color: #1f2937;
    margin-right: 8px;
    min-width: 50px;
  }

  .invoice-product-card-value {
    color: #6b7280;
  }

  .invoice-product-card-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 6px;
    padding-top: 8px;
    border-top: 1px dashed #e5e7eb;
  }

  .invoice-product-card-detail-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .invoice-product-card-detail-label {
    font-size: 7pt;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
  }

  .invoice-product-card-detail-value {
    font-size: 8pt;
    font-weight: 600;
    color: #1f2937;
  }

  .invoice-product-card-detail-value.discount {
    color: #ef4444;
  }

  /* Summary - Full width on mobile */
  .invoice-summary-wrapper {
    margin-bottom: 15px;
  }

  .invoice-summary-spacer {
    display: none;
  }

  .invoice-summary-cell {
    display: block;
    width: 100% !important;
  }

  .invoice-summary-table {
    width: 100%;
  }

  .invoice-summary-label,
  .invoice-summary-value,
  .invoice-summary-discount-value {
    padding: 6px 10px;
    font-size: 7.5pt;
  }

  .invoice-summary-value,
  .invoice-summary-discount-value {
    text-align: right !important;
  }

  .invoice-summary-total-label,
  .invoice-summary-total-value {
    padding: 8px 10px;
    font-size: 9pt;
  }

  .invoice-summary-total-value {
    text-align: right !important;
  }

  .invoice-summary-paid-label,
  .invoice-summary-paid-value {
    padding: 6px 10px;
    font-size: 8pt;
  }

  .invoice-summary-paid-value {
    text-align: right !important;
  }

  .invoice-summary-due-label,
  .invoice-summary-due-value {
    padding: 6px 10px;
    font-size: 8pt;
  }

  .invoice-summary-due-value {
    text-align: right !important;
  }

  /* Footer */
  .invoice-footer {
    margin-top: 15px;
    padding-top: 12px;
  }

  .invoice-footer-text p {
    font-size: 7pt;
  }

  .invoice-footer-thanks p {
    font-size: 9pt;
  }
}

/* Responsive Design - Small Mobile */
@media (max-width: 576px) {
  .action-btn {
    padding: 0.75rem 0.5rem;
    font-size: 0.75rem;
    min-width: calc(50% - 0.25rem);
  }

  .invoice-print {
    padding: 8px 10px;
    font-size: 7.5pt;
  }

  .invoice-main-title {
    font-size: 12pt;
  }

  .invoice-ref-badge {
    font-size: 8pt;
    padding: 3px 8px;
  }

  .invoice-meta-table {
    max-width: 100%;
  }

  .invoice-meta-label,
  .invoice-meta-value {
    font-size: 7pt;
  }

  .invoice-box-header {
    font-size: 8pt;
    padding: 5px 8px;
  }

  .invoice-box-content {
    padding: 8px;
  }

  .invoice-box-name {
    font-size: 9pt;
  }

  .invoice-box-details {
    font-size: 6.5pt;
  }

  /* Mobile cards on small screens */
  .invoice-products-mobile-header {
    font-size: 8pt;
    padding: 6px 10px;
  }

  .invoice-product-card {
    padding: 10px;
  }

  .invoice-product-card-header {
    margin-bottom: 8px;
    padding-bottom: 6px;
  }

  .invoice-product-card-name {
    font-size: 8.5pt;
  }

  .invoice-product-card-total {
    font-size: 9pt;
  }

  .invoice-product-card-row {
    font-size: 7pt;
  }

  .invoice-product-card-label {
    min-width: 45px;
    font-size: 6.5pt;
  }

  .invoice-product-card-details {
    gap: 6px;
    margin-top: 4px;
    padding-top: 6px;
  }

  .invoice-product-card-detail-label {
    font-size: 6.5pt;
  }

  .invoice-product-card-detail-value {
    font-size: 7.5pt;
  }

  .invoice-summary-label,
  .invoice-summary-value,
  .invoice-summary-discount-value {
    padding: 5px 8px;
    font-size: 7pt;
  }

  .invoice-summary-value,
  .invoice-summary-discount-value {
    text-align: right !important;
  }

  .invoice-summary-total-label,
  .invoice-summary-total-value {
    padding: 7px 8px;
    font-size: 8.5pt;
  }

  .invoice-summary-total-value {
    text-align: right !important;
  }

  .invoice-summary-paid-label,
  .invoice-summary-paid-value,
  .invoice-summary-due-label,
  .invoice-summary-due-value {
    padding: 5px 8px;
    font-size: 7.5pt;
  }

  .invoice-summary-paid-value,
  .invoice-summary-due-value {
    text-align: right !important;
  }
}

/* Print styles for screen preview (also used by print) */

/* Screen-only styles - hide certain elements in print */
@media screen {
  .no-print {
    display: block;
  }
  
  .invoice {
    background: white;
    padding: 20px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }
}

@media print {
  .no-print {
    display: none !important;
  }
}

.invoice-print {
  font-family: 'DejaVu Sans', 'Arial', sans-serif;
  font-size: 9pt;
  color: #1f2937;
  line-height: 1.4;
  padding: 15px 20px;
  max-width: 100%;
}

/* Header Section */
.invoice-header-table {
  width: 100%;
  margin-bottom: 12px;
  border-collapse: collapse;
}

.invoice-logo-cell {
  width: 30%;
  vertical-align: top;
}

.invoice-logo {
  max-height: 60px;
  max-width: 180px;
}

.invoice-title-cell {
  width: 70%;
  vertical-align: top;
  text-align: right;
}

.invoice-main-title {
  font-size: 18pt;
  font-weight: bold;
  color: #1a56db;
  margin-bottom: 6px;
  letter-spacing: 0.5px;
}

.invoice-ref-badge {
  display: inline-block;
  background: #f3f4f6;
  padding: 5px 12px;
  border-radius: 4px;
  font-size: 10pt;
  font-weight: bold;
  color: #4b5563;
  margin-bottom: 8px;
}

.invoice-meta-table {
  width: 100%;
  font-size: 8pt;
  margin-top: 6px;
  border-collapse: collapse;
}

.invoice-meta-label {
  text-align: right;
  color: #6b7280;
  font-weight: 600;
  padding: 3px;
}

.invoice-meta-value {
  text-align: right;
  color: #1f2937;
  font-weight: 500;
  padding: 3px;
}

.invoice-status-badge {
  padding: 3px 8px;
  border-radius: 3px;
  font-size: 7pt;
  font-weight: bold;
  text-transform: uppercase;
  display: inline-block;
}

.invoice-status-completed {
  background: #d1fae5;
  color: #065f46;
}

.invoice-status-pending {
  background: #fef3c7;
  color: #92400e;
}

.invoice-status-partial {
  background: #dbeafe;
  color: #1e40af;
}

.invoice-status-default {
  background: #e5e7eb;
  color: #374151;
}

/* Divider */
.invoice-divider {
  height: 2px;
  background: #1a56db;
  margin: 8px 0 10px 0;
}

/* Bill To / From Section */
.invoice-billto-table {
  width: 100%;
  margin-bottom: 12px;
  border-collapse: collapse;
}

.invoice-billto-cell {
  width: 48%;
  vertical-align: top;
}

.invoice-spacer-cell {
  width: 4%;
}

.invoice-box {
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.invoice-box-header {
  background: #1a56db;
  padding: 5px 10px;
  border-bottom: 1px solid #3b82f6;
  color: #ffffff;
  font-size: 9pt;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.invoice-box-content {
  padding: 8px 10px;
  background: #f9fafb;
}

.invoice-box-name {
  font-size: 10pt;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 4px;
}

.invoice-box-details {
  font-size: 7.5pt;
  color: #6b7280;
  line-height: 1.5;
}

.invoice-box-details div {
  margin-bottom: 2px;
}

.invoice-box-details strong {
  color: #1f2937;
}

/* Products Table */
.invoice-products-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 10px;
  border: 1px solid #e5e7eb;
}

.invoice-products-header {
  background: #1a56db;
}

.invoice-products-th-left {
  padding: 6px 5px;
  text-align: left;
  font-size: 8pt;
  font-weight: bold;
  color: #ffffff;
  text-transform: uppercase;
  border-right: 1px solid rgba(255,255,255,0.2);
}

.invoice-products-th-right {
  padding: 6px 5px;
  text-align: right;
  font-size: 8pt;
  font-weight: bold;
  color: #ffffff;
  text-transform: uppercase;
  border-right: 1px solid rgba(255,255,255,0.2);
}

.invoice-products-table thead tr th:last-child {
  border-right: none;
}

.invoice-product-name-cell {
  padding: 5px;
  vertical-align: top;
}

.invoice-product-name {
  font-weight: 600;
  font-size: 8.5pt;
  color: #1f2937;
  margin-bottom: 1px;
}

.invoice-product-code {
  font-size: 7pt;
  color: #6b7280;
}

.invoice-product-imei {
  font-size: 7pt;
  color: #3b82f6;
  margin-top: 1px;
}

.invoice-product-price-cell {
  padding: 5px;
  text-align: right;
  font-size: 8.5pt;
  color: #1f2937;
}

.invoice-product-discount-cell {
  padding: 5px;
  text-align: right;
  font-size: 8.5pt;
  color: #ef4444;
}

.invoice-product-total-cell {
  padding: 5px;
  text-align: right;
  font-size: 9pt;
  font-weight: bold;
  color: #1a56db;
}

.invoice-products-row-even {
  background: #f9fafb;
}

.invoice-products-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
}

/* Summary Section */
.invoice-summary-wrapper {
  width: 100%;
  margin-bottom: 10px;
  border-collapse: collapse;
}

.invoice-summary-spacer {
  width: 58%;
}

.invoice-summary-cell {
  width: 42%;
  vertical-align: top;
}

.invoice-summary-table {
  width: 100%;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  border-collapse: collapse;
}

.invoice-summary-row {
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
}

.invoice-summary-row-alt {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.invoice-summary-label {
  padding: 5px 10px;
  font-size: 8pt;
  font-weight: 600;
  color: #6b7280;
}

.invoice-summary-value {
  padding: 5px 10px;
  text-align: right !important;
  font-size: 8.5pt;
  font-weight: 600;
  color: #1f2937;
}

.invoice-summary-discount-value {
  padding: 5px 10px;
  text-align: right !important;
  font-size: 8.5pt;
  font-weight: 600;
  color: #ef4444;
}

.invoice-summary-total-row {
  background: #1a56db;
}

.invoice-summary-total-label {
  padding: 8px 10px;
  font-size: 10pt;
  font-weight: bold;
  color: #ffffff;
}

.invoice-summary-total-value {
  padding: 8px 10px;
  text-align: right !important;
  font-size: 11pt;
  font-weight: bold;
  color: #ffffff;
}

.invoice-summary-paid-row {
  background: #d1fae5;
  border-bottom: 1px solid #a7f3d0;
}

.invoice-summary-paid-label {
  padding: 6px 10px;
  font-size: 8.5pt;
  font-weight: bold;
  color: #065f46;
}

.invoice-summary-paid-value {
  padding: 6px 10px;
  text-align: right !important;
  font-size: 9pt;
  font-weight: bold;
  color: #065f46;
}

.invoice-summary-due-row {
  background: #fef3c7;
}

.invoice-summary-due-label {
  padding: 6px 10px;
  font-size: 8.5pt;
  font-weight: bold;
  color: #92400e;
}

.invoice-summary-due-value {
  padding: 6px 10px;
  text-align: right !important;
  font-size: 9pt;
  font-weight: bold;
  color: #92400e;
}

/* Footer */
.invoice-footer {
  margin-top: 15px;
  padding-top: 10px;
  border-top: 2px solid #e5e7eb;
}

.invoice-footer-text {
  padding: 8px 10px;
  background: #f9fafb;
  border-left: 3px solid #1a56db;
  border-radius: 3px;
  margin-bottom: 10px;
}

.invoice-footer-text p {
  font-size: 7.5pt;
  color: #6b7280;
  line-height: 1.5;
  margin: 0;
}

.invoice-footer-thanks {
  text-align: center;
  padding: 8px 0;
}

.invoice-footer-thanks p {
  font-size: 10pt;
  font-weight: bold;
  color: #1a56db;
  margin: 0;
  letter-spacing: 0.3px;
}
</style>

<!-- Non-scoped styles for htmlToPaper - these will be copied to print window -->
<style>
/* Base invoice styles for print window */
#print_Invoice {
  font-family: 'DejaVu Sans', 'Arial', sans-serif;
}

.invoice-print {
  font-family: 'DejaVu Sans', 'Arial', sans-serif;
  font-size: 9pt;
  color: #1f2937;
  line-height: 1.4;
  padding: 15px 20px;
  max-width: 100%;
}

/* Header Section */
.invoice-header-table {
  width: 100%;
  margin-bottom: 12px;
  border-collapse: collapse;
}

.invoice-logo-cell {
  width: 30%;
  vertical-align: top;
}

.invoice-logo {
  max-height: 60px;
  max-width: 180px;
}

.invoice-title-cell {
  width: 70%;
  vertical-align: top;
  text-align: right;
}

.invoice-main-title {
  font-size: 18pt;
  font-weight: bold;
  color: #1a56db;
  margin-bottom: 6px;
  letter-spacing: 0.5px;
}

.invoice-ref-badge {
  display: inline-block;
  background: #f3f4f6;
  padding: 5px 12px;
  border-radius: 4px;
  font-size: 10pt;
  font-weight: bold;
  color: #4b5563;
  margin-bottom: 8px;
}

.invoice-meta-table {
  width: 100%;
  font-size: 8pt;
  margin-top: 6px;
  border-collapse: collapse;
}

.invoice-meta-label {
  text-align: right;
  color: #6b7280;
  font-weight: 600;
  padding: 3px;
}

.invoice-meta-value {
  text-align: right;
  color: #1f2937;
  font-weight: 500;
  padding: 3px;
}

.invoice-status-badge {
  padding: 3px 8px;
  border-radius: 3px;
  font-size: 7pt;
  font-weight: bold;
  text-transform: uppercase;
  display: inline-block;
}

.invoice-status-completed {
  background: #d1fae5;
  color: #065f46;
}

.invoice-status-pending {
  background: #fef3c7;
  color: #92400e;
}

.invoice-status-partial {
  background: #dbeafe;
  color: #1e40af;
}

.invoice-status-default {
  background: #e5e7eb;
  color: #374151;
}

.invoice-divider {
  height: 2px;
  background: #1a56db;
  margin: 8px 0 10px 0;
}

/* Bill To / From Section */
.invoice-billto-table {
  width: 100%;
  margin-bottom: 12px;
  border-collapse: collapse;
}

.invoice-billto-cell {
  width: 48%;
  vertical-align: top;
}

.invoice-spacer-cell {
  width: 4%;
}

.invoice-box {
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.invoice-box-header {
  background: #1a56db;
  padding: 5px 10px;
  border-bottom: 1px solid #3b82f6;
  color: #ffffff;
  font-size: 9pt;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.invoice-box-content {
  padding: 8px 10px;
  background: #f9fafb;
}

.invoice-box-name {
  font-size: 10pt;
  font-weight: bold;
  color: #1f2937;
  margin-bottom: 4px;
}

.invoice-box-details {
  font-size: 7.5pt;
  color: #6b7280;
  line-height: 1.5;
}

.invoice-box-details div {
  margin-bottom: 2px;
}

.invoice-box-details strong {
  color: #1f2937;
}

/* Products Table */
.invoice-products-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 10px;
  border: 1px solid #e5e7eb;
}

.invoice-products-header {
  background: #1a56db;
}

.invoice-products-th-left {
  padding: 6px 5px;
  text-align: left;
  font-size: 8pt;
  font-weight: bold;
  color: #ffffff;
  text-transform: uppercase;
  border-right: 1px solid rgba(255,255,255,0.2);
}

.invoice-products-th-right {
  padding: 6px 5px;
  text-align: right;
  font-size: 8pt;
  font-weight: bold;
  color: #ffffff;
  text-transform: uppercase;
  border-right: 1px solid rgba(255,255,255,0.2);
}

.invoice-products-table thead tr th:last-child {
  border-right: none;
}

.invoice-product-name-cell {
  padding: 5px;
  vertical-align: top;
}

.invoice-product-name {
  font-weight: 600;
  font-size: 8.5pt;
  color: #1f2937;
  margin-bottom: 1px;
}

.invoice-product-code {
  font-size: 7pt;
  color: #6b7280;
}

.invoice-product-imei {
  font-size: 7pt;
  color: #3b82f6;
  margin-top: 1px;
}

.invoice-product-price-cell {
  padding: 5px;
  text-align: right;
  font-size: 8.5pt;
  color: #1f2937;
}

.invoice-product-discount-cell {
  padding: 5px;
  text-align: right;
  font-size: 8.5pt;
  color: #ef4444;
}

.invoice-product-total-cell {
  padding: 5px;
  text-align: right;
  font-size: 9pt;
  font-weight: bold;
  color: #1a56db;
}

.invoice-products-row-even {
  background: #f9fafb;
}

.invoice-products-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
}

/* Summary Section */
.invoice-summary-wrapper {
  width: 100%;
  margin-bottom: 10px;
  border-collapse: collapse;
}

.invoice-summary-spacer {
  width: 58%;
}

.invoice-summary-cell {
  width: 42%;
  vertical-align: top;
}

.invoice-summary-table {
  width: 100%;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  border-collapse: collapse;
}

.invoice-summary-row {
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
}

.invoice-summary-row-alt {
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.invoice-summary-label {
  padding: 5px 10px;
  font-size: 8pt;
  font-weight: 600;
  color: #6b7280;
}

.invoice-summary-value {
  padding: 5px 10px;
  text-align: right !important;
  font-size: 8.5pt;
  font-weight: 600;
  color: #1f2937;
}

.invoice-summary-discount-value {
  padding: 5px 10px;
  text-align: right !important;
  font-size: 8.5pt;
  font-weight: 600;
  color: #ef4444;
}

.invoice-summary-total-row {
  background: #1a56db;
}

.invoice-summary-total-label {
  padding: 8px 10px;
  font-size: 10pt;
  font-weight: bold;
  color: #ffffff;
}

.invoice-summary-total-value {
  padding: 8px 10px;
  text-align: right !important;
  font-size: 11pt;
  font-weight: bold;
  color: #ffffff;
}

.invoice-summary-paid-row {
  background: #d1fae5;
  border-bottom: 1px solid #a7f3d0;
}

.invoice-summary-paid-label {
  padding: 6px 10px;
  font-size: 8.5pt;
  font-weight: bold;
  color: #065f46;
}

.invoice-summary-paid-value {
  padding: 6px 10px;
  text-align: right !important;
  font-size: 9pt;
  font-weight: bold;
  color: #065f46;
}

.invoice-summary-due-row {
  background: #fef3c7;
}

.invoice-summary-due-label {
  padding: 6px 10px;
  font-size: 8.5pt;
  font-weight: bold;
  color: #92400e;
}

.invoice-summary-due-value {
  padding: 6px 10px;
  text-align: right !important;
  font-size: 9pt;
  font-weight: bold;
  color: #92400e;
}

/* Footer */
.invoice-footer {
  margin-top: 15px;
  padding-top: 10px;
  border-top: 2px solid #e5e7eb;
}

.invoice-footer-text {
  padding: 8px 10px;
  background: #f9fafb;
  border-left: 3px solid #1a56db;
  border-radius: 3px;
  margin-bottom: 10px;
}

.invoice-footer-text p {
  font-size: 7.5pt;
  color: #6b7280;
  line-height: 1.5;
  margin: 0;
}

.invoice-footer-thanks {
  text-align: center;
  padding: 8px 0;
}

.invoice-footer-thanks p {
  font-size: 10pt;
  font-weight: bold;
  color: #1a56db;
  margin: 0;
  letter-spacing: 0.3px;
}

@media print {
  @page {
    size: A4;
    margin: 10mm 15mm;
  }
  
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }
  
  body {
    font-family: 'DejaVu Sans', 'Arial', sans-serif;
    font-size: 9pt;
    color: #1f2937;
    line-height: 1.4;
    padding: 15px 20px;
    max-width: 100%;
    background: white;
  }
  
  /* Hide everything except the invoice */
  body > *:not(#print_Invoice) {
    display: none !important;
  }
  
  #print_Invoice {
    position: relative;
    width: 100%;
    margin: 0;
    padding: 0;
    background: white;
    display: block !important;
  }
  
  .invoice-print {
    padding: 15px 20px;
    max-width: 100%;
    font-size: 9pt;
    background: white;
    color: #1f2937;
  }
  
  /* Hide non-printable elements */
  .no-print {
    display: none !important;
  }
  
  /* Ensure colors print */
  .invoice-logo,
  .invoice-status-badge,
  .invoice-box-header,
  .invoice-products-header,
  .invoice-summary-total-row,
  .invoice-summary-paid-row,
  .invoice-summary-due-row {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  /* Prevent page breaks */
  .invoice-header-table,
  .invoice-billto-table,
  .invoice-products-table,
  .invoice-summary-wrapper,
  .invoice-summary-table {
    page-break-inside: avoid;
  }
  
  .invoice-box,
  .invoice-summary-table tr {
    page-break-inside: avoid;
  }
}
</style>