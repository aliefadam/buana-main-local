<template>
  <v-container
    v-observe-visibility="onVisible"
    class="no-padding-margin"
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
      export-excel
      :table-loading.sync="loading"
      :show-expand="showExpand"
      :single-expand="singleExpand"
      :data="data"
      :items-options="itemsOptions"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      delete-mode="delete"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      :table-only="tableOnly"
      :table-fixed-header="tableFixedHeader"
    >
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b> {{ $refs.template.itemsTotal }}
      </template>

      <template v-slot:menu-after-filter>
        <v-btn color="primary" outlined small @click="openReport">
          <v-icon small left>mdi-printer</v-icon>Report
        </v-btn>
        <v-btn color="primary" outlined small @click="openCheckSubassy">
          <v-icon small left>mdi-printer</v-icon>Check Subassembly
        </v-btn>
        <!-- <v-btn small color="success" outlined v-if="!nointeraction"  @click="sendWa" >Send Wa</v-btn> -->
      </template>

      <template v-slot:item.created="props">
        <span>Created By: </span>{{ props.item.created_by_name }}<br />
        <span>Created Date: </span>{{ props.item.created_date }}<br />
      </template>
      <template v-slot:item.modified="props">
        <span>Modified By: </span>{{ props.item.modified_by_name }}<br />
        <span>Modified Date: </span>{{ props.item.modified_date }}
      </template>

      <template> </template>
      <template v-slot:expanded-item="props">
        <td
          ref="expanded"
          :colspan="props.headers.length"
          style="height: auto"
          :key="props.item[itemKey]"
        >
          {{ "parent_id:" + props.item[itemKey] + "::" + props.item.id }}
          <report-item-group
            title="Activity"
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
            is-dashboard
            table-only
            :key="props.item[itemKey]"
            :sel="{
              parent_id: props.item.id,
            }"
            name=""
            :data="{
              parent_id: props.item[itemKey],
            }"
          ></report-item-group>
        </td>
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="dialogItemGroup"
      title="Report Rebuild Activity"
      min-height="75%"
      fullscreen
    >
      <report-item-group
        :key="selected.id"
        :sel="processData"
        name=""
        :data="dataid"
      ></report-item-group>
    </v-action-dialog>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "",
  creator: "",
  components: {
    "report-item-group": "url:ui/rebuildmachine/reportentry_item_group.vue",
  },
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    history: {
      type: Boolean,
      default: false,
    },
    nointeraction: {
      type: Boolean,
      default: false,
    },
    revise: {
      type: Boolean,
      default: false,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    tableFixedHeader: {
      type: Boolean,
      default: true,
    },
    disableTable: {
      type: Boolean,
      default: false,
    },
    showExpand: {
      type: Boolean,
      default: true,
    },
    singleExpand: {
      type: Boolean,
      default: true,
    },
    showColumn: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {},
        filterType: {},
      },
    },
  },
  data: function () {
    return {
      valid: false,
      approval_notes: "",
      name: "Report Rebuild Machine",
      dataid: {},
      processData: {},
      url: "rebuildmachine/report",
      itemKey: "id",
      loading: false,
      dialogItemGroup: false,
      headers: [
        {
          text: "Id",
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
          required: true,
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
          text: "parent_id",
          value: "parent_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
          disabled: false,
          visible: false,
          required: true,
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
          text: "Date",
          value: "date",
          // "default": new Date().formatDate('YYYY-MM-DD'),
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: true,
          required: true,
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
          text: "Type Activity",
          value: "type",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          clearable: true,
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          data_value: [
            { text: "Replacement", value: "1" },
            { text: "Repair", value: "2" },
            { text: "Installation", value: "3" },
          ],
        },
        {
          text: "Section",
          value: "section_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          clearable: true,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          data_value: ["SE", "VE", "MAX", "HCF"],
        },
        {
          text: "Subassembly",
          value: "s_subassy",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          clearable: true,
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "maintenance/subassembly",
          searchby: ["subassy_info"],
          formatter: ["sub_assembly", "subassy_info"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
            subassy: true,
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Check Section",
          value: "s_section",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          clearable: true,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          data_value: ["SE", "VE", "MAX", "HCF"],
        },
        {
          text: "Check Register",
          value: "sub_register_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          clearable: true,
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Part No",
          value: "partno_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          clearable: true,
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "maintenance/subassembly",
          searchby: ["part_info"],
          formatter: function (val) {
            return {
              text: val.part_info,
              value: val.part_info,
              article_number: val.art_num,
            };
          },
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
            part_info: true,
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
          required: true,
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
          url: App.url + "users",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              groups: "rebuild_report_page",
            },
            filterType: {
              groups: "%",
            },
            filterCondition: {},
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
          url: App.url + "users",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              groups: "rebuild_report_page",
            },
            filterType: {
              groups: "%",
            },
            filterCondition: {},
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
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      isInDom: false,
      isInViewport: false,
      dataid: {},
    };
  },
  watch: {},
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
    allowRevisi: function () {
      var self = this;

      if (!self.selected) return true;
      return false;
    },
  },
  methods: {
    // openReport: function(){
    // 	var self = this
    // 	var params = JSON.parse(JSON.stringify(self.$refs.template.defaultItemsOptions))
    // 	params.limit = -1
    // 	params.rand = randomId()
    // 	params = new URLSearchParams(params).toString()
    // 	window.open('https://main.buanamultiteknik.com/api/rebuildmachine/report.xlsx?'+params)
    // },

    sendWa: async function () {
      var self = this;
      try {
        var r = await axios.post(App.url + "rebuildmachine/report/sendwa");
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      } catch (error) {
        App.errorMsg();
      }
    },

    openReport: function () {
      var self = this;
      var params = JSON.parse(
        JSON.stringify(self.$refs.template.defaultItemsOptions),
      );
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      params.limit = -1;
      params.rand = randomId();

      var prm = window.btoa(JSON.stringify(params));
      window.open(
        "https://main.buanamultiteknik.com/api/report/rebuild/index?parameter=" +
          prm,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },

    openCheckSubassy: function () {
      var self = this;
      var params = JSON.parse(
        JSON.stringify(self.$refs.template.defaultItemsOptions),
      );
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      params.limit = -1;
      params.rand = randomId();

      var prm = window.btoa(JSON.stringify(params));
      window.open(
        "https://main.buanamultiteknik.com/api/report/rebuild/checksubassembly?parameter=" +
          prm,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },

    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
        self.dataid = {};
      } else {
        self.selected = val;
        self.processData = {
          parent_id: val.id,
        };

        self.dataid = {
          parent_id: val.id,
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    onVisible: function (isVisible, e) {
      var self = this;
      self.isInViewport = !!isVisible;
      self.isInDom = !!e.target.isConnected;
    },
  },
  mounted: function () {},
};
</script>
