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
      <template v-slot:item.spb_details="props">
        <span>SPB No:</span> {{ props.item.spb_no }}<br />
        <span>Created By:</span> {{ props.item.created_by_name }}<br />
        <span>Created date:</span> {{ props.item.created_date }}<br />
      </template>
      <template v-slot:item.approved="props">
        {{ status[props.item.approved] }}<br /><br />
        <span v-if="props.item.approved_date">
          <b>Validated</b><br />
          <span>By:</span> {{ props.item.approved_by_name }}<br />
          <span>Date:</span> {{ props.item.approved_date }}<br /><br />
        </span>
        <span v-else></span>
        <!-- <b v-if="props.item.approval_date">Approved 1 Date:  {{props.item.approval_date}}</b> -->
      </template>
      <template v-slot:item.reference="props">
        <span>PR No: </span>
        <span v-if="props.item.pr_no">{{ props.item.pr_no }}</span>
        <span v-else>-</span><br />
        <b>Doc URL:</b>
        <a
          :href="props.item.pr_doc_url_final"
          v-if="props.item.pr_doc_url_final"
          target="_blank"
          >Open Link</a
        ><span v-else>-</span><br /><br />
        <span>PO No: </span>
        <span v-if="props.item.po_no">{{ props.item.po_no }}</span
        ><span v-else>-</span> <br />
        <b>Doc URL:</b>
        <span v-if="props.item.po_no" @click="openReport"
          ><a>Open Link</a></span
        >
        <!-- <a :href="'https://decafet.com/api/report?type=pdf&file=po&filename='+props.item.po_no.replace(/\//g, '_').replace(/\-/g, '_')+'&engine=easytemplate&po_id='+props.item.po_id" target="_blank" v-if="props.item.po_no">
                    Open Link</a> -->
        <span v-else> -</span>
      </template>
      <template v-slot:item.approval_notes="props">
        <span>Ask Approval: </span><br />{{ props.item.ask_approval_notes
        }}<br /><br />
        <span>Approved Logistik:</span><br />{{ props.item.approval_notes
        }}<br /><br />
        <!-- <span>Approved Kepala Bagian:</span> {{ props.item.approval2_notes }}<br />
                <span>Reject Peminta:</span> {{ props.item.reject_notes }}<br />
                <span>Reject Kepala Bagian:</span> {{ props.item.reject2_notes }}<br /> -->
        <span>Revisi</span><br />{{ props.item.revisi_notes }}<br />
      </template>

      <!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->
      <!-- 
            <template v-slot:prepend-menu>
            </template>
			 -->

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
          color="primary"
          v-if="!hideApproval"
          outlined
          small
          @click="openNote('askApproval')"
          :disabled="disableAskApproval"
        >
          Ask Approval
        </v-btn>
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
        <!-- <v-btn small color="primary" outlined @click="openItem" :disabled="!selected" v-else>Items</v-btn> -->
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
        <v-row
          no-gutters
          style="flex: 1"
          v-if="selected.approved <= 0 && selected.po_id != null"
        >
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
              :hide-add-button="
                selected.po_id != null || selected.approved == 1
              "
              @after-delete="loadSpbAvailable"
              :key="selected.id"
              :data="stockData"
              ref="spbStock"
            ></stock>
          </v-cell>
        </v-row>
      </v-container>
      <spb-item2
        v-else
        type="bom"
        :data="stockData"
        :sel="processData"
        :key="selected.id"
      ></spb-item2>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogNote"
      title="Ask Approval Note"
      min-height="75%"
      @save="saveFlag"
    >
      <v-textarea label="Note" v-model="ask_approval_notes"></v-textarea>
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
              :<span v-if="selected.arrival_date">
                {{
                  new Date(selected.arrival_date).formatDate("D MMMM YYYY")
                }}</span
              >
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
    "spb-item2": "url:ui/bom/warehouse/spb_item2.vue",
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
          flag: 0,
          approved: "0, 3",
        },
        filterType: {
          flag: "!=",
          approved: "btw",
        },
      },
    },
  },
  data: function () {
    return {
      name: "SPB Entry",
      itemKey: "id",
      url: "warehouse/spb",
      loading: false,
      status: {
        "-1": "Rejected",
        0: "New",
        1: "Asking for Approval Logistik",
        2: "Asking for Approval Kepala Bagian",
        4: "Approved by Kepala Bagian",
      },
      dialogNote: false,
      action: "",
      ask_approval_notes: "",
      headers: [
        {
          text: "SPB Details",
          value: "spb_details",
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
          alias: "spb_no",
        },
        {
          text: "SPB No",
          value: "spb_no",
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
            "Spazio",
            "Trowulan",
            "Kediri",
            "Kepanjen",
            "China",
            "KTNG Pasuruan",
          ],
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
          text: "SPB Type",
          value: "spb_from",
          type: "list",
          filter: false,
          visible: false,
          data_value: ["From PO", "Manual"],
          default: "From PO",
          input: function (data) {
            var self = App.page();
            self.headersObj.po_id.form = false;
            self.headersObj.bom_receive_id.form = false;
            if (data.data == "From PO") {
              self.headersObj.po_id.form = true;
              self.headersObj.po_id.data = null;
            }
          },
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
          text: "BOM Receive No",
          value: "bom_receive_id",
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
          form: false,
          filter: true,
          groupable: false,
          url: App.url + "transaction/bom", //"bom/payment/complete",
          searchby: ["name"],
          formatter: ["id", "info"],
          options: {
            filter: {
              machine_no: "null",
            },
            filterType: {
              machine_no: "!=",
            },
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "bom_no",
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
          page: "0",
          limit: "10",
        },
        {
          text: "PR No",
          value: "pr_no",
          align: "start",
          sortable: true,
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
          value: "pr_doc_url_final",
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
          type: "list",
          disabled: false,
          visible: true,
          required: true,
          form: false,
          filter: true,
          groupable: false,
          data_value: [
            {
              text: "New",
              value: 0,
            },
            {
              text: "Asking for Approval Logistik",
              value: 1,
            },
            {
              text: "Asking for Approval Kepala Bagian",
              value: 2,
            },
          ],
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
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "logistic_admin",
              sub_group_name: "logistic_admin",
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
              group_name: "logistic_admin",
              sub_group_name: "logistic_admin",
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
        {
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
          text: "Approval Notes",
          value: "approval_notes",
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
    saveFlag: function () {
      var self = this;
      try {
        self[self.action]();
        self.action = "";
        self.ask_approval_notes = "";
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
        self.ask_approval_notes = "";
      } catch (err) {
        App.errorMsg();
      }
    },
    askApproval: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (!c) return true;
      if (self.selected.approved <= 0) {
        var r = await axios.put(App.url + self.url, {
          approved: 1,
          pk: self.selected.id,
          ask_approval_notes: self.ask_approval_notes,
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
        self.headersObj.po_id.form = true;
        self.headersObj.spb_from.data = "From PO";
        self.headersObj.bom_receive_id.form = false;
      } else {
        self.selected = val;
        if (![false, undefined, "", null].includes(val.po_id)) {
          self.headersObj.po_id.form = true;
          self.headersObj.bom_receive_id.form = false;
          self.headersObj.spb_from.data = "From PO";
          val.spb_from = "From PO";
        } else {
          self.headersObj.po_id.form = false;
          self.headersObj.bom_receive_id.form = false;
          self.headersObj.spb_from.data = "Manual";
          val.spb_from = "Manual";
        }
        if (val.po_id != null)
          self.processData = {
            purchase_order_id: val.po_id,
            spb_id: val.id,
          };
        else
          self.processData = {
            purchase_order_id: 0,
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
          } else {
            self.print.existing[val.item_id].allocation.push(val.allocation);
            self.print.existing[val.item_id].notes.push(val.notes);
            self.print.existing[val.item_id].qty.push(val.qty);
            self.print.existing[val.item_id].unit.push(val.unit);
          }
          self.print.existing[val.item_id].count++;
          no++;
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
  },
  mounted: function () {
    loadMultipleLibrary(
      "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js",
    );
  },
};
</script>
