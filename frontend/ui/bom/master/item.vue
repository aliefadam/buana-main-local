<template>
  <v-container
    v-observe-visibility="onVisible"
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
      :data="data"
      :items-options="itemsOptions"
      @update:selected-row="onSelectedRow"
      @close-edit="closeEdit"
      @open-edit="openEdit"
      :single-select="true"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :name="name"
      :table-only="tableOnly"
    >
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:item.datasheet="props">
        <a
          :href="props.item.datasheet"
          v-if="props.item.datasheet"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span>
      </template>
      <template v-slot:item.price_per_item="props">
        {{ parseFloat(props.item.price_per_item).format(2, 3) }}
      </template>
      <template v-slot:item.is_active="props">
        <span style="text-align: center">
          {{ isActive(props.item.is_active) }}
        </span>
      </template>
      <template v-slot:item.created="props">
        <b>Created</b><br />
        <span>BY:</span> {{ props.item.created_by_name }}<br />
        <span>DATE:</span> {{ props.item.created_date }}<br /><br />
        <b>Modified</b><br />
        <span>BY:</span> {{ props.item.modified_by_name }}<br />
        <span>DATE:</span> {{ props.item.modified_date }}
      </template>
      <template v-slot:item.modified="props">
        <span>BY:</span> {{ props.item.modified_by_name }}<br />
        <span>DATE:</span> {{ props.item.modified_date }}<br />
      </template>
      <template v-slot:menu-after-filter>
        <v-btn v-if="subItem" small color="primary" outlined @click="importBOM"
          ><v-icon small left></v-icon>Import From BOM Receive</v-btn
        >
        <v-btn v-if="!subItem" small color="primary" outlined @click="showChild"
          ><v-icon small left></v-icon>Sub Parts</v-btn
        >
        <!-- <v-btn small color="primary" outlined @click="textToBase64Barcode"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn> -->
        <!-- <v-btn small color="primary" outlined @click="function(){
					qtyBarcode = prompt('Print qty?',1 )
					if(qtyBarcode){
						if(!isNaN(qtyBarcode))
							showBarcode=true
					}
				}" :disabled="!selected"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn> -->
      </template>
    </v-template>
    <v-action-dialog
      label-save="print"
      @save="$refs.vprint.print()"
      v-model="dialogPrint"
      title="Barcode"
      min-height="75%"
      fullscreen
    >
      <v-print ref="vprint" style="margin: auto; color: #000; padding: 20pt">
        <table class="default-table no-border" id="qr">
          <tr v-for="(row, rowI) in images" :key="rowI">
            <td
              v-for="(col, colI) in row"
              :key="colI"
              img="50,100"
              style="text-align: center"
              width="5%"
            >
              <img v-bind:src="col" :index="colI + 'img'" />
            </td>
          </tr>
        </table>
      </v-print>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="subItemDialog"
      title="Sub Part"
      min-height="75%"
      fullscreen
    >
      <v-item
        v-if="!subItem"
        :key="selected.id"
        name=""
        :data="dataid"
        sub-item
      ></v-item>
      <!-- <v-item :key="selected.id" :data="subInfo"></v-item> -->
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="showBarcodeDialog"
      title=" Barcode"
      fullscreen
    >
      <v-btn small color="primary" outlined @click="$refs.vprintbarcode.print()"
        >Print</v-btn
      >
      <v-print ref="vprintbarcode" style="color: #000">
        <item-barcode
          :qty="qtyBarcode"
          v-model="dataBarcode"
          :key="version"
          :page-break="qtyBarcode > 1 ? true : false"
          :chunk-size="3"
        ></item-barcode>
      </v-print>
    </v-action-dialog>
  </v-container>
</template>

<style scoped>
.no-border,
.no-border td,
.no-border th {
  border: 0px !important;
}
</style>

<script>
module.exports = {
  name: "",
  props: {
    value: undefined,
    data: {
      type: Object,
      default: {},
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    subItem: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          is_subpart: null,
        },
        filterType: {
          is_subpart: "isnull",
        },
      },
    },
  },
  components: {
    "v-print": "url:ui/base/print.vue",
    "item-barcode": "url:ui/bom/transaction/barcode_stock.vue",
    "v-item": "url:ui/bom/master/item.vue",
  },
  data: function () {
    return {
      dataid: {},
      name: "item",
      itemKey: "id",
      qtyBarcode: 0,
      showBarcode: false,
      showBarcodeDialog: false,
      dataBarcode: [],
      loading: false,
      url: "bom/item",
      dialogPrint: false,
      subItemDialog: false,
      isInDom: false,
      isInViewport: false,
      selected: false,
      images: [],
      headers: [
        {
          text: "Id",
          value: "id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: 100,
          type: "auto",
          disabled: false,
          form: false,
          visible: false,
          filter: false,
          groupable: false,
          cellClass: "",
        },
        {
          text: "Is Subpart",
          value: "is_subpart",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: 100,
          type: "auto",
          disabled: false,
          form: false,
          visible: false,
          filter: false,
          groupable: false,
          cellClass: "",
        },
        {
          text: "Item No",
          value: "item_no",
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
          required: true,
          form: true,
          filter: true,
        },
        {
          text: "Item Unit",
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
            "CYL",
          ],
        },
        /*{
					"text": "Price",
					"value": "price_per_item",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "float",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "Currency",
					"value": "currency",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false
				}, */ {
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
          required: true,
          form: true,
          filter: true,
          groupable: false,
          data_value: ["Jasa", "Material", "Part", "Tool"],
          paging: true,
          page: "0",
          limit: "10",
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
          required: false,
          form: true,
          filter: true,
          groupable: false,
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
          required: false,
          form: true,
          filter: true,
          groupable: false,
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
        },
        {
          text: "Subassembly",
          value: "subassembly",
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
          // "url": App.url + "maintenance/subassembly",
          // "searchby": ['subassy_info'],
          // "formatter": ['sub_assembly', 'subassy_info'],
          // "options": {
          //     "filter": {},
          //     "filterType": {},
          //     "filterCondition": {},
          //     subassy: true
          // },
          // "paging": true,
          // "page": "1",
          // "limit": "10",
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
          visible: true,
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
          text: "Datasheet",
          value: "datasheet",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          rules: [
            /* v => (v || '' ).trim().length > 10 */ (v) =>
              new RegExp(
                "^([a-zA-Z]+:\\/\\/)?" + // protocol
                  "((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|" + // domain name
                  "((\\d{1,3}\\.){3}\\d{1,3}))" + // OR IP (v4) address
                  "(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*" + // port and path
                  "(\\?[;&a-z\\d%_.)" + // query string
                  "(\\#[-a-z\\d_]*)?$", // fragment locator
                "/^(?:(?:https?|ftp):\/\/)?(?:www\.)?[a-z0-9-]+(?:\.[a-z0-9-]+)+[^\s]*$/",
                "i",
              ).test(v) || "Datasheet must be valid URL!",
          ],
          disabled: false,
          visible: true,
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
          page: "0",
          limit: "10",
        },
        // {
        // 	"text": "Active",
        // 	"value": "is_active",
        // 	"align": "start",
        // 	"sortable": true,
        // 	"filterable": false,
        // 	"divider": false,
        // 	"class": "",
        // 	"width": "auto",
        // 	"type": "switch",
        // 	"disabled": false,
        // 	"visible": true,
        // 	"required": true,
        // 	"form": true,
        // 	"filter": false,
        // 	"groupable": false,
        // 	// "default": 1,
        // 	"data_value":  [{ text:"Non Active", value:0}, {text:"Active", value:1}]
        // },
        {
          text: "Active",
          value: "is_active",
          readonly: false,
          type: "switch",
          form: true,
          default: 0,
          data_value: [0, 1],
          visible: true,
          required: true,
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
              group_name: "adm_admin",
              sub_group_name: "adm_admin",
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
              group_name: "adm_admin",
              sub_group_name: "adm_admin",
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
      ],
      version: randomId(),
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

          self.dataBarcode = [];

          self.$refs.template.selectedRowTmp.map((val) => {
            self.dataBarcode.push({
              barcodeQr: "BMT.item." + val.id,
              item_name: val.item_name,
              item_no: val.item_no,
              manufacture_pn: val.manufacture_pn,
            });
          });

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
    subInfo: function () {
      var self = this;
      return {
        is_subpart: self.selected.id,
      };
    },
  },
  methods: {
    importBOM: async function (val) {
      var self = this;
      try {
        var res = await axios.get(App.url + "bom/item/create_from_bom", {
          params: {},
        });
        if (res.data.status == false) throw res.data;
        else App.successMsg();
      } catch (error) {
        App.errorMsg(error);
      }
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.dataid = {};
      } else {
        self.selected = val;
        self.dataid = {
          is_subpart: 1,
        };
      }
    },
    showChild: function () {
      var self = this;
      self.subItemDialog = true;
    },
    closeEdit: function () {
      var self = this;
      self.headersObj.unit.readonly = false;
      self.headersObj.item_type.readonly = false;
    },
    openEdit: function () {
      var self = this;
      self.headersObj.unit.readonly = true;
      self.headersObj.item_type.readonly = true;
    },
    textToBase64Barcode: async function () {
      var self = this;
      var res = await axios.get(App.url + self.url, {
        params: Object.assign(
          {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          self.$refs.template.defaultItemsOptions,
        ),
      });

      var canvas = document.createElement("canvas"),
        images = [],
        tmp = [];
      self.$refs.template.items.map(function (val, i) {
        if (i % 4 == 0 && i != 0) {
          images.push(tmp);
          tmp = [];
        }
        JsBarcode(canvas, val.item_no, { format: "CODE128" });
        tmp.push(canvas.toDataURL("image/png"));
      });
      if (tmp.length != 0) images.push(tmp);
      self.images = images;
      self.$nextTick(() => {
        //tableToExcel(document.getElementById('qr'))
        self.dialogPrint = true;
      });
    },
    onVisible: function (isVisible, e) {
      var self = this;
      self.isInViewport = !!isVisible;
      self.isInDom = !!e.target.isConnected;
      if (self.data.is_subpart != undefined) {
        self.itemsOptions = {
          filter: self.data,
        };
        //console.log(self.isInViewport)
        if (self.isInViewport) {
          self.$nextTick(() => {
            self.$refs.template.getItems();
          });
        }
      }
    },
    isActive: function (f) {
      if (f == 1) {
        return "Active";
      } else return "Non Active";
    },
  },
  mounted: function () {
    loadMultipleLibrary(
      "https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/barcodes/JsBarcode.code128.min.js",
    );
    loadMultipleLibrary(
      "https://main.buanamultiteknik.com/js/qrcode.js;https://cdn.jsdelivr.net/npm/bwip-js@4.4.0/dist/bwip-js.min.js",
    );
  },
};
</script>
