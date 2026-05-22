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
      :disable-delete-button="checkSelected('approved') >= 0"
      :disable-edit-button="checkSelected('approved') >= 0"
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
      <!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->

      <template v-slot:menu-after-filter> </template>

      <!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
      <template v-slot:item.npb_details="props">
        <span>NPB No:</span> {{ props.item.npbno }}<br />
        <span>Created By:</span> {{ props.item.created_by_name }}<br />
        <span>Created Date:</span> {{ props.item.created_date }}<br />
      </template>
      <template v-slot:item.info_notes="props">
        <span> {{ props.item.notes }}</span
        ><br /><br />
        <b v-if="props.item.notify_id"
          >Notify Group: {{ props.item.notify_name }}</b
        >
        <b v-else></b>
      </template>
      <template v-slot:item.approved="props">
        {{ status[props.item.approved] }}
      </template>
      <template v-slot:item.tipe="props">
        {{ ["Pengeluaran", "Peminjaman"][props.item.tipe] }}
      </template>

      <template v-slot:item.approval_status="props">
        <span>Status:</span> {{ status[props.item.approved] }}<br />
        <span>Validated By:</span> {{ props.item.approved_by_name }}<br />
        <span>Validated Date:</span> {{ props.item.approved_date }}<br />
      </template>

      <template v-slot:item.approval_notes="props">
        <span>Ask Approval:</span><br />{{ props.item.ask_approval_notes
        }}<br />
        <span>Approved Peminta:</span><br />{{ props.item.approval_notes
        }}<br />
        <!-- <span>Approved Kepala Bagian:</span> {{ props.item.approval2_notes }}<br />
                <span>Reject Peminta:</span> {{ props.item.reject_notes }}<br />
                <span>Reject Kepala Bagian:</span> {{ props.item.reject2_notes }}<br />
                <span>Revisi Peminta:</span><br />{{ props.item.revisi_notes }}<br />
                <span>Revisi Kepala Bagian:</span><br />{{ props.item.revisi_notes }}<br /> -->
      </template>

      <template v-slot:append-menu>
        <v-btn
          color="primary"
          outlined
          small
          @click="openNote('revision')"
          :disabled="!allowRevisi"
        >
          Revisi
        </v-btn>
        <v-btn
          small
          color="primary"
          outlined
          @click="openPrint"
          :disabled="!selected"
          >Print NPB</v-btn
        >
        <v-btn
          color="success"
          outlined
          small
          @click="openNote('approve')"
          :disabled="disableApproval"
        >
          Approve
        </v-btn>
        <v-btn
          color="error"
          outlined
          small
          @click="openNote('reject')"
          :disabled="disableApproval"
        >
          Reject
        </v-btn>
        <v-btn
          small
          color="primary"
          outlined
          @click="openItem"
          :disabled="!selected"
          >Items</v-btn
        >
      </template>

      <v-action-dialog
        fullscreen
        :actions="false"
        title="NPB Item"
        v-model="dialogItem"
      >
        <npb-item
          :data="{ npb_id: selected.id }"
          :table-only="[4].includes(checkSelected('flag'))"
        ></npb-item>
      </v-action-dialog>
      <v-action-dialog
        label-save="print"
        @save="$refs.vprint.print()"
        v-model="dialogPrint"
        title="NPB Print"
        min-height="75%"
        fullscreen
      >
        <v-print ref="vprint" style="margin: auto; color: #000; padding: 20pt">
          <nav class="tw-header print-header">
            <h1 class="px-2 text-xl text-center my-auto uppercase font-bold">
              <img src="../img/logo2.png" />
            </h1>
            <div style="align-self: end; font-size: 10px">
              Jl. Mayjend Yono Soewoyo No. 35 - Surabaya 60225 - Indonesia -
              Telp. (031) 99900899
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
            <br />NOTA
            {{
              selected.tipe == 0 ? "PENGAMBILAN" : "PENGELUARAN"
            }}
            BARANG<br />
            <span style="font-size: 12px; font-weight: normal"
              >No: {{ selected.npbno }}</span
            ><br />
            <span style="font-size: 12px; font-weight: normal">{{
              selected.notes
            }}</span
            ><br />
            <span style="font-size: 12px; font-weight: normal"
              >Allocation: {{ selected.project_name }}</span
            >
          </div>
          <br /><br /><br />
          <table
            class="default-table with-border print-table table-align-top"
            style="max-width: 100%"
          >
            <thead>
              <tr>
                <th>No.</th>
                <th>Part No.</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Satuan</th>
                <th>Po No</th>
                <th>Keterangan</th>
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
                    {{ item.item_no }}
                  </td>
                  <td :rowspan="item.count" v-if="i == 0">
                    <b>{{ item.item_name }} </b><br /><b>Manufacture PN:</b>
                    {{ item.manufacture_pn }}<br />
                    <b>Description:</b> {{ item.specification }}
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
                    {{ item.po_no_item[i] }}
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
          <span v-if="selected.tipe == 1"> Notes: Peminjaman </span>
          <br />
          <br />
          <table
            style="
              border: 0;
              border-collapse: collapse;
              font-size: 12px;
              width: 100%;
            "
          >
            <tr>
              <td style="page-break-inside: avoid">
                <table class="default-table no-border">
                  <tr>
                    <td style="width: 33%"></td>
                    <td style="width: 33%"></td>
                    <td style="width: 33%">
                      Surabaya,
                      {{
                        new Date(selected.created_date).formatDate(
                          "D MMMM YYYY",
                        )
                      }}
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
              </td>
            </tr>
          </table>
        </v-print>
      </v-action-dialog>
    </v-template>
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
    "npb-item": "url:ui/bom/warehouse/npb_item.vue",
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
        filter: {
          approved: 2,
          flag: 1,
          kabag_id: App.userData.data[0].userid,
        },
        filterType: {},
      },
    },
  },
  data: function () {
    return {
      dialogNote: false,
      note: "",
      action: "",
      status: {
        "-1": "Rejected",
        0: "New",
        1: "Asking Approval",
        2: "Waiting for Approval",
        4: "Approved by Kabag",
      },
      name: "NPB",
      itemKey: "id",
      url: "transaction/npb",
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
          text: "NPB Details",
          value: "npb_details",
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
          text: "NPB No",
          value: "npb__no",
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
          text: "Type",
          value: "tipe",
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
          default: 0,
          form: true,
          filter: true,
          groupable: false,
          data_value: [
            { text: "Pengeluaran", value: 0 },
            { text: "Peminjaman", value: 1 },
          ],
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
          form: true,
          filter: true,
          groupable: false,
          clearable: true,
          url: App.url + "project/project",
          searchby: ["full"],
          formatter: ["id", "full"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "project_no",
        },
        {
          text: "Project Name",
          value: "project_name",
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
          form: false,
          filter: true,
          groupable: false,
          url: "",
          searchby: "",
          formatter: "",
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
          text: "Notes",
          value: "info_notes",
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
        {
          text: "NPB Notes",
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
          text: "Notify Grup",
          value: "notify_id",
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
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "admin/nowa",
          searchby: ["description"],
          formatter: ["id", "description"],
          options: {
            filter: {
              flag: 1,
            },
            filterType: {},
            filterCondition: {},
          },
          alias: "notify_name",
        },
        {
          text: "Item No",
          value: "item_in_npb",
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
          alias: "item_no",
          filter: true,
          groupable: false,
          url: App.url + "bom/item",
          searchby: ["full"],
          formatter: ["item_search", "full"],
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
        },
        {
          text: "Subassembly",
          value: "subassembly_in_npb",
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
          clearable: true,
          url: App.url + "maintenance/msubassembly",
          searchby: ["subassy"],
          formatter: ["subassy_search", "subassy"],
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
          text: "Approval 1 Note",
          value: "approval1_notes",
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
          value: "approval2_notes",
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
          text: "Peminta",
          value: "peminta_id",
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
              groups: "npb_peminta",
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
              groups: "npb_kabag",
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
          value: "approval_status",
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
          text: "Validated By",
          value: "approved_by",
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
          alias: "approved_by_name",
        },
        {
          text: "Validated Date",
          value: "approved_date",
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
      ],
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      isInDom: false,
      isInViewport: false,
      dialogItem: false,
      dialogPrint: false,
      print: {},
      dialogPrint: false,
      print: {},
      /* dialogNote: false,
				approval_note: '' */
    };
  },
  watch: {
    dialogItem: function (val) {
      if (!val) {
        this.$refs.template.getItems();
      }
    },
  },
  computed: {
    disableApproval: function () {
      var self = this;
      if (self.selected == false) return true;
      if (self.selected.approved == 2) return false;
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
      if (self.selected.approved <= 4) return true;
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
      var q = confirm("Are you sure you want to revise this NPB?");
      if (!q) return true;
      else {
        try {
          var r = await axios.get(App.url + "transaction/npb/revisi", {
            params: {
              id: self.selected.id,
              revisi_notes: self.note,
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
    checkSelected: function (key, value) {
      var self = this;
      if (self.selected == false) return false;
      if (value == undefined) return self.selected[key];
      return self.selected[key] == value;
    },
    openItem: function () {
      var self = this;
      self.dialogItem = true;
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
    approve: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (!c) return true;
      if (self.selected.approved == 2) {
        var r = await axios.put(App.url + self.url, {
          approved: 4,
          approval3_notes: self.note,
          pk: self.selected.id,
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.dialogNote = false;
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
        });
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.dialogNote = false;
    },
    completeNPB: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (c) {
        var res = await axios.put(App.url + "transaction/npb", {
          pk: self.selected.id,
          approved: 1,
        });

        if (!res.data.status) App.errorMsg();
        else {
          App.successMsg();
          self.$refs.template.getItems();
        }
      }
    },
    openPrint: async function () {
      try {
        var self = this;
        var res = await axios.get(App.url + "transaction/npbitem", {
          params: {
            filter: {
              npb_id: self.selected.id,
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
            self.print.existing[val.item_id].po_no_item = [val.po_no_item];

            no++;
          } else {
            self.print.existing[val.item_id].allocation.push(val.allocation);
            self.print.existing[val.item_id].notes.push(val.notes);
            self.print.existing[val.item_id].qty.push(val.qty);
            self.print.existing[val.item_id].unit.push(val.unit);
            self.print.existing[val.item_id].po_no_item.push(val.po_no_item);
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
                  "https://main.buanamultiteknik.com/#/sa/info/npb_approved_" +
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
                  "https://main.buanamultiteknik.com/#/sa/info/npb_approved3_" +
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
      delete self.itemsOptions.filter.peminta_id;
      delete self.itemsOptions.filter.kabag_id;
    }
  },
};
</script>
