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
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      :item-height-as-min-height="itemHeightAsMinHeight"
      :table-fill="tableFill"
      :table-fixed-header="tableFixedHeader"
      :show-expand="showExpand"
      :single-expand="singleExpand"
      :single-select="false"
      :data="data"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      :table-only="tableOnly"
      hide-delete-button
      hide-add-button
      show-complete-po
    >
      <template v-slot:menu-after-filter>
        <v-btn
          :disabled="!selected"
          color="primary"
          @click="printExcel"
          outlined
          small
          ><v-icon small left>mdi-file-excel</v-icon>
          Excel
        </v-btn>
      </template>
      <template v-slot:item.invoice_total_price="props">
        {{ Number(props.item.invoice_total_price).format(2, 3) }}
      </template>

      <template v-slot:item.btn_payment_info="props">
        <v-btn color="primary" outlined small @click="openPaidForm(props.item)"
          ><v-icon small left>mdi-newspaper</v-icon>
          Edit Info </v-btn
        ><br /><br />
      </template>
      <template v-slot:item.amount="props">
        <b>Currency:</b> {{ props.item.currency }}<br />
        <b>Amount Price:</b>
        {{ Number(props.item.invoice_total_price).format(2, 3) }}<br />
        <b>PCT:</b> {{ Number(props.item.payment_pct) }} %
      </template>
      <template v-slot:item.reference_detail="props">
        <span v-if="props.item.po_no"
          ><b>No PO:</b>
          <a @click.stop.prevent="openReport2(props.item)">{{ props.item.po_no }}</a></span
        ><span v-else
          ><b
            >No: {{ props.item.as_reference == 0 ? "" : "As Reference" }}</b
          ></span
        ><br />
        <b>Title</b> :{{
          props.item.as_reference == 0 ? props.item.title : props.item.uraian
        }}
        <br />
        <b>Reference No</b> :{{
          props.item.cash_advance_ticket_no || props.item.tef_invoice_no || "-"
        }}<br />
        <span v-if="props.item.cash_advance_ticket_no">
          <b>FINANCE:</b> {{ props.item.cash_advance_ticket_no }}<br />
          <b>Matched by:</b> {{ props.item.cash_advance_match_type || "-" }}
          <span v-if="props.item.cash_advance_reference"
            >({{ props.item.cash_advance_reference }})</span
          ><br />
        </span>
        <!-- <b>Payment Code</b> :{{ props.item.kode_pembayaran }}<br/> -->
        <b>Supplier</b> :{{ props.item.supplier_name }}<br />
        <b>Proof of Invoice:</b>
        <a
          :href="props.item.invoice_doc_url"
          v-if="props.item.invoice_doc_url"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span>
      </template>
      <template v-slot:item.bank_info="props">
        <b>Bank name:</b> {{ props.item.bank }}<br />
        <b>Account no/IBAN:</b> {{ props.item.bank_account_no }}<br />
        <b>Account name:</b> {{ props.item.bank_account_name }}<br />
        <b>BIC/Swift Code:</b> {{ props.item.bic_swift_code }}
      </template>
      <template v-slot:item.multipayment_info="props">
        <v-btn color="warning" outlined small @click="openPaidForm(props.item)"
          ><v-icon small left>mdi-pencil</v-icon>
          Edit </v-btn
        ><br />
        <b>Debited Account:</b> {{ props.item.debited_acc }}<br />
        <b>Tranfer Type:</b> {{ props.item.transfer_type }}<br />
        <b>Transaction Code:</b> {{ props.item.transaction_code }}
      </template>
      <template v-slot:item.is_paid="props">
        <v-icon v-if="props.item.is_paid == 0" color="error"
          >mdi-close-thick</v-icon
        >
        <v-icon v-else color="success">mdi-check-bold</v-icon>
        <br />
      </template>
    </v-template>
    <v-action-dialog @save="saveFile" title="Invoice Info" v-model="dialogFile">
      <v-autoform v-model="formFile" :valid="valid"></v-autoform>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="dialogReport"
      title="Purchase Order Report"
      fullscreen
    >
      <iframe
        v-if="reportUrl"
        :src="reportUrl"
        frameborder="0"
        style="border: 0; width: 100%; height: calc(100vh - 96px)"
      ></iframe>
    </v-action-dialog>
  </v-container>
</template>

<style scoped>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}
</style>

<script>
module.exports = {
  name: "Transfer List Items",
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    sel: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    showExpand: {
      type: Boolean,
      default: false,
    },
    singleExpand: {
      type: Boolean,
      default: false,
    },
    tableFixedHeader: {
      type: Boolean,
      default: true,
    },
    itemHeightAsMinHeight: {
      type: Boolean,
      default: false,
    },
    tableFill: {
      type: Boolean,
      default: true,
    },
    hidefilterbutton: {
      type: Boolean,
      default: true,
    },
  },
  data: function () {
    return {
      valid: false,
      dialogFile: false,
      name: "listTransferItemFinance",
      itemKey: "id",
      dataInvoice: [],
      url: "bom/List_transfer_item_finance",
      formFile: [
        {
          text: "Debited Account",
          value: "debited_acc",
          type: "list",
          default: "4101233299",
          data_value: [
            { text: "410-1236999 (BCA Kas Besar)", value: "4101236999" },
            { text: "410-1233299 (Teknik)", value: "4101233299" },
            { text: "410-1019777 (Office)", value: "4101019777" },
          ],
          required: true,
        },
        // {

        //     text: "Debited Account",
        //     value: "paid_date",
        //     align: "start",
        //     type: "date",
        //     required: false,
        // },
        {
          text: "Transfer Type",
          value: "transfer_type",
          align: "start",
          type: "list",
          default: "BCA",
          data_value: [
            {
              text: "Transfer sesama BCA",
              value: "BCA",
            },
            {
              text: "Transfer Bank Lain dalam negeri dengan LLG",
              value: "LLG",
            },
            {
              text: "Transfer Bank Lain dalam negeri dengan RTGS",
              value: "RTGS",
            },
          ],
          required: false,
        },
        {
          text: "Receiver Bank Code",
          value: "receiver_bank_code",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          data_value: [],
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Transaction Code",
          value: "transaction_code",
          align: "start",
          type: "list",
          required: false,
          default: 78,
          data_value: [
            {
              text: "Payroll / Gaji",
              value: 70,
            },
            {
              text: "Pembayaran dividen",
              value: 71,
            },
            {
              text: "Distribusi bantuan dana pemerintah",
              value: 72,
            },
            {
              text: "Pembayaran tagihan",
              value: 73,
            },
            {
              text: "Pembayaran lainnya",
              value: 78,
            },
            {
              text: "Pengembalian DKE pembayaran",
              value: 79,
            },
            {
              text: "Pembayaran cicilan",
              value: 80,
            },
            {
              text: "Pembayaran tagihan",
              value: 81,
            },
            {
              text: "Pembayaran pajak",
              value: 82,
            },
            {
              text: "Pembayaran lainnya",
              value: 88,
            },
            {
              text: "Pengembalian DKE pembayaran",
              value: 89,
            },
          ],
        },
        {
          text: "Payment Value",
          value: "payment_value",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          data_value: [],
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
          groupable: false,
          input: function (data) {
            var self = App.get("listTransferItemFinance");
            self.formFileObj.difference.data =
              self.selected.invoice_total_price - data.data;
            self.formFile = App.updateObject(self.formFile);
          },
        },
        {
          text: "difference",
          value: "difference",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          readonly: true,
          class: "",
          width: "auto",
          type: "float",
          data_value: [],
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
          groupable: false,
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
        },
        {
          text: "payment id",
          value: "payment_id",
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
        },
        {
          text: "Invoice No",
          value: "invoice_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          data_value: [],
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          paging: true,
          page: "1",
          limit: "10",
          alias: "invoice_no",
        },
        {
          text: "Reference No",
          value: "po_no",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
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
          page: "0",
          limit: "10",
        },
        {
          text: "Invoice Detail",
          value: "reference_detail",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          data_value: [],
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          url: "",
          searchby: "",
          formatter: "",
          options: "",
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "PO No",
          value: "po_no",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
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
          page: "0",
          limit: "10",
        },
        {
          text: "Title",
          value: "title",
          type: "text",
          form: false,
          visible: false,
        },
        {
          text: "Total Price",
          value: "total_price",
          type: "float",
          form: false,
          visible: false,
        },
        {
          text: "invoice date",
          value: "invoice_date",
          type: "varchar",
          form: false,
          visible: false,
        },
        {
          text: "Supplier",
          value: "supplier_name",
          type: "varchar",
          form: false,
          visible: false,
        },
        {
          text: "Invoice Amount",
          value: "amount",
          type: "varchar",
          form: false,
          sortable: false,
        },
        {
          text: "Receiver's Bank Info",
          value: "bank_info",
          type: "varchar",
          form: false,
          sortable: false,
        },
        {
          text: "Bank Account No",
          value: "bank_account_no",
          type: "varchar",
          form: false,
          visible: false,
        },
        {
          text: "account name",
          value: "bank_account_name",
          type: "varchar",
          form: false,
          visible: false,
        },
        {
          text: "invoice total price",
          value: "invoice_total_price",
          type: "varchar",
          form: false,
          visible: false,
        },
        {
          text: "Paid Date",
          value: "paid_date",
          type: "date",
          form: false,
          visible: false,
        },
        {
          text: "Proof URL",
          value: "proof_url",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          data_value: [],
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
          groupable: false,
          url: "",
          searchby: [],
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Notes",
          value: "invoice_payment_notes",
          type: "text",
          form: true,
          visible: true,
          filter: false,
          sortable: false,
        },
        {
          text: "Multi Payment Info",
          value: "multipayment_info",
          sortable: false,
          readonly: false,
          type: "varchar",
          form: false,
          visible: true,
        },
        {
          text: "Payment Status",
          value: "is_paid",
          readonly: false,
          type: "switch",
          form: false,
          default: 100,
          data_value: [0, 1],
          visible: true,
        },
        {
          text: "",
          value: "btn_payment_info",
          type: "varchar",
          visible: false,
          form: false,
          filter: false,
        },
      ],
      selected: false,
      selectedAll: false,
      paidFormSelected: false,
      dialogReport: false,
      reportUrl: "",
    };
  },
  computed: {
    headersObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.headers).map((key) => {
        var val = self.headers[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
    formFileObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.formFile).map((key) => {
        var val = self.formFile[key];
        tmp[val.value] = val;
      });
      return tmp;
    },
  },
  methods: {
    printExcel: function (val) {
      var self = this;
      if (val) {
        self.loading = true;
        try {
          App.tmp.target = self;

          self.go = true;
          self.dataInvoice = [];
          self.$refs.template.selectedRowTmp.map((val) => {
            self.dataInvoice.push(val.id);
          });
          window.open(
            "https://main.buanamultiteknik.com/api/bom/list_transfer_item_finance/excel?ids=" +
              self.dataInvoice.join(",") +
              "&rand=" +
              randomId(),
          );
        } catch {}
      }
      // {console.log(val)}
      // window.open('https://main.buanamultiteknik.com/api/bom/list_transfer_item_finance/excelbca?id='+self.selected.id+'&rand='+randomId())
    },
    allowPaymentInfo: function (item) {
      var self = this;
      if (item == undefined) return false;
      if (item.approved < 3) return false;
      if (item.is_paid > 0) return false;
      return true;
    },
    openPaidForm: function (item) {
      var self = this;
      if (self.sel.is_request_payment) {
        self.formFileObj.payment_value.form = true;
      } else {
        self.formFileObj.payment_value.form = false;
      }
      self.paidFormSelected = item;
      self.dialogFile = true;
    },
    saveFile: async function () {
      var self = this,
        sel = self.paidFormSelected;
      const formData = {};
      self.formFile.map(function (val, i) {
        var key = val.value;
        //if(key!='difference')
        formData[key] = val.data;
      });
      formData["id"] = sel.invoice_id;
      formData["invoice_total_price"] = sel.invoice_total_price;
      formData["payment_id"] = sel.payment_id;
      formData["po_id"] = sel.po_id;
      var res = await axios.post(App.url + "bom/invoice/test", formData);
      if (!res.data.status) {
        App.errorMsg();
      } else {
        App.successMsg();

        self.dialogFile = false;
      }
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
      } else {
        self.selected = val;
      }
    },
    openReport2: function (item) {
      var self = this;
      var target = item || self.selected;
      if (!target || !target.po_id || !target.po_no) {
        App.errorMsg("PO data not found");
        return;
      }
      var name = target.po_no.replace(/\//g, "_").replace(/\-/g, "_");
      var randomid = randomId();
      self.reportUrl =
        "https://main.buanamultiteknik.com/api/data/reportpo2?id=" +
          target.po_id +
          "&filename=" +
          name +
          "&idx=" +
          randomid;
      self.dialogReport = true;
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
  },
  mounted: function () {},
};
</script>
