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
      :item-height-as-min-height="itemHeightAsMinHeight"
      :table-fill="tableFill"
      @update="update"
      @save="save"
      :table-loading.sync="loading"
      :show-expand="showExpand"
      :single-expand="singleExpand"
      :data="data"
      :items-options="itemsOptions"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      delete-mode="delete"
      :active-column="activeColumn"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      :table-only="tableOnly"
      :table-fixed-header="tableFixedHeader"
    >
      <!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					
				</td>
			</template> -->
      <!-- 
			<template v-slot:expanded-item="props">
				<td :colspan="props.headers.length" style="height: auto;">
				</td>
			</template>
			 -->
      <!-- 
			<template v-slot:item.item_name="props">
			</template>
			 -->
      <template v-slot:append-dialog-add>
        <v-overlay :value="overlay" class="zIndexOverlay"
          ><b style="font-size: 16px">Uploading data...</b></v-overlay
        >
      </template>
      <template v-slot:append-dialog-update>
        <v-overlay :value="overlay" class="zIndexOverlay"
          ><b style="font-size: 16px">Uploading data...</b></v-overlay
        >
      </template>
      <!-- 
			 -->
      <!-- 
            <template v-slot:prepend-menu>
            </template>
			 -->
      <!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
      <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
      <template v-slot:item.datasheet2="props">
        <a
          v-if="props.item.datasheet"
          :href="
            'https://main.buanamultiteknik.com/api/uploads/' +
            props.item.datasheet
          "
          target="_blank"
          >{{ props.item.datasheet }}</a
        >
      </template>
    </v-template>
  </v-container>
</template>

<style>
.zIndexOverlay {
  z-index: 10000 !important;
}
</style>

<script>
module.exports = {
  name: "item-list",
  creator: "",
  components: {
    /* 'document-form': 'url:ui/ewis/test/document_form.vue' */
  },
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
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
    disableTable: {
      type: Boolean,
      default: false,
    },
    activeColumn: {
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
      name: "list",
      itemKey: "id",
      url: "rfq/Rfqlist",
      overlay: false,
      loading: false,
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
          text: "Rfq Id",
          value: "rfq_id",
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
          text: "Name",
          value: "name",
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
          text: "Type",
          value: "type",
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
          text: "Description",
          value: "description",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: true,
          required: true,
          form: true,
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
          text: "Datasheet",
          value: "datasheet2",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "file",
          disabled: false,
          visible: true,
          required: false,
          form: true,
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
          type: "int",
          disabled: false,
          visible: true,
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
          visible: true,
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
          type: "int",
          disabled: false,
          visible: true,
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
          visible: true,
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
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
      } else {
        self.selected = val;
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
    save: async function (params) {
      var self = this;
      self.overlay = true;
      try {
        const formData = new FormData();
        //formData.append('file', files);
        Object.keys(params).forEach(function (key) {
          var val = params[key];
          formData.append(key, val);
        });
        var res = await axios.post(App.url + "rfq/rfqlist", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });

        self.$refs.template.dialogAdd = false;
        self.$refs.template.getItems();
        self.overlay = false;
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
        var res = await axios.post(App.url + "rfq/rfqlist", formData, {
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
        });

        self.$refs.template.dialogUpdate = false;
        self.$refs.template.getItems();
        self.overlay = false;
      } catch (err) {
        self.overlay = false;
      }
    },
  },
  mounted: function () {},
};
</script>
