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
      :single-select="false"
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

      <template v-slot:item.location="props">
        {{ location(props.item.location) }}
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
      <template v-slot:menu-after-filter>
        <!-- <v-btn small color="primary" outlined @click="textToBase64Barcode"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn> -->
        <v-btn
          small
          color="primary"
          outlined
          @click="showBarcode = true"
          :disabled="!selected"
          ><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn
        >
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
            >
              <img v-bind:src="col" :index="colI + 'img'" />
            </td>
          </tr>
        </table>
      </v-print>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="showBarcodeDialog"
      title=" Barcode Rack"
      fullscreen
    >
      <v-btn small color="primary" outlined @click="$refs.vprintbarcode.print()"
        >Print</v-btn
      >
      <v-print ref="vprintbarcode" style="color: #000">
        <rack-barcode
          v-model="dataBarcode"
          :key="version"
          :chunk-size="2"
        ></rack-barcode>
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
    "v-print": "url:ui/base/print.vue",
    "rack-barcode": "url:ui/bom/transaction/barcode_rack.vue",
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
        filter: {},
        filterType: {},
      },
    },
  },
  data: function () {
    return {
      name: "rack",
      itemKey: "id",
      url: "warehouse/rack",
      loading: false,
      dialogPrint: false,
      showBarcode: false,
      showBarcodeDialog: false,
      dialogPrint: false,
      dataBarcode: [],
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
          text: "Location",
          value: "location",
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
            { text: "Trowulan", value: "1" },
            { text: "Spazio", value: "2" },
          ],
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

          self.go = true;

          self.dataBarcode = [];

          self.$refs.template.selectedRowTmp.map((val) => {
            self.dataBarcode.push({
              barcodeQr: "BMT.RACK." + val.naem,
              name: val.name,
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
        JsBarcode(canvas, val.name, { format: "CODE128" });
        tmp.push(canvas.toDataURL("image/png"));
      });
      if (tmp.length != 0) images.push(tmp);
      self.images = images;
      self.$nextTick(() => {
        //tableToExcel(document.getElementById('qr'))
        self.dialogPrint = true;
      });
    },
    location: function (f) {
      if (f == 1) {
        return "Trowulan";
      }
      if (f == 2) {
        return "Spazio";
      }
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
