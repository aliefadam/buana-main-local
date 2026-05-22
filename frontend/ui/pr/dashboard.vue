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
      hide-delete-button
      :table-loading.sync="loading"
      :show-expand="true"
      :single-expand="true"
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
      <!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
      <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
      <template v-slot:menu-after-filter>
        <!-- <v-btn small color="primary" outlined :disabled="selected == false" @click="dialogPart=true">Parts</v-btn> -->
        <!-- <v-btn small color="primary" outlined :disabled="selected == false" @click="dialogNotes=true">Notes</v-btn> -->
        <!-- <v-btn small color="primary" outlined :disabled="selected == false" v-if="history" @click="dialogFile=true">Upload Print</v-btn> -->
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="print"
          >Document</v-btn
        >
        <v-btn
          small
          color="success"
          outlined
          v-if="(tableOnly && !history) || !nointeraction"
          :disabled="selected == false"
          @click="
            action = 'approve';
            dialogNote = true;
          "
          >Approve</v-btn
        >
        <v-btn
          small
          color="red"
          outlined
          v-if="(tableOnly && !history) || !nointeraction"
          :disabled="selected == false"
          @click="
            action = 'reject';
            dialogNote = true;
          "
          >Reject</v-btn
        >
        <v-btn
          color="warning"
          outlined
          small
          @click="
            action = 'revision';
            dialogNote = true;
          "
          :disabled="allowRevisi"
          v-if="(tableOnly && !history) || selected.askapproval == 1 || revise"
        >
          Revise
        </v-btn>
        <!--  v-if="history" -->
      </template>
      <template v-slot:menu-after-add>
        <v-btn
          small
          color="error"
          outlined
          v-if="!tableOnly && !history"
          :disabled="disableAskApproval"
          @click="
            action = 'delete';
            dialogNote = true;
          "
          ><v-icon>mdi-close</v-icon>Cancel</v-btn
        >
      </template>
      <template v-slot:item.pr="props">
        <div style="white-space: nowrap">
          <span>No:</span> {{ props.item.pr_no }}<br />
          <span>Date:</span> {{ props.item.pr_date }}<br />
          <span>Created By:</span> {{ props.item.created_by_name }}<br />
          <span>Created Date:</span> {{ props.item.created_date }}
          <!-- <span>Modified By:</span> {{props.item.modified_by_name}}<br/>
					<span>Modified Date:</span> {{props.item.modified_date}}<br/> -->
          <a
            v-if="history && props.item.filepath != null"
            :download="props.item.filepath.trim().split('+++')[1]"
            target="_blank"
            :href="
              'https://main.buanamultiteknik.com/api/uploads/pr' +
              props.item.id +
              '/' +
              props.item.filepath.trim().split('+++')[0]
            "
            ><br />Printed Document </a
          ><span v-else></span><br />
        </div>
      </template>
      <!-- <template v-slot:item.reference="props">
				<span>RFQ No:</span><br><a :href="'#/rfq/rfq/'+props.item.rfq_id" v-if="props.item.rfq_no" target="_blank">{{ props.item.rfq_no }}</a><span v-else>{{props.item.rfq_no}}</span>
				<span><br>Ragic No:</span><br>{{props.item.ragic_no}}
				<span v-if="props.item.attachment"><br>Attachment: </span><a :href="props.item.attachment" v-if="props.item.attachment" target="_blank">Open Link</a><span v-else><br/></span>
			</template> -->
      <template v-slot:item.created="props">
        <span>By:</span> {{ props.item.created_by_name }}<br />
        <span>Date:</span> {{ props.item.created_date }}<br />
      </template>
      <template v-slot:item.modified="props">
        <span>By:</span> {{ props.item.modified_by_name }}<br />
        <span>Date:</span> {{ props.item.modified_date }}<br />
      </template>
      <template v-slot:item.approval_history="props">
        <b>Approver</b><br />
        <span>By:</span> {{ props.item.approved_by_name }}<br />
        <span>Date:</span> {{ props.item.peminta_setuju }}<br /><br />
        <b>Validator</b><br />
        <span>By:</span> {{ props.item.validated_by_name }}<br />
        <span>Date:</span> {{ props.item.penyetuju_setuju }}<br />
        <!-- <b>Rejected By:</b> {{props.item.rejected_by_name}}<br/>
				<b>Rejected Date:</b> {{props.item.rejected_date}}<br/> -->
      </template>
      <template v-slot:item.approval_notes="props">
        <b>Ask Approval:</b><br />
        {{ props.item.askapproval_notes }}<br /><br />
        <b>Approval 1:</b><br />
        {{ props.item.approval1_notes }}<br /><br />
        <b>Approval 2:</b><br />
        {{ props.item.approval2_notes }}<br />
        <!-- <template v-if="props.item.reject_notes!=null"><br/><span>Reject:</span> {{props.item.reject_notes}}<br/></template> -->
      </template>
      <template v-slot:item.status="props">
        {{ status(props.item) }}
      </template>
      <template v-slot:item.approval_info="props">
        <b>{{ status(props.item) }}</b>
        <span v-if="props.item.ask_approval">
          <br /><br /><span>Asking Date:</span> {{ props.item.ask_approval
          }}<br /><span>Admin Notes:</span>
          {{ props.item.askapproval_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.peminta_setuju">
          <br /><br /><b>Approved:</b><br />
          <span>By:</span> {{ props.item.peminta }}<br />
          <span>Date:</span> {{ props.item.peminta_setuju }}<br />
          <span>Notes:</span> {{ props.item.approval1_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.penyetuju_setuju">
          <br /><br /><b>Approved:</b><br />
          <span>By:</span> {{ props.item.menyetujui }}<br />
          <span>Date:</span> {{ props.item.penyetuju_setuju }}<br />
          <span>Notes:</span> {{ props.item.approval2_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.revise_date">
          <br /><br /><b>Revised:</b><br />
          <span>By:</span> {{ props.item.revise_by_name }}<br />
          <span>Date:</span> {{ props.item.revise_date }}<br />
          <span>Notes:</span> {{ props.item.revise_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.revision_date">
          <br /><br /><b>Revised:</b><br />
          <span>By:</span> {{ props.item.revision_by_name }}<br />
          <span>Date:</span> {{ props.item.revision_date }}<br />
          <span>Notes:</span> {{ props.item.revisi_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.rejected_date">
          <br /><br /><b>Rejected:</b><br />
          <span>By:</span> {{ props.item.rejected_by_name }}<br />
          <span>Date:</span> {{ props.item.rejected_date }}<br />
          <span>Notes:</span> {{ props.item.reject_notes }} </span
        ><span v-else></span>
      </template>
      <template v-slot:expanded-item="props">
        <td
          ref="expanded"
          :colspan="props.headers.length"
          style="height: auto"
          :key="props.item[itemKey]"
        >
          <pr-part
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
            table-only
            is-dashboard
            :key="props.item[itemKey]"
            :sel="{
              pr_id: props.item.id,
            }"
            name=""
            :data="{
              pr_id: props.item[itemKey],
            }"
          ></pr-part>
        </td>
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="dialogPart"
      title="Purchase Requisition Item"
      min-height="75%"
      fullscreen
    >
      <pr-part
        :key="selected.id"
        :data="dataid"
        :table-only="tableOnly"
      ></pr-part>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="dialogNotes"
      title="PR Notes"
      min-height="75%"
      fullscreen
    >
      <pr-notes
        :key="selected.id"
        :data="dataid"
        :table-only="tableOnly"
      ></pr-notes>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogNote"
      :title="titleNote"
      min-height="75%"
      @save="act"
    >
      <v-textarea label="Note" v-model="askapproval_notes"></v-textarea>
      <v-switch
        v-if="selected.status == 1"
        label="A.N Buana Susilo"
        v-model="isAn"
      ></v-switch>
    </v-action-dialog>
    <!--v-action-dialog v-model="dialogNote" :title="titleNote" min-height="75%" @save="act">
            <v-textarea label="Note" v-model="approval_note"></v-textarea>
        </v-action-dialog>
        <v-action-dialog v-model="dialogNote" :title="titleNote" min-height="75%" @save="act">
            <v-textarea label="Note" v-model="revisi_notes"></v-textarea>
        </v-action-dialog>
        <v-action-dialog v-model="dialogNote" :title="titleNote" min-height="75%" @save="act">
            <v-textarea label="Note" v-model="reject_notes"></v-textarea>
        </v-action-dialog-->
    <v-action-dialog
      @save="saveFile"
      title="Upload Print"
      v-model="dialogFile"
      v-if="history"
    >
      <v-autoform v-model="formFile" :valid="valid"></v-autoform>
    </v-action-dialog>
  </v-container>
</template>

<style scoped></style>

<script>
module.exports = {
  name: "",
  creator: "",
  components: {
    "pr-part": "url:ui/pr/part.vue",
    "pr-notes": "url:ui/pr/notes.vue",
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
    nointeraction: {
      type: Boolean,
      default: false,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    reject: {
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
          status: "0,2",
        },
        filterType: {
          flag: "!=",
          status: "btw",
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
      askapproval_notes: "",
      approval_note: "",
      isAn: 0,
      revisi_notes: "",
      reject_notes: "",
      name: "Purchase Requisition",
      dataid: {},
      itemKey: "id",
      url: "bom/pr",
      loading: false,
      dialogPart: false,
      headers: [
        {
          text: "",
          value: "data-table-expand",
        },
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
          text: "PR Details",
          value: "pr",
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
          text: "PR Date",
          value: "pr_date",
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
          form: true,
          filter: true,
          groupable: false,
          default: new Date().formatDate("YYYY-MM-DD"),
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
          text: "Subject",
          value: "pr_subject",
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
        /*{
					"text": "Project",
					"value": "project_no",
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
					"form": true,
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
				}, */ {
          text: "Item No",
          value: "item_in_pr",
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
          alias: "item_no",
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
          text: "Status",
          value: "status",
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
          default: "New",
          data_value: [
            {
              text: "New",
              value: 0,
            },
            {
              text: "Asking for Approval 1",
              value: 1,
            },
            {
              text: "Asking for Approval 2",
              value: 2,
            },
          ],
        },
        {
          text: "Reference No",
          value: "reference",
          align: "start",
          sortable: false,
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
          text: "RFQ No",
          value: "rfq_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          clearable: true,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "rfq/rfq",
          searchby: ["rfq_no"],
          formatter: ["id", "rfq_no"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "rfq_no",
        },
        {
          text: "Ragic No",
          value: "ragic_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: false,
          clearable: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "ragic/ragic",
          searchby: ["order_id"],
          formatter: ["ragic_id", "order_id"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "ragic_no",
        },
        {
          text: "Notes",
          value: "pr_notes",
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
          text: "Attachment",
          value: "attachment",
          align: "start",
          sortable: false,
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
          page: "1",
          limit: "10",
        },
        {
          text: "Requested By",
          value: "pr_peminta",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          clearable: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "pr_peminta",
              sub_group_name: "pr_peminta",
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
          alias: "peminta",
        },
        {
          text: "Approved By",
          value: "pr_menyetujui",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          visible: true,
          clearable: true,
          required: false,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "users/info",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {
              group_name: "pr_penyetuju",
              sub_group_name: "pr_penyetuju",
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
          alias: "menyetujui",
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
          text: "Approval Info",
          value: "approval_info",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Ask Approval Notes",
          value: "askapproval_notes",
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
          page: "1",
          limit: "10",
        },
        {
          text: "Requestor Notes",
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
          text: "Approver Notes",
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
          text: "Revise Notes",
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
          text: "Reject Notes",
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
              group_name: "pr_admin",
              sub_group_name: "pr_admin",
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
              group_name: "pr_admin",
              sub_group_name: "pr_admin",
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
    };
  },
  watch: {},
  computed: {
    titleNote: function () {
      var self = this;
      // if(self.action == 'approve'){
      // 	return 'Approval '+(self.selected.peminta_setuju == null ? 1 : 2)+' Notes'
      // }
      if (self.action == "approve" && self.selected.peminta_setuju == null) {
        return "Requestor Notes ";
      }
      if (self.action == "approve" && self.selected.peminta_setuju != null) {
        return "Approver Notes ";
      }
      if (self.action == "reject") {
        return "Reject Notes";
      }
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
      if (self.selected.ask_approval != null) return true;
      return false;
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
      /*if(self.selected.penyetuju_setuju == null || self.selected.peminta_setuju == null)
					return true*/
      return false;
    },
  },
  methods: {
    status: function (item) {
      if (item.status == 0) return "New";
      if (item.status == 1) return "Asking for Approval 1";
      if (item.status == 2) return "Asking for Approval 2";
      if (item.status == 3) return "Approved 2";
      if (item.status == -1) return "Rejected";
      if (item.rev_date != null) return "New";
    },
    saveFile: async function () {
      var self = this;
      const formData = new FormData();
      self.formFile.map(function (val, i) {
        var key = val.value;
        formData.append(key, val.data);
      });
      formData.append("id", self.selected.id);
      var res = await axios.post(App.url + "bom/pr/uploadprint", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (!res.data.status) {
        App.errorMsg();
      } else {
        App.successMsg();

        self.dialogFile = false;
        self.getAttachment();
      }
    },
    revision: async function () {
      var self = this;
      var q = confirm("Are you sure want to revise this PR?");
      if (!q) return true;
      else {
        try {
          var params = {
            pk: self.selected.id,
            reviser: 1,
            status: 0,
            revise_notes: self.askapproval_notes,
          };
          params["revise_notes"] = self.askapproval_notes;
          var r = await axios.put(App.url + "bom/pr", params);
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
        self.dataid = {};
      } else {
        self.selected = val;
        self.dataid = {
          pr_id: val.id,
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
    approve: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (c) {
        var params = {
          approved: self.selected.peminta_setuju == null ? 1 : 2,
          pk: self.selected.id,
          status: self.selected.peminta_setuju == null ? 2 : 3,
        };
        //console.log('approval'+(self.selected.peminta_setuju == null ? 1 : 2)+'_notes', self.askapproval_notes)
        params[
          "approval" + (self.selected.peminta_setuju == null ? 1 : 2) + "_notes"
        ] = self.askapproval_notes;
        params["isAn"] = self.isAn;
        var r = await axios.put(App.url + "bom/pr", params);
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
      if (c) {
        var params = {
          reject: 1,
          pk: self.selected.id,
          status: -1,
        };
        params["reject_notes"] = self.askapproval_notes;
        var r = await axios.put(App.url + "bom/pr", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.dialogNote = false;
    },
    askApproval: async function () {
      var self = this;
      var c = confirm("Are you sure?");
      if (c) {
        var params = {
          askapproval: 1,
          pk: self.selected.id,
          status: 1,
        };
        params["askapproval_notes"] = self.askapproval_notes;
        var r = await axios.put(App.url + "bom/pr", params);
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
      if (c) {
        var params = {
          flag: 0,
          pk: self.selected.id,
        };
        params["delete_notes"] = self.askapproval_notes;
        var r = await axios.put(App.url + "bom/pr", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.dialogNote = false;
    },
    print: function () {
      var w = screen.width * 0.75;
      var h = screen.height * 0.5;
      var left = screen.width / 2 - w / 2;
      var top = screen.height / 2 - h / 2;
      open(
        "https://main.buanamultiteknik.com/api/report/pr/index?pr_id=" +
          this.selected.id +
          "&uid=" +
          randomId(),
        "PR " + this.selected.pr_no,
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );
    },
  },
  mounted: function () {
    var self = this;
    if (self.showColumn) {
      self.headersObj.status.data_value = [
        {
          text: "Approved 2",
          value: 3,
        },
        {
          text: "Rejected",
          value: -1,
        },
      ];
    }
  },
};

/* Status Approval
0=new
1=asking for approval 1
2=asking for approval 2
3= approved 2*/
</script>
