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
      :item-height-as-min-height="itemHeightAsMinHeight"
      :table-fill="tableFill"
      :table-fixed-header="tableFixedHeader"
      :items-options="itemsOptions"
      :data="data"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      @save="save"
      @update="update"
    >
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:menu-after-filter>
        <v-btn color="primary" outlined small @click="openReport">
          <v-icon small left>mdi-printer</v-icon>Report
        </v-btn>
      </template>

      <template v-slot:item.photo="props">
        <a
          v-if="props.item.attachment != null"
          target="_blank"
          :href="
            'https://main.buanamultiteknik.com/api/uploads/lp' +
            props.item.part_no +
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
              'https://main.buanamultiteknik.com/api/uploads/lp' +
              props.item.part_no +
              '/' +
              props.item.attachment.slice(0).split('+++')[0]
            "
          />
        </a>
      </template>

      <template v-slot:item.brand="props">
        {{ brandStatus(props.item.brand) }}
      </template>
      <template v-slot:item.detaillist="props">
        <span>Article No:</span> {{ props.item.part_no }}<br />
        <span>Hauni PN:</span> {{ props.item.part_number }}<br />
        <span>Hauni Subassy:</span> {{ props.item.sub_assembly }}
      </template>

      <template v-slot:item.createdmodified="props">
        <!-- <span>Created By: </span>{{ props.item.created_by_name }}<br/>
				<span>Created Date: </span>{{ props.item.created_date }}<br/> -->
        <!-- <span>Modified By: </span>{{ props.item.modified_by_name }}<br/>
				<span>Modified Date: </span>{{ props.item.modified_date }} -->
      </template>
    </v-template>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "Entry",
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
      default: true,
    },
    isDashboard: {
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
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          brand: "2",
        },
        filterType: {
          brand: "in",
        },
      },
    },
  },
  components: {},
  data: function () {
    return {
      valid: false,
      name: "List Part Koerber",
      processData: {},
      itemKey: "id",
      url: "rebuildmachine/listpart",
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
          paging: true,
          page: "0",
          limit: "10",
        },
        {
          text: "Detail List",
          value: "detaillist",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          clearable: false,
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
        },
        {
          text: "Brand",
          value: "brand",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          default: 2,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          data_value: [{ text: "KOERBER", value: 2 }],
        },
        {
          text: "Article Number",
          value: "part_no",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
          clearable: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Hauni Part Number",
          value: "parthauni_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          clearable: true,
          required: false,
          alias: "part_number",
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "maintenance/subassembly",
          searchby: ["partsubassy"],
          formatter: ["id", "partsubassy"],
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
          text: "Hauni Subassembly",
          value: "subassy_id",
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
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "maintenance/subassembly",
          searchby: ["subassy_info"],
          formatter: ["sub_assembly", "subassy_info"],
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
          text: "QTY",
          value: "qty",
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
          required: false,
          form: true,
          filter: true,
          groupable: false,
          paging: true,
          page: "1",
          limit: "10",
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
          form: true,
          filter: true,
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
          filter: true,
          groupable: false,
        },
        {
          text: "Created/Modified",
          value: "createdmodified",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
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
      ],
      dialogItem: false,
      selected: false,
      dialogPIC: false,
      dataid: {},
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
  },
  methods: {
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
        "https://main.buanamultiteknik.com/api/report/listpart/index?parameter=" +
          prm,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },
    brandStatus: function (f) {
      if (f == 1) {
        return "CDYH";
      }
      if (f == 2) {
        return "Koerber";
      }
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
          // id: self.data.id,
        };
        self.dataid = {
          // id: self.data.id,
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    onOpenAdd: function () {
      var self = this;
      self.$nextTick(() => {
        setTimeout(() => {
          var tmp = {};
          self.$refs.template.formModelAdd.map((val) => {
            tmp[val.value] = val;
          });
          tmp.item_id.form = true;
          tmp.item_id.required = true;
          tmp.qty.form = true;
          tmp.qty.required = true;
          tmp.notes.form = true;
          tmp.notes.required = true;
        }, 100);
      });
    },
    onCloseAdd: function () {
      var self = this;
      self.$nextTick(() => {
        setTimeout(() => {
          var tmp = {};
          self.$refs.template.formModelAdd.map((val) => {
            tmp[val.value] = val;
          });
          tmp.item_id.form = true;
          tmp.item_id.required = true;
          tmp.qty.form = true;
          tmp.qty.required = true;
          tmp.notes.form = true;
          tmp.notes.required = true;
        }, 100);
      });
    },
    onAddItem: function () {
      var self = this;
      self.$refs.sjItem.$refs.template.openAdd();
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
          App.url + "rebuildmachine/listpart",
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
        Object.keys(params).forEach(function (key) {
          var val = params[key];
          formData.append(key, val);
        });
        formData.append("part_no", self.selected.part_no);
        var res = await axios.post(
          App.url + "rebuildmachine/listpart",
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
  },
  mounted: function () {
    //         this.itemsOptions = {
    //             filter: {
    // 	    brand:'2',
    // 	},
    // 	filterType: {
    // 	    brand:'in'
    // 	}
    //         }
  },
};
</script>
