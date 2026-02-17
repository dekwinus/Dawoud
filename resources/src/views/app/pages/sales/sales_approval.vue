<template>
  <div class="main-content">
    <breadcumb :page="$t('SalesApproval')" :folder="$t('Sales')" />
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="sales"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
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

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a
              title="View Details"
              class="cursor-pointer text-info mr-2"
              @click="Show_Sale_Details(props.row)"
            >
              <i class="nav-icon i-Eye font-weight-bold"></i>
            </a>
            <a
              title="Approve"
              class="cursor-pointer text-success mr-2"
              @click="Approve_Sale(props.row.id)"
            >
              <i class="nav-icon i-Yes font-weight-bold"></i> {{ $t('Approve') }}
            </a>
            <a
              title="Reject"
              class="cursor-pointer text-danger"
              @click="Reject_Sale(props.row.id)"
            >
              <i class="nav-icon i-Close-Window font-weight-bold"></i> {{ $t('Reject') }}
            </a>
          </span>
          <div v-else-if="props.column.field == 'approval_status'">
            <span
              v-if="props.row.approval_status == 'approved'"
              class="badge badge-outline-success"
            >{{ $t('Approved') }}</span>
            <span
              v-else-if="props.row.approval_status == 'pending'"
              class="badge badge-outline-warning"
            >{{ $t('Pending') }}</span>
            <span v-else class="badge badge-outline-danger">{{ $t('Rejected') }}</span>
          </div>
          <span v-else-if="props.column.field == 'GrandTotal'">
            {{ formatPriceWithSymbol(currentUser.currency, props.row.GrandTotal, 2) }}
          </span>
        </template>
      </vue-good-table>
    </div>

    <!-- Sale Details Modal -->
    <b-modal
      id="sale-detail-modal"
      :title="$t('SaleDetail')"
      size="lg"
      ok-only
      :ok-title="$t('Close')"
    >
      <div v-if="selectedSale">
        <b-row class="mb-3">
          <b-col md="6">
            <strong>{{ $t('Reference') }}:</strong> {{ selectedSale.Ref }}
          </b-col>
          <b-col md="6">
            <strong>{{ $t('date') }}:</strong> {{ selectedSale.date }}
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col md="6">
            <strong>{{ $t('Customer') }}:</strong> {{ selectedSale.client_name }}
          </b-col>
          <b-col md="6">
            <strong>{{ $t('warehouse') }}:</strong> {{ selectedSale.warehouse_name }}
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col md="6">
            <strong>{{ $t('Created_by') }}:</strong> {{ selectedSale.created_by }}
          </b-col>
          <b-col md="6">
            <strong>{{ $t('GrandTotal') }}:</strong>
            {{ formatPriceWithSymbol(currentUser.currency, selectedSale.GrandTotal, 2) }}
          </b-col>
        </b-row>

        <h6 class="mt-4 mb-2">{{ $t('ProductList') }}</h6>
        <b-table-simple bordered small>
          <b-thead>
            <b-tr>
              <b-th>{{ $t('ProductName') }}</b-th>
              <b-th class="text-center">{{ $t('Qty') }}</b-th>
              <b-th class="text-right">{{ $t('UnitPrice') }}</b-th>
              <b-th class="text-right">{{ $t('Total') }}</b-th>
            </b-tr>
          </b-thead>
          <b-tbody>
            <b-tr v-for="(item, idx) in selectedSaleDetails" :key="idx">
              <b-td>{{ item.product_name }}</b-td>
              <b-td class="text-center">{{ item.quantity }}</b-td>
              <b-td class="text-right">{{ formatPriceWithSymbol(currentUser.currency, item.price, 2) }}</b-td>
              <b-td class="text-right">{{ formatPriceWithSymbol(currentUser.currency, item.total, 2) }}</b-td>
            </b-tr>
          </b-tbody>
        </b-table-simple>

        <div v-if="selectedSalePayments && selectedSalePayments.length > 0" class="mt-3">
          <h6>{{ $t('Payments') }}</h6>
          <b-table-simple bordered small>
            <b-thead>
              <b-tr>
                <b-th>{{ $t('Reference') }}</b-th>
                <b-th>{{ $t('ModePaiement') }}</b-th>
                <b-th class="text-right">{{ $t('Amount') }}</b-th>
                <b-th>{{ $t('Status') }}</b-th>
              </b-tr>
            </b-thead>
            <b-tbody>
              <b-tr v-for="(p, idx) in selectedSalePayments" :key="idx">
                <b-td>{{ p.Ref }}</b-td>
                <b-td>{{ p.payment_method }}</b-td>
                <b-td class="text-right">{{ formatPriceWithSymbol(currentUser.currency, p.montant, 2) }}</b-td>
                <b-td>
                  <span v-if="p.status == 'approved'" class="badge badge-outline-success">{{ $t('Approved') }}</span>
                  <span v-else-if="p.status == 'pending'" class="badge badge-outline-warning">{{ $t('Pending') }}</span>
                  <span v-else class="badge badge-outline-danger">{{ $t('Rejected') }}</span>
                </b-td>
              </b-tr>
            </b-tbody>
          </b-table-simple>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
import { mapGetters } from "vuex";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Sales Approval"
  },
  data() {
    return {
      isLoading: true,
      serverParams: {
        page: 1,
        perPage: 10
      },
      limit: "10",
      search: "",
      totalRows: 0,
      sales: [],
      selectedSale: null,
      selectedSaleDetails: [],
      selectedSalePayments: []
    };
  },

  computed: {
    ...mapGetters(["currentUser"]),
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
          label: this.$t("Customer"),
          field: "client_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("warehouse"),
          field: "warehouse_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("GrandTotal"),
          field: "GrandTotal",
          tdClass: "text-right",
          thClass: "text-right"
        },
        {
          label: this.$t("Created_by"),
          field: "created_by",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Status"),
          field: "approval_status",
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

    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Sales(currentPage);
      }
    },

    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Sales(1);
      }
    },

    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Sales(1);
    },

    //----------------------------------- Get All Pending Sales ------------------------------\\
    Get_Sales(page) {
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "sales-approval?page=" + page +
          "&search=" + this.search +
          "&limit=" + this.limit
        )
        .then(response => {
          this.sales = response.data.sales;
          this.totalRows = response.data.totalRows;
          NProgress.done();
          this.isLoading = false;
        })
        .catch(error => {
          NProgress.done();
          this.isLoading = false;
          if (error.response && error.response.status === 403) {
            this.makeToast("danger", this.$t("Unauthorized"), this.$t("You_do_not_have_permission"));
          }
        });
    },

    //----------------------------------- Show Sale Details Modal -------------------------\\
    Show_Sale_Details(row) {
      NProgress.start();
      axios
        .get("sales-approval/" + row.id)
        .then(response => {
          this.selectedSale = response.data.sale;
          this.selectedSaleDetails = response.data.details;
          this.selectedSalePayments = response.data.payments;
          this.$bvModal.show("sale-detail-modal");
          NProgress.done();
        })
        .catch(error => {
          NProgress.done();
          this.makeToast("danger", this.$t("Failed"), this.$t("Unknown_Error"));
        });
    },

    //----------------------------------- Approve Sale ----------------------------------\\
    Approve_Sale(id) {
      this.$swal({
        title: this.$t("Are_you_sure"),
        text: this.$t("You_wont_be_able_to_revert_this"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#d33",
        confirmButtonText: this.$t("Yes_Approve_it"),
        cancelButtonText: this.$t("Cancel")
      }).then(result => {
        if (result.value) {
          NProgress.start();
          axios
            .post("sales-approval/" + id + "/approve")
            .then(response => {
              NProgress.done();
              if (response.data.success) {
                this.makeToast(
                  "success",
                  this.$t("Approved"),
                  this.$t("Sale_Approved_Successfully")
                );
                this.Get_Sales(this.serverParams.page);
              } else {
                this.makeToast(
                  "danger",
                  this.$t("Failed"),
                  response.data.message
                );
              }
            })
            .catch(error => {
              NProgress.done();
              this.makeToast("danger", this.$t("Failed"), this.$t("Unknown_Error"));
            });
        }
      });
    },

    //----------------------------------- Reject Sale ----------------------------------\\
    Reject_Sale(id) {
      this.$swal({
        title: this.$t("Are_you_sure"),
        text: this.$t("You_wont_be_able_to_revert_this"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: this.$t("Yes_Reject_it"),
        cancelButtonText: this.$t("Cancel")
      }).then(result => {
        if (result.value) {
          NProgress.start();
          axios
            .post("sales-approval/" + id + "/reject")
            .then(response => {
              NProgress.done();
              if (response.data.success) {
                this.makeToast(
                  "success",
                  this.$t("Rejected"),
                  this.$t("Sale_Rejected_Successfully")
                );
                this.Get_Sales(this.serverParams.page);
              } else {
                this.makeToast(
                  "danger",
                  this.$t("Failed"),
                  response.data.message
                );
              }
            })
            .catch(error => {
              NProgress.done();
              this.makeToast("danger", this.$t("Failed"), this.$t("Unknown_Error"));
            });
        }
      });
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
    this.Get_Sales(1);
  }
};
</script>
