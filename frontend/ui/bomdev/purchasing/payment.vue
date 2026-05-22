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
      :disable-delete-button="disableDeleteButton"
      :hide-delete-button="hideDeleteButton"
      :hide-add-button="hideAddButton"
      @open-edit="onOpenEdit"
      @open-add="onOpenEdit(true)"
      :items-options="itemsOptions"
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
        <v-btn
          color="primary"
          v-if="!hideAddInvoice"
          outlined
          small
          @click="dialogItem = true"
          :disabled="!selected"
        >
          Add Invoice
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="openReport"
          :disabled="!selected"
        >
          <v-icon small left>mdi-print</v-icon>Print
        </v-btn>
        <!-- <v-btn color="primary" outlined small @click="askApproval" :disabled="!selected">
                    Ask Approval
                </v-btn> -->
        <v-btn
          color="primary"
          v-if="!hideApproval"
          outlined
          small
          @click="askApproval"
          :disabled="checkApproval1"
        >
          Ask Approval 1
        </v-btn>
        <v-btn
          color="primary"
          v-if="!hideApproval"
          outlined
          small
          @click="askApproval"
          :disabled="checkApproval2"
        >
          Ask Approval 2
        </v-btn>
        <!-- <v-btn small color="primary" outlined v-if="showCompletePo" @click="dialogComplete = true">Complete PO</v-btn> -->
      </template>
      <!-- <template v-slot:item.approved="props">
				{{approvedStatus(props.item.approved)}}
			</template> -->
      <template v-slot:item.payment_no="props">
        <b>Payment no:</b> {{ props.item.payment_no }}<br />
        <b>Payment date:</b> {{ props.item.payment_date
        }}<!-- <br/>
				<b>Department:</b> {{props.item.dept_name}} -->
      </template>
      <template v-slot:item.approved="props">
        <b>Status:</b> {{ approvedStatus(props.item.approved) }}<br />
        <b>Validated date:</b> {{ props.item.approved1_date }}<br />
        <b>Validated by:</b> {{ props.item.approved1 }}<br />
        <b>Approved date:</b> {{ props.item.approved2_date }}<br />
        <b>Approved by:</b> {{ props.item.approved2 }}
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="dialogItem"
      title="Detail"
      min-height="75%"
      fullscreen
      :key="selected.id"
    >
      <payment-item
        :data="processData"
        :key="selected.id"
        :table-only="selected.approved != 0"
      ></payment-item>
    </v-action-dialog>
    <!-- <v-action-dialog :actions="false" v-model="dialogComplete" title="Complete PO" min-height="75%" fullscreen>
            <complete-po></complete-po>
        </v-action-dialog> -->
  </v-container>
</template>

<style scoped>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}
</style>

<script>
module.exports = {
  name: "",
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    showCompletePo: {
      type: Boolean,
      default: false,
    },
    disableDeleteButton: {
      type: Boolean,
      default: false,
    },
    hideDeleteButton: {
      type: Boolean,
      default: false,
    },
    hideAddButton: {
      type: Boolean,
      default: false,
    },
    hideApproval: {
      type: Boolean,
      default: false,
    },
    hideAddInvoice: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          approved: "0,3",
        },
        filterType: {
          approved: "btw",
        },
        sortBy: ["payment_date"],
        sortDesc: ["desc"],
      },
    },
  },
  components: {
    "payment-item": App.ui + "purchasing/payment_item.vue",
    "complete-po": App.ui + "purchasing/complete_po.vue",
  },
  data: function () {
    return {
      name: "Payment",
      processData: {},
      itemKey: "id",
      url: App.folderRoot + "payment",
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
          text: "Payment",
          value: "payment_no",
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
          text: "payment date",
          value: "payment_date",
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
          default: new Date().formatDate("YYYY-MM-DD"),
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
          text: "notes",
          value: "notes",
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
          value: "dept_name",
          form: false,
          visible: false,
          type: "varchar",
          align: "start",
          sortable: true,
          filterable: false,
          filter: true,
        },
        {
          text: "Approval History",
          value: "approved",
          form: false,
          visible: true,
          type: "varchar",
          align: "start",
          sortable: true,
          filterable: false,
          filter: true,
        },
        {
          text: "signed payment doc url",
          value: "signed_payment_doc_url",
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
      ],
      dialogComplete: false,
      dialogItem: false,
      selected: false,
      dataid: {},
      defaultForm: [],
    };
  },
  watch: {
    dialogItem: function (val) {
      if (!val) {
        self.$refs.template.getItems();
      }
    },
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
    checkApproval1: function () {
      var self = this;
      if (self.selected.item_count < 1) return true;
      if (self.selected.approved < 0) return true;
      if (!self.selected) return true;
      if (self.selected.approved == 0 || self.selected.approved == -1)
        return false;
      return true;
    },
    checkApproval2: function () {
      var self = this;
      if (self.selected.item_count < 1) return true;
      if (self.selected.approved < 0) return true;
      if (!self.selected) return true;
      if (self.selected.approved == 2 || self.selected.approved == -2)
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
        self.headersObj.signed_payment_doc_url.form = true;
      } else {
        self.headers = JSONfn.parse(JSONfn.stringify(self.defaultForm));
      }
      if (add != true) {
        self.headers.map(function (val) {
          val.data = self.selected[val.value];
        });
      }
    },
    openReport: function () {
      var self = this;
      window.open(
        "https://main.buanamultiteknik.com/api/report/payment/index?id=" +
          self.selected.id,
      );
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
      } else {
        if (val.approved != 0) {
          self.disableDeleteButton = true;
        } else self.disableDeleteButton = false;
        self.selected = val;
        self.processData = {
          payment_id: val.id,
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
        var r = await axios.put(App.model + "payment", {
          approved: 1,
          pk: self.selected.id,
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      } else if (self.selected.approved == 2) {
        var r = await axios.put(App.model + "payment", {
          approved: 3,
          pk: self.selected.id,
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      if (self.selected.approved == 1) {
        App.errorMsg("Telah ada permintaan approve (Approval 1)!");
      }
      /* if(self.selected.approved==2){
					App.errorMsg('Telah di approve (Approval 1)!')
				} */
      if (self.selected.approved == 3) {
        App.errorMsg("Telah ada permintaan approve (Approval 2)!");
      }
      if (self.selected.approved == 4) {
        App.errorMsg("Telah di approve (Approval 2)!");
      }
      if (self.selected.approved == -1) {
        App.errorMsg("Telah di reject (Approval 1)!");
      }
      if (self.selected.approved == -2) {
        App.errorMsg("Telah di reject (Approval 2)!");
      }
    },
    approvedStatus: function (f) {
      if (f == 0) {
        return "New";
      }
      if (f == 1) {
        return "Asking for approval (Approval 1)";
      }
      if (f == 2) {
        return "Approved (Approval 1)";
      }
      if (f == -1) {
        return "Rejected (Approval 1)";
      }
      if (f == 3) {
        return "Asking for approval (Approval 2)";
      }
      if (f == 4) {
        return "Approved (Approval 2)";
      }
      if (f == -2) {
        return "Rejected (Approval 2)";
      }
    },
  },
  mounted: function () {
    var self = this;
    self.defaultForm = JSONfn.parse(JSONfn.stringify(self.headers));
  },
};
</script>
