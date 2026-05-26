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
      :table-loading.sync="loading"
      :show-expand="showExpand"
      @save="save"
      @update="update"
      @open-edit="onOpenEdit"
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

      <!--          <template v-slot:menu-after-filter>-->
      <!--<v-btn color="primary" outlined small @click="openReport">-->
      <!--                <v-icon small left>mdi-printer</v-icon>Report-->
      <!--            </v-btn>-->
      <!--            <v-btn color="primary" outlined small @click="openCheckSubassy">-->
      <!--                <v-icon small left>mdi-printer</v-icon>Check Subassembly-->
      <!--            </v-btn >-->
      <!-- <v-btn small color="success" outlined v-if="!nointeraction"  @click="sendWa" >Send Wa</v-btn> -->
      <!--</template>-->

      <template v-slot:item.created_modified="props">
        <span>Created By: </span>{{ props.item.created_by_name }}<br />
        <span>Created Date: </span>{{ props.item.created_date }}<br />
        <span>Modified By: </span>{{ props.item.modified_by_name }}<br />
        <span>Modified Date: </span>{{ props.item.modified_date }}
      </template>

      <template v-slot:item.activity="props">
        <b>{{ typeActivity(props.item.type_activity) }}</b
        ><br /><br />
        <div v-if="props.item.npb_id">
          <span>NPB No: {{ props.item.npb__no }}<br /></span>
          <span>PN: {{ props.item.manufacture_pn }}<br /></span>
        </div>
        <div v-else></div>
      </template>

      <template v-slot:item.photo="props">
        <a
          v-if="props.item.attachment != null"
          target="_blank"
          :href="
            'https://main.buanamultiteknik.com/api/uploads/rm2_' +
            props.item.part_id +
            '/' +
            props.item.attachment.slice(0).split('+++')[0]
          "
        >
          <img
            style="
              border: 1px solid #ddd;
              border-radius: 4px;
              padding: 5px;
              height: 150px;
            "
            :src="
              'https://main.buanamultiteknik.com/api/uploads/rm2_' +
              props.item.part_id +
              '/' +
              props.item.attachment.slice(0).split('+++')[0]
            "
          />
        </a>
      </template>
    </v-template>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "Activity",
  creator: "",
  components: {},
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
    sel: {
      type: Object,
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
      default: false,
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
      name: "Daily Report",
      dataid: {},
      processData: {},
      url: "rebuildmachine/reportrebuild2",
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
          text: "Report ID",
          value: "report_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
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
          text: "Time",
          value: "time",
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
          text: "Start Time",
          value: "start_time",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "time",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "End Time",
          value: "end_time",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "time",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Activity",
          value: "activity",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          clearable: true,
          disabled: false,
          visible: true,
          required: true,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Type Activity",
          value: "type_activity",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          clearable: false,
          disabled: false,
          visible: false,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          data_value: [],
        },
        {
          text: "Qty",
          value: "qty",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "NPB No",
          value: "npb_id",
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
          url: App.url + "transaction/npb",
          searchby: ["npb__no"],
          formatter: ["id", "npb__no"],
          options: {
            filter: {
              approved: 4,
            },
            filterType: {},
            filterCondition: {},
          },
          input: function (data) {
            var self = App.$get("Activity");
            if (data.data) {
              self.headersObj.item_replacement_id.options.filter = {
                npb_id: data.data,
              };
            } else delete self.headersObj.item_replacement_id.options.filter;
            //self.headersObj.item_replacement_id.data = null
            self.headersObj.item_replacement_id.update = null;
            self.headers = App.updateObject(self.headers);
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "npb__no",
        },
        {
          text: "Part Replacement",
          value: "item_replacement_id",
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
          url: App.url + "transaction/npbitem",
          // "searchby": ['full'],
          formatter: ["item_id", "full"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "manufacture_pn",
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
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Photo",
          value: "attachment",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "file",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Photo",
          value: "photo",
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
          text: "Part",
          value: "part_id",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          clearable: true,
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Created/Modified",
          value: "created_modified",
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
          type: "datetime",
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
          type: "datetime",
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
  },
  methods: {
    typeActivity: function (f) {
      if (f == 1) {
        return "Installation";
      }
      if (f == 2) {
        return "Painting";
      }
      if (f == 3) {
        return "Replacement Subassy";
      }
      if (f == 4) {
        return "Replacement Part";
      }
    },

    save: async function (params) {
      var self = this;
      self.overlay = true;
      try {
        const formData = new FormData();
        Object.keys(params).forEach(function (key) {
          var val = params[key];
          formData.append(key, val);
        });
        var res = await axios.post(
          App.url + "rebuildmachine/reportrebuild2",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          },
        );

        if (!res.data.status) App.errorMsg();
        else {
          App.successMsg();
          self.$refs.template.dialogAdd = false;
          self.$refs.template.getItems();
          console.log(val);
        }
      } catch (err) {
        self.overlay = false;
      }
    },
    update: async function (params) {
      var self = this;
      self.overlay = true;
      try {
        const formData = new FormData();
        //formData.append('file', files);
        Object.keys(params).forEach(function (key) {
          var val = params[key];
          formData.append(key, val);
        });
        formData.append("part_id", self.data.part_id);
        var res = await axios.post(
          App.url + "rebuildmachine/reportrebuild2",
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          },
        );

        if (!res.data.status) App.errorMsg();
        else {
          App.successMsg();
          self.$refs.template.dialogUpdate = false;
          self.$refs.template.getItems();
        }
      } catch (error) {
        self.overlay = false;
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
          part_id: self.data.part_id,
          is_part: self.data.is_part,
        };

        self.dataid = {
          part_id: self.data.part_id,
          is_part: self.data.is_part,
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    onVisible: function (isVisible) {
      var self = this;
      if (isVisible) {
        self.itemsOptions.filter = {
          part_id: self.data.part_id,
        };
        self.$refs.template.defaultItemsOptions.filter = {
          part_id: self.data.part_id,
        };
        is_part: self.data.is_part;
        self.$refs.template.getItems();
      }
    },
    onOpenEdit: function () {
      var self = this;
      self.headersObj.date.form = true;
      self.headersObj.start_time = true;
      self.headersObj.end_time = true;
      self.headersObj.type_activity = true;
      self.headersObj;
      if (
        self.selected.type_activity == 1 ||
        self.selected.type_activity == 2
      ) {
        self.headersObj.notes.form = true;
        self.headersObj.attachment.form = true;
        self.headersObj.npb_id.form = false;
        self.headersObj.item_replacement_id.form = false;
      } else if (
        self.selected.type_activity == 3 ||
        self.selected.type_activity == 4
      ) {
        self.headersObj.npb_id.form = true;
        self.headersObj.item_replacement_id.form = true;
        self.headersObj.notes.form = true;
        self.headersObj.attachment.form = true;
      } else {
        self.headersObj.npb_id.form = false;
        self.headersObj.item_replacement_id.form = false;
        self.headersObj.notes.form = false;
        self.headersObj.attachment.form = false;
      }
      setTimeout(() => {
        self.headersObj.attachment.data = null;
      }, 300);
    },
  },
  mounted: function () {
    var self = this;
    self.defaultForm = JSONfn.parse(JSONfn.stringify(self.headers));
    if (self.data.is_part == 1) {
      self.headersObj.type_activity.data_value = [
        {
          text: "Installation",
          value: "1",
        },
        {
          text: "Painting",
          value: "2",
        },
        {
          text: "Replacement Part",
          value: "4",
        },
      ];
      self.headersObj.type_activity.input = function (data) {
        var self = App.$get("Activity");
        if (data.data.toString() == "1" || data.data.toString() == "2") {
          self.headersObj.notes.form = true;
          self.headersObj.attachment.form = true;
          self.headersObj.npb_id.form = false;
          self.headersObj.item_replacement_id.form = false;
        } else if (data.data.toString() == "4") {
          self.headersObj.npb_id.form = true;
          self.headersObj.item_replacement_id.form = true;
          self.headersObj.notes.form = true;
          self.headersObj.attachment.form = true;
        } else {
          self.headersObj.npb_id.form = false;
          self.headersObj.item_replacement_id.form = false;
          self.headersObj.notes.form = false;
          self.headersObj.attachment.form = false;
        }
        self.headers = App.updateObject(self.headers);
      };
    } else {
      self.headersObj.type_activity.default = "3";
      self.headersObj.type_activity.readonly = true;
      self.headersObj.type_activity.data_value = [
        {
          text: "Replacement Subassy",
          value: "3",
        },
      ];
      self.headersObj.npb_id.form = true;
      self.headersObj.item_replacement_id.form = true;
      self.headersObj.notes.form = true;
      self.headersObj.attachment.form = true;
      self.headers = App.updateObject(self.headers);
    }

    self.$refs.template.getItems();
  },
};
</script>
