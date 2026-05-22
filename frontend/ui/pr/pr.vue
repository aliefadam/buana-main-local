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
      :disable-edit-button="disableOpen"
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
      <template v-slot:after-header>
        <v-row>
          <v-col cols="3" v-if="!history">
            <v-card
              class="mx-auto"
              max-width="344"
              outlined
              title="Total PR with Status New"
            >
              <v-list-item three-line>
                <v-list-item-content>
                  <div class="text-overline mb-4" style="font-weight: bold">
                    New
                  </div>
                  <v-list-item-title
                    class="text-h5 mb-1"
                    style="font-weight: bold"
                  >
                    {{ total_new_pr }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-card>
          </v-col>
          <v-col cols="3" v-if="!history">
            <v-card
              class="mx-auto"
              max-width="344"
              outlined
              title="Total PR with Status Asking Approval"
            >
              <v-list-item three-line>
                <v-list-item-content>
                  <div class="text-overline mb-4" style="font-weight: bold">
                    Pending
                  </div>
                  <v-list-item-title
                    class="text-h5 mb-1"
                    style="font-weight: bold"
                  >
                    {{ total_pending_pr }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-card>
          </v-col>
          <v-col cols="3" v-if="history">
            <v-card
              class="mx-auto"
              max-width="344"
              outlined
              title="Total PR with Status Final Approved"
            >
              <v-list-item three-line>
                <v-list-item-content>
                  <div class="text-overline mb-4" style="font-weight: bold">
                    Final Approved
                  </div>
                  <v-list-item-title
                    class="text-h5 mb-1"
                    style="font-weight: bold"
                  >
                    {{ total_approved_pr }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-card>
          </v-col>

          <v-col cols="3" v-if="history">
            <v-card
              class="mx-auto"
              max-width="344"
              outlined
              title="Total PR with Status Rejected"
            >
              <v-list-item three-line>
                <v-list-item-content>
                  <div class="text-overline mb-4" style="font-weight: bold">
                    Rejected
                  </div>
                  <v-list-item-title
                    class="text-h5 mb-1"
                    style="font-weight: bold"
                  >
                    {{ total_rejected_pr }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-card>
          </v-col>
          <v-col cols="3" v-if="history">
            <v-card
              class="mx-auto"
              max-width="344"
              outlined
              title="Total PR with Status Cancel"
            >
              <v-list-item three-line>
                <v-list-item-content>
                  <div class="text-overline mb-4" style="font-weight: bold">
                    Cancel
                  </div>
                  <v-list-item-title
                    class="text-h5 mb-1"
                    style="font-weight: bold"
                  >
                    {{ total_canceled_pr }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-card>
          </v-col>
          <!-- <v-col cols="3" v-if="history">
                        <v-card class="mx-auto" max-width="344" outlined title="Total PR with Status Deleted">
                            <v-list-item three-line>
                                <v-list-item-content>
                                    <div class="text-overline mb-4" style="font-weight: bold">
                                        Deleted
                                    </div>
                                    <v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
                                        {{total_deleted_pr}}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card>
                    </v-col> -->
        </v-row>
      </template>
      <template v-slot:title-body v-if="$refs.template && !history">
        <b>PR Active: </b> {{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:title-body v-else>
        <b>PR History: </b> {{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:menu-after-filter>
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="dialogNotes = true"
          >Notes</v-btn
        >
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="print"
          v-if="!history"
          >Preview</v-btn
        >
        <v-btn
          small
          color="primary"
          outlined
          :disabled="disableOpen"
          @click="dialogPart = true"
          >Parts</v-btn
        >
        <!-- <v-btn small color="primary" outlined :disabled="selected == false" v-if="history" @click="dialogFile=true">Upload Print</v-btn> -->
        <v-btn
          small
          color="success"
          outlined
          v-if="tableOnly && !history"
          :disabled="selected == false"
          @click="
            action = 'approve';
            dialogNote = true;
          "
          >Approve</v-btn
        >
        <v-btn
          small
          color="warning"
          outlined
          v-if="tableOnly && !history"
          :disabled="selected == false"
          @click="
            action = 'reject';
            dialogNote = true;
          "
          >Reject</v-btn
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
        <v-btn
          small
          color="success"
          :dark="!disableAskApproval"
          :outlined="disableAskApproval"
          v-if="!tableOnly && !history"
          :disabled="disableAskApproval"
          @click="
            action = 'approved';
            dialogNote = true;
          "
          >Approved</v-btn
        >
        <v-btn
          small
          color="primary"
          outlined
          v-if="!tableOnly && !history"
          :disabled="disableAskApproval"
          @click="dialogUploadApproval = true"
          >Upload Approval</v-btn
        >
        <v-btn
          color="primary"
          outlined
          small
          @click="openRevision()"
          :disabled="allowRevisi"
          v-if="history || selected.askapproval == 1 || revise"
        >
          Revise
        </v-btn>
        <!-- <v-btn small color="error" outlined v-if="history" :disabled="disableAskCancel" @click="openCancel()" ><v-icon>mdi-close</v-icon>Cancel</v-btn> -->
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
          ><v-icon>mdi-close</v-icon>Delete</v-btn
        >
      </template>
      <template v-slot:item.pr="props">
        <div style="white-space: nowrap">
          <span>No:</span> {{ props.item.pr_no }}<br />
          <span>Date:</span> {{ props.item.pr_date }}<br />
          <span>Created By:</span> {{ props.item.created_by_name }}<br />
          <span>Created Date:</span> {{ props.item.created_date }}<br />
          <span>Modified By:</span> {{ props.item.modified_by_name }}<br />
          <span>Modified Date:</span> {{ props.item.modified_date }}<br />
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
            >Printed Document </a
          ><span v-else></span><br />
        </div>
      </template>
      <template v-slot:item.pr_notes="props">
        <span v-if="props.item.pr_notes">{{ props.item.pr_notes }}<br /></span
        ><span v-else></span>
        <span v-if="props.item.attachment"><br />Attachment: </span
        ><a
          :href="props.item.attachment"
          v-if="props.item.attachment"
          target="_blank"
          >Open Link</a
        ><span v-else></span>
      </template>
      <template v-slot:item.reference="props">
        <span>RFQ No:</span><br /><a
          :href="'#/rfq/rfq/' + props.item.rfq_id"
          v-if="props.item.rfq_no"
          target="_blank"
          >{{ props.item.rfq_no }}</a
        ><span v-else>{{ props.item.rfq_no }}</span
        ><br />
        <span><br />Ragic No:</span><br />{{ props.item.ragic_no }}
      </template>
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
        <div style="justify-content: center; display: flex">
          <v-alert
            dense
            color="#ffcc99"
            v-if="
              props.item.status == 1 ||
              props.item.status == 2 ||
              props.item.status == -2 ||
              props.item.status == -3
            "
            style="
              margin: 0 !important;
              padding: 4px 8px;
              width: auto;
              display: inline-block;
              font-weight: bold;
              font-size: 0.875rem;
            "
          >
            Pending
          </v-alert>
          <v-alert
            dense
            color="#f5e699"
            v-if="props.item.status == 0"
            style="
              margin: 0 !important;
              padding: 4px 8px;
              width: auto;
              display: inline-block;
              font-weight: bold;
              font-size: 0.875rem;
            "
          >
            New
          </v-alert>
          <v-alert
            dense
            color="#bfdda8"
            v-if="props.item.status == 3"
            style="
              margin: 0 !important;
              padding: 4px 8px;
              width: auto;
              display: inline-block;
              font-weight: bold;
              font-size: 0.875rem;
            "
          >
            Final Approved
          </v-alert>
          <v-alert
            dense
            color="#22faff"
            v-if="props.item.status == -4"
            style="
              margin: 0 !important;
              padding: 4px 8px;
              width: auto;
              display: inline-block;
              font-weight: bold;
              font-size: 0.875rem;
            "
          >
            Canceled
          </v-alert>
          <v-alert
            dense
            color="#ff3e15"
            v-if="props.item.status == -1"
            style="
              margin: 0 !important;
              padding: 4px 8px;
              width: auto;
              display: inline-block;
              font-weight: bold;
              font-size: 0.875rem;
            "
          >
            Rejected
          </v-alert>
          <!-- <v-alert dense color="#e622ff" v-if="props.item.status==-1"  style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Deleted
					</v-alert> -->
        </div>
      </template>
      <!-- <template v-slot:item.status="props">
             {{status(props.item)}}
            </template> -->
      <template v-slot:item.approval_info="props">
        <b>{{ status(props.item) }}</b>
        <span v-if="props.item.ask_approval">
          <br /><br /><span>Asking Date:</span> {{ props.item.ask_approval
          }}<br /><span>Admin Notes:</span>
          {{ props.item.askapproval_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.peminta_setuju">
          <br /><br /><b>Approved:</b><br />
          <span>By:</span> {{ props.item.approved_by_name }}<br />
          <span>Date:</span> {{ props.item.peminta_setuju }}<br />
          <span>Notes:</span> {{ props.item.approval1_notes }} </span
        ><span v-else></span>
        <span v-if="props.item.penyetuju_setuju">
          <br /><br /><b>Approved:</b><br />
          <span>By:</span> {{ props.item.validated_by_name }}<br />
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
        :sel="processData"
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
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogRevise"
      title="Revise Notes"
      min-height="75%"
      @save="revision"
    >
      <v-textarea label="Note" v-model="revisi_notes"></v-textarea>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogCancel"
      title="Canceled Notes"
      min-height="75%"
      @save="askCancel"
    >
      <v-textarea label="Note" v-model="ask_canceled_note"></v-textarea>
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
    <v-action-dialog
      @save="saveUploadApproval"
      title="Upload Approval"
      v-model="dialogUploadApproval"
      min-height="75%"
    >
      <v-autoform
        v-model="formUploadApproval"
        :valid="validUploadApproval"
      ></v-autoform>
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
          status: "0,1,2,-2,-3",
          // created_by: App.userData.data[0].userid
        },
        filterType: {
          flag: "!=",
          status: "in",
        },
      },
    },
  },
  data: function () {
    return {
      exchangeData: {},
      valid: false,
      dialogFile: false,
      dialogUploadApproval: false,
      validUploadApproval: false,
      formFile: [
        {
          text: "File",
          value: "file",
          type: "file",
          required: true,
        },
      ],
      formUploadApproval: [
        {
          text: "Approval File",
          value: "file",
          type: "file",
          required: true,
        },
      ],
      processData: {},
      dialogNote: false,
      dialogNotes: false,
      dialogRevise: false,
      dialogCancel: false,
      action: "",
      askapproval_notes: "",
      approval_note: "",
      revisi_notes: "",
      reject_notes: "",
      ask_canceled_note: "",
      total_new_pr: 0,
      total_approved_pr: 0,
      total_pending_pr: 0,
      total_rejected_pr: 0,
      total_canceled_pr: 0,
      total_deleted_pr: 0,
      name: "Purchase Requisition",
      itemKey: "id",
      url: "bom/pr",
      loading: false,
      dialogPart: false,
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
          text: "pr_id",
          value: "pr_id",
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
          text: "Currency",
          value: "currency",
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
          filter: false,
          groupable: false,
          default: "IDR",
          data_value: ["IDR", "CNY", "EUR", "USD", "BDT", "SGD"],
          input: function (data) {
            var self = App.page();
            self.headersObj.exchange_rate.required = false;

            console.log(data.data.trim().toLowerCase());

            if (data.data.trim().toLowerCase() != "idr") {
              self.headersObj.exchange_rate.required = true;
              self.headersObj.rate_date.required = true;
            } else {
              self.headersObj.exchange_rate.required = false;
              self.headersObj.exchange_rate.data = 0;
              self.headersObj.rate_date.required = false;
            }

            if (data.data.trim().toUpperCase() != "IDR") {
              var ex = data.data.trim().toUpperCase();

              console.log("ini upper case : " + ex);

              console.log("ini selft  : " + self.exchangeData[ex]);

              if (self.exchangeData[ex]) {
                self.headersObj.exchange_rate.data = self.exchangeData[ex];
                self.headersObj.exchange_rate.update = self.exchangeData[ex];
                self.headers = App.updateObject(self.headers);

                console.log("ini kondisi 1 : " + self.exchangeData[ex]);
              } else {
                console.log(
                  "ini kondisi 2 : " + data.data.trim().toUpperCase(),
                );
                axios
                  .get(App.url + "bom/payment/exchange", {
                    params: {
                      currency: data.data.trim().toUpperCase(),
                    },
                  })
                  .then(function (response) {
                    var data = response.data;
                    console.log("response : " + data);
                    self.headersObj.exchange_rate.data =
                      data.match(/\d+(.\d+)/)[0];
                    self.headers = App.updateObject(self.headers);
                  })
                  .catch(function (error) {});
              }
            } else {
              self.headers = App.updateObject(self.headers);
            }
          },
        },
        {
          text: "Exchange Rate",
          value: "exchange_rate",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
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
          precision: "4",
          page: "0",
          limit: "10",
        },
        {
          text: "Rate Date",
          value: "rate_date",
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
          // "alias": "ragic_no"
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
              ).test(v) || "Link must be valid URL!",
          ],
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
          text: "Request Kedatangan Barang",
          value: "user_request_date",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
          disabled: false,
          visible: true,
          required: true,
          form: true,
          filter: false,
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
          text: "Approval Status",
          value: "status",
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
          form: false,
          filter: true,
          groupable: false,
          default: "New",
          data_value: [
            {
              text: "New",
              value: "0",
            },
            {
              text: "Asking for Approval 1",
              value: 1,
            },
            {
              text: "Asking for Approval 2",
              value: 2,
            },
            {
              text: "Asking for Cancelation 1",
              value: -2,
            },
            {
              text: "Asking for Cancelation 2",
              value: -3,
            },
          ],
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
          text: "Approval 1 Notes",
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
          text: "Approval 2 Notes",
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
          text: "Last Modified",
          value: "modified",
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
        {
          text: "Rev",
          value: "rev",
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
      if (self.action == "approve") {
        return (
          "Approval " +
          (self.selected.peminta_setuju == null ? 1 : 2) +
          " Notes"
        );
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
      if (self.action == "approved") {
        return "Approved Notes";
      }
      if (self.action == "delete") {
        return "Delete Notes";
      }
      if (self.action == "askCancel") {
        return "Ask Cancel Notes";
      }
      if (self.action == "cancel") {
        return (
          "Cancel " +
          (self.selected.peminta_setuju == null ? -2 : -3) +
          " Notes"
        );
      }
    },
    disableAskApproval: function () {
      var self = this;
      if (!this.selected) return true;
      if (self.selected.status > 0 || self.selected.status < 0) return true;
      return false;
    },
    disableAskCancel: function () {
      var self = this;
      if (!this.selected) return true;
      if (self.selected.status == 3) return false;
      return true;
    },
    disableOpen: function () {
      var self = this;

      if (!self.selected) return true;
      if (self.selected.status > 0 || self.selected.status < 0) return true;
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
      if (item.status == 0 && item.flag == 0) return "Deleted";
      if (item.status == 0 && item.flag != 0) return "New";
      if (item.status == 1) return "Asking for Approval 1";
      if (item.status == 2) return "Asking for Approval 2";
      if (item.status == 3) return "Final Approved";
      if (item.status == -1) return "Rejected";
      if (item.status == -2) return "Asking for Cancel 1";
      if (item.status == -3) return "Asking for Cancel 2";
      if ((item.status = -4)) return "Canceled";
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
    saveUploadApproval: async function () {
      var self = this;
      const fileField = self.formUploadApproval.find(function (val) {
        return val.value === "file";
      });
      if (!fileField || !fileField.data) {
        App.errorMsg({ message: "Approval file is required" });
        return;
      }
      const formData = new FormData();
      self.formUploadApproval.map(function (val) {
        formData.append(val.value, val.data);
      });
      formData.append("id", self.selected.id);
      var res = await axios.post(App.url + "bom/pr/uploadapproval", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });
      if (!res.data.status) {
        App.errorMsg(res.data);
      } else {
        App.successMsg();
        self.dialogUploadApproval = false;
        self.formUploadApproval[0].data = undefined;
        self.$refs.template.getItems();
      }
    },
    openRevision: function () {
      var self = this;
      this.revisi_notes = "";
      this.dialogRevise = true;
      /*if (self.selected.in_po == 0) {
        this.dialogRevise = true;
      } else {
        alert("System Can't revise this PR because PO is Asking for Approval!");
      }*/
    },
    revision: async function () {
      var self = this;
      if (self.loading) return;
      self.loading = true;
      var q = confirm("Are you sure want to revise this PR?");
      if (!q) return true;
      else {
        try {
          var r = await axios.get(App.url + "bom/pr/revisi", {
            params: {
              id: self.selected.id,
              revisi_notes: self.revisi_notes,
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
      self.dialogRevise = false;
      self.loading = false;
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
          pr_id: val.id,
          rev: val.rev,
        };
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
      var c = confirm("Are you sure want to Approve this PR?");
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
      var c = confirm("Are you sure want to Reject this PR?");
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
      var c = confirm("Are you sure want to Ask Approval this PR?");
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
    approved: async function () {
      var self = this;
      var c = confirm("Are you sure want to set this PR as Approved?");
      if (c) {
        var params = {
          pk: self.selected.id,
          approved_notes: self.askapproval_notes,
        };
        var r = await axios.put(App.url + "bom/pr", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.dialogNote = false;
    },
    openCancel: function () {
      var self = this;
      this.ask_canceled_note = "";
      if (self.selected.in_po == 0) {
        this.dialogCancel = true;
      } else {
        alert("System Can't cancel this PR because PO is Asking for Approval!");
      }
    },
    askCancel: async function () {
      var self = this;
      var c = confirm("Are you sure want to Ask Cancel this PR?");
      if (c) {
        var params = {
          askcancel: 1,
          pk: self.selected.id,
          status: -2,
        };
        params["ask_canceled_note"] = self.ask_canceled_note;
        var r = await axios.put(App.url + "bom/pr", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.dialogCancel = false;
    },
    delete: async function () {
      var self = this;
      var c = confirm("Are you sure want to Delete this PR?");
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
      var doc = open(
        "https://main.buanamultiteknik.com/api/report/pr/index?pr_id=" +
          this.selected.id +
          "&uid=" +
          randomId(),
        `scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`,
      );

      //this.renderPDF(doc, this.selected.attachment)
    },
    async getData() {
      try {
        var self = this;
        var data = await axios.get(App.url + "bom/pr/total_pr");
        // self.total_pr = parseInt(data.data.data[0].new_rfq)+parseInt(data.data.data[0].rfq_clarification)+parseInt(data.data.data[0].rfq_quotation)
        self.total_new_pr = data.data.data[0].pr_new;
        self.total_approved_pr = data.data.data[0].pr_approved;
        self.total_pending_pr = data.data.data[0].pr_pending;
        self.total_rejected_pr = data.data.data[0].pr_rejected;
        self.total_canceled_pr = data.data.data[0].pr_canceled;
        self.total_deleted_pr = data.data.data[0].pr_deleted;
      } catch (e) {}
    },
    makeThumb(page) {
      // draw page to fit into 96x96 canvas
      var vp = page.getViewport({ scale: 1 });
      var canvas = document.createElement("canvas");
      var scalesize = 1;
      canvas.width = vp.width * scalesize;
      canvas.height = vp.height * scalesize;
      var scale = Math.min(canvas.width / vp.width, canvas.height / vp.height);
      return page
        .render({
          canvasContext: canvas.getContext("2d"),
          viewport: page.getViewport({ scale: scale }),
        })
        .promise.then(function () {
          return canvas;
        });
    },
    renderPDF(w, url) {
      var temp = url; //"https://raw.githubusercontent.com/mozilla/pdf.js/ba2edeae/web/compressed.tracemonkey-pldi-09.pdf";
      var table = `<table style="border: 0; border-collapse: collapse;">`;
      pdfjsLib
        .getDocument(temp)
        .promise.then(async function (doc) {
          var pages = [];
          while (pages.length < doc._pdfInfo.numPages)
            pages.push(pages.length + 1);
          var images = [];
          await Promise.all(
            pages.map(function (num, i) {
              // create a div for each page and build a small canvas for it
              var div = document.createElement("div");
              return doc
                .getPage(num)
                .then(makeThumb)
                .then(function (canvas) {
                  var img = new Image();
                  images[i] =
                    `<tr><td><img width="${canvas.width}" height="${canvas.height}" src="${canvas.toDataURL()}" /></td></tr>`;
                  //img.src = canvas.toDataURL();
                  //div.appendChild(img);
                });
            }),
          );
          //self.reportImages = images
          table += images.join("") + "</table>";
          var doc = new DOMParser().parseFromString(table, "text/xml");
          document.body.appendChild(doc.children[0]);
        })
        .catch(console.error);
    },
  },
  mounted: function () {
    var self = this;
    /* if(document.querySelector('#pdfjs')==null){
				var list = document.createElement("script");
				list.type = 'module'
				list.id = 'pdfjs'
				list.src = './library/pdf.mjs'
				document.getElementsByTagName("body")[0].appendChild(list);
				var list2 = document.createElement("script");
				list2.type = 'module'
				list2.id = 'pdfjs2'
				list2.src = './library/pdfviewer.mjs'
				document.getElementsByTagName("body")[0].appendChild(list2);
			} */
    if (self.showColumn) {
      self.headersObj.status.data_value = [
        {
          text: "Final Approved",
          value: 3,
        },
        {
          text: "Rejected",
          value: -1,
        },
        {
          text: "Canceled",
          value: -4,
        },
      ];
    }
    if (check_user(["administrator"])) {
      delete self.itemsOptions.filter.created_by;
    }
    self.getData();
  },
};

/* Status Approval
0=new
1=asking for approval 1
2=asking for approval 2
3= approved 2*/
</script>
