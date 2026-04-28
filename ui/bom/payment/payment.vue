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
      show-expand
      single-expand
    >
      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          outlined
          small
          @click="revision"
          :disabled="disallowRevisi"
          v-if="history"
        >
          Revise
        </v-btn>
        <v-btn
          color="primary"
          v-if="!hideAddInvoice"
          outlined
          small
          @click="dialogItem = true"
          :disabled="allowInvoice"
        >
          Add Invoice
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="openReportWeb"
          :disabled="!selected ? true : selected.item_count == 0"
        >
          <v-icon small left>mdi-printer</v-icon>Print
        </v-btn>

        <!-- <v-btn
          v-if="check_user(['titik', 'dp', 'satrio'])"
          color="primary"
          outlined
          small
          @click="openReportWeb"
          :disabled="!selected"
        >
          <v-icon small left>mdi-print</v-icon>Print Web
        </v-btn> -->
        <!-- <v-btn color="primary" outlined small @click="dialogPaymentInfo=true" :disabled="!selected ? true :selected.item_count == 0">
                    <v-icon small left>mdi-newspaper</v-icon>Payment Info
                </v-btn> -->

        <!-- <v-btn color="primary" outlined small @click="downloadExcel" :disabled="!selected ? true :selected.item_count == 0" >
					<v-icon small left>mdi-file-excel</v-icon>Excel
				</v-btn> -->
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
          Ask Approval
        </v-btn>
        <!-- <v-btn color="primary" v-if="!hideApproval" outlined small @click="askApproval" :disabled="checkApproval2">
                    Ask Approval 2
                </v-btn> -->
        <!-- <v-btn color="primary" v-if="history" outlined small @click="toggleDone" :disabled="!selected ? true : selected.item_count == 0">
                    Toggle Done
                </v-btn> -->
        <!-- <v-btn small color="primary" outlined v-if="showCompletePo" @click="dialogComplete = true">Complete PO</v-btn> -->
      </template>
      <template v-slot:item.signed_payment_doc_url="props">
        <a
          :href="props.item.signed_payment_doc_url"
          v-if="props.item.signed_payment_doc_url"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span>
      </template>
      <template v-slot:item.payment_no="props">
        <b>Payment No:</b> {{ props.item.payment_no }}<br />
        <b>Payment Date:</b> {{ props.item.payment_date
        }}<!-- <br/>
				<b>Department:</b> {{props.item.dept_name}} -->
      </template>
      <template v-slot:item.done="props">
        {{ props.item.done == 1 ? "Done" : "-" }}
      </template>
      <template v-slot:item.approved="props">
        <b>Status:</b> {{ approvedStatus(props.item.approved) }}<br />
        <span v-if="props.item.approved1_date"
          ><b>Validated date:</b> {{ props.item.approved1_date }}<br /></span
        ><span v-else></span>
        <span v-if="props.item.approved1"
          ><b>Validated by:</b> {{ props.item.approved1 }}<br /></span
        ><span v-else></span>
        <span v-if="props.item.approved2_date"
          ><b>Approved date:</b> {{ props.item.approved2_date }}<br /></span
        ><span v-else></span>
        <span v-if="props.item.approved2"
          ><b>Approved by:</b> {{ props.item.approved2 }}</span
        ><span v-else></span>
      </template>
      <template v-slot:expanded-item="props">
        <td
          ref="expanded"
          :colspan="props.headers.length"
          style="height: auto"
          :key="props.item[itemKey]"
        >
          <payment-item
            table-only
            :data="{
              payment_id: props.item[itemKey],
            }"
            :sel="props.item"
            :key="props.item[itemKey]"
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
            :show-payment-info="showPaymentInfo"
            :history="history"
          ></payment-item>
        </td>
      </template>
      <template v-slot:item.created="props">
        <b>By:</b> {{ props.item.created_by_name }}<br />
        <b>Date:</b> {{ props.item.created_date }}<br />
      </template>
      <template v-slot:item.is_request_payment="props">
        <span>{{ props.item.is_request_payment ? "Yes" : "No" }}</span>
      </template>
      <template v-slot:item.totalrp="props">
        <span><b>Total </b>Rp. {{ props.item.totalrp }}</span
        ><br />
        <span
          ><b>Credit Note:</b> Rp.
          {{ props.item.credit_note_total || props.item.credit_notes || "0.00"
          }}<br /><br
        /></span>
        <span v-if="props.item.credit_note_total || props.item.credit_notes"
          ><b>Grand Total:</b> Rp.
          {{
            (
              parseFloat(
                (props.item.totalrp || "0").toString().replace(/,/g, ""),
              ) -
              parseFloat(
                (props.item.credit_note_total || props.item.credit_notes || "0")
                  .toString()
                  .replace(/,/g, ""),
              )
            ).toLocaleString("en-US", {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            })
          }}</span
        >
        <span v-else><b>Grand Total: </b>{{ props.item.totalrp }}</span>
      </template>
      <template v-slot:item.modified="props">
        <b>By:</b> {{ props.item.modified_by_name }}<br />
        <b>Date:</b> {{ props.item.modified_date }}<br />
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
        :history="history"
      ></payment-item>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="dialogPaymentInfo"
      title="Detail Info"
      min-height="75%"
      fullscreen
    >
      <payment-info
        :data="processData"
        :key="selected.id"
        :table-only="selected.approved != 0"
        :history="history"
      ></payment-info>
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
  name: "payment",
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
    history: {
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
        // sortBy: ["payment_date"],
        // sortDesc: ["desc"],
      },
    },
    showPaymentInfo: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    "payment-item": "url:ui/bom/payment/payment_item.vue",
    "payment-info": "url:ui/bom/payment/payment_info.vue",
  },
  data: function () {
    return {
      name: "Payment",
      processData: {},
      itemKey: "id",
      url: "bom/payment",
      dialogPaymentInfo: false,
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
          text: "Payment No",
          value: "payment_no",
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
          text: "Payment Date",
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
          text: "Notes",
          value: "notes",
          align: "start",
          sortable: false,
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
          text: "Credit Note",
          value: "credit_note_id",
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
          filter: false,
          groupable: false,
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
          text: "Validated Date",
          value: "validated_date",
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
          filter: false,
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
          text: "Approved Date",
          value: "approved_date",
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
          filter: false,
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
          text: "Approval History",
          value: "approved",
          form: false,
          visible: true,
          type: "varchar",
          align: "start",
          sortable: false,
          filterable: false,
          filter: false,
        },
        {
          text: "PO No",
          value: "po_in_payment",
          form: false,
          visible: false,
          type: "varchar",
          align: "start",
          sortable: false,
          filterable: false,
          filter: true,
        },
        {
          text: "Grand Total",
          value: "totalrp",
          form: false,
          visible: true,
          type: "varchar",
          align: "start",
          sortable: false,
          filterable: false,
          filter: false,
        },
        {
          text: "Payment Status",
          value: "is_paid",
          readonly: false,
          type: "switch",
          form: false,
          default: 100,
          data_value: [0, 1],
          visible: false,
        },
        {
          text: "Signed payment Doc URL",
          value: "signed_payment_doc_url",
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
          page: "0",
          limit: "10",
        },
        {
          text: "Request Payment?",
          value: "is_request_payment",
          type: "switch",
          data_value: [0, 1],
          required: false,
          form: true,
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
              group_name: "payment_admin",
              sub_group_name: "payment_admin",
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
              group_name: "payment_admin",
              sub_group_name: "payment_admin",
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
        {
          text: "",
          value: "data-table-expand",
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
      var self = this;
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
    allowInvoice: function () {
      var self = this;

      if (!self.selected) return true;
      if (self.selected.approved > 0) return true;
      return false;
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
    disallowRevisi: function () {
      var self = this;

      if (!self.selected) return true;
      if (self.selected.approved == 4) return false;
      return true;
    },
  },
  methods: {
    revision: async function () {
      var self = this;
      var q = confirm("Are you sure you want to revise this Payment?");
      if (!q) return true;
      else {
        try {
          var r = await axios.get(App.url + "bom/payment/revisi", {
            params: {
              id: self.selected.id,
            },
          });
          if (!r.data.status) App.errorMsg(r.data);
          else {
            self.$refs.template.getItems();
            App.successMsg();
          }
        } catch (err) {
          App.errorMsg();
        }
      }
    },
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
      var c = prompt("Page break row number?", 0);
      window.open(
        "https://main.buanamultiteknik.com/api/report/payment/index?id=" +
          self.selected.id +
          "&rand=" +
          randomId() +
          "&pagebreak=" +
          c,
      );
      // window.open('https://main.buanamultiteknik.com/api/report/payment/index?id='+self.selected.id+'&rand='+randomId()+'&pagebreak=0&debughtml=true')
    },

    openReportWeb: function () {
      var self = this;
      window.open(
        "https://main.buanamultiteknik.com/api/report/payment/index?debughtml=true&id=" +
          self.selected.id +
          "&rand=" +
          randomId() +
          "&pagebreak=0",
      );
    },
    downloadExcel: function () {
      var self = this;
      var c = prompt("Page break row number?", 0);
      window.open(
        "https://main.buanamultiteknik.com/api/report/payment/excel?id=" +
          self.selected.id +
          "&rand=" +
          randomId() +
          "&pagebreak=" +
          c,
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
    toggleDone: async function () {
      var self = this;
      var r = await axios.put(App.url + "bom/payment", {
        done: self.selected.done == 0 ? 1 : 0,
        pk: self.selected.id,
      });
      if (!r.data.status) App.errorMsg(r.data);
      else {
        self.$refs.template.getItems();
        App.successMsg();
      }
    },
    askApproval: async function (val) {
      var self = this;
      if (self.selected.approved == 0) {
        var r = await axios.put(App.url + "bom/payment", {
          approved: 1,
          pk: self.selected.id,
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      } else if (self.selected.approved == 2) {
        var r = await axios.put(App.url + "bom/payment", {
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
        return "Asking for Approval 1";
      }
      if (f == 2) {
        return "Approved Approval 1";
      }
      if (f == -1) {
        return "Rejected Approval 1";
      }
      if (f == 3) {
        return "Asking for Approval 2";
      }
      if (f == 4) {
        return "Approved Approval 2";
      }
      if (f == -2) {
        return "Rejected Approval 2";
      }
    },
  },
  mounted: function () {
    var self = this;
    self.defaultForm = JSONfn.parse(JSONfn.stringify(self.headers));
    if (self.history) {
      self.headersObj.validated_date.filter = true;
      self.headersObj.approved_date.filter = true;
      self.headers = App.updateObject(self.headers);
    }
  },
};
</script>
