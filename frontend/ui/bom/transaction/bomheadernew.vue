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
      <template v-slot:menu-after-filter>
        <v-btn
          small
          color="primary"
          :disabled="selected == false"
          @click="itemDialog = true"
          outlined
          >Items</v-btn
        >
        <v-btn
          small
          color="primary"
          outlined
          @click="showBarcode = true"
          :disabled="!selected"
          ><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn
        >
      </template>
      <!-- 
			<template v-slot:item.item_name="props">
			</template>
			 -->
      <!-- 
			<template v-slot:append-dialog-add>>
			</template>
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
      <template v-slot:item.project_id="props">
        {{ props.item.project_no }} -- {{ props.item.project_name }}
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="itemDialog"
      title="Bom Item"
      fullscreen
    >
      <bom-item :data="dataid" :key="selected.id" ref="bom"></bom-item>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="showBarcodeDialog"
      title="Bom Barcode"
      fullscreen
    >
      <v-btn small color="primary" outlined @click="print">Print</v-btn>
      <v-print ref="vprint" style="color: #000">
        <bom-barcode v-model="dataBarcode" :key="selected.id"></bom-barcode>
      </v-print>
    </v-action-dialog>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "",
  creator: "",
  components: {
    "bom-item": "url:ui/bom/transaction/bomnew.vue",
    "bom-barcode": "url:ui/bom/transaction/barcode_new.vue",
    "v-print": "url:ui/base/print.vue",
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
    disableTable: {
      type: Boolean,
      default: false,
    },
    activeColumn: {
      default: "flag",
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
        filter: {
          isPack: 1,
        },
        filterType: {},
      },
    },
  },
  data: function () {
    return {
      name: "bomheader",
      itemKey: "id",
      url: "transaction/bomheader",
      showBarcode: false,
      showBarcodeDialog: false,
      dataBarcode: [],
      itemDialog: false,
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
          page: "1",
          limit: "10",
        },
        {
          text: "Bom No",
          value: "bom_no",
          align: "start",
          sortable: true,
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
          text: "Machine Name",
          value: "machine_name",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
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
          text: "Project No",
          value: "project_id",
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
          form: false,
          filter: false,
          groupable: false,
          clearable: true,
          // "default": App.page().selected.project_no,
          url: App.url + "project/project",
          searchby: ["full"],
          // "formatter": ["id", "full"],
          formatter: function (val) {
            return {
              value: val.id,
              text: val.full,
              category_item: val.category,
            };
          },
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
            category_item: true,
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "project_no",
        },
        {
          text: "Type",
          value: "isPack",
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
          default: "1",
          form: true,
          data_value: [
            { text: "Part List", value: 0 },
            { text: "Pack", value: 1 },
          ],
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
          type: "int",
          disabled: false,
          visible: true,
          required: true,
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
      go: false,
    };
  },
  watch: {
    async showBarcodeDialog(val) {
      if (val == false) this.showBarcode = false;
    },
    async showBarcode(val) {
      var self = this;
      if (val) {
        self.loading = true;
        try {
          App.tmp.target = self;
          //var res = await axios.get(App.url+self.url+'/getItemsPack', {
          var res = await axios.get(App.url + self.url + "/getitemsall", {
            params: {
              bom_header_id: self.selected.id,
            },
          });
          self.go = true;

          self.dataBarcode = res.data.data;
          self.loading = false;
          self.showBarcodeDialog = true;
        } catch (err) {
          self.loading = false;
          App.errorMsg();
        }
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
    dataid: function () {
      if (this.selected == false) return {};
      return {
        bom_header_id: this.selected.id,
      };
    },
  },
  methods: {
    print: function () {
      var w = window.open("", "wnd");
      w.document.body.innerHTML =
        document.getElementsByClassName("section-to-print")[0].outerHTML;
      w.print();
      w.setTimeout(() => {
        w.close();
      }, 250);
    },
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
  },
  mounted: function () {
    loadMultipleLibrary(
      "https://main.buanamultiteknik.com/js/qrcode.js;https://cdn.jsdelivr.net/npm/bwip-js@4.4.0/dist/bwip-js.min.js",
    );
  },
};
</script>
