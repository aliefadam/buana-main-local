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
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :items-options="itemsOptions"
      :name="name"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      :table-only="tableOnly"
      :show-expand="true"
      :single-expand="true"
    >
      <template v-slot:menu-after-filter>
        <!-- <v-btn color="primary" outlined small @click="dialogItem = true" :disabled="!selected">
                    Add Invoice
                </v-btn> -->
        <v-btn
          v-if="type == 2"
          color="primary"
          outlined
          small
          @click="openReport"
          :disabled="!selected"
        >
          <v-icon small left>mdi-print</v-icon>Print
        </v-btn>
        <v-btn
          color="primary"
          v-if="type == 1"
          outlined
          small
          @click="approve"
          :disabled="checkApproval1"
        >
          Approve
        </v-btn>
        <v-btn
          color="primary"
          v-if="type == 2"
          outlined
          small
          @click="approve"
          :disabled="checkApproval2"
        >
          Approve
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="reject"
          :disabled="!selected"
        >
          Reject
        </v-btn>
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
            :key="props.item[itemKey]"
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
          ></payment-item>
        </td>
      </template>
      <template v-slot:item.payment_no="props">
        <b>Payment no:</b> {{ props.item.payment_no }}<br />
        <b>Payment date:</b> {{ props.item.payment_date }}
      </template>
      <template v-slot:item.approved="props">
        <b>Status:</b> {{ approvedStatus(props.item.approved) }}<br />
        <b>Validated date:</b> {{ props.item.approved1_date }}<br />
        <b>Validated by:</b> {{ props.item.approved1 }}<br />
        <b>Approved date:</b> {{ props.item.approved2_date }}<br />
        <b>Approved by:</b> {{ props.item.approved2 }}
      </template>
    </v-template>
    <!-- <v-action-dialog :actions="false" v-model="dialogItem" title="Detail" min-height="75%" fullscreen
            :key="selected.id">
            <payment-item :data="processData" :key="selected.id"></payment-item>
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
    type: {
      default: 0,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          approved: 1,
        },
      },
    },
  },
  components: {
    "payment-item": App.ui + "purchasing/payment_item.vue",
  },
  data: function () {
    return {
      name: "Invoice",
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
        { text: "", value: "data-table-expand" },
      ],
      dialogItem: false,
      selected: false,
      dataid: {},
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
    checkApproval1: function () {
      var self = this;
      if (!self.selected) return true;
      if (self.selected.approved == 1) return false;
      return true;
    },
    checkApproval2: function () {
      var self = this;
      if (!self.selected) return true;
      if (self.selected.approved == 3) return false;
      return true;
    },
  },
  methods: {
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
    approve: async function () {
      var self = this;
      var params = {
        approved: self.selected.approved == 1 ? 2 : 4,
        pk: self.selected.id,
      };
      if (self.selected.approved == 1) {
        params.approved1 = App.userData.data[0].name;
        params.approved1_date = new Date().formatDate("YYYY-MM-DD HH:mm:ss");
      }
      if (self.selected.approved == 3) {
        params.approved2 = App.userData.data[0].name;
        params.approved2_date = new Date().formatDate("YYYY-MM-DD HH:mm:ss");
      }
      var r = await axios.put(App.model + "payment", params);
      if (!r.data.status) App.errorMsg(r.data);
      else {
        self.$refs.template.getItems();
        App.successMsg();
      }
    },
    comment: async function () {
      var self = this;
      var r = await axios.post(App.model + "payment_comment", {
        notes: self.po_comment,
        payment_id: self.selected.id,
      });
      if (!r.data.status) App.errorMsg(r.data);
      else {
        self.$refs.template.getItems();
        App.successMsg();
        self.dialogComment = false;
        self.$refs.template.getItems();
      }
    },
    reject: async function () {
      var self = this;
      var r = await axios.put(App.model + "payment", {
        approved: self.selected.approved == 1 ? -1 : -2,
        pk: self.selected.id,
      });
      if (!r.data.status) App.errorMsg(r.data);
      else {
        self.$refs.template.getItems();
        App.successMsg();
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
  mounted: function () {},
};
</script>
