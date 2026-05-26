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
      :disable-delete-button="disableDelete"
      :disable-edit-button="disableEdit"
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

      <template v-slot:item.approved="props">
        {{ status[props.item.approved] }}
      </template>

      <!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->
      <!-- 
            <template v-slot:prepend-menu>
            </template>
			 -->

      <!--        <template v-slot:menu-after-filter>-->
      <!--<v-btn small color="primary" outlined @click="openPrint" :disabled="!selected">Print SPB</v-btn>-->
      <!--            <v-btn color="primary" v-if="!hideApproval" outlined small @click="askApproval" :disabled="disableAskApproval">-->
      <!--                Ask Approval-->
      <!--            </v-btn>-->
      <!--        </template>-->

      <template v-slot:item.details="props">
        <b>SPB No:</b> {{ props.item.spb_no }}<br />
        <b>Created By:</b> {{ props.item.created_by_name }}<br />
        <b>Created Date:</b>
        {{ new Date(props.item.created_date).formatDate("D MMMM YYYY") }}<br />
      </template>

      <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
      <template v-slot:append-menu>
        <v-btn
          small
          color="primary"
          outlined
          @click="openItem"
          :disabled="!selected"
          >Items</v-btn
        >
      </template>
    </v-template>

    <v-action-dialog
      :actions="false"
      v-model="dialogItem"
      title="SPB Item"
      min-height="75%"
      fullscreen
    >
      <v-container
        style="width: 100%; height: 100%; display: flex; flex-direction: column"
        v-if="selected != false"
      >
        <v-row no-gutters style="flex: 1" v-if="selected.approved <= 0">
          <v-cell cols="12" style="width: 100%">
            <spb-item
              @after-insert-stock="loadSpbStock"
              :key="selected.id"
              :sel="processData"
              :data="dataid"
              ref="spbAvailable"
            ></spb-item>
          </v-cell>
        </v-row>
        <v-row no-gutters style="flex: 1">
          <v-cell cols="12" style="width: 100%">
            <stock
              :table-only="selected.approved == 1"
              @after-delete="loadSpbAvailable"
              :key="selected.id"
              :data="stockData"
              ref="spbStock"
            ></stock>
          </v-cell>
        </v-row>
      </v-container>
    </v-action-dialog>
    <v-action-dialog
      label-save="print"
      @save="$refs.vprint.print()"
      v-model="dialogPrint"
      title="SPB Print"
      min-height="75%"
      fullscreen
    >
      <v-print ref="vprint" style="margin: auto; color: #000; padding: 20pt">
        <nav class="tw-header print-header">
          <h1 class="px-2 text-xl text-center my-auto uppercase font-bold">
            <img src="../img/logo2.png" />
          </h1>
          <div style="align-self: end; font-size: 10px">
            Jl. Mayjend Yono Soewoyo No. 35 - Surabaya 60225 - Indonesia - Telp.
            (031) 99900899
          </div>
        </nav>
        <div
          style="
            font-size: 20px;
            font-weight: bold;
            padding-top: 14px;
            padding-bottom: 14px;
            text-align: center;
          "
        >
          SURAT PENERIMAAN BARANG
        </div>
        <table class="default-table no-border">
          <tr>
            <td style="width: 35px">PO</td>
            <td>: {{ selected.po_no }}</td>
          </tr>
          <tr>
            <td>Date</td>
            <td>
              : {{ new Date(selected.created_date).formatDate("D MMMM YYYY") }}
            </td>
          </tr>
        </table>
        <br /><br /><br />
        <table
          class="default-table with-border print-table table-align-top"
          style="max-width: 100%"
        >
          <thead>
            <tr>
              <th>No.</th>
              <th>Nama Barang</th>
              <th>Jumlah</th>
              <th>Satuan</th>
              <th>Alokasi</th>
              <th>Catatan</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(item, index) in print.existing">
              <tr
                v-for="(v, i) in item.count"
                :key="index + '-' + i"
                :class="i != 0 ? 'no-top-border' : ''"
              >
                <td
                  style="text-align: center"
                  :rowspan="item.count"
                  v-if="i == 0"
                >
                  {{ item.no }}
                </td>
                <td :rowspan="item.count" v-if="i == 0">
                  ITEM NO: {{ item.item_no }}<br />
                  DEVICE NAME: {{ item.item_name }}<br />
                  ORIGINAL MANUFACTURE: {{ item.original_manufacture }}<br />
                  MANUFACTURE PN: {{ item.manufacture_pn }}<br />
                  DESCRIPTION: {{ item.specification }}
                </td>
                <td
                  :style="
                    'text-align: right; width: 50pt;' +
                    (item.count != v ? '' : '')
                  "
                >
                  {{ numberFormat(item.qty[i]) }}
                </td>
                <td
                  :style="
                    'text-align: center; width: 50pt;' +
                    (item.count != v ? '' : '')
                  "
                >
                  {{ item.unit[i] }}
                </td>
                <td
                  :style="
                    'text-align: center; width: 50pt;' +
                    (item.count != v ? '' : '')
                  "
                >
                  {{ item.allocation[i] }}
                </td>
                <td
                  :style="
                    'text-align: left; width: 120pt;' +
                    (item.count != v ? '' : '')
                  "
                >
                  {{ item.notes[i] }}
                </td>
              </tr>
            </template>
          </tbody>
        </table>
        <br />
        <br />
        <table class="default-table no-border">
          <tr>
            <td style="width: 33%"></td>
            <td style="width: 33%"></td>
            <td style="width: 33%">
              Surabaya,
              {{ new Date(selected.created_date).formatDate("D MMMM YYYY") }}
            </td>
          </tr>
          <tr>
            <td style="width: 33%">
              <br />
              Peminta<br /><br />
              <div id="qr_approval" style="min-height: 80pt"></div>
              ( {{ selected.peminta_name }} )
            </td>
            <td style="width: 33%">
              <br />
              <!--Peminta<br/><br/><br/><br/>-->
              <!--( {{selected.peminta_name}} )-->
            </td>
            <td style="width: 33%">
              <br />
              Kepala Lembaga<br /><br />
              <div id="qr_approval3" style="min-height: 80pt"></div>
              ( {{ selected.kabag_name }} )
            </td>
          </tr>
        </table>
      </v-print>
    </v-action-dialog>
  </v-container>
</template>

<style>
.table-align-top td {
  vertical-align: top;
}
.print-table td {
  text-wrap: wrap;
}
.print-header {
  border-bottom: 3px double black !important;
  border-style: double;
  padding: 0 !important;
}
.no-border,
.no-border td,
.no-border th {
  border: 0px !important;
}
.with-border,
.with-border td,
.with-border th {
  border: 1px solid black !important;
}
.no-top-border td {
  border-top: 0 !important;
}
.td-no-border-bottom {
  border-bottom: 0 !important;
}
</style>

<script>
module.exports = {
  name: "",
  creator: "",
  components: {
    /* 'document-form': 'url:ui/ewis/test/document_form.vue' */
    "spb-item": "url:ui/bom/warehouse/spb_item.vue",
    stock: "url:ui/bom/warehouse/spb_stock.vue",
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
    hideApproval: {
      type: Boolean,
      default: false,
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
          flag: 1,
          approved: 4,
        },
        filterType: {},
      },
    },
  },
  data: function () {
    return {
      name: "spb",
      itemKey: "id",
      url: "warehouse/spb",
      loading: false,
      status: {
        "-1": "Rejected",
        0: "New",
        1: "Waiting for Approval",
        2: "Approved by Peminta",
        4: "Approved by Kabag",
      },
      headers: [
        {
          text: "SPB Details",
          value: "details",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
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
          text: "SPB No",
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
          alias: "spb_no",
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
          visible: false,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          data_value: ["Spazio", "Trowulan", "Kediri"],
        },
        {
          text: "PO No",
          value: "po_id",
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
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "bom/purchase_order", //"bom/payment/complete",
          searchby: ["po_no"],
          formatter: ["id", "po_no"],
          options: {
            filter: {
              approved: 3,
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "po_no",
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
        },
        {
          text: "Peminta",
          value: "peminta_id",
          align: "start",
          sortable: false,
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
          url: App.url + "users",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              groups: "spb_peminta",
            },
            filterType: {
              groups: "%",
            },
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "peminta_name",
        },
        {
          text: "Kepala Bagian",
          value: "kabag_id",
          align: "start",
          sortable: false,
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
          url: App.url + "users",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              groups: "spb_kabag",
            },
            filterType: {
              groups: "%",
            },
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "kabag_name",
        },
        {
          text: "Approved By",
          value: "approved_by",
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
          alias: "approved_by_name",
        },
        /*{
					"text": "Created Date",
					"value": "created_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "datetime",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Created By",
					"value": "created_by",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "created_by_name"
				}, {
					"text": "Modified Date",
					"value": "modified_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "datetime",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Modified By",
					"value": "modified_by",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "modified_by_name"
				}, */ {
          text: "Flag",
          value: "flag",
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
          text: "Status",
          value: "approved",
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
      ],
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      isInDom: false,
      isInViewport: false,
      processData: {},
      dataid: {},
      stockData: {},
      dialogItem: false,
      dialogPrint: false,
      print: {},
    };
  },
  watch: {},
  computed: {
    disableAskApproval: function () {
      var self = this;
      if (self.selected == false) return true;
      if (self.selected.approved <= 0) return false;
      return true;
    },
    disableEdit: function () {
      var self = this;
      if (self.selected == false) return true;
      if (self.selected.approved <= 0) return false;
      return true;
    },
    disableDelete: function () {
      var self = this;
      if (self.selected == false) return true;
      if (self.selected.approved < 2) return false;
      return true;
    },
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
    openItem: function () {
      var self = this;
      self.dialogItem = true;
    },
    loadSpbStock: function () {
      var self = this;
      self.$refs.spbStock.$refs.template.getItems();
    },
    loadSpbAvailable: function () {
      var self = this;
      self.$refs.spbAvailable.$refs.template.getItems();
    },
    askApproval: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (!c) return true;
      if (self.selected.approved <= 0) {
        var r = await axios.put(App.url + self.url, {
          approved: 1,
          pk: self.selected.id,
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      } else if (self.selected.approved == 1) {
        App.errorMsg("Telah ada permintaan approval!");
      } else if (self.selected.approved == 2) {
        App.errorMsg("Telah di approve (Approval 2)!");
      } else if (self.selected.approved == 3) {
        App.errorMsg("Telah di approve (Approval 3)!");
      } else if (self.selected.approved == -1) {
        App.errorMsg("Approval di reject!");
      }
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
        self.dataid = {};
        self.stockData = {};
      } else {
        self.selected = val;
        self.processData = {
          purchase_order_id: val.po_id,
          spb_id: val.id,
        };
        self.dataid = {
          purchase_order_id: val.po_id,
        };
        self.stockData = {
          spb_id: val.id,
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
    openPrint: async function () {
      try {
        var self = this;
        var res = await axios.get(App.url + "warehouse/spbitem", {
          params: {
            filter: {
              spb_id: self.selected.id,
            },
            sortBy: ["item_id"],
          },
        });
        self.print.data = res.data.data;
        self.print.existing = {};
        var no = 1;
        self.print.data.map((val) => {
          if (self.print.existing[val.item_id] == undefined) {
            self.print.existing[val.item_id] = val;
            self.print.existing[val.item_id].no = no;
            self.print.existing[val.item_id].count = 0;
            self.print.existing[val.item_id].allocation = [val.allocation];
            self.print.existing[val.item_id].notes = [val.notes];
            self.print.existing[val.item_id].qty = [val.qty];
            self.print.existing[val.item_id].unit = [val.unit];
            no++;
          } else {
            self.print.existing[val.item_id].allocation.push(val.allocation);
            self.print.existing[val.item_id].notes.push(val.notes);
            self.print.existing[val.item_id].qty.push(val.qty);
            self.print.existing[val.item_id].unit.push(val.unit);
          }
          self.print.existing[val.item_id].count++;
        });
        //get approval1 qr info
        self.$nextTick(() => {
          setTimeout(() => {
            document.getElementById("qr_approval").innerHTML = "";
            document.getElementById("qr_approval3").innerHTML = "";
            if (self.selected.approved >= 2)
              var qrcode = new QRCode("qr_approval", {
                text:
                  "https://main.buanamultiteknik.com/#/sa/info/spb_approved_" +
                  self.selected.id,
                width: 100,
                height: 100,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H,
              });
            if (self.selected.approved >= 4)
              var qrcode = new QRCode("qr_approval3", {
                text:
                  "https://main.buanamultiteknik.com/#/sa/info/spb_approved3_" +
                  self.selected.id,
                width: 100,
                height: 100,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H,
              });
          }, 250);
        });
        self.dialogPrint = true;
      } catch (err) {
        App.errorMsg();
      }
    },
  },
  mounted: function () {
    var self = this;
    loadMultipleLibrary(
      "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js",
    );
    if (check_user(["spb_peminta"])) {
      self.itemsOptions = {
        filter: {
          flag: 1,
          approved: 2,
        },
        filterType: {
          approved_by: App.userData.data[0].userid,
        },
      };
    }
    if (check_user(["spb_kabag"])) {
      self.itemsOptions = {
        filter: {
          flag: 1,
          approved: 4,
        },
        filterType: {
          approved2_by: App.userData.data[0].userid,
        },
      };
    }
    if (check_user(["administrator"])) {
      self.itemsOptions = {
        filter: {
          flag: 1,
          approved: 4,
        },
        filterType: {
          //approved2_by: App.userData.data[0].userid
        },
      };
    }
    self.$nextTick(() => {
      self.$refs.template.getItems();
    });
  },
};
</script>
