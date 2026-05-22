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
      :hide-delete-button="hideDeleteButton"
      :hide-add-button="hideAddButton"
      :items-options="itemsOptions"
      @open-edit="onOpenEdit"
      @open-add="onOpenEdit(true)"
      :disable-edit-button="disableEdit"
      :disable-delete-button="disableDelete"
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
      <template v-slot:menu-after-filter>
        <!-- <v-btn color="primary" outlined small @click="dialogItem = true" :disabled="!selected">
                    <v-icon small left>mdi-plus</v-icon>Add Item
                </v-btn> -->
        <v-btn
          color="primary"
          outlined
          small
          @click="dialogItemGroup = true"
          :disabled="!selected"
        >
          <!-- <v-icon small left>mdi-plus</v-icon> -->Show Items
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="openReport"
          :disabled="allowPrint"
        >
          <v-icon small left>mdi-printer</v-icon
          ><template v-if="!hideAddButton">Preview</template
          ><template v-else>Print</template>
        </v-btn>
        <v-btn
          color="primary"
          v-if="!hideApproval"
          outlined
          small
          @click="askApproval"
          :disabled="allowAskApproval"
        >
          Ask Approval
        </v-btn>
        <v-btn
          small
          color="primary"
          outlined
          v-if="hideAddButton"
          @click="dialogComplete = true"
          >Complete PO</v-btn
        >
      </template>
      <template v-slot:item.po_no="props">
        <b>PO no:</b> {{ props.item.po_no }}<br />
        <b>PO Date:</b> {{ props.item.po_date }}<br />
        <b>Department:</b> {{ props.item.dept_name }}<br />
        <b>Order Type:</b> {{ props.item.order_type }}
      </template>
      <template v-slot:item.currency_desc="props">
        <b>Currency:</b> {{ props.item.currency }}<br />
        <b>Exchange Rate:</b> {{ Number(props.item.exchange_rate).format(2, 3)
        }}<br />
        <b>Rate Date:</b> {{ props.item.rate_date }}
      </template>
      <template v-slot:item.supplier="props">
        <b>Supplier:</b> {{ props.item.supplier_name }}<br />
        <b>Promised Delivery Date:</b> {{ props.item.promised_delivery_date }}
      </template>
      <template v-slot:item.final_quote_url="props">
        <b>Final Quote URL:</b>
        <a
          :href="props.item.final_quote_url"
          v-if="props.item.final_quote_url"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span><br />
        <b>Signed PO URL:</b>
        <a
          :href="props.item.signed_po_url"
          v-if="props.item.signed_po_url"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span>
      </template>
      <template v-slot:item.approved="props">
        {{ approvedStatus(props.item.approved) }}
      </template>
      <template v-slot:item.charge1="props">
        <b>Charge 1:</b> {{ Number(props.item.charge1).format(2, 3) }}<br />
        <b>Charge 1 Desc:</b> {{ props.item.charge1_desc }}<br />
        <b>Charge 2:</b> {{ Number(props.item.charge2).format(2, 3) }}<br />
        <b>Charge 2 Desc:</b> {{ props.item.charge2_desc }}
      </template>
      <!-- <template v-slot:item.charge2="props">
				{{Number(props.item.charge2).format(3,3)}}
			</template> -->
    </v-template>
    <!-- <v-action-dialog :actions="false" v-model="dialogItem" title="Purchase Order Item" min-height="75%" fullscreen>
            <purchase-item :key="selected.id" :sel="processData" name="" :data="dataid"></purchase-item>
        </v-action-dialog> -->
    <v-action-dialog
      :actions="false"
      v-model="dialogItemGroup"
      title="Purchase Order Item"
      min-height="75%"
      fullscreen
    >
      <purchase-item-group
        :key="selected.id"
        :sel="processData"
        name=""
        :data="dataid"
        :is-dashboard="!allowPrint2 || hideAddButton"
      ></purchase-item-group>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="dialogReport"
      title="Purchase Order Report"
      min-height="75%"
      fullscreen
    >
      <report
        :key="selected.id"
        :sel="processData"
        name=""
        :data="dataid"
      ></report>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="dialogComplete"
      title="Complete PO"
      min-height="75%"
      fullscreen
    >
      <complete-po></complete-po>
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
  name: "po",
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    hideApproval: {
      type: Boolean,
      default: false,
    },
    hideAddButton: {
      type: Boolean,
      default: false,
    },
    hideDeleteButton: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          approved: "0,1",
        },
        filterType: {
          approved: "btw",
        },
      },
    },
  },
  components: {
    "purchase-item": App.ui + "purchasing/purchaseorderitem.vue",
    report: App.ui + "report/purchaseorder.vue",
    "purchase-item-group": App.ui + "purchasing/purchaseorderitemgroup.vue",
    "complete-po": App.ui + "purchasing/complete_po.vue",
  },
  data: function () {
    return {
      disableDelete: false,
      disableEdit: false,
      name: "Purchase Order",
      processData: {},
      itemKey: "id",
      url: App.folderRoot + "purchaseorder",
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
          page: "0",
          limit: "10",
        },
        {
          text: "PO Details",
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
          page: "0",
          limit: "10",
        },
        {
          text: "Status",
          value: "approved",
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
        },
        {
          text: "PO NO",
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
          text: "po date",
          value: "po_date",
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
          default: new Date().formatDate("YYYY-MM-DD"),
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
          page: "0",
          limit: "10",
        },
        {
          text: "Department",
          value: "dept_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          data_value: [],
          visible: false,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          url: App.model + "department",
          searchby: ["id"],
          formatter: ["id", "dept_name"],
          pk: "id",
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "dept_name",
        },
        {
          text: "Ref. Offer No",
          value: "ref_offer_no",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Ref. Internal BMT",
          value: "ref_internal_bmt",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "order type",
          value: "order_type",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          default: "Lokal",
          data_value: ["Lokal", "Import"],
        },
        {
          text: "Supplier",
          value: "supplier_id",
          align: "start",
          sortable: true,
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
          filter: true,
          groupable: false,
          url: App.model + "supplier",
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
          alias: "supplier_name",
        },
        {
          text: "Promised delivery date",
          value: "promised_delivery_date",
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
          page: "0",
          limit: "10",
        },
        {
          text: "Currency",
          value: "currency",
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
          form: true,
          filter: true,
          groupable: false,
          default: "IDR",
          data_value: ["IDR", "CNY", "EUR", "USD"],
          input: function (data) {
            var self = App.page();
            if (data.data.trim().toLowerCase() != "idr") {
              self.headersObj.exchange_rate.required = true;
              self.headersObj.rate_date.required = true;
            } else {
              self.headersObj.exchange_rate.required = false;
              self.headersObj.rate_date.required = false;
            }

            self.headers = App.updateObject(self.headers);
          },
        },
        {
          text: "exchange rate",
          value: "exchange_rate",
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
          page: "0",
          limit: "10",
        },
        {
          text: "rate date",
          value: "rate_date",
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
          page: "0",
          limit: "10",
        },
        {
          text: "PO Title",
          value: "title",
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
          filter: true,
          groupable: false,
        },
        {
          text: "PO Notes",
          value: "note",
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
          filter: true,
          groupable: false,
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
          form: true,
          filter: true,
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
          form: true,
          filter: true,
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
          form: true,
          filter: true,
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
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Supplier",
          value: "supplier",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: true,
          form: false,
          data_value: [],
          filter: false,
          groupable: false,
          url: App.model + "supplier",
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
          alias: "supplier_name",
        },
        {
          text: "Documents",
          value: "final_quote_url",
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
          page: "0",
          limit: "10",
        },
        {
          text: "Currency",
          value: "currency_desc",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          default: "IDR",
          data_value: ["IDR", "CNY", "EUR", "USD"],
          input: function (data) {
            var self = App.page();
            if (data.data.trim().toLowerCase() != "idr") {
              self.headersObj.exchange_rate.required = true;
              self.headersObj.rate_date.required = true;
            } else {
              self.headersObj.exchange_rate.required = false;
              self.headersObj.rate_date.required = false;
            }

            self.headers = App.updateObject(self.headers);
          },
        },
        {
          text: "PO Charge",
          value: "charge1",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Final Quote URL",
          value: "final_quote_url",
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
          text: "Signed PO URL",
          value: "signed_po_url",
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
          text: "Last Revised Comment",
          value: "comment",
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
          filter: false,
          groupable: false,
        },
      ],
      dialogItem: false,
      dialogItemGroup: false,
      dialogReport: false,
      selected: false,
      dialogComplete: false,
      dataid: {},
      defaultForm: [],
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
    allowPrint: function () {
      var self = this;
      if (!self.selected) return true;
      if (self.selected.approved == -1) return true;
      /* if(self.selected.approved == 2)
					return false */
      return false;
    },
    allowPrint2: function () {
      var self = this;
      if (!self.selected) return true;
      if (self.selected.approved == 2) return false;
      return true;
    },
    allowAskApproval: function () {
      var self = this;
      if (self.selected.item_count < 1) return true;
      if (self.selected.approved < 0) return true;
      if (!self.selected) return true;
      if (self.selected.approved == 0 || self.selected.approved == -1)
        return false;
      return true;
    },
  },
  methods: {
    onOpenEdit: function (add) {
      var self = this;
      if (self.selected.approved >= 2 && add != true) {
        self.headers.map(function (val) {
          val.form = false;
        });
        self.headersObj.signed_po_url.form = true;
      } else {
        self.headers = JSONfn.parse(JSONfn.stringify(self.defaultForm));
      }
      if (add != true) {
        self.headers.map(function (val) {
          if (self.selected[val.value] !== undefined)
            val.data = self.selected[val.value];
        });
      }
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
        self.dataid = {};
        self.disableDelete = false;
        //self.disableEdit = false
      } else {
        self.selected = val;
        self.processData = {
          purchase_order_id: val.id,
          supplier_id: val.supplier_id,
          currency: val.currency,
          charge1: val.charge1,
          charge2: val.charge2,
        };
        if (val.approved == 2 || val.approved == 1) {
          self.disableDelete = true;
          //self.disableEdit = true
        } else {
          self.disableDelete = false;
          //self.disableEdit = false
        }
        if (val.approved == 1) {
          self.disableEdit = true;
        }
        if (val.approved < 0) {
          self.disableEdit = true;
        }
        if (val.approved == 2) {
          self.disableEdit = false;
        }
        self.dataid = {
          purchase_order_id: val.id,
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    askApproval: async function (val) {
      var self = this;
      if (self.selected.approved == 0) {
        var r = await axios.put(App.model + "purchaseorder", {
          approved: 1,
          pk: self.selected.id,
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      if (self.selected.approved == 1) {
        App.errorMsg("Telah ada permintaan approve!");
      }
      if (self.selected.approved == 2) {
        App.errorMsg("Telah di approve!");
      }
      if (self.selected.approved == -1) {
        App.errorMsg("Telah di reject!");
      }
    },
    approvedStatus: function (f) {
      if (f == 0) {
        return "New";
      }
      if (f == 1) {
        return "Asking for approval";
      }
      if (f == 2) {
        return "Approved";
      }
      if (f == -1) {
        return "Rejected";
      }
    },
    openReport: function () {
      var self = this;
      //dialogReport=true
      var name = self.selected.po_no.replace(/\//g, "_").replace(/\-/g, "_");
      window.open(
        "https://decafet.com/api/report?type=pdf&file=po&filename=" +
          name +
          "&engine=easytemplate&po_id=" +
          self.selected.id,
      );
      //window.open('https://main.buanamultiteknik.com/api/report/po/index?po_id='+self.selected.id)
    },
  },
  mounted: function () {
    var self = this;
    self.defaultForm = JSONfn.parse(JSONfn.stringify(self.headers));
  },
};
</script>
