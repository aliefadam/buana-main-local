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
      hide-add-invoice
      hide-approval
      hide-delete-button
      hide-add-button
      hide-edit-button
      show-complete-po
      @open-edit="onOpenEdit"
      @open-add="onOpenEdit(true)"
      :items-options="itemsOptions"
      v-model="value"
      ref="template"
      :url="url"
      :headers="headers"
      :name="name"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      :table-only="tableOnly"
      show-expand
      single-expand
    >
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          outlined
          small
          @click="openReport"
          :disabled="!selected ? true : selected.item_count == 0"
        >
          <v-icon small left>mdi-printer</v-icon>Print
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="openPreview"
          :disabled="!selected ? true : selected.item_count == 0"
        >
          <v-icon small left>mdi-printer</v-icon>Preview
        </v-btn>
        <!-- <v-btn color="primary" outlined small @click="downloadExcel" :disabled="!selected ? true :selected.item_count == 0" >
					<v-icon small left>mdi-file-excel</v-icon>Excel
				</v-btn> -->
        <v-menu offset-y>
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="primary" dark v-bind="attrs" v-on="on" outlined small
              ><v-icon small left>mdi-file-excel</v-icon>
              Excel
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="a('BCA')">BCA</v-list-item>
            <v-list-item @click="a('LLG')">LLG</v-list-item>
          </v-list>
        </v-menu>
      </template>
      <template v-slot:item.signed_payment_doc_url="props">
        <a
          :href="props.item.signed_payment_doc_url"
          v-if="props.item.signed_payment_doc_url"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span>
      </template>
      <template v-slot:item.btn_payment_info="props">
        <v-btn color="primary" outlined small @click="openPaidForm(props.item)">
          Payment Info
        </v-btn>
      </template>
      <template v-slot:item.payment_no="props">
        <b>No:</b> {{ props.item.payment_no }}<br />
        <b>Date:</b> {{ props.item.payment_date }}<br />
        <b>Created By:</b> {{ props.item.created_by_name }}<br />
        <b>Created Date:</b> {{ props.item.created_date }}<br />
        <!-- <b>Modified By:</b> {{props.item.modified_by_name}}<br/>
				<b>Modified Date:</b> {{props.item.modified_date}}<br/> -->
      </template>
      <template v-slot:item.done="props">
        {{ props.item.done == 1 ? "Done" : "-" }}
      </template>
      <template v-slot:item.approved="props">
        <b>Status:</b> {{ approvedStatus(props.item.approved) }}<br />
        <b>Validated date:</b> {{ props.item.approved1_date }}<br />
        <b>Validated by:</b> {{ props.item.approved1 }}<br />
        <b>Approved date:</b> {{ props.item.approved2_date }}<br />
        <b>Approved by:</b> {{ props.item.approved2 }}
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
          ></payment-item>
        </td>
      </template>
      <template v-slot:item.created="props">
        <b>By:</b> {{ props.item.created_by_name }}<br />
        <b>Date:</b> {{ props.item.created_date }}<br />
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
      ></payment-item>
    </v-action-dialog>
    <v-action-dialog
      @save="saveFile"
      title="List Transfer Info"
      v-model="dialogFile"
    >
      <v-autoform v-model="formFile" :valid="valid"></v-autoform>
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
  name: "Transfer List",
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
    showCompletePo: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          approved: "0, 1, 2, 3,-2,-1,4",
        },
        filterType: {
          approved: "in",
        },
      },
    },
  },
  components: {
    "payment-item": "url:ui/bom/finance/list_transfer_item_finance.vue",
  },

  data: function () {
    return {
      items: [{ title: "BCA" }, { title: "LLG" }],
      valid: false,
      dialogFile: false,
      name: "Transfer List",
      processData: {},
      itemKey: "id",
      url: "bom/list_transfer_finance",
      formFile: [
        {
          text: "Transaction Date",
          value: "eff_date",
          default: new Date().formatDate("YYYY-MM-DD"),
          align: "start",
          type: "date",
          required: true,
        },
        {
          text: "Charges Type",
          value: "charges_type",
          align: "start",
          type: "list",
          default: "OUR",
          data_value: [
            {
              text: "Biaya dibebankan kepada pengirim",
              value: "OUR",
            },
            {
              text: "Biaya dibebankan kepada penerima",
              value: "BEN",
            },
            {
              text: "Biaya dibebankan 50:50 kepada pengirim dan penerima",
              value: "SHA",
            },
          ],
          required: true,
          form: false,
          visible: false,
        },
        {
          text: "Email",
          value: "beneficiary_email",
          align: "start",
          type: "text",
          required: false,
          disabled: true,
          form: false,
          visible: false,
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
          page: "0",
          limit: "10",
        },
        {
          text: "Transfer List",
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
          text: "Date",
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
          text: "Grand Total Price (RP)",
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
          text: "Status",
          value: "",
          align: "start",
          sortable: false,
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
          text: "Payment Status",
          value: "status_pembayaran",
          readonly: false,
          type: "list",
          form: false,
          filter: true,
          //default: 100,
          data_value: ["0", "1"],
          visible: false,
        },
        {
          text: "Signed Doc URL",
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
          value: "btn_payment_info",
          type: "varchar",
          sortable: "false",
          visible: true,
          form: false,
          filter: false,
        },
        {
          text: "",
          value: "data-table-expand",
        },
      ],
      dialogComplete: false,
      dialogItem: false,
      selected: false,
      selectedAll: false,
      paidFormSelected: false,
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
    formfileObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.formFile).map((key) => {
        var val = self.formFile[key];
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
    disallowRevisi: function () {
      var self = this;

      if (!self.selected) return true;
      if (self.selected.approved == 4) return false;
      return true;
    },
  },
  methods: {
    a: function (val) {
      var self = this;
      {
        console.log(val);
      }
      window.open(
        "https://main.buanamultiteknik.com/api/bom/list_transfer_finance/excelbca?id=" +
          self.selected.id +
          "&transfer_type=" +
          val +
          "&rand=" +
          randomId(),
      );
    },
    // saveFile: async function() {
    //     var self = this;
    //     const formData = new FormData();
    //     self.formFile.map(function(val, i) {
    //         var key = val.value;
    //         formData.append(key, val.data);
    //     });
    //     formData.append("id", self.selected.id);
    //     var res = await axios.post(App.url + "bom/list_transfer_finance/paid", formData, {
    //         headers: {
    //             "Content-Type": "multipart/form-data",
    //         },
    //     });
    //     if (!res.data.status) {
    //         App.errorMsg();
    //     } else {
    //         App.successMsg();

    //         self.dialogFile = false;
    //     }
    // },
    saveFile: async function () {
      var self = this,
        sel = self.paidFormSelected;
      const formData = {};
      self.formFile.map(function (val, i) {
        var key = val.value;
        formData[key] = val.data;
      });
      formData["id"] = sel.id;
      var res = await axios.post(
        App.url + "bom/list_transfer_finance/paid",
        formData,
      );
      if (!res.data.status) {
        App.errorMsg();
      } else {
        App.successMsg();

        self.dialogFile = false;
      }
    },
    openPaidForm: function (item) {
      var self = this;
      self.paidFormSelected = item;
      // if (self.selected.is_paid == 1) App.errorMsg("Invoice has been paid!");
      // else
      self.dialogFile = true;
    },
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
    },
    openPreview: function () {
      var self = this;
      //var c = prompt('Page break row number?', 0)
      window.open(
        "https://main.buanamultiteknik.com/api/report/payment/index?id=" +
          self.selected.id +
          "&rand=" +
          randomId() +
          "&pagebreak=0&debughtml=true",
      );
    },
    downloadExcel: function () {
      var self = this;
      console.log(self.selected);
      // window.open('https://main.buanamultiteknik.com/api/bom/list_transfer_finance/excelbca?id='+self.selected.id+'&rand='+randomId())
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
        self.formfileObj.eff_date.data = val.eff_date;
        self.formfileObj.charges_type.data = val.charges_type;
        self.formfileObj.beneficiary_email.data = val.beneficiary_email;
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
        return "Approved (Approval 1)";
      }
      if (f == -1) {
        return "Rejected (Approval 1)";
      }
      if (f == 3) {
        return "Asking for Approval 2)";
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
