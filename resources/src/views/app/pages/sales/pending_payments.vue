<template>
  <div class="main-content">
    <breadcumb :page="$t('PendingPayments')" :folder="$t('Sales')" />
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="payments"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
          placeholder: $t('Search_this_table'),
          enabled: true,
        }"
        :pagination-options="{
          enabled: true,
          mode: 'records',
          nextLabel: 'next',
          prevLabel: 'prev',
        }"
        styleClass="tableOne table-hover vgt-table"
      >
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button variant="outline-info ripple m-1" size="sm" v-b-toggle.sidebar-right>
            <i class="i-Filter-2"></i>
            {{ $t("Filter") }}
          </b-button>
          <b-button @click="Payments_PDF()" size="sm" variant="outline-success ripple m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <vue-excel-xlsx
              class="btn btn-sm btn-outline-danger ripple m-1"
              :data="payments"
              :columns="columns"
              :file-name="'pending_payments'"
              :file-type="'xlsx'"
              :sheet-name="'pending_payments'"
              >
              <i class="i-File-Excel"></i> EXCEL
          </vue-excel-xlsx>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
             <a title="Approve" class="cursor-pointer text-success mr-3" v-if="props.row.status == 'pending'" @click="Approve_Payment(props.row.id)">
                <i class="nav-icon i-Yes font-weight-bold"></i> Approve
              </a>
              <a title="Reject" class="cursor-pointer text-danger" v-if="props.row.status == 'pending'" @click="Reject_Payment(props.row.id)">
                <i class="nav-icon i-Close-Window font-weight-bold"></i> Reject
              </a>
          </span>
          <div v-else-if="props.column.field == 'status'">
            <span
              v-if="props.row.status == 'approved'"
              class="badge badge-outline-success"
            >{{$t('Approved')}}</span>
            <span
              v-else-if="props.row.status == 'pending'"
              class="badge badge-outline-info"
            >{{$t('Pending')}}</span>
            <span v-else class="badge badge-outline-danger">{{$t('Rejected')}}</span>
          </div>
          <span v-else-if="props.column.field == 'date'">
            {{ formatDisplayDate(props.row.date) }}
          </span>
          <span v-else-if="props.column.field == 'montant'">
            {{ formatPriceWithSymbol(currentUser.currency, props.row.montant, 2) }}
          </span>
           <div v-else-if="props.column.field == 'Ref_Sale'">
              <router-link
                :to="'/app/sales/detail/'+props.row.sale_id"
              >
                <span class="ul-btn__text ml-1">{{props.row.Ref_Sale}}</span>
              </router-link>
            </div>
        </template>
      </vue-good-table>
    </div>

    <!-- Sidebar Filter -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- date  -->
          <b-col md="12">
            <b-form-group :label="$t('date')">
              <b-form-input type="date" v-model="Filter_date"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Reference -->
          <b-col md="12">
            <b-form-group :label="$t('Reference')">
              <b-form-input label="Reference" :placeholder="$t('Reference')" v-model="Filter_Ref"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Customer  -->
          <b-col md="12">
            <b-form-group :label="$t('Customer')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Customer')"
                v-model="Filter_Client"
                :options="clients.map(clients => ({label: clients.name, value: clients.id}))"
              />
            </b-form-group>
          </b-col>

          <!-- Status  -->
          <b-col md="12">
            <b-form-group :label="$t('Status')">
              <v-select
                v-model="Filter_status"
                :reduce="label => label.value"
                :placeholder="$t('Choose_Status')"
                :options="[
                    {label: 'Pending', value: 'pending'},
                    {label: 'Approved', value: 'approved'},
                    {label: 'Rejected', value: 'rejected'}
                ]"
              ></v-select>
            </b-form-group>
          </b-col>

          <b-col md="12">
            <b-button @click="Get_Payments(1)" variant="primary" block size="sm" class="mb-2 btn-pill">
              <i class="i-Filter-2"></i>
              {{ $t("Filter") }}
            </b-button>
          </b-col>
          <b-col md="12">
            <b-button @click="Reset_Filter()" variant="danger" block size="sm" class="btn-pill">
               <i class="i-Power-2"></i>
              {{ $t("Reset") }}
            </b-button>
          </b-col>
        </b-row>
      </div>
    </b-sidebar>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";
import jsPDF from "jspdf";
import "jspdf-autotable";
import moment from "moment";

export default {
  metaInfo: {
    title: "Pending Payments"
  },
  data() {
    return {
      isLoading: true,
      serverParams: {
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      limit: "10",
      search: "",
      totalRows: "",
      Filter_date: "",
      Filter_Ref: "",
      Filter_Client: "",
      Filter_status: "pending", // Default to pending
      payments: [],
      clients: [], 
      sales: [],
      payment_methods: []
    };
  },

  computed: {
    ...mapGetters(["currentUserPermissions", "currentUser"]),
    columns() {
      return [
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Sale"),
          field: "Ref_Sale",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Customer"),
          field: "client_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Amount"),
          field: "montant",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("ModePaiement"),
          field: "payment_method",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Status"),
          field: "status",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Action"),
          field: "actions",
          html: true,
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    }
  },

  methods: {
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Payments(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Payments(1);
      }
    },

    //---- Event Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Payments(this.serverParams.page);
    },

    //---- Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Payments(this.serverParams.page);
    },

    //---- Reset Filter
    Reset_Filter() {
      this.search = "";
      this.Filter_date = "";
      this.Filter_Ref = "";
      this.Filter_Client = "";
      this.Filter_status = "pending";
      this.Get_Payments(this.serverParams.page);
    },

    //----------------------------------- Get All Payments ------------------------------\\
    Get_Payments(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "payment/sales?page=" +
            page +
            "&Ref=" +
            this.Filter_Ref +
            "&client_id=" +
            this.Filter_Client +
            "&status=" +
            this.Filter_status +
            "&date=" +
            this.Filter_date +
            "&SortField=" +
            this.serverParams.sort.field +
            "&SortType=" +
            this.serverParams.sort.type +
            "&search=" +
            this.search +
            "&limit=" +
            this.limit
        )
        .then(response => {
          this.payments = response.data.payments;
          this.clients = response.data.clients;
          this.totalRows = response.data.totalRows;

          // Complete the animation of theprogress bar.
          NProgress.done();
          this.isLoading = false;
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //----------------------------------- Approve Payment ------------------------------\\
    Approve_Payment(id) {
       this.$swal({
        title: this.$t("Are_you_sure"),
        text: this.$t("You_wont_be_able_to_revert_this"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: this.$t("Yes_Approve_it"),
        cancelButtonText: this.$t("Cancel")
      }).then(result => {
        if (result.value) {
          axios
            .post("payment/sales/approve/" + id)
            .then(response => {
              if (response.data.success) {
                this.makeToast(
                  "success",
                  this.$t("Approved"),
                  this.$t("Payment_Approved_Successfully")
                );
                this.Get_Payments(this.serverParams.page);
              } else {
                 this.makeToast(
                  "danger",
                   this.$t("Failed"),
                  response.data.message
                );
              }
            })
            .catch(error => {
              this.makeToast("danger", this.$t("Failed"), this.$t("Unknown_Error"));
            });
        }
      });
    },

     //----------------------------------- Reject Payment ------------------------------\\
    Reject_Payment(id) {
       this.$swal({
        title: this.$t("Are_you_sure"),
        text: this.$t("You_wont_be_able_to_revert_this"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: this.$t("Yes_Reject_it"),
        cancelButtonText: this.$t("Cancel")
      }).then(result => {
        if (result.value) {
          axios
            .post("payment/sales/reject/" + id)
            .then(response => {
               if (response.data.success) {
                  this.makeToast(
                    "success",
                    this.$t("Rejected"),
                    this.$t("Payment_Rejected_Successfully")
                  );
                  this.Get_Payments(this.serverParams.page);
               } else {
                  this.makeToast(
                  "danger",
                   this.$t("Failed"),
                  response.data.message
                );
               }
            })
            .catch(error => {
              this.makeToast("danger", this.$t("Failed"), this.$t("Unknown_Error"));
            });
        }
      });
    },

     //----------------------------------- Payments PDF ------------------------------\\
    Payments_PDF() {
      var self = this;
      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "Date", dataKey: "date" },
        { title: "Ref", dataKey: "Ref" },
        { title: "Sale", dataKey: "Ref_Sale" },
        { title: "Customer", dataKey: "client_name" },
        { title: "Amount", dataKey: "montant" },
        { title: "Status", dataKey: "status" }
      ];
      pdf.autoTable(columns, self.payments);
      pdf.text("Pending Payments List", 40, 25);
      pdf.save("Pending_Payments.pdf");
    },
    
    //-------------------------------- Formating Date ---------------------------------\\
    formatDisplayDate(date) {
        if (!date) return "";
        return moment(date).format("YYYY-MM-DD");
    },
    formatPriceWithSymbol(currency, price, decimals) {
        return currency + " " + parseFloat(price).toFixed(decimals);
    },
    makeToast(variant, title, msg) {
        this.$bvToast.toast(msg, {
            title: title,
            variant: variant,
            solid: true
        });
    }

  },

  created() {
    this.Get_Payments(1);
  }
};
</script>
