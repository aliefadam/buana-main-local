<template>
  <v-container
    style="
      padding: 0 !important;
      height: 100%;
      display: flex;
      flex-direction: column;
      margin: 0;
      width: 100%;
      max-width: 100%;
    "
  >
    <v-template
      :items-options="itemsOptions"
      export-excel
      @close-add="resetHeader"
      :disable-edit-button="disableEditButton"
      :disable-delete-button="disableDeleteButton"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      :table-only="tableOnly"
    >
      <template v-slot:after-header>
        <v-row>
          <v-col cols="3">
            <v-card
              class="mx-auto"
              max-width="344"
              outlined
              title="Total PO with Status New"
            >
              <v-list-item three-line>
                <v-list-item-content>
                  <div class="text-overline mb-4" style="font-weight: bold">
                    Balanced Due
                  </div>
                  <v-list-item-title
                    class="text-h5 mb-1"
                    style="font-weight: bold"
                  >
                    {{ total_balanced }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-card>
          </v-col>
        </v-row>
      </template>

      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          outlined
          small
          @click="openPOItems"
          :disabled="!selected"
        >
          PO Items
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="openDetail"
          :disabled="!selected"
        >
          Details
        </v-btn>
        <!-- <v-btn color="primary" outlined small v-if="history" @click="openPaidForm" :disabled="!selected">
                    Payment Info
                </v-btn> -->
      </template>
      <template v-slot:item.invoice="props">
        <b>Invoice No:</b> {{ props.item.invoice_no }}<br />
        <b>Invoice Date:</b> {{ props.item.invoice_date }}<br />
      </template>
      <template v-slot:item.uraian="props">
        <span>{{
          props.item.as_reference == 0 ? props.item.title : props.item.uraian
        }}</span>
      </template>
      <template v-slot:item.po_id="props">
        {{ props.item.po_no }}<br />
        <b>As Reference: </b> {{ props.item.as_reference == 0 ? "No" : "Yes" }}
      </template>
      <!--<template v-slot:item.as_reference="props">-->
      <!--	{{props.item.as_reference == 0 ? "No" : "Yes"}}-->
      <!--</template>-->
      <template v-slot:item.invoice_doc_url="props">
        <a
          :href="props.item.invoice_doc_url"
          v-if="props.item.invoice_doc_url"
          target="_blank"
          >Invoice Document</a
        ><span v-else>Invoice Document: -</span><br />
        <a
          :download="props.item.proof_of_transfer.trim().split('+++')[1]"
          target="_blank"
          :href="
            'https://main.buanamultiteknik.com/api/uploads/invoice' +
            props.item.id +
            '/' +
            props.item.proof_of_transfer.trim().split('+++')[0]
          "
          v-if="props.item.proof_of_transfer.trim() != ''"
          >Proof of Payment </a
        ><span v-else>Proof of Payment: -</span>
      </template>
      <template v-slot:item.total_price="props">
        {{ parseFloat(props.item.total_price).format(2, 3) }}
      </template>
      <template v-slot:item.is_paid="props" v-if="showPaid">
        <v-icon v-if="props.item.is_paid == 0" color="error"
          >mdi-close-thick</v-icon
        >
        <v-icon v-else color="success">mdi-check-bold</v-icon><br />
      </template>
      <template v-slot:item.created="props">
        <span>By:</span> {{ props.item.created_by_name }}<br />
        <span>Date:</span> {{ props.item.created_date }}<br />
      </template>
      <template v-slot:item.modified="props">
        <span>By:</span> {{ props.item.modified_by_name }}<br />
        <span>Date:</span> {{ props.item.modified_date }}<br />
      </template>
    </v-template>
    <!-- <v-action-dialog :actions="false" v-model="dialogItem" title="Detail" min-height="75%" fullscreen :key="selected.id">
            <table style="width: 100%; height: 100%;">
                <tr>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="poInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="supplierInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="billInf" hide-details></v-autoform>
                        
                        <v-btn color="primary" outlined small @click="saveBill">
                            Save
                        </v-btn>
                    </td>
                </tr>
            </table>
        </v-action-dialog> -->
    <v-action-dialog
      class="long-dialog"
      :actions="false"
      v-model="dialogItem"
      title="Detail"
      min-height="75%"
      :key="selected.id"
    >
      <v-card flat>
        <v-card-title primary-title> Supplier Information </v-card-title>
        <table class="table">
          <thead>
            <tr>
              <th>Supplier</th>
              <th>Bank Info</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                {{ supplierfObj.supplier_name.data }}
              </td>
              <td>
                <b>Bank name:</b> {{ supplierfObj.bank.data }}<br />
                <b>Account no/IBAN:</b> {{ supplierfObj.bank_account_no.data
                }}<br />
                <b>Currency:</b> {{ poinfObj.currency.data }}<br />
                <b>Account name:</b> {{ supplierfObj.bank_account_name.data
                }}<br />
                <b>BIC/Swift Code:</b> {{ supplierfObj.bic_swift_code.data }}
              </td>
            </tr>
          </tbody>
        </table>
      </v-card>
      <v-card flat>
        <v-card-title primary-title> Invoice Amount </v-card-title>
        <table class="table">
          <thead>
            <tr>
              <th>Currency</th>
              <th>Total Item Price</th>
              <th>PO Charge</th>
              <th>Grand Total Price</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                {{ poinfObj.currency.data }}
              </td>
              <td>
                {{ poinfObj.total_price.data }}
              </td>
              <td>
                <b>Charge 1:</b> {{ poinfObj.charge1.data }}<br />
                <b>Charge 1 Desc:</b> {{ poinfObj.charge1_desc.data }}<br />
                <b>Charge 2:</b> {{ poinfObj.charge2.data }}<br />
                <b>Charge 2 Desc:</b> {{ poinfObj.charge2_desc.data }}
              </td>
              <td>
                {{ poinfObj.grand_total.data }}
              </td>
            </tr>
            <tr>
              <td
                colspan="2"
                style="background-color: #ddebf6; font-weight: bold"
              >
                Total Payment
              </td>
              <td
                colspan="2"
                style="background-color: #ddebf6; font-weight: bold"
              >
                Remaining Payment
              </td>
            </tr>
            <tr v-if="selected">
              <td colspan="2"></td>
              <td colspan="2">
                {{ numberFormat(selected.remaining_payment, 4) }}
              </td>
            </tr>
            <tr>
              <td
                colspan="4"
                style="background-color: #ddebf6; font-weight: bold"
              >
                Total Paid (Including Reduction and Charge)
              </td>
            </tr>
            <tr v-if="selected">
              <td colspan="4">
                {{ numberFormat(selected.total_paid_fiat, 4) }}
              </td>
            </tr>
          </tbody>
        </table>
      </v-card>
      <v-autoform v-model="billInf" hide-details></v-autoform>
      <div class="flex">
        <v-spacer></v-spacer>
        <v-btn color="primary" outlined small @click="saveBill"> Save </v-btn>
      </div>
      <!-- <table style="width: 100%; height: 100%;">
                <tr>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="poInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="supplierInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="billInf" hide-details></v-autoform>
                        
                        <v-btn color="primary" outlined small @click="saveBill">
                            Save
                        </v-btn>
                    </td>
                </tr>
            </table> -->
    </v-action-dialog>

    <v-action-dialog
      @save="saveFile"
      title="Paid Info"
      v-model="dialogFile"
      v-if="history"
    >
      <v-autoform v-model="formFile" :valid="valid"></v-autoform>
    </v-action-dialog>

    <v-action-dialog
      :actions="false"
      title="PO Items"
      v-model="dialogPO"
      fullscreen
    >
      <invoice-item :data="invInfo"></invoice-item>
    </v-action-dialog>
  </v-container>
</template>

<style scoped>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}
</style>
<style>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}

table.table {
  border: 0px;
  border-collapse: collapse;
  font-size: 12px;
  width: 100%;
}

table.table tr th {
  border-bottom: 1px solid #c5c5c5;
  margin-bottom: 4px;
  background-color: #ddebf6;
}

table.table tr:last-child th {
  height: 4px;
}

table.table th > div {
  padding: 0 4px;
  border-radius: 3px;
  text-align: center;
  white-space: nowrap;
}

table.table td > div {
  padding: 0 4px;
  border-radius: 3px;
  white-space: nowrap;
}

table.table td {
  padding: 1.5px;
}

table.table td,
table.table th {
  padding: 2px 4px;
  border: 1px solid black !important;
  min-width: 40px;
}

table.table td.table-title {
  font-weight: bold;
  text-align: center;
}
</style>

<script>
module.exports = {
  name: "invoice",
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    showPaid: {
      type: Boolean,
      default: false,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    history: {
      type: Boolean,
      default: false,
    },
    disableDeleteButton: {
      type: Boolean,
      default: false,
    },
    disableEditButton: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          payment_id: null,
        },
        filterType: {
          payment_id: "isnull",
        },
      },
    },
  },
  components: {
    "invoice-item": "url:ui/bomdev/payment/invoice/poitems.vue",
  },
  data: function () {
    return {
      valid: false,
      dialogFile: false,
      dialogPO: false,
      total_balanced: 0,
      formFile: [
        {
          text: "File",
          value: "file",
          type: "file",
          required: true,
        },
        {
          text: "Is Paid",
          value: "is_paid",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          required: false,
        },
        {
          text: "Payment Date",
          value: "paid_date",
          align: "start",
          type: "date",
          required: false,
        },
      ],
      name: "Invoice",
      processData: {},
      itemKey: "id",
      url: "bom/invoice",
      billInf: [
        {
          text: "Exchange Rate",
          value: "exchange_rate",
          readonly: false,
          type: "float",
          form: false,
          default: 0,
        },
        {
          text: "Payment",
          value: "payment_pct_fiat",
          readonly: false,
          type: "float",
          form: true,
          default: 100,
          input: function (data) {
            try {
              var self = App.$get("invoice");
              self.$nextTick(function () {
                if (Number(data.data) > 100)
                  self.billfObj.payment_pct.data = 100;
                if (Number(data.data) < 0) self.billfObj.payment_pct.data = 0;
                self.billfObj.payment_pct.data =
                  Number(data.data) * (100 / self.selected.grand_total_price);
                self.billfObj.invoice_total_price.data =
                  Number(self.selected.grand_total_price) *
                    Number(self.billfObj.payment_pct.data / 100) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
                self.billInf = App.updateObject(self.billInf);
              });
            } catch (err) {
              console.log(err);
            }
          },
        },
        {
          text: "Outstanding Balance (%)",
          value: "payment_pct",
          readonly: false,
          type: "float",
          form: true,
          default: 100,
          input: function (data) {
            try {
              var self = App.$get("invoice");
              self.$nextTick(function () {
                if (Number(data.data) > 100)
                  self.billfObj.payment_pct.data = 100;
                if (Number(data.data) < 0) self.billfObj.payment_pct.data = 0;
                self.billfObj.payment_pct_fiat.data =
                  Number(self.selected.grand_total_price) *
                  Number(self.billfObj.payment_pct.data / 100);
                self.billfObj.invoice_total_price.data =
                  Number(self.selected.grand_total_price) *
                    Number(self.billfObj.payment_pct.data / 100) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
                self.billInf = App.updateObject(self.billInf);
              });
            } catch (err) {
              console.log(err);
            }
          },
        },
        {
          text: "Payment Amount",
          value: "payment_amount",
          readonly: false,
          type: "float",
          form: false,
          default: 0,
          input: function (data) {
            try {
              var self = App.$get("invoice");
              self.$nextTick(function () {
                self.billfObj.invoice_total_price.data =
                  Number(self.billfObj.payment_amount.data) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
                self.billInf = App.updateObject(self.billInf);
              });
            } catch (err) {
              console.log(err);
            }
          },
        },
        {
          text: "Invoice Charge",
          value: "invoice_charge",
          readonly: false,
          type: "float",
          precision: 2,
          input: function (data) {
            var self = App.$get("invoice");
            self.$nextTick(function () {
              if (self.selected.as_reference == 1)
                self.billfObj.invoice_total_price.data =
                  Number(self.billfObj.payment_amount.data) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
              else
                self.billfObj.invoice_total_price.data =
                  Number(self.selected.grand_total_price) *
                    (Number(self.billfObj.payment_pct.data) / 100) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
              self.billInf = App.updateObject(self.billInf);
            });
          },
        },
        {
          text: "Invoice Charge Note",
          value: "invoice_charge_note",
          readonly: false,
          type: "text",
        },
        {
          text: "Invoice Reduction",
          value: "invoice_reduction",
          readonly: false,
          type: "float",
          precision: 2,
          input: function (data) {
            var self = App.$get("invoice");
            self.$nextTick(function () {
              if (self.selected.as_reference == 1)
                self.billfObj.invoice_total_price.data =
                  Number(self.billfObj.payment_amount.data) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
              else
                self.billfObj.invoice_total_price.data =
                  Number(self.selected.grand_total_price) *
                    (Number(self.billfObj.payment_pct.data) / 100) +
                  Number(self.billfObj.invoice_charge.data || 0) -
                  Number(self.billfObj.invoice_reduction.data || 0);
              self.billInf = App.updateObject(self.billInf);
            });
          },
        },
        {
          text: "Invoice Reduction Note",
          value: "invoice_reduction_note",
          readonly: false,
          type: "text",
        },
        {
          text: "Reimburse To",
          value: "reimburse_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          data_value: [],
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "bom/supplier",
          searchby: ["name"],
          formatter: ["id", "bank_info"],
          options: {
            filter: {
              is_reimburse: 1,
              flag: 1,
            },
            filterType: {},
            filterCondition: {
              is_reimburse: "AND",
              flag: "AND",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "reimburse_name",
        },
        {
          text: "Invoice Notes",
          value: "notes",
          readonly: false,
          type: "text",
        },
        {
          text: "Invoice Total Price",
          value: "invoice_total_price",
          readonly: true,
          type: "float",
          precision: 2,
        },
        {
          text: "Estimate Departure",
          value: "etd",
          readonly: false,
          type: "text",
        },

        {
          text: "Is Paid by Finance?",
          value: "is_paid",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          form: true,
        },
      ],
      supplierInf: [
        {
          text: "Supplier ID",
          value: "supplier_id",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Supplier Name",
          value: "supplier_name",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Bank Name",
          value: "bank",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Account No/IBAN",
          value: "bank_account_no",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Currency",
          value: "supplier_currency",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Account Name",
          value: "bank_account_name",
          readonly: true,
          type: "varchar",
        },
        {
          text: "BIC/SWIFT Code",
          value: "bic_swift_code",
          readonly: true,
          type: "varchar",
        },
      ],
      poInf: [
        {
          text: "Currency",
          value: "currency",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Total Item Price",
          value: "total_price",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Charge 1",
          value: "charge1",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Charge 1 Description",
          value: "charge1_desc",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Charge 2",
          value: "charge2",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Charge 2 Description",
          value: "charge2_desc",
          readonly: true,
          type: "varchar",
        },
        {
          text: "Grand Total Price",
          value: "grand_total",
          readonly: true,
          type: "varchar",
        },
      ],
      headers: [
        {
          text: "id",
          value: "id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Invoice",
          value: "invoice",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Invoice No",
          value: "invoice_no",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Invoice Date",
          value: "invoice_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Title",
          value: "uraian",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "PO No",
          value: "po_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          data_value: [],
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "bom/purchaseorder",
          searchby: ["po_no"],
          formatter: ["id", "po_no"],
          options: {
            filter: {
              total_payment_pct: "100",
              approved: 3,
            },
            filterType: {
              total_payment_pct: "<",
            },
            filterCondition: {},
            invoice: true,
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "po_no",
        },
        {
          text: "Total Payment Po",
          value: "total_payment_po",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          default: 100,
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Ref. Invoice No",
          value: "ref_invoice_no",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Payment Code",
          value: "kode_pembayaran",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Petty Cash",
          value: "petty_cash",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          visible: false,
          // input: function(data) {
          // 	console.log('cash is', Number(data.data) == 1)
          //     if (Number(data.data) == 1) {
          //         App.$get("invoice").billfObj.reimburse_id.data = 96;
          //     } else {
          //         App.$get("invoice").billfObj.reimburse_id.data = null;
          //         // App.$get('invoice').headersObj.grand_total_price.data = null
          //     }
          //     App.$get("invoice").billfObj = App.updateObject(App.$get("invoice").billfObj);
          // },
        },

        {
          text: "Payment Status",
          value: "is_paid",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          required: true,
        },
        {
          text: "As Reference",
          value: "as_reference",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          input: function (data) {
            if (data.data == 1) {
              App.$get("invoice").headersObj.supplier_id.data = null;
              App.$get("invoice").headersObj.charge1.data = 0;
              App.$get("invoice").headersObj.charge1_desc.data = "";
              App.$get("invoice").headersObj.charge2.data = 0;
              App.$get("invoice").headersObj.charge2_desc.data = "";
              App.$get("invoice").headersObj.total_price.data = 0;
              App.$get("invoice").headersObj.payment_pct.data = 100;

              App.$get("invoice").headersObj.uraian.form = true;
              App.$get("invoice").headersObj.supplier_id.form = true;
              App.$get("invoice").headersObj.charge1.form = true;
              App.$get("invoice").headersObj.charge1_desc.true = true;
              App.$get("invoice").headersObj.charge2.form = true;
              App.$get("invoice").headersObj.charge2_desc.form = true;
              App.$get("invoice").headersObj.total_price.form = true;
              App.$get("invoice").headersObj.payment_pct.form = true;
              // App.$get('invoice').headersObj.grand_total_price.form = true
              App.$get("invoice").headersObj.po_id.form = false;

              App.$get("invoice").headersObj.po_id.data = null;
            } else {
              App.$get("invoice").headersObj.uraian.form = false;
              App.$get("invoice").headersObj.supplier_id.form = false;
              App.$get("invoice").headersObj.charge1.form = false;
              App.$get("invoice").headersObj.charge1_desc.true = false;
              App.$get("invoice").headersObj.charge2.form = false;
              App.$get("invoice").headersObj.charge2_desc.form = false;
              App.$get("invoice").headersObj.total_price.form = false;
              App.$get("invoice").headersObj.payment_pct.form = false;
              // App.$get('invoice').headersObj.grand_total_price.form = false
              App.$get("invoice").headersObj.po_id.form = true;

              App.$get("invoice").headersObj.uraian.data = null;
              App.$get("invoice").headersObj.supplier_id.data = null;
              App.$get("invoice").headersObj.charge1.data = null;
              App.$get("invoice").headersObj.charge1_desc.data = null;
              App.$get("invoice").headersObj.charge2.data = null;
              App.$get("invoice").headersObj.charge2_desc.data = null;
              App.$get("invoice").headersObj.total_price.data = null;
              App.$get("invoice").headersObj.payment_pct.data = null;
              // App.$get('invoice').headersObj.grand_total_price.data = null
            }
          },
          visible: false,
        },
        {
          text: "Payment Status",
          value: "is_paid",
          readonly: false,
          type: "switch",
          form: false,
          default: 0,
          data_value: [0, 1],
          visible: false,
        },
        {
          text: "Supplier",
          value: "supplier_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: true,
          form: true,
          data_value: [],
          filter: false,
          groupable: false,
          url: App.url + "bom/supplier",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Total",
          value: "total_price",
          readonly: false,
          type: "float",
          form: false,
          default: 100,
        },
        {
          text: "Charge 1",
          value: "charge1",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          precision: 2,
          groupable: false,
        },
        {
          text: "Charge 1 Desc",
          value: "charge1_desc",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Charge 2",
          value: "charge2",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          precision: 2,
          groupable: false,
        },
        {
          text: "Charge 2 Desc",
          value: "charge2_desc",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Outstanding Balance (%)",
          value: "payment_pct",
          readonly: false,
          type: "float",
          form: false,
          default: 100,
          filter: true,
        },
        {
          text: "Due date",
          value: "due_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Document URL",
          value: "invoice_doc_url",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: true,
          required: false,
          form: true,
          filter: false,
          groupable: false,
          url: "",
          searchby: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Created",
          value: "created",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Created By",
          value: "created_by",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "master",
              sub_group_name: "master",
            },
            filterType: {},
            filterCondition: {
              group_name: "or",
              sub_group_name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "created_by_name",
        },
        {
          text: "Created Date",
          value: "created_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Created Date",
          value: "crea_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          url: "",
          search: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Modified",
          value: "modified",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Modified By",
          value: "modified_by",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "master",
              sub_group_name: "master",
            },
            filterType: {},
            filterCondition: {
              group_name: "or",
              sub_group_name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "modified_by_name",
        },
        {
          text: "Modified Date",
          value: "modified_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Modified Date",
          value: "mod_date",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          url: "",
          search: [],
          formatter: [],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        /* ,
					{
						"text": "Grand Total Price",
						"value": "grand_total_price",
						"readonly": false,
						"type": "float",
						"form": false,
						"default": 100
					} */
        {
          text: "",
          value: "data-table-expand",
        },
      ],
      dialogItem: false,
      selected: false,
      dataid: {},
    };
  },
  computed: {
    invInfo: function () {
      var self = this;
      return {
        invoice_id: self.selected.id,
      };
    },
    headersObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.headers).map((key) => {
        var val = self.headers[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
    poinfObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.poInf).map((key) => {
        var val = self.poInf[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
    supplierfObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.supplierInf).map((key) => {
        var val = self.supplierInf[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
    billfObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.billInf).map((key) => {
        var val = self.billInf[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
  },
  methods: {
    openPaidForm: function () {
      var self = this;
      if (self.selected.is_paid == 1) App.errorMsg("Invoice has been paid!");
      else self.dialogFile = true;
    },
    saveFile: async function () {
      var self = this;
      const formData = new FormData();
      self.formFile.map(function (val, i) {
        var key = val.value;
        formData.append(key, val.data);
      });
      formData.append("id", self.selected.id);
      var res = await axios.post(App.url + "bom/invoice/paid", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (!res.data.status) {
        App.errorMsg();
      } else {
        App.successMsg();

        self.dialogFile = false;
        self.getAttachment();
      }
    },
    checkHeader: function () {
      var self = this;
      if (this.selected.as_reference == 1) {
        self.headersObj.uraian.form = true;
        self.headersObj.supplier_id.form = true;
        self.headersObj.charge1.form = true;
        self.headersObj.charge1_desc.true = true;
        self.headersObj.charge2.form = true;
        self.headersObj.charge2_desc.form = true;
        self.headersObj.total_price.form = true;
        self.headersObj.payment_pct.form = true;
        self.billfObj.exchange_rate.form = true;
        self.headersObj.po_id.form = false;
        // self.billfObj.reimburse_id.data="96";
      } else {
        self.headersObj.uraian.form = false;
        self.headersObj.supplier_id.form = false;
        self.headersObj.charge1.form = false;
        self.headersObj.charge1_desc.true = false;
        self.headersObj.charge2.form = false;
        self.headersObj.charge2_desc.form = false;
        self.headersObj.total_price.form = false;
        self.headersObj.payment_pct.form = false;
        self.billfObj.exchange_rate.form = false;
        self.headersObj.po_id.form = true;
        // self.billfObj.reimburse_id.data=null;
      }
    },
    resetHeader: function () {
      var self = this;
      self.headersObj.uraian.form = false;
      self.headersObj.supplier_id.form = false;
      self.headersObj.charge1.form = false;
      self.headersObj.charge1_desc.true = false;
      self.headersObj.charge2.form = false;
      self.headersObj.charge2_desc.form = false;
      self.headersObj.total_price.form = false;
      self.headersObj.payment_pct.form = false;
      // self.headersObj.grand_total_price.form = false
      self.headersObj.po_id.form = true;
    },
    openPOItems: function () {
      var self = this;
      self.dialogPO = true;
    },
    openDetail: async function () {
      var self = this;
      if (self.selected.as_reference == 1) {
        self.billfObj.payment_pct.form = true;
        if (self.poinfObj.currency.data != null)
          if (self.poinfObj.currency.data.toUpperCase() != "IDR") {
            /* var response = await axios.get(App.url+'bom/payment/exchange', {
								params: {
									currency: self.poinfObj.currency.data.toUpperCase()
								}
							})
							var data = response.data
							self.billfObj.exchange_rate.data = data.match(/\d+(.\d+)/)[0] */
            axios
              .get(App.url + "bom/payment/exchange", {
                params: {
                  currency: self.poinfObj.currency.data.toUpperCase(),
                },
              })
              .then(function (response) {
                var data = response.data;
                self.billfObj.exchange_rate.data = data.match(/\d+(.\d+)/)[0];
              })
              .catch(function (error) {});
          }
        //self.billfObj.payment_amount.form = true
      } else {
        self.billfObj.payment_pct.form = true;
        self.billfObj.reimburse_id.data = null;
        //self.billfObj.payment_amount.form = false
      }
      if (self.selected.petty_cash == 1) {
        self.billfObj.reimburse_id.data = "96";
      }

      self.billInf = App.updateObject(self.billInf);
      self.dialogItem = true;
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
      } else {
        val.as_reference = parseInt(val.as_reference);
        val.petty_cash = parseInt(val.petty_cash);
        self.checkHeader();
        if (val.payment_id !== null) {
          self.disableDeleteButton = true;
          self.disableEditButton = true;
        } else {
          self.disableDeleteButton = false;
          self.disableEditButton = false;
        }

        if (val.payment_flag == 0) {
          self.disableDeleteButton = false;
          self.disableEditButton = false;
        }

        if (val.payment_approved < 0) {
          self.disableDeleteButton = false;
          self.disableEditButton = false;
        }
        self.selected = val;

        self.poinfObj.currency.data = val.currency;
        self.poinfObj.total_price.data = Number(val.total_price).format(2, 3);
        self.poinfObj.charge1.data = Number(val.charge1).format(2, 3);
        self.poinfObj.charge1_desc.data = val.charge1_desc;
        self.poinfObj.charge2.data = Number(val.charge2).format(2, 3);
        self.poinfObj.charge2_desc.data = val.charge2_desc;
        self.selected.remaining_payment =
          val.grand_total_price - val.total_paid_fiat;
        self.poinfObj.grand_total.data = Number(val.grand_total_price).format(
          2,
          3,
        );
        // self.poinfObj.total_payment_po.data=Number(val.total_payment_po).format(2, 3);

        self.supplierfObj.supplier_id.data = val.supplier_id;
        self.supplierfObj.supplier_name.data = val.supplier_name;
        self.supplierfObj.bank.data = val.bank;
        self.supplierfObj.bank_account_no.data = val.bank_account_no;
        self.supplierfObj.supplier_currency.data = val.supplier_currency;
        self.supplierfObj.bank_account_name.data = val.bank_account_name;
        self.supplierfObj.bic_swift_code.data = val.bic_swift_code;

        self.billfObj.payment_pct_fiat.data =
          val.payment_pct_fiat == 0 && val.payment_pct != 0
            ? Number(self.selected.grand_total_price) *
              Number(self.selected.payment_pct / 100)
            : Number(val.payment_pct_fiat);
        self.billfObj.payment_pct.data = Number(val.payment_pct).format(2);
        self.billfObj.invoice_charge.data = numberFormat(
          Number(val.invoice_charge),
          4,
        );
        self.billfObj.invoice_charge_note.data = val.invoice_charge_note;
        self.billfObj.invoice_reduction.data = numberFormat(
          Number(val.invoice_reduction),
          4,
        );
        self.billfObj.invoice_reduction_note.data = val.invoice_reduction_note;
        self.billfObj.reimburse_id.data = val.reimburse_id;
        self.billfObj.notes.data = val.notes;
        self.billfObj.etd.data = val.etd;
        self.billfObj.invoice_total_price.data = Number(
          val.invoice_total_price,
        ).format(2, 3);
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    saveBill: async function () {
      var self = this;
      self.$nextTick(async () => {
        setTimeout(async () => {
          if (
            self.billfObj.payment_pct_fiat.data >
            self.selected.remaining_payment
          ) {
            App.errorMsg("Payment Melebihi Remaining Payment!");
          } else {
            try {
              var res = await axios.post(App.url + self.url + "/savebill", {
                id: self.selected.id,
                reimburse_id: self.billfObj.reimburse_id.data,
                payment_pct: self.billfObj.payment_pct.data,
                payment_pct_fiat: self.billfObj.payment_pct_fiat.data,
                payment_amount: self.billfObj.payment_amount.data,
                invoice_charge: self.billfObj.invoice_charge.data,
                invoice_charge_note: self.billfObj.invoice_charge_note.data,
                invoice_reduction: self.billfObj.invoice_reduction.data,
                invoice_reduction_note:
                  self.billfObj.invoice_reduction_note.data,
                notes: self.billfObj.notes.data,
                etd: self.billfObj.etd.data,
              });
              if (!res.data.status) throw res.data;
              App.successMsg();
              self.$refs.template.getItems();
              self.dialogItem = false;
            } catch (e) {
              App.errorMsg(e);
            }
          }
        });
      });
    },
  },
  mounted: function () {
    var self = this;
    if (self.showPaid) {
      self.headersObj.is_paid.visible = true;
    }
    if (self.history)
      self.headersObj.po_id.options = {
        filter: {},
        filterType: {},
        filterCondition: {},
      };
  },
};
</script>
