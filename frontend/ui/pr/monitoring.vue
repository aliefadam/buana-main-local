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
      :table-fixed-header="tableFixedHeader"
      table-only
    >
      <template v-slot:after-header>
        <v-row>
          <v-col cols="2">
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
          <v-col cols="2">
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
          <v-col cols="2">
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
          <v-col cols="2">
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
          <v-col cols="2">
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
          <v-col cols="3" v-if="history">
            <v-card
              class="mx-auto"
              max-width="344"
              outlined
              title="Total PR with Status Deleted"
            >
              <v-list-item three-line>
                <v-list-item-content>
                  <div class="text-overline mb-4" style="font-weight: bold">
                    Deleted
                  </div>
                  <v-list-item-title
                    class="text-h5 mb-1"
                    style="font-weight: bold"
                  >
                    {{ total_deleted_pr }}
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-card>
          </v-col>
        </v-row>
      </template>
      <template v-slot:title-body v-if="$refs.template">
        <b>Total All PR: </b> {{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:menu-after-filter>
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="openRemainingBudgetDialog(selected)"
          >Show Remaining Budget</v-btn
        >
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="print"
          >Preview</v-btn
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
      <template v-slot:item.reference="props">
        <span>RFQ No:</span><br /><a
          :href="'#/rfq/rfq/' + props.item.rfq_id"
          v-if="props.item.rfq_no"
          target="_blank"
          >{{ props.item.rfq_no }}</a
        ><span v-else>{{ props.item.rfq_no }}</span
        ><br /><br />
        <span>Ragic No:</span> {{ props.item.ragic_no }}<br />
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
          <v-alert
            dense
            color="#e622ff"
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
            Deleted
          </v-alert>
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
      @save="saveFile"
      title="Upload Print"
      v-model="dialogFile"
      v-if="history"
    >
      <v-autoform v-model="formFile" :valid="valid"></v-autoform>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="dialogRemainingBudget"
      title="Remaining Budget"
      min-height="75%"
      fullscreen
    >
      <div style="padding: 12px">
        <table class="default-table with-border" style="width: 100%">
          <thead>
            <tr>
              <th style="padding: 5px; width: 150px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Charged To</th>
              <th style="padding: 5px; width: 150px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Item No</th>
              <th style="padding: 5px; width: 150px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Part</th>
              <th style="padding: 5px; width: 150px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Subledger</th>
              <th style="padding: 5px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">QTY</th>
              <th style="padding: 5px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Price</th>
              <th style="padding: 5px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">SubTotal</th>
              <th style="padding: 5px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Currency</th>
              <th style="padding: 5px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Grand Total</th>
              <th style="padding: 5px" v-if="showOperationalRemainingBreakdown" colspan="2">Remaining Budget</th>
              <th style="padding: 5px" v-else :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Remaining Budget</th>
              <th style="padding: 5px" :rowspan="showOperationalRemainingBreakdown ? 2 : 1">Force Reason Input Minus Budget</th>
            </tr>
            <tr v-if="showOperationalRemainingBreakdown">
              <th style="padding: 5px">Sub Operational</th>
              <th style="padding: 5px">Department</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="remainingBudgetLoading">
              <td :colspan="showOperationalRemainingBreakdown ? 12 : 11" style="text-align: center; padding: 5px">
                <v-progress-circular
                  indeterminate
                  size="18"
                  width="2"
                  style="margin-right: 8px"
                ></v-progress-circular>
                Loading...
              </td>
            </tr>
            <tr v-else-if="remainingBudgetItems.length === 0">
              <td :colspan="showOperationalRemainingBreakdown ? 12 : 11" style="padding: 5px; text-align: center">
                No data
              </td>
            </tr>
            <tr
              v-else
              v-for="(item, index) in remainingBudgetItems"
              :key="index"
            >
              <td
                style="padding: 5px"
                v-if="item.show_project_budget"
                :rowspan="item.project_budget_rowspan"
              >
                {{ item.project_budget_label || "-" }}
              </td>
              <td style="padding: 5px">{{ item.item_no || "-" }}</td>
              <td style="padding: 5px">{{ item.part_name || "-" }}</td>
              <td style="padding: 5px">{{ item.subledger_name || "-" }}</td>
              <td style="padding: 5px">
                {{ Number(item.qty || 0).format(2, 3) }}
              </td>
              <td style="padding: 5px">
                {{ Number(item.price || 0).format(2, 3) }}
              </td>
              <td style="padding: 5px">
                {{ Number(item.subtotal || 0).format(2, 3) }}
              </td>
              <td style="padding: 5px">{{ item.currency || "-" }}</td>
              <td style="padding: 5px">
                {{ "Rp. " + Number(item.grand_total_idr || 0).format(2, 3) }}
              </td>
              <td
                style="padding: 5px"
                v-if="item.show_remaining"
                :rowspan="item.remaining_rowspan"
                :colspan="showOperationalRemainingBreakdown && !item.is_operational ? 2 : 1"
              >
                <div v-if="remainingBudgetCalcLoading">
                  Calculate Remaining Budget...
                </div>
                <div
                  v-else
                  :style="{
                    color:
                      Number(item.remaining_budget) < 0 ? '#ff5252' : '#000',
                  }"
                >
                  {{
                    item.remaining_budget !== undefined
                      ? "Rp. " + Number(item.remaining_budget).format(2, 3)
                      : "-"
                  }}
                </div>
              </td>
              <td
                style="padding: 5px"
                v-if="showOperationalRemainingBreakdown && item.is_operational && item.show_remaining"
                :rowspan="item.remaining_rowspan"
              >
                <div v-if="remainingBudgetCalcLoading">
                  Calculate Remaining Budget...
                </div>
                <div
                  v-if="!remainingBudgetCalcLoading && item.remaining_bugdet_total_department !== undefined && item.remaining_bugdet_total_department !== null"
                  :style="{ color: Number(item.remaining_bugdet_total_department) < 0 ? '#ff5252' : '#000' }"
                >
                  {{ "Rp. " + Number(item.remaining_bugdet_total_department).format(2, 3) }}
                </div>
                <div v-else-if="!remainingBudgetCalcLoading">-</div>
              </td>
              <td style="padding: 5px">
                {{ item.force_budget_minus_reason || "-" }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
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
        filter: {},
        filterType: {},
        filterCondition: {},
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
      processData: {},
      dialogNote: false,
      dialogNotes: false,
      action: "",
      askapproval_notes: "",
      approval_note: "",
      revisi_notes: "",
      reject_notes: "",
      total_new_pr: 0,
      total_pending_pr: 0,
      total_approved_pr: 0,
      total_rejected_pr: 0,
      total_canceled_pr: 0,
      name: "Purchase Requisition",
      itemKey: "id",
      url: "bom/pr",
      loading: false,
      dialogPart: false,
      dialogRemainingBudget: false,
      remainingBudgetItems: [],
      remainingBudgetLoading: false,
      remainingBudgetCalcLoading: false,
      showOperationalRemainingBreakdown: false,
      remainingBudgetPrId: null,
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
        } /*{
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
				}, */,
        {
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
          text: "Approval Status",
          value: "status",
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
            {
              text: "Final Approved",
              value: 3,
            },
            {
              text: "Canceled",
              value: -4,
            },
            {
              text: "Rejected",
              value: -1,
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
              group_name: "pr_group",
              sub_group_name: "pr_group",
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
              group_name: "pr_group",
              sub_group_name: "pr_group",
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
      if (item.status == 3) return "Final Approved";
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
      var q = confirm("Are you sure?");
      if (!q) return true;
      else {
        try {
          var r = await axios.get(App.url + "bom/pr/revisi", {
            params: {
              id: self.selected.id,
              revisi_notes: self.askapproval_notes,
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
          pr_id: val.id,
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
    fetchRemainingBudgetItems: async function (prId) {
      var self = this;
      self.remainingBudgetLoading = true;
      self.remainingBudgetCalcLoading = false;
      try {
        var partRes = await axios.get(App.url + "bom/prpart", {
          params: {
            filter: {
              pr_id: prId,
            },
            limit: -1,
          },
        });
        var parts =
          partRes && partRes.data && Array.isArray(partRes.data.data)
            ? partRes.data.data
            : [];
        var prPartIds = parts
          .map((p) => p.id)
          .filter((id) => id !== undefined && id !== null && id !== "");
        var partMap = {};
        parts.map((p) => {
          partMap[p.id] = p;
        });

        if (prPartIds.length === 0) {
          self.remainingBudgetItems = [];
          self.remainingBudgetLoading = false;
          return;
        }

        var subledgerRes = await axios.get(App.url + "bom/prsubledger", {
          params: {
            filter: {
              pr_part_id: prPartIds,
            },
            filterType: {
              pr_part_id: "in",
            },
            limit: -1,
          },
        });
        var items =
          subledgerRes &&
          subledgerRes.data &&
          Array.isArray(subledgerRes.data.data)
            ? subledgerRes.data.data
            : [];
        self.remainingBudgetItems = items.map((item) => {
          var qty = Number(item.qty || 0);
          var price = Number(item.unit_price || 0);
          var subtotal = qty * price;
          var exchangeRate = Number(item.exchange_rate || 0);
          if (!exchangeRate || exchangeRate <= 0) exchangeRate = 1;
          var part = partMap[item.pr_part_id] || {};

          var projectLabel = "";
          if (item.project_no) projectLabel += item.project_no;
          if (item.project_name) {
            projectLabel += (projectLabel ? " - " : "") + item.project_name;
          }
          if (!projectLabel) projectLabel = "-";
          if (item.budget_name) projectLabel += " (" + item.budget_name + ")";

          return Object.assign({}, item, {
            subledger_id: item.id,
            subtotal: subtotal,
            grand_total_idr: subtotal * exchangeRate,
            project_budget_label: projectLabel,
            project_budget_group_key: "sid:" + String(item.id || ""),
            item_no: part.item_no || item.no || "-",
            part_name: part.subject || item.description || "-",
            subledger_name: item.description || "-",
            price: price,
            show_project_budget: true,
            project_budget_rowspan: 1,
            show_remaining: true,
            remaining_rowspan: 1,
          });
        });
        self.rebuildRemainingBudgetGroups();

        if (self.remainingBudgetItems.length > 0) {
          await self.calculateRemainingBudget();
        }
      } catch (e) {
        App.errorMsg(e);
        self.remainingBudgetItems = [];
      }
      self.remainingBudgetLoading = false;
    },
    getProjectTypeMap: async function (subledgerIds) {
      if (!Array.isArray(subledgerIds) || subledgerIds.length === 0) return {};
      try {
        var r = await axios.get(App.url + "bom/prsubledger", {
          params: {
            filter: {
              id: subledgerIds,
            },
            filterType: {
              id: "in",
            },
            limit: -1,
          },
        });
        var rows = r && r.data && Array.isArray(r.data.data) ? r.data.data : [];
        var map = {};
        rows.map((d) => {
          if (d && d.id !== undefined) map[d.id] = d.project_type;
        });
        return map;
      } catch (e) {
        console.log("Remaining budget project_type fetch error:", e);
        return {};
      }
    },
    calculateRemainingBudget: async function () {
      var self = this;
      if (self.remainingBudgetItems.length === 0) {
        self.showOperationalRemainingBreakdown = false;
        return;
      }
      var groupedIds = {};
      self.remainingBudgetItems.map((item) => {
        var price = Number(item.unit_price || item.price || 0);
        var subledgerId = item.subledger_id || item.id;
        if (
          subledgerId === undefined ||
          subledgerId === null ||
          subledgerId === ""
        )
          return;
        if (!groupedIds[price]) groupedIds[price] = [];
        groupedIds[price].push(subledgerId);
      });
      var subledgerIds = self.remainingBudgetItems
        .map((item) => item.subledger_id || item.id)
        .filter((id) => id !== undefined && id !== null && id !== "");
      var projectTypeMap = await self.getProjectTypeMap(subledgerIds);
      var groupedProjectIds = {};
      var groupedOperationalIds = {};
      Object.keys(groupedIds).map((price) => {
        groupedProjectIds[price] = [];
        groupedOperationalIds[price] = [];
        groupedIds[price].map((subledgerId) => {
          var projectType = projectTypeMap[subledgerId];
          if (String(projectType || "").toLowerCase() === "operational") {
            groupedOperationalIds[price].push(subledgerId);
          } else {
            groupedProjectIds[price].push(subledgerId);
          }
        });
        groupedProjectIds[price] = groupedProjectIds[price].filter(
          (v, i, a) =>
            v !== undefined && v !== null && v !== "" && a.indexOf(v) === i,
        );
        groupedOperationalIds[price] = groupedOperationalIds[price].filter(
          (v, i, a) =>
            v !== undefined && v !== null && v !== "" && a.indexOf(v) === i,
        );
      });

      self.remainingBudgetCalcLoading = true;
      try {
        var remainingMap = {};
        var remainingTotalDepartmentMap = {};
        var requests = [];
        Object.keys(groupedProjectIds).map((price) => {
          if (groupedProjectIds[price].length === 0) return;
          requests.push(
            axios
              .get(
                "https://panel.buanamultiteknik.com/api/budget/project-budget/subledger",
                {
                  params: {
                    price: price,
                    subledger_id: groupedProjectIds[price].join(","),
                  },
                },
              )
              .then((budgetRes) => {
                var budgetData =
                  budgetRes &&
                  budgetRes.data &&
                  budgetRes.data.data &&
                  Array.isArray(budgetRes.data.data)
                    ? budgetRes.data.data
                    : [];
                budgetData.map((d) => {
                  remainingMap[d.subledger_id] = d.remaining;
                  remainingTotalDepartmentMap[d.subledger_id] =
                    d.remaining_bugdet_total_department;
                  if (d.project_name || d.budget_name) {
                    var label = d.project_name || "-";
                    if (d.budget_name) label += " (" + d.budget_name + ")";
                    remainingMap["label_" + d.subledger_id] = label;
                  }
                });
              }),
          );
        });
        Object.keys(groupedOperationalIds).map((price) => {
          if (groupedOperationalIds[price].length === 0) return;
          requests.push(
            axios
              .get(
                "https://panel.buanamultiteknik.com/api/budget/operational-budget/subledger",
                {
                  params: {
                    price: price,
                    subledger_id: groupedOperationalIds[price].join(","),
                  },
                },
              )
              .then((budgetRes) => {
                var budgetData =
                  budgetRes &&
                  budgetRes.data &&
                  budgetRes.data.data &&
                  Array.isArray(budgetRes.data.data)
                    ? budgetRes.data.data
                    : [];
                budgetData.map((d) => {
                  remainingMap[d.subledger_id] = d.remaining;
                  remainingTotalDepartmentMap[d.subledger_id] =
                    d.remaining_bugdet_total_department;
                  var department = d.department_name || "-";
                  var typeOperational = d.type_operational_name || "-";
                  var subTypeOperational = d.sub_type_operational_name || "-";
                  remainingMap["label_" + d.subledger_id] =
                    department +
                    " -> " +
                    typeOperational +
                    " -> " +
                    subTypeOperational;
                });
              }),
          );
        });
        await Promise.all(requests);
        self.remainingBudgetItems = self.remainingBudgetItems.map((item) => {
          var sid = item.subledger_id || item.id;
          var apiLabel = remainingMap["label_" + sid];
          var projectType = projectTypeMap[sid];
          var isOperational = String(projectType || "").toLowerCase() === "operational";
          return Object.assign({}, item, {
            remaining_budget: remainingMap[sid],
            remaining_bugdet_total_department: remainingTotalDepartmentMap[sid],
            is_operational: isOperational,
            project_budget_label: apiLabel || item.project_budget_label || "-",
            // only merge rows when we got a clear label from budget API.
            // if API label is missing, keep it unique per subledger to avoid wrong merge.
            project_budget_group_key: apiLabel ? "api:" + apiLabel : "sid:" + String(sid),
          });
        });
        self.rebuildRemainingBudgetGroups();
        self.showOperationalRemainingBreakdown = self.remainingBudgetItems.some(
          (item) => !!item.is_operational,
        );
      } catch (e) {
        console.log("Remaining budget API error:", e);
        self.showOperationalRemainingBreakdown = false;
      }
      self.remainingBudgetCalcLoading = false;
    },
    rebuildRemainingBudgetGroups: function () {
      var self = this;
      if (!Array.isArray(self.remainingBudgetItems) || self.remainingBudgetItems.length === 0)
        return;
      var grouped = self.remainingBudgetItems.slice();
      var i = 0;
      while (i < grouped.length) {
        var key =
          grouped[i].project_budget_group_key ||
          grouped[i].project_budget_label ||
          "-";
        var j = i + 1;
        while (
          j < grouped.length &&
          (grouped[j].project_budget_group_key ||
            grouped[j].project_budget_label ||
            "-") === key
        ) {
          j++;
        }
        var count = j - i;
        grouped[i] = Object.assign({}, grouped[i], {
          show_project_budget: true,
          project_budget_rowspan: count,
          show_remaining: true,
          remaining_rowspan: count,
        });
        for (var k = i + 1; k < j; k++) {
          grouped[k] = Object.assign({}, grouped[k], {
            show_project_budget: false,
            project_budget_rowspan: 1,
            show_remaining: false,
            remaining_rowspan: 1,
          });
        }
        i = j;
      }
      self.remainingBudgetItems = grouped;
    },
    openRemainingBudgetDialog: function (item) {
      var self = this;
      if (!item || !item.id) return;
      self.dialogRemainingBudget = true;
      self.remainingBudgetItems = [];
      self.showOperationalRemainingBreakdown = false;
      self.remainingBudgetPrId = item.id;
      self.fetchRemainingBudgetItems(item.id);
    },
  },
  mounted: function () {
    var self = this;
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

