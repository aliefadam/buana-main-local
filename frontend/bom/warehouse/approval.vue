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
      :show-expand="true"
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
      <!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->
      <!-- 
            <template v-slot:prepend-menu>
            </template>
			 -->

      <template v-slot:item.approved="props">
        {{ status[props.item.approved] }}
      </template>
      <template v-slot:item.photo="props">
        <a
          :href="props.item.photo"
          v-if="props.item.photo != ''"
          target="_blank"
          >Photo</a
        >
        <span v-else>-</span>
      </template>

      <template v-slot:menu-after-filter>
        <v-btn
          small
          color="primary"
          outlined
          @click="openPrint"
          :disabled="!selected"
          >Print SPB</v-btn
        >
        <v-btn
          color="success"
          v-if="!hideApproval"
          outlined
          small
          @click="openNote('approve')"
          :disabled="disableApproval"
        >
          Approve
        </v-btn>
        <v-btn
          color="error"
          v-if="!hideApproval"
          outlined
          small
          @click="openNote('reject')"
          :disabled="disableApproval"
        >
          Reject
        </v-btn>
        <v-btn
          color="warning"
          outlined
          small
          @click="openNote('revision')"
          :disabled="!allowRevisi"
        >
          Revise
        </v-btn>
        <!-- <v-btn small color="primary" outlined @click="dialogItem=true" :disabled="!selected">Items</v-btn> -->
      </template>

      <template v-slot:item.details="props">
        <span>SPB No:</span> {{ props.item.spb_no }}<br />
        <span>Created By:</span> {{ props.item.created_by_name }}<br />
        <span>Created Date:</span>
        {{ new Date(props.item.created_date).formatDate("D MMMM YYYY") }}<br />
      </template>
      <template v-slot:item.reference="props">
        <span>PR No: </span
        ><a
          :href="props.item.pr_doc_url_final"
          v-if="props.item.pr_doc_url_final"
          target="_blank"
          >{{ props.item.pr_no }}</a
        ><span v-else>{{ props.item.pr_no }}</span>
        <br />
        <span>PO No: </span>
        <span v-if="props.item.po_no" @click="openReport"
          ><a>{{ props.item.po_no }}</a></span
        ><span v-else>-</span><span v-else>-</span>
      </template>
      <template v-slot:expanded-item="props">
        <td
          ref="expanded"
          :colspan="props.headers.length"
          style="height: auto"
          :key="props.item[itemKey]"
        >
          <spb-item
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
            table-only
            :key="props.item[itemKey]"
            :sel="{
              spb_id: props.item.id,
            }"
            name=""
            :data="{
              spb_id: props.item[itemKey],
            }"
          ></spb-item>
        </td>
      </template>
      <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
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
        <v-row no-gutters style="flex: 1">
          <v-cell cols="12" style="width: 100%">
            <stock
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
      <v-print
        ref="vprint"
        style="margin: auto; color: #000; max-width: 100%; padding: 20pt"
      >
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
            <td style="width: 100px">PO NO</td>
            <td>: {{ selected.po_no }}</td>
          </tr>
          <tr>
            <td>PR NO</td>
            <td>: {{ selected.pr_no }}</td>
          </tr>
          <tr>
            <td>ARRIVAL DATE</td>
            <td>
              :
              <span v-if="selected.arrival_date">{{
                new Date(selected.arrival_date).formatDate("D MMMM YYYY")
              }}</span>
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
              Logistik<br /><br />
              <div id="qr_approval" style="min-height: 80pt"></div>
              ( {{ selected.kelog_name }} )
            </td>
            <td style="width: 33%">
              <br />
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
    <v-action-dialog
      v-model="dialogNote"
      title="Note"
      min-height="75%"
      @save="saveFlag"
    >
      <v-textarea label="Note" v-model="note"></v-textarea>
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
    "spb-item": "url:ui/bom/warehouse/spb_stock.vue",
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
      default: true,
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
          approved: 1,
          flag: 1,
          kelog_id: App.userData.data[0].userid,
        },
        filterType: {},
        approval: 1,
      },
    },
  },
  data: function () {
    return {
      dialogNote: false,
      note: "",
      action: "",
      name: "spb",
      itemKey: "id",
      url: "warehouse/spb",
      loading: false,
      status: {
        "-1": "Rejected",
        0: "New",
        1: "Waiting for Approval Kepala Bagian",
        2: "Approved",
      },
      headers: [
        {
          text: "",
          value: "data-table-expand",
        },
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
          filterable: true,
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
          text: "SPB Notes",
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
          text: "Approval 1 Note",
          value: "approval_notes",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
        },
        {
          text: "Reject 1 Note",
          value: "reject_notes",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
        },
        {
          text: "Approval 2 Note",
          value: "approval3_notes",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
        },
        {
          text: "Reject 2 Note",
          value: "reject2_notes",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
        },
        {
          text: "Revision Note",
          value: "revisi_notes",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
        },
        {
          text: "Arrival Date",
          value: "arrival_date",
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
          text: "Reference",
          value: "reference",
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
          text: "PR No",
          value: "pr_no",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          data_value: [],
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: true,
        },
        {
          text: "PR DOC URL",
          value: "pr_doc_url",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
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
          visible: false,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "bom/payment/complete",
          searchby: ["po_no"],
          formatter: ["po_id", "po_no"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "po_no",
        },
        {
          text: "PO DOC URL",
          value: "po_doc_url",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
          disabled: false,
          visible: false,
          required: false,
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
        {
          text: "Logistik",
          value: "kelog_id",
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
          url: App.url + "users",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              groups: "kepala_logistik",
            },
            filterType: {
              groups: "%",
            },
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "kelog_name",
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
          text: "Status Approval",
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
          required: true,
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "users",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              groups: "adm_logistik",
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
        /*{
					"text": "Approved By",
					"value": "approved_by",
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
					"alias": "approved_by_name"
				} , {
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
          text: "Approved",
          value: "approved",
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
          text: "Ask Approval Notes",
          value: "ask_approval_notes",
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
          filter: false,
          groupable: false,
        } /*{
					"text": "Photo",
					"value": "photo",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"data_value": [],
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
                }*/,
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
      dialogPrint: false,
      print: {},
    };
  },
  watch: {},
  computed: {
    disableApproval: function () {
      var self = this;
      if (self.selected == false) return true;
      if (self.selected.approved == 1) return false;
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
    allowRevisi: function () {
      var self = this;

      if (!self.selected) return false;
      if (self.selected.approved > 0 && self.selected.approved < 4) return true;
      return false;
    },
  },
  methods: {
    saveFlag: function () {
      var self = this;
      try {
        self[self.action]();
        self.action = "";
        self.note = "";
        self.dialogNote = false;
      } catch (err) {
        App.errorMsg();
      }
    },
    openNote: function (action) {
      var self = this;
      try {
        self.action = action;
        self.dialogNote = true;
        self.note = "";
      } catch (err) {
        App.errorMsg();
      }
    },
    revision: async function () {
      var self = this;
      var q = confirm("Are you sure you want to revise this SPB?");
      if (!q) return true;
      else {
        try {
          var r = await axios.get(App.url + "warehouse/spb/revisi", {
            params: {
              id: self.selected.id,
              revisi_notes: self.note,
              is_bom: self.selected.is_bom,
            },
          });
          if (!r.data.status) App.errorMsg(r.data);
          else {
            self.$refs.template.getItems();
            App.successMsg();
          }
        } catch (err) {
          App.errorMsg();
        }
      }
    },
    loadSpbStock: function () {
      var self = this;
      self.$refs.spbStock.$refs.template.getItems();
    },
    loadSpbAvailable: function () {
      var self = this;
      self.$refs.spbAvailable.$refs.template.getItems();
    },
    approve: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (!c) return true;
      if (self.selected.approved == 1) {
        var r = await axios.put(App.url + self.url, {
          approved: 2,
          pk: self.selected.id,
          approval_notes: self.note,
          is_bom: self.selected.is_bom,
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
    },
    reject: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (!c) return true;
      if (self.selected.approved == 1) {
        var r = await axios.put(App.url + self.url, {
          approved: -1,
          pk: self.selected.id,
          reject_notes: self.note,
          is_bom: self.selected.is_bom,
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
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
          spb_id: val.id,
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
    openReport: function () {
      var self = this;
      var name = self.selected.po_no.replace(/\//g, "_").replace(/\-/g, "_");
      var randomid = randomId();
      window.open(
        "https://main.buanamultiteknik.com/api/data/reportponoprice?id=" +
          self.selected.po_id +
          "&filename=" +
          name +
          "&idx=" +
          randomid,
      );
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
            limit: -1,
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
    if (check_user(["administrator"])) {
      delete self.itemsOptions.filter.kelog_id;
      delete self.itemsOptions.filter.kabag_id;
    }
  },
};
</script>
