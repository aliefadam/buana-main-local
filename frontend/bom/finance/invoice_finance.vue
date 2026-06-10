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
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:menu-after-filter> </template>
      <template v-slot:item.invoice="props">
        <b>Invoice No:</b> {{ props.item.invoice_no }}<br />
        <b>Transfer List No:</b> {{ props.item.payment_no }}<br />
        <b>Invoice Date:</b> {{ props.item.invoice_date }}<br />
        <b>Created By:</b> {{ props.item.created_by_name }}<br />
        <b>Created Date:</b> {{ props.item.created_date }}
      </template>
      <template v-slot:item.invoice_detail="props">
        <span v-if="props.item.po_no"
          ><b>No PO:</b>
          <a @click="openReport2">{{ props.item.po_no }}</a></span
        ><span v-else
          ><b
            >No: {{ props.item.as_reference == 0 ? "" : "As Reference" }}</b
          ></span
        ><br />
        <b>Title</b> :{{
          props.item.as_reference == 0 ? props.item.title : props.item.uraian
        }}
        <br />
        <b>Supplier</b> :{{ props.item.supplier_name }}<br />
        <b>Reference No</b> :{{ props.item.ref_invoice_no }}<br />
        <!-- <b>Payment Code</b> :{{ props.item.kode_pembayaran }}<br /> -->
        <b>Proof of Invoice:</b>
        <a
          :href="props.item.invoice_doc_url"
          v-if="props.item.invoice_doc_url"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span><br />
        <b>Notes:</b> {{ props.item.notes }}
      </template>
      <template v-slot:item.uraian="props">
        <span>{{
          props.item.as_reference == 0 ? props.item.title : props.item.uraian
        }}</span>
      </template>
      <template v-slot:item.invoice_amount="props">
        <b>Currency:</b> {{ props.item.currency }}<br />
        <b>Amount:</b> {{ displayMoney(props.item.invoice_total_price) }}<br />
        <b>PCT:</b> {{ displayNumber(props.item.payment_pct) }} %<br />
        <b>Exchange Rate:</b> {{ displayMoney(props.item.exchange_rate) }}<br />
        <b>Admin Fee:</b> {{ displayMoney(props.item.admin_fee) }}
      </template>
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
      <template v-slot:item.is_paid_info="props">
        <v-icon v-if="props.item.is_paid == 0" color="error"
          >mdi-close-thick</v-icon
        >
        <v-icon v-else color="success">mdi-check-bold</v-icon>
      </template>
      <template v-slot:item.bank_info="props">
        <span v-if="props.item.reimburse_id == null">
          <b>Bank name:</b> {{ props.item.bank }}<br />
          <b>Account no/IBAN:</b> {{ props.item.bank_account_no }}<br />
          <b>Account name:</b> {{ props.item.bank_account_name }}<br />
          <b>BIC/Swift Code:</b> {{ props.item.bic_swift_code }}</span
        >
        <span v-else>
          <b>Bank name:</b> {{ props.item.r_bank }}<br />
          <b>Account no/IBAN:</b> {{ props.item.r_bank_account }}<br />
          <b>Account name:</b> {{ props.item.r_bank_account_name }}<br />
          <b>BIC/Swift Code:</b> {{ props.item.r_bic_swift_code }}
        </span>
      </template>
      <template v-slot:item.multipayment_info="props">
        <b>Debited Account:</b> {{ props.item.debited_acc }}<br />
        <b>Tranfer Type:</b> {{ props.item.transfer_type }}<br />
        <b>Transaction Code:</b> {{ props.item.transaction_code }}
      </template>
      <template v-slot:item.payment_info="props">
        <b>Payment Date:</b> {{ props.item.paid_date }}<br />
        <span v-if="props.item.proof_of_transfer.trim() != ''"
          ><b>Proof of Payment:</b>
          <a
            :download="props.item.proof_of_transfer.trim().split('+++')[1]"
            target="_blank"
            :href="
              'https://main.buanamultiteknik.com/api/uploads/invoice' +
              props.item.id +
              '/' +
              props.item.proof_of_transfer.trim().split('+++')[0]
            "
            >Open Link</a
          ></span
        ><span v-else><b>Proof of Payment:</b> -</span><br />
        <v-btn
          color="warning"
          outlined
          small
          @click="openPaidForm(props.item)"
          :disabled="!allowPaymentInfo(props.item)"
          ><v-icon small left>mdi-pencil</v-icon>
          Edit Info
        </v-btn>
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
    <v-action-dialog @save="saveFile" title="Payment Info" v-model="dialogFile">
      <v-autoform
        :key="formFileKey"
        v-model="formFile"
        :valid="valid"
      ></v-autoform>
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
      default: true,
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
          payment_id: "notnull",
        },
      },
    },
  },
  components: {},
  data: function () {
    return {
      valid: false,
      dialogFile: false,
      formFileKey: 0,
      formData: {
        invoice_id: null,
        credit_note_id: null,
        remaining_credit_note: null,
      },
      formFile: [
        {
          text: "Payment Date",
          value: "paid_date",
          align: "start",
          type: "date",
          required: false,
        },
        {
          text: "File",
          value: "file",
          type: "file",
          required: false,
        },
        {
          text: "Payment Status",
          value: "is_paid",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          required: false,
        },
        {
          text: "Credit Note",
          value: "credit_note_id",
          align: "start",
          width: "auto",
          type: "list",
          form: false,
          url: App.url + "bom/creditnote",
          search: ["credit_no"],
          formatter: ["id", "credit_no"],
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
          text: "Remaining Credit Note",
          value: "remaining_credit_note",
          align: "start",
          width: "auto",
          type: "varchar",
          form: false,
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
          text: "Exchange Rate",
          value: "exchange_rate",
          align: "start",
          width: "auto",
          type: "varchar",
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
          text: "Admin Fee",
          value: "admin_fee",
          align: "start",
          width: "auto",
          type: "varchar",
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
      ],
      name: "Invoice",
      processData: {},
      itemKey: "id",
      url: "bom/Invoicefinance",
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
          visible: true,
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
          page: "1",
          limit: "10",
        },
        {
          text: "Payment No",
          value: "payment_no",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Invoice Detail",
          value: "invoice_detail",
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
          text: "Paid Date",
          value: "paid_date",
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
          visible: false,
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
          visible: false,
          required: false,
          form: false,
          filter: true,
          groupable: false,
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
          visible: false,
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
          filter: false,
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
          visible: false,
          required: false,
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Invoice Amount",
          value: "invoice_amount",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          filter: false,
          groupable: false,
        },
        {
          text: "Receiver's Bank Info",
          value: "bank_info",
          type: "varchar",
          form: false,
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
          text: "Payment Info",
          value: "payment_info",
          sortable: false,
          readonly: false,
          type: "varchar",
          form: false,
          visible: true,
        },
        {
          text: "Payment Status",
          value: "is_paid_info",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          required: true,
          filter: false,
          sortable: false,
        },
        {
          text: "Is Paid",
          value: "is_paid",
          type: "switch",
          data_value: [0, 1],
          default: 0,
          required: true,
          filter: false,
          sortable: false,
          visible: false,
        },
        {
          text: "Paid Status",
          value: "is_paid",
          type: "list",
          data_value: [0, 1],
          //default: 0,
          form: false,
          required: false,
          filter: true,
          sortable: false,
          visible: false,
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
          visible: false,
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
          visible: false,
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
          text: "Payment %",
          value: "payment_pct",
          readonly: false,
          type: "float",
          form: false,
          default: 100,
          filter: true,
          visible: false,
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
          visible: false,
          required: false,
          form: true,
          filter: false,
          groupable: false,
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
          visible: false,
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
          visible: false,
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
          filter: false,
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
  },
  methods: {
    toNumberSafe: function (val) {
      var num = Number(val);
      return Number.isFinite(num) ? num : 0;
    },
    displayMoney: function (val) {
      return this.toNumberSafe(val).format(2, 3);
    },
    displayNumber: function (val) {
      return this.toNumberSafe(val);
    },
    setEditFormData: function (item) {
      var self = this;
      self.formFile.map(function (field) {
        if (field.value === "file") {
          field.data = null;
          return;
        }
        if (field.value === "exchange_rate" || field.value === "admin_fee") {
          field.data = self.toNumberSafe(item[field.value]);
          return;
        }
        if (field.value === "is_paid") {
          field.data = self.toNumberSafe(item[field.value]);
          return;
        }
        field.data = item[field.value] ?? null;
      });
      self.formFile = App.updateObject(self.formFile);
    },
    fetchAndSetRemainingCredit: function (creditNoteId, invoiceId) {
      var self = this;

      // buat id request baru
      const requestId = ++self.lastCreditRequestId;

      axios
        .get(
          `https://panel.buanamultiteknik.com/api/budget/project-budget/credit-note?credit_note_id=${creditNoteId}&invoice_id=${invoiceId}`,
        )
        .then(function (response) {
          // jika bukan request terakhir → abaikan

          try {
            var remaining = response?.data?.data?.remaining;

            if (remaining === undefined || remaining === null) {
              throw new Error("Data kosong");
            }

            var formattedRemaining = new Intl.NumberFormat("id-ID", {
              style: "currency",
              currency: "IDR",
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            }).format(remaining);

            let remainingField = self.formFile.find(
              (f) => f.value === "remaining_credit_note",
            );

            if (remainingField) {
              remainingField.data = formattedRemaining;
            }

            console.log(remainingField.data);

            self.$forceUpdate();
          } catch (error) {
            let remainingField = self.formFile.find(
              (f) => f.value === "remaining_credit_note",
            );
            if (remainingField) {
              remainingField.data = 0;
            }
            console.log(remainingField.data);

            self.$forceUpdate();
          }
        })
        .catch(function () {
          let remainingField = self.formFile.find(
            (f) => f.value === "remaining_credit_note",
          );
          if (remainingField) {
            remainingField.data = 0;
          }
          console.log(remainingField.data);

          self.$forceUpdate();
        });
    },
    allowPaymentInfo: function (item) {
      var self = this;
      if (item == undefined) return false;
      if (item.approved < 3) return false;
      /* if (item.is_paid > 0)
                return false */
      return true;
    },
    openPaidForm: function (item) {
      var self = this;

      self.selected = item; // set manual supaya sinkron
      self.formData.invoice_id = self.toNumberSafe(item.id || item.invoice_id);
      self.setEditFormData(item);
      self.formFileKey += 1;

      console.log(item);
      // cari field credit_note
      let creditField = self.formFile.find((f) => f.value === "credit_note_id");

      if (item && item.credit_note_id) {
        // tampilkan field
        creditField.form = false;
        creditField.readonly = true;
        creditField.data = item.credit_note_id;

        // KODE LAMA
        // creditField.form = true;
        // creditField.readonly = true;
        // creditField.data = item.credit_note_id;
      } else {
        // sembunyikan field
        creditField.form = false;
        creditField.readonly = true;
        creditField.data = null;
      }

      self.fetchAndSetRemainingCredit(item.credit_note_id, item.id);

      self.formData.credit_note_id = item.credit_note_id;
      // remaining_credit_note
      console.log(creditField);
      this.$forceUpdate();

      self.dialogFile = true;
    },
    saveFile: async function () {
      var self = this;
      var targetId = parseInt(
        self.formData.invoice_id ||
          (self.selected || {}).id ||
          (self.selected || {}).invoice_id ||
          0,
        10,
      );
      if (!targetId) {
        App.errorMsg("Invalid invoice id");
        return;
      }
      const formData = new FormData();
      self.formFile.map(function (val, i) {
        var key = val.value;
        if (key === "file") {
          var fileData = val.data;
          var uploadedFile = null;
          if (fileData instanceof File) {
            uploadedFile = fileData;
          } else if (Array.isArray(fileData) && fileData.length > 0) {
            uploadedFile = fileData[0];
          } else if (fileData && fileData.files && fileData.files.length > 0) {
            uploadedFile = fileData.files[0];
          }
          if (uploadedFile) {
            formData.append(key, uploadedFile, uploadedFile.name || "upload.bin");
          }
          return;
        }
        formData.append(key, val.data);
      });
      formData.append("id", targetId);

      var res = await axios.post(App.url + "bom/invoice/paid", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      // try {
      //     const res = await axios.post(App.url + "bom/invoice/paid", formData, {
      //         headers: {
      //             "Content-Type": "multipart/form-data",
      //         },
      //     });

      //     console.log("Success:", res.data);

      // } catch (error) {
      //     console.log("=== ERROR API ===");
      //     console.log("Message:", error.message);

      //     if (error.response) {
      //         console.log("Status Code:", error.response.status);
      //         console.log("Response Data:", error.response.data);
      //     }

      //     if (error.request) {
      //         console.log("Request Object:", error.request);
      //     }

      //     console.log("Full Error Object:", error);
      // }

      if (!res.data.status) {
        console.log(res.data);
        App.errorMsg();
      } else {
        App.successMsg();
        self.dialogFile = false;
        self.formFileKey += 1;
        if (self.$refs.template && self.$refs.template.getItems) {
          self.$refs.template.getItems();
        }
      }
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
      } else {
        val.as_reference = parseInt(val.as_reference);
        self.selected = val;
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    openReport2: function () {
      var self = this;
      //dialogReport=true
      var name = self.selected.po_no.replace(/\//g, "_").replace(/\-/g, "_");
      var randomid = randomId();
      window.open(
        "https://main.buanamultiteknik.com/api/data/reportpo2?id=" +
          self.selected.po_id +
          "&filename=" +
          name +
          "&idx=" +
          randomid,
      );
    },
  },
  mounted: function () {
    var self = this;
  },
};
</script>
