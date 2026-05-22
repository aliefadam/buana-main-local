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
      <template v-slot:expanded-item="props">
        <td ref="expanded" :colspan="props.headers.length" style="height: auto">
          <stock
            :data="{ item_id: props.item.item_id }"
            :table-fixed-header="false"
            :key="props.item[itemKey]"
          ></stock>
        </td>
      </template>

      <template v-slot:menu-after-filter>
        <!-- <v-btn small color="primary" outlined @click="function(){
					qtyBarcode = prompt('Print qty?',1 )
					if(qtyBarcode){
						if(!isNaN(qtyBarcode))
							showBarcode=true
					}
				}" :disabled="!selected"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn> -->
      </template>
      <!-- 
			<template v-slot:expanded-item="props">
				<td :colspan="props.headers.length" style="height: auto;">
				</td>
			</template>
			 -->

      <template v-slot:item.datasheet="props">
        <a :href="props.item.datasheet">Datasheet</a>
      </template>

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
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="showBarcodeDialog"
      title="Bom Barcode"
      fullscreen
    >
      <v-btn small color="primary" outlined @click="print">Print</v-btn>
      <v-print ref="vprint" style="color: #000">
        <stock-barcode
          :qty="qtyBarcode"
          v-model="dataBarcode"
          :key="version"
          :page-break="qtyBarcode > 1 ? true : false"
        ></stock-barcode>
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
    "stock-barcode": "url:ui/bom/transaction/barcode_stock.vue",
    "v-print": "url:ui/base/print.vue",
    stock: "url:ui/bom/warehouse/stock.vue",
  },
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: true,
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
      name: "stock",
      itemKey: "item_id",
      url: "transaction/stockgroup",
      qtyBarcode: 0,
      showBarcode: false,
      showBarcodeDialog: false,
      dataBarcode: [],
      loading: false,
      stockOptions: {},
      stockData: {},
      headers: [
        {
          text: "Item Id",
          value: "item_id",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Item No",
          value: "item_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          data_value: [],
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: true,
          groupable: false,
          alias: "item_no",
          url: App.url + "bom/item",
          searchby: ["full"],
          formatter: ["id", "full"],
          options: {
            filter: {
              is_active: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "item_no",
        },
        {
          text: "Item Name",
          value: "item_name",
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
          text: "Item Type",
          value: "item_type",
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
          form: true,
          filter: true,
          groupable: false,
          data_value: ["Jasa", "Material", "Part", "Tool"],
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Unit",
          value: "unit",
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
          form: true,
          filter: true,
          groupable: false,
          data_value: [
            "SHEET",
            "TON",
            "LTR",
            "KG",
            "PACK",
            "BOX",
            "PCS",
            "MTR",
            "DZN",
            "LOT",
            "LOG",
            "SET",
            "UNIT",
            "ROLL",
            "SQM",
          ],
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
          text: "Manufacture PN",
          value: "manufacture_pn",
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
          text: "Racks",
          value: "racks",
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
          form: false,
          filter: true,
        },
        {
          text: "Original Manufacture",
          value: "original_manufacture",
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
          text: "Article No",
          value: "article_no",
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
          form: true,
          filter: true,
          groupable: false,
        },
        {
          text: "Specification",
          value: "specification",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Subassembly",
          value: "subassembly",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Subassembly Description",
          value: "subassy_desc",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Equivalent Part No Hauni",
          value: "equivalent",
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
          required: false,
          form: true,
          filter: true,
          groupable: false,
          // "url": App.url + "maintenance/subassembly",
          // "searchby": ['part_info'],
          // "formatter": function(val) {
          //     return {
          //         text: val.part_info,
          //         value: val.part_number,
          //         article_number: val.art_num
          //     }
          // },
          // "options": {
          //     "filter": {},
          //     "filterType": {},
          //     "filterCondition": {},
          //     part_info: true
          // },
          // "paging": true,
          // "page": "1",
          // "limit": "10",
        },
        {
          text: "Qty",
          value: "qty",
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
          text: "Datasheet",
          value: "datasheet",
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
      version: 0,
    };
  },
  watch: {
    async showBarcode(val) {
      var self = this;
      if (val) {
        self.loading = true;
        try {
          App.tmp.target = self;

          self.go = true;

          self.dataBarcode = [
            {
              barcodeQr: "BMT.stock." + self.selected.id,
              item_name: self.selected.item_name,
              item_no: self.selected.item_no,
              manufacture_pn: self.selected.manufacture_pn,
            },
          ];

          if (isNaN(self.qtyBarcode)) self.qtyBarcode = 1;
          if (Number(self.qtyBarcode) > 1) {
            var arr = [];
            for (i = 1; i <= Number(self.qtyBarcode); i++) {
              arr = arr.concat(self.dataBarcode);
            }
            self.dataBarcode = arr;
          }
          self.version = randomId();
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
  },
  methods: {
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
      } else {
        self.selected = val;
        self.stockOptions = { filter: { item_id: val.item_id } };
        self.stockData = { item_id: val.item_id };
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
