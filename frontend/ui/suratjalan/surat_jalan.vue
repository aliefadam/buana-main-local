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
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b> {{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          outlined
          small
          @click="
            action = 'revision';
            dialogNote = true;
          "
          :disabled="allowRevisi"
          v-if="history || selected.approved == 3 || revise"
        >
          Revise
        </v-btn>
        <!--<v-btn small color="primary" outlined :disabled="selected == false || disableAskApproval " @click="dialogItemGroup=true" v-if="!history"  >Items</v-btn>-->
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="dialogItemGroup = true"
          v-if="!history"
          >Items</v-btn
        >
        <!-- <v-btn small color="primary" outlined :disabled="selected == false" @click="dialogNotes=true">Notes</v-btn> -->
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="print"
          >Preview</v-btn
        >
        <v-btn
          small
          color="success"
          outlined
          v-if="!tableOnly && !history"
          :disabled="disableAskApproval"
          @click="
            action = 'askApproval';
            dialogNote = true;
          "
          >Ask Approval</v-btn
        >
      </template>
      <template v-slot:item.surat_jalan_detail="props">
        <span>No: </span>{{ props.item.sj_no }}<br />
        <span>Date: </span>{{ props.item.sj_date }}<br />
        <span>Created By: </span>{{ props.item.created_by_name }}<br />
        <span>Created Date: </span>{{ props.item.created_date }}<br />
        <span>Modified By: </span>{{ props.item.modified_by_name }}<br />
        <span>Modified Date: </span>{{ props.item.modified_date }}
      </template>
      <template v-slot:item.detail_status="props">
        <b>{{ status[props.item.approved] }}</b>
        <span v-if="props.item.ask_approval_date">
          <br /><br /><span>Asking Date:</span> {{ props.item.ask_approval_date
          }}<br /><span>Admin Notes:</span>
          {{ props.item.ask_approval_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.approved_date">
          <br /><br /><b>Approved:</b><br />
          <span>By:</span> {{ props.item.approved_by_name }}<br />
          <span>Date:</span> {{ props.item.approved_date }}<br />
          <span>Notes:</span> {{ props.item.approved_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.rev > 0">
          <br /><br /><b>Revised:</b><br />
          <span>By:</span> {{ props.item.revised_name }}<br />
          <span>Date:</span> {{ props.item.revised_date }}<br />
          <span>Notes:</span> {{ props.item.revised_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.rejected_date">
          <br /><br /><b>Rejected:</b><br />
          <span>By:</span> {{ props.item.rejected_by_name }}<br />
          <span>Date:</span> {{ props.item.rejected_date }}<br />
          <span>Notes:</span> {{ props.item.rejected_notes }} </span
        ><span v-else></span>
      </template>
      <template v-slot:item.address="props">
        <span>From: </span> {{ addressInfo(props.item.from_info) }}<br />
        <!--<span>To: </span>{{addressInfo(props.item.to_info)}}<br/>-->
        <span>Place Name: </span>{{ props.item.place }}<br />
        <span>UP: </span>{{ props.item.up }}
      </template>
      <template v-slot:expanded-item="props">
        <td
          ref="expanded"
          :colspan="props.headers.length"
          style="height: auto"
          :key="props.item[itemKey]"
        >
          <sj-item-group
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
            :table-only="selected.approved != 0"
            :key="props.item[itemKey]"
            :sel="{
              sj_id: props.item.id,
            }"
            name=""
            :data="{
              sj_id: props.item[itemKey],
            }"
          ></sj-item-group>
        </td>
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="dialogItemGroup"
      title="Surat Jalan Item"
      min-height="75%"
      fullscreen
    >
      <sj-item-group
        :key="selected.id"
        :sel="processData"
        name=""
        :data="dataid"
      ></sj-item-group>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogNote"
      :title="titleNote"
      min-height="75%"
      @save="act"
    >
      <v-textarea label="Note" v-model="approval_notes"></v-textarea>
    </v-action-dialog>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "",
  creator: "",
  components: {
    "sj-item-group": "url:ui/suratjalan/surat_jalan_item_group.vue",
  },
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    history: {
      type: Boolean,
      default: false,
    },
    revise: {
      type: Boolean,
      default: false,
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
    showColumn: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          flag: 0,
          approved: "0, 1",
        },
        filterType: {
          flag: "!=",
          approved: "in",
        },
      },
    },
  },
  data: function () {
    return {
      valid: false,
      dialogFile: false,
      formFile: [
        {
          text: "File",
          value: "file",
          type: "file",
          required: true,
        },
      ],
      dialogNote: false,
      dialogNotes: false,
      action: "",
      approval_notes: "",
      name: "Surat Jalan",
      dataid: {},
      processData: {},
      url: "suratjalan/suratjalan",
      itemKey: "id",
      loading: false,
      dialogItemGroup: false,
      status: {
        "-1": "Rejected",
        0: "New",
        1: "Asking for Approval",
        3: "Approved",
      },
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
          text: "sj_id",
          value: "sj_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "int",
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
          text: "Detail Surat Jalan",
          value: "surat_jalan_detail",
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
          text: "No",
          value: "sj_no",
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
          text: "Date",
          value: "sj_date",
          default: new Date().formatDate("YYYY-MM-DD"),
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
          page: "0",
          limit: "10",
        },
        {
          text: "Address",
          value: "address",
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
          text: "From",
          value: "from_info",
          align: "start",
          sortable: false,
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
          data_value: [
            { text: "BMT Spazio", value: 1 },
            { text: "BMT Trowulan", value: 2 },
            { text: "BMT Kepanjen", value: 3 },
            { text: "BMT Kediri", value: 4 },
            { text: "Nojorono", value: 5 },
          ],
        },
        {
          text: "To",
          value: "to_info",
          align: "start",
          sortable: false,
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
          data_value: [
            { text: "BMT Spazio", value: 1 },
            { text: "BMT Trowulan", value: 2 },
            { text: "BMT Kepanjen", value: 3 },
            { text: "BMT Kediri", value: 4 },
          ],
        },
        {
          text: "Place Name",
          value: "place",
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
          text: "UP",
          value: "up",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "text",
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
          text: "Item No",
          value: "item_in_sj",
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
          text: "Pengirim",
          value: "pengirim",
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
              groups: "sj_validasi",
            },
            filterType: {
              groups: "%",
            },
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "pengirim_name",
        },
        {
          text: "Notes",
          value: "sj_notes",
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
          text: "Detail Status Approval",
          value: "detail_status",
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
          text: "Approval Note",
          value: "approved_note",
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
          text: "Reject Note",
          value: "rejected_note",
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
          value: "revised_note",
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
          text: "Jenis Kendaraan",
          value: "jenis_kendaraan",
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
        },
        {
          text: "Nopol",
          value: "nopol",
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
        },
        {
          text: "Nama",
          value: "nama",
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
        },
        {
          text: "No. HP",
          value: "no_hp",
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
        },
        {
          text: "Waktu Berangkat",
          value: "jam_berangkat",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "datetime",
          disabled: false,
          visible: false,
          required: false,
          form: true,
          filter: false,
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
          type: "list",
          disabled: false,
          visible: false,
          required: true,
          form: false,
          filter: false,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "sj_validasi",
              sub_group_name: "sj_validasi",
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
          alias: "approved_by_name",
        },
        {
          text: "Rejected By",
          value: "rejected_by",
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
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "sj_validasi",
              sub_group_name: "sj_validasi",
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
          alias: "rejected_by_name",
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
              group_name: ["sj_page", "logistic_admin"],
              sub_group_name: ["sj_page", "logistic_admin"],
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
          paging: true,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: ["sj_page", "logistic_admin"],
              sub_group_name: ["sj_page", "logistic_admin"],
            },
            filterType: {},
            filterCondition: {
              group_name: "or",
              sub_group_name: "or",
            },
          },
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
      //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
      selected: false,
      //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
      selectedAll: [],
      isInDom: false,
      isInViewport: false,
      dataid: {},
    };
  },
  watch: {},
  computed: {
    titleNote: function () {
      var self = this;
      if (self.action == "revision") {
        return "Revisi Notes";
      }
      if (self.action == "askApproval") {
        return "Ask Approval Notes";
      }
      if (self.action == "delete") {
        return "Cancel Notes";
      }
    },
    disableAskApproval: function () {
      var self = this;
      if (!this.selected) return true;
      if (self.selected.approved == 0) return false;
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

      if (!self.selected) return true;
      return false;
    },
  },
  methods: {
    status: function (item) {
      if (item.status == 0) return "New";
      if (item.status == 1) return "Asking for Approval";
      if (item.status == 3) return "Approved";
      if (item.status == -1) return "Rejected";
    },
    addressInfo: function (f) {
      if (f == 1) {
        return "BMT Spazio";
      }
      if (f == 2) {
        return "BMT Trowulan";
      }
      if (f == 3) {
        return "BMT Kepanjen";
      }
      if (f == 4) {
        return "BMT Kediri";
      }
    },
    revision: async function () {
      var self = this;
      var q = confirm("Are you sure?");
      if (!q) return true;
      else {
        try {
          var r = await axios.get(App.url + "suratjalan/suratjalan/revisi", {
            params: {
              id: self.selected.id,
              revisi_notes: self.approval_notes,
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
      self.dialogNote = false;
    },
    act: function () {
      this[this.action]();
    },
    onSelectedRow: function (val) {
      var self = this;
      if (val === undefined) {
        self.selected = false;
        self.processData = {};
        self.dataid = {};
      } else {
        self.selected = val;
        self.processData = {
          sj_id: val.id,
        };

        self.dataid = {
          sj_id: val.id,
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
    askApproval: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (!c) return true;
      if (c) {
        var params = {
          approved: 1,
          pk: self.selected.id,
        };
        params["approval_notes"] = self.approval_notes;
        var r = await axios.put(App.url + "suratjalan/suratjalan", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.dialogNote = false;
    },
    delete: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (!c) return true;
      if (c) {
        var params = {
          flag: 0,
          pk: self.selected.id,
        };
        var r = await axios.put(App.url + "suratjalan/suratjalan", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
    },
    print: function () {
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      open(
        "https://main.buanamultiteknik.com/api/report/suratjalan/index?sj_id=" +
          this.selected.id +
          "&uid=" +
          randomId(),
        this.selected.sj_no,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },
  },
  mounted: function () {
    var self = this;
    if (self.history) {
      self.headersObj.approved_by.filter = true;
      self.headersObj.rejected_by.filter = true;
    }
  },
};
</script>
