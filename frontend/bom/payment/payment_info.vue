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
    <v-card outlined style="margin: 8px; background-color: #2b688c">
      <v-list-item two-line>
        <v-list-item-content>
          <div class="text-overline font-weight-bold text-white mb-4">
            {{ data.title }}
          </div>
          <v-list-item-subtitle>
            <v-row no-gutters dense>
              <v-col
                cols="6"
                md="3"
                lg="1"
                class="text-subtitle font-weight-bold text-white"
                >Payment No</v-col
              >
              <v-col
                cols="6"
                md="3"
                lg="2"
                class="text-subtitle font-weight-bold text-white"
                >:</v-col
              >
              <v-col
                cols="6"
                md="3"
                lg="1"
                class="text-subtitle font-weight-bold text-white"
                >Notes</v-col
              >
              <v-col
                cols="6"
                md="3"
                lg="2"
                class="text-subtitle font-weight-bold text-white"
                >:</v-col
              >
              <v-col
                cols="6"
                md="3"
                lg="1"
                class="text-subtitle font-weight-bold text-white"
                >Grand Total Price</v-col
              >
              <v-col
                cols="6"
                md="3"
                lg="2"
                class="text-subtitle font-weight-bold text-white"
                >:</v-col
              >
            </v-row>
          </v-list-item-subtitle>
        </v-list-item-content>
      </v-list-item>
    </v-card>
    <v-template
      :item-height-as-min-height="itemHeightAsMinHeight"
      :table-fill="tableFill"
      :table-fixed-header="tableFixedHeader"
      :show-expand="showExpand"
      :single-expand="singleExpand"
      :data="data"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      :table-only="tableOnly"
      :hide-filter-button="hidefilterbutton"
      :hide-add-button="hideAddButton"
      :hide-edit-button="hideEditButton"
      :hide-delete-button="hideDeleteButton"
    >
      <template v-slot:item.invoice_total_price="props">
        {{ Number(props.item.invoice_total_price).format(2, 3) }}
      </template>
      <template v-slot:item.po_no="props">
        <a
          :href="
            'https://decafet.com/api/report?type=pdf&file=po&filename=' +
            props.item.po_no.replace(/\//g, '_').replace(/\-/g, '_') +
            '&engine=easytemplate&po_id=' +
            props.item.po_id
          "
          target="_blank"
          v-if="props.item.po_no"
          >{{ props.item.po_no }}</a
        ><span v-else>{{ props.item.po_no }}</span>
        <b> {{ props.item.as_reference == 0 ? "" : "As Reference" }}</b>
      </template>
      <template v-slot:item.currency="props">
        <b>Currency:</b> {{ props.item.currency }}<br />
        <b>Total Price:</b>
        {{ Number(props.item.invoice_total_price).format(2, 3) }}<br />
        <b>PCT:</b> {{ Number(props.item.payment_pct) }} %<br />
      </template>
      <template v-slot:item.title="props">
        <span>{{
          props.item.as_reference == 0 ? props.item.title : props.item.uraian
        }}</span>
      </template>
      <template v-slot:item.reference_detail="props">
        <b>Title</b> :{{ props.item.title }}<br />
        <b>Supplier</b> :{{ props.item.supplier_name }}<br />
        <b>Proof of Invoice:</b>
        <a
          :href="props.item.proof_url"
          v-if="props.item.proof_url"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span><br />
      </template>
      <template v-slot:item.payment_info="props">
        <b>Receiver Bank No:</b> {{ props.item.message }}<br />
        <b>Amount:</b> {{ props.item.bank_account_no }}<br />
        <b>Remark:</b> {{ props.item.bank_account_name }}<br />
        <b>Proof of Payment:</b>
        <a
          :download="props.item.proof_of_transfer.trim().split('+++')[1]"
          :href="
            'https://main.buanamultiteknik.com/api/uploads/invoice' +
            props.item.invoice_id +
            '/' +
            props.item.proof_of_transfer.trim().split('+++')[0]
          "
          v-if="props.item.proof_of_transfer"
          target="_blank"
          >Open Link</a
        >
      </template>
      <template v-slot:item.status="props">
        <v-icon v-if="props.item.status == 0" color="error"
          >mdi-close-thick</v-icon
        >
        <v-icon v-else color="success">mdi-check-bold</v-icon>
        <br />
      </template>
    </v-template>
  </v-container>
</template>

<style scoped>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}
</style>

<script>
module.exports = {
  name: "paymentitem",
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
    history: {
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
    hideAddButton: {
      type: Boolean,
      default: true,
    },
    hideEditButton: {
      type: Boolean,
      default: true,
    },
    hideDeleteButton: {
      type: Boolean,
      default: true,
    },
  },
  data: function () {
    return {
      valid: false,
      dialogFile: false,
      name: "Payment",
      itemKey: "id",
      url: "bom/payment_info",
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
          type: "list",
          data_value: [],
          disabled: false,
          visible: true,
          required: false,
          form: true,
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
          page: "0",
          limit: "10",
        },
        {
          text: "Reference Detail",
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
          text: "Title",
          value: "title",
          type: "varchar",
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
          text: "Amount",
          value: "currency",
          type: "varchar",
          form: false,
        },
        {
          text: "Invoice Notes",
          value: "invoice_payment_notes",
          readonly: false,
          type: "text",
        },
        {
          text: "Payment Info",
          value: "payment_info",
          type: "varchar",
          form: false,
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
          text: "Payment Status",
          value: "status",
          readonly: false,
          align: "center",
          type: "switch",
          form: false,
          default: 0,
          data_value: [0, 1],
          visible: true,
        },
      ],
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
  },
  methods: {
    getData: async function () {
      var self = this;
      var res = await axios.get(App.url + "bom/payment", {
        params: {
          filter: {
            pk: self.selected.payment_id,
          },
        },
      });

      if (!res.data.status) App.errorMsg();
      else {
        self.data = res.data.data[0];
        App.title = "#" + self.data.id;
      }
    },
  },
  mounted: async function () {
    var self = this;
    await Promise.all([Promise.resolve(self.getData())]);
  },
};
</script>
