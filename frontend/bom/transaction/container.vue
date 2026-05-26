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
      hide-filter-button
      :disable-table="disableTable"
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
      :table-fixed-header="tableFixedHeader"
    >
      <template v-slot:title>
        <v-text-field
          name="name"
          label="Search"
          v-model="search"
        ></v-text-field>
      </template>
      <template v-slot:menu-after-filter>
        <!-- <v-btn small color="primary" outlined @click="showBarcode=true" :disabled="!selected"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn> -->
        <v-btn small color="primary" outlined @click="createKoli"
          >Generate Barcode Koli</v-btn
        >
        <v-btn small color="primary" outlined @click="createSubkoli"
          >Generate Barcode Subkoli</v-btn
        >
        <!--<v-btn color="primary" outlined small @click="openReport">-->
        <!--                <v-icon small left>mdi-printer</v-icon>Print-->
        <!--            </v-btn>-->
        <v-btn
          small
          color="primary"
          :disabled="selected == false"
          @click="itemDialog = true"
          outlined
          >Koli</v-btn
        >
        <v-btn
          small
          color="primary"
          :disabled="selected == false"
          @click="itemsDialog = true"
          outlined
          >Add Items</v-btn
        >
        <v-btn
          small
          color="primary"
          :disabled="selected == false"
          @click="
            getReportItems();
            reportDialog = true;
          "
          outlined
        >
          <v-icon small left>mdi-printer</v-icon>Print</v-btn
        >
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="itemDialog"
      title="Koli"
      fullscreen
    >
      <koli :data="dataid" :key="selected.id"></koli>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="itemsDialog"
      title="Add Items"
      fullscreen
    >
      <subkoli-part :data="dataid" :key="selected.id"></subkoli-part>
    </v-action-dialog>
    <v-action-dialog
      v-model="reportDialog"
      title="Report"
      fullscreen
      label-save="print"
      @save="$refs.vprint.print()"
    >
      <v-print
        ref="vprint"
        style="padding: 20pt !important; margin: 0; padding-top: 0"
      >
        <table class="simple-table default-table default">
          <thead>
            <tr>
              <td
                colspan="15"
                style="
                  border-top: 0 !important;
                  border-right: 0 !important;
                  border-left: 0 !important;
                  height: 20pt;
                  background-color: #fff;
                "
              >
                &nbsp;
              </td>
            </tr>
            <tr>
              <th rowspan="2">No</th>
              <th rowspan="2">Item</th>
              <th rowspan="2">Drawing Number</th>
              <th rowspan="2">Part Number</th>
              <th rowspan="2">Thumbnail</th>
              <th rowspan="2">Qty</th>
              <th rowspan="2">Description</th>
              <th rowspan="2">Specification</th>
              <th rowspan="2">Application</th>
              <th rowspan="2">PO Number</th>
              <th colspan="3">Dimension Packaging</th>
              <th rowspan="2">Subcolly No</th>
              <th rowspan="2">Colly No</th>
            </tr>
            <tr>
              <th>Length (cm)</th>
              <th>Width (cm)</th>
              <th>Height (cm)</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(item, index) in reportItems">
              <tr v-if="item.no == 1" :key="item.koli_id + 'header'">
                <!-- v-if="(item.photo2||'').toString().trim()!=''"  -->
                <td
                  class="simple-table-td"
                  colspan="14"
                  v-if="item.photo2 != null"
                >
                  <img
                    :src="
                      'https://main.buanamultiteknik.com/api/transaction/subkolipart/photo2?id=' +
                      item.koli_id
                    "
                    style="
                      max-width: 1000px;
                      max-height: 1000px;
                      object-fit: contain;
                      margin: auto;
                      width: 300px;
                      height: 300px;
                    "
                  />
                </td>
                <td v-else class="simple-table-td" colspan="14"></td>
                <td class="simple-table-td" :rowspan="item.no_span + 1">
                  {{ item.koli_id }}
                </td>
              </tr>
              <tr v-if="item.no_subkoli == 1" :key="item.subkoli_id + 'header'">
                <!--  v-if="(item.photo||'').toString().trim()!=''" -->
                <td
                  class="simple-table-td"
                  colspan="13"
                  v-if="item.photo != null"
                >
                  <img
                    :src="
                      'https://main.buanamultiteknik.com/api/transaction/subkolipart/photo?id=' +
                      item.subkoli_id
                    "
                    style="
                      max-width: 500px;
                      max-height: 100px;
                      object-fit: contain;
                      margin: auto;
                      width: 100px;
                      height: 100px;
                    "
                  />
                </td>
                <td v-else class="simple-table-td" colspan="13"></td>
                <td class="simple-table-td" :rowspan="item.no_subkoli_span + 1">
                  {{ item.subkoli_id }}
                </td>
              </tr>
              <!--<tr v-if="item.no == 1 :key="item.koli_id+'header_koli'">-->
              <!--	<td class="simple-table-td" colspan="9">-->
              <!--	</td>-->
              <!--	<td class="simple-table-td"></td>-->
              <!--	<td class="simple-table-td"></td>-->
              <!--	<td class="simple-table-td"></td>-->
              <!--	<td class="simple-table-td"></td>-->
              <!--	<td class="simple-table-td"></td>-->
              <!--	<td class="simple-table-td">{{item.koli_id}}</td>-->
              <!--</tr>-->
              <tr :key="index">
                <td class="simple-table-td">{{ index + 1 }}</td>
                <td class="simple-table-td">{{ item.item }}</td>
                <td class="simple-table-td">{{ item.dwg_no }}</td>
                <td class="simple-table-td">{{ item.part_number }}</td>
                <td v-if="item.photo3 != null" class="simple-table-td">
                  <img
                    :src="
                      'https://main.buanamultiteknik.com/api/transaction/subkolipart/photo3?id=' +
                      item.id
                    "
                    style="
                      max-width: 100px;
                      max-height: 100px;
                      object-fit: contain;
                    "
                  />
                </td>
                <td v-else class="simple-table-td"></td>
                <td class="simple-table-td">{{ item.qty }}</td>
                <td class="simple-table-td">{{ item.description }}</td>
                <td class="simple-table-td">{{ item.specification }}</td>
                <td class="simple-table-td">{{ item.application }}</td>
                <td
                  class="simple-table-td"
                  v-if="item.no_po == 1"
                  :style="
                    reportItems[index + 1].no_po == 2
                      ? 'border-bottom: 0 !important'
                      : ''
                  "
                >
                  {{ item.po_no }}
                </td>
                <td
                  class="simple-table-td"
                  v-else
                  style="border-top: 0 !important; border-bottom: 0 !important"
                ></td>
                <td class="simple-table-td">{{ item.length }}</td>
                <td
                  class="simple-table-td"
                  v-if="item.width_span != undefined"
                  :rowspan="item.width_span"
                >
                  {{ item.width }}
                </td>
                <td
                  class="simple-table-td"
                  v-if="item.height_span != undefined"
                  :rowspan="item.height_span"
                >
                  {{ item.height }}
                </td>
                <!-- <td class="simple-table-td" v-if="item.no_subkoli==1" :style="(reportItems[index+1]||{no_subkoli:2}).no_subkoli == 2 ? 'border-bottom: 0 !important' : ''">{{item.subkoli_id}}</td> -->
                <!-- <td class="simple-table-td" v-else style="border-top: 0 !important; border-bottom: 0 !important;"></td> -->
                <!-- <td class="simple-table-td" style="border-top: 0 !important; border-bottom: 0 !important;"></td> -->
                <!-- <td></td> -->
              </tr>
            </template>
          </tbody>
          <tfoot>
            <tr>
              <td
                colspan="15"
                style="
                  border-bottom: 0 !important;
                  border-right: 0 !important;
                  border-left: 0 !important;
                  height: 20pt;
                "
              >
                &nbsp;
              </td>
            </tr>
          </tfoot>
        </table>
      </v-print>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="showBarcodeDialog"
      title="Barcode"
      fullscreen
    >
      <v-btn small color="primary" outlined @click="print">Print</v-btn>
      <v-print ref="vprint" style="color: #000">
        <koli-barcode v-model="dataBarcode" :key="selected.id"></koli-barcode>
      </v-print>
    </v-action-dialog>
  </v-container>
</template>

<style>
.simple-table-td {
  vertical-align: top;
}
table.default {
  border: 1px solid #ececef;
  font-size: 10px;
}
table.default th,
table.default th:first-child,
table.default th:last-child,
table.default td,
table.default td:first-child,
table.default td:last-child {
  border: 1px solid #ececef !important;
}
</style>

<script>
module.exports = {
  name: "container",
  creator: "",
  components: {
    koli: "url:ui/bom/transaction/koli.vue",
    "subkoli-part": "url:ui/bom/transaction/subkoli_part.vue",
    "koli-barcode": "url:ui/bom/transaction/barcode2.vue",
    "v-print": "url:ui/base/print2.vue",
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
      name: "Create Barcode",
      itemKey: "id",
      search: "",
      url: "transaction/container",
      showBarcode: false,
      reportDialog: false,
      showBarcodeDialog: false,
      dataBarcode: [],
      reportItems: [],
      loading: false,
      go: false,
      itemDialog: false,
      itemsDialog: false,
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
          text: "Description",
          value: "description",
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
          text: "Created Date",
          value: "created_date",
          align: "start",
          sortable: false,
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
          text: "Created By",
          value: "created_by",
          align: "start",
          sortable: false,
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
          text: "Modified Date",
          value: "modified_date",
          align: "start",
          sortable: false,
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
        {
          text: "Modified By",
          value: "modified_by",
          align: "start",
          sortable: false,
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
    search: function (val) {
      var self = this;
      if (val.length >= 3) {
        self.$refs.template.defaultItemsOptions.filter.description = val;
      }
      if (val.length == 0) {
        delete self.$refs.template.defaultItemsOptions.filter.description;
      }
      self.$refs.template.getItems();
    },
    async showBarcodeDialog(val) {
      if (val == false) this.showBarcode = false;
    },
    async showBarcode(val) {
      var self = this;
      if (val) {
        self.loading = true;
        try {
          App.tmp.target = self;
          var res = await axios.get(App.url + "/transaction/koli", {
            params: {
              filter: {
                container_id: self.selected.id,
              },
              limit: 999999,
            },
          });

          self.go = true;
          var tmp = [];

          // res.data.data.map(val=>{
          // 	if(Number(val.qty)<=60)
          // 		tmp.push(Array(Number(val.qty)).fill(val))
          // 	else
          // 		tmp.push([val])
          // })
          //console.log(tmp)
          res.data.data.map((val) => {
            val.print = `${val.container_id}.${val.id}.a`;
          });
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
        container_id: this.selected.id,
      };
    },
  },
  methods: {
    async getReportItems() {
      var self = this;
      try {
        var res = await axios.get(App.url + "transaction/subkolipart/report", {
          params: {
            container_id: self.selected.id,
          },
        });
        var totalSubkoli = 0;
        var koli = 0,
          subkoli = 0,
          po = 0,
          width = 0,
          height = 0;
        res.data.data.map((val, i) => {
          if (val.no == 1) {
            if (res.data.data[koli].no_span) {
              res.data.data[koli].no_span += totalSubkoli;
            }
            koli = i;
            width = i;
            height = i;
            subkoli = i;
            totalSubkoli = 0;
            po = i;
            res.data.data[koli].no_span = 0;
            res.data.data[subkoli].no_subkoli_span = 0;
            res.data.data[koli].no_po_span = 0;
          }
          if (val.no_subkoli == 1) {
            totalSubkoli++;
            subkoli = i;
            width = i;
            height = i;
            po = i;
            res.data.data[subkoli].no_subkoli_span = 0;
            res.data.data[koli].no_po_span = 0;
          }
          if (val.no_po == 1) {
            po = i;
            res.data.data[koli].no_po_span = 0;
          }
          if (val.width != res.data.data[width].width) {
            width = i;
          }
          if (res.data.data[width].width_span == undefined)
            res.data.data[width].width_span = 0;
          if (val.height != res.data.data[height].height) {
            height = i;
          }
          if (res.data.data[height].height_span == undefined)
            res.data.data[height].height_span = 0;
          if (val.photo != null) {
            res.data.data[koli].photo = val.photo;
          }
          if (val.photo2 != null) {
            res.data.data[koli].photo2 = val.photo2;
          }
          if (val.photo3 != null) {
            res.data.data[koli].photo3 = val.photo3;
          }
          res.data.data[width].width_span++;
          res.data.data[height].height_span++;
          res.data.data[koli].no_span++;
          res.data.data[subkoli].no_subkoli_span++;
          res.data.data[po].no_po_span++;
        });
        res.data.data[koli].no_span += totalSubkoli;
        self.reportItems = res.data.data;
      } catch (error) {
        App.errorMsg();
      }
    },
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
    createKoli: async function () {
      var self = this;
      var q = prompt("Input jumlah koli!");
      App.tmp.target = self;
      if (q) {
        try {
          if (isNaN(q)) {
            alert("Isi dengan angka!");
          } else {
            var res = await axios.post(App.url + "transaction/koli/auto", {
              qty: Number(q),
            });
            self.go = true;
            var tmp = [];

            res.data.data.map((val) => {
              val.print = `BMT.Colly.${val.id.toString().padStart(4, "0")}`;
            });
            self.dataBarcode = res.data.data;
            self.loading = false;
            self.showBarcodeDialog = true;
          }
        } catch (err) {
          console.log(err);
          App.errorMsg();
        }
      }
    },
    createSubkoli: async function () {
      var self = this;
      var q = prompt("Input jumlah subkoli!");
      App.tmp.target = self;
      if (q) {
        try {
          if (isNaN(q)) {
            alert("Isi dengan angka!");
          } else {
            var res = await axios.post(App.url + "transaction/subkoli/auto", {
              qty: Number(q),
            });
            self.go = true;
            var tmp = [];

            res.data.data.map((val) => {
              val.print = `BMT.SUBCOLLY.${val.id.toString().padStart(4, "0")}`;
            });
            self.dataBarcode = res.data.data;
            self.loading = false;
            self.showBarcodeDialog = true;
          }
        } catch (err) {
          console.log(err);
          App.errorMsg();
        }
      }
    },
    openReport: function () {
      var self = this;
      window.open(
        "https://main.buanamultiteknik.com/api/report/container/index?id=" +
          self.selected.id +
          "&rand=" +
          randomId(),
      );
    },
  },
  mounted: function () {
    loadMultipleLibrary(
      "https://main.buanamultiteknik.com/js/qrcode.js;https://cdn.jsdelivr.net/npm/bwip-js@4.4.0/dist/bwip-js.min.js",
    );
  },
};
</script>
