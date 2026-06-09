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
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false || subledgerReviseLoading"
          @click="openSubledgerRevise"
          >Subledger Revise</v-btn
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
          :dark="!disableApproved"
          :outlined="disableApproved"
          v-if="!tableOnly && !history"
          :disabled="disableApproved"
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
              Number(props.item.status) === 1 ||
              Number(props.item.status) === 2 ||
              Number(props.item.status) === -2 ||
              Number(props.item.status) === -3
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
            v-if="Number(props.item.status) === 0"
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
            v-if="Number(props.item.status) === 3"
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
            v-if="Number(props.item.status) === -4"
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
            v-if="Number(props.item.status) === -1"
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
      :actions="false"
      v-model="dialogSubledgerRevise"
      title="Subledger Revise"
      min-height="75%"
      fullscreen
    >
      <div
        style="
          padding: 16px;
          display: flex;
          flex-direction: column;
          gap: 16px;
          min-height: 100%;
          background: #f5f7fb;
        "
      >
        <v-alert dense text color="info" v-if="subledgerReviseLoading">
          <div
            style="
              display: flex;
              align-items: center;
              gap: 10px;
            "
          >
            <v-progress-circular
              indeterminate
              color="info"
              size="18"
              width="2"
            ></v-progress-circular>
            <span>Loading part dan subledger...</span>
          </div>
        </v-alert>

        <v-alert
          dense
          text
          color="warning"
          v-else-if="subledgerReviseGroups.length === 0"
        >
          Data part / subledger untuk PR ini belum ada.
        </v-alert>

        <v-card v-for="part in subledgerReviseGroups" :key="part.id" outlined>
          <div
            style="
              padding: 16px;
              border-bottom: 1px solid #e0e0e0;
              background: #eef3ff;
            "
          >
            <div style="font-size: 12px; font-weight: bold; color: #4b5563">
              PART
            </div>
            <div style="margin-top: 8px; font-size: 16px; font-weight: bold">
              {{ part.item_name || part.item_no || "Part" }}
            </div>
            <div style="margin-top: 4px; color: #4b5563">
              <span>Item No:</span> {{ part.item_no || "-" }}
              <span style="margin-left: 16px">Order:</span>
              {{ part.order_no || "-" }}
            </div>
            <div
              v-if="part.notes"
              style="margin-top: 8px; color: #4b5563; white-space: pre-wrap"
            >
              {{ part.notes }}
            </div>
          </div>

          <div
            style="
              padding: 16px;
              display: flex;
              flex-direction: column;
              gap: 12px;
            "
          >
            <v-card
              v-for="subledger in part.subledgers"
              :key="subledger.id"
              outlined
            >
              <div
                style="
                  padding: 16px;
                  display: flex;
                  gap: 24px;
                  align-items: flex-start;
                  justify-content: space-between;
                  flex-wrap: wrap;
                "
              >
                <div style="flex: 1 1 720px; min-width: 280px">
                  <div
                    style="font-size: 12px; font-weight: bold; color: #4b5563"
                  >
                    SUBLEDGER
                  </div>
                  <div style="margin-top: 8px; font-weight: bold">
                    {{ subledger.description || "-" }}
                  </div>

                  <div
                    style="
                      margin-top: 12px;
                      display: grid;
                      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                      gap: 10px 20px;
                      color: #4b5563;
                    "
                  >
                    <div><span>Qty:</span> {{ subledger.qty || 0 }}</div>
                    <div>
                      <span>Allocation:</span> {{ subledger.allocation || "-" }}
                    </div>
                    <div>
                      <span>Currency:</span> {{ subledger.currency || "IDR" }}
                    </div>
                    <div>
                      <span>Unit Price:</span>
                      {{ formatMoney(subledger.unit_price, subledger.currency) }}
                    </div>
                    <div>
                      <span>Exchange Rate:</span>
                      {{ subledger.exchange_rate || "-" }}
                    </div>
                    <div>
                      <span>Year Budget:</span>
                      {{ subledger.year_budget || "-" }}
                    </div>
                    <div style="grid-column: 1 / -1">
                      <span>Project:</span>
                      {{
                        [subledger.project_no, subledger.project_name]
                          .filter(Boolean)
                          .join(" - ") || "-"
                      }}
                    </div>
                  </div>
                </div>

                <div
                  style="
                    flex: 0 0 auto;
                    min-width: 180px;
                    display: flex;
                    justify-content: flex-end;
                  "
                >
                  <v-btn
                    small
                    color="primary"
                    outlined
                    @click="openEditAllocation(subledger)"
                  >
                    Edit Budget Allocation
                  </v-btn>
                </div>
              </div>
            </v-card>

            <v-alert dense text color="grey" v-if="part.subledgers.length === 0">
              Part ini belum punya subledger.
            </v-alert>
          </div>
        </v-card>
      </div>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogEditAllocation"
      title="Edit Allocation"
      min-height="75%"
      @save="saveEditAllocation"
    >
      <div
        v-if="editAllocationLoading"
        style="
          min-height: 240px;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-direction: column;
          gap: 12px;
        "
      >
        <v-progress-circular
          indeterminate
          color="primary"
          size="42"
        ></v-progress-circular>
        <div style="color: #4b5563">Loading allocation data...</div>
      </div>
      <v-autoform
        v-else
        v-model="editAllocationForm"
        :valid="editAllocationValid"
      ></v-autoform>
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
      dialogEditAllocation: false,
      validUploadApproval: false,
      editAllocationValid: false,
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
      editAllocationForm: [
        {
          text: "Type",
          value: "project_type",
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
          filter: false,
          groupable: false,
          clearable: true,
          data_value: ["Project", "Operational", "Persediaan"],
          input: function (val) {
            var self = App.page();
            self.handleEditAllocationTypeChange(val.data);
          },
        },
        {
          text: "Department",
          value: "dept_id",
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
          filter: false,
          groupable: false,
          clearable: true,
          url: App.url + "bom/department",
          searchby: ["id", "dept_name"],
          formatter: ["id", "dept_name"],
          alias: "dept_name",
          options: {
            filter: {},
            filterType: {},
            filterCondition: {
              id: "or",
              dept_name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function (val) {
            var self = App.page();
            self.onEditAllocationDepartmentChange(val.data);
          },
        },
        {
          text: "Type Department",
          value: "type_operational_id",
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
          filter: false,
          groupable: false,
          clearable: true,
          url: App.url + "bom/type",
          searchby: ["id", "name"],
          formatter: ["id", "name"],
          alias: "type_operational",
          options: {
            filter: {},
            filterType: {},
            filterCondition: {
              id: "or",
              name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function (val) {
            var self = App.page();
            self.onEditAllocationTypeDepartmentChange(val.data);
          },
        },
        {
          text: "Sub Type Department",
          value: "sub_type_operational_id",
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
          filter: false,
          groupable: false,
          clearable: true,
          url: App.url + "bom/subtype",
          searchby: ["id", "name"],
          formatter: ["id", "name"],
          alias: "sub_type_operational",
          options: {
            filter: {},
            filterType: {},
            filterCondition: {
              id: "or",
              name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function () {
            var self = App.page();
            self.refreshEditAllocationRemainingBudget();
          },
        },
        {
          text: "Project No",
          value: "project_id",
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
          filter: false,
          groupable: false,
          clearable: true,
          url: App.url + "project/project",
          searchby: ["full"],
          pk: "id",
          formatter: function (val) {
            return {
              value: val.id,
              text: val.full,
              category_item: val.category,
            };
          },
          alias: "project_no",
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
            category_item: true,
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function (data) {
            var self = App.page();
            if (data.data) {
              self.editAllocationObj.budget_id.options.filter.project_id =
                data.data;
              self.editAllocationObj.budget_id.data = null;
              self.editAllocationObj.budget_id.update = null;
            } else {
              self.editAllocationObj.budget_id.options.filter.project_id = -1;
              self.editAllocationObj.budget_id.data = null;
              self.editAllocationObj.budget_id.update = null;
            }
            self.resetEditAllocationRemainingBudget();
            self.editAllocationForm = App.updateObject(self.editAllocationForm);
          },
        },
        {
          text: "Budget",
          value: "budget_id",
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
          filter: false,
          groupable: false,
          clearable: true,
          url: App.url + "budget/budget",
          searchby: ["budget_name"],
          formatter: ["id", "budget_name"],
          pk: "id",
          alias: "budget_name",
          options: {
            filter: {
              project_id: -1,
            },
            filterType: {},
            filterCondition: {
              project_id: "and",
              budget_name: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
          input: function () {
            var self = App.page();
            self.refreshEditAllocationRemainingBudget();
          },
        },
        {
          text: "",
          value: "remaining_budget",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: true,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          readonly: true,
        },
        {
          text: "Total Harga",
          value: "total_harga",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: true,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
          readonly: true,
        },
      ],
      processData: {},
      dialogNote: false,
      dialogNotes: false,
      dialogSubledgerRevise: false,
      dialogRevise: false,
      dialogCancel: false,
      subledgerReviseLoading: false,
      subledgerReviseGroups: [],
      editAllocationLoading: false,
      editAllocationTarget: null,
      editAllocationRemainingBudgetValue: null,
      editAllocationTotalHargaValue: null,
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
      var status = Number(self.selected && self.selected.status);
      if (!this.selected) return true;
      if (status > 0 || status < 0) return true;
      return false;
    },
    disableApproved: function () {
      var self = this;
      if (!this.selected) return true;
      // status from API can be string, normalize first
      var status = Number(self.selected.status);
      // Allow manual approved only for Pending 1
      return status !== 1;
    },
    disableAskCancel: function () {
      var self = this;
      var status = Number(self.selected && self.selected.status);
      if (!this.selected) return true;
      if (status === 3) return false;
      return true;
    },
    disableOpen: function () {
      var self = this;
      var status = Number(self.selected && self.selected.status);

      if (!self.selected) return true;
      if (status > 0 || status < 0) return true;
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
    editAllocationObj: function () {
      var tmp = {},
        self = this;
      Object.keys(self.editAllocationForm).map((key) => {
        var val = self.editAllocationForm[key];
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
      var status = Number(item.status);
      if (status === 0 && item.flag == 0) return "Deleted";
      if (status === 0 && item.flag != 0) return "New";
      if (status === 1) return "Asking for Approval 1";
      if (status === 2) return "Asking for Approval 2";
      if (status === 3) return "Final Approved";
      if (status === -1) return "Rejected";
      if (status === -2) return "Asking for Cancel 1";
      if (status === -3) return "Asking for Cancel 2";
      if (status === -4) return "Canceled";
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
    openSubledgerRevise: async function () {
      var self = this;
      if (!self.selected || !self.selected.id) return;
      self.dialogSubledgerRevise = true;
      await self.getSubledgerReviseData(self.selected.id);
    },
    getSubledgerReviseData: async function (prId) {
      var self = this;
      self.subledgerReviseLoading = true;
      self.subledgerReviseGroups = [];
      try {
        var partRes = await axios.get(App.url + "bom/prpart", {
          params: {
            filter: {
              pr_id: prId,
            },
            filterType: {},
            sortBy: ["order_no", "id"],
            sortDesc: [false, false],
            limit: -1,
          },
        });
        var parts =
          partRes && partRes.data && Array.isArray(partRes.data.data)
            ? partRes.data.data
            : [];
        var prPartIds = parts
          .map(function (part) {
            return part.id;
          })
          .filter(function (id) {
            return id !== undefined && id !== null && id !== "";
          });
        var subledgers = [];

        if (prPartIds.length > 0) {
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
          subledgers =
            subledgerRes &&
            subledgerRes.data &&
            Array.isArray(subledgerRes.data.data)
              ? subledgerRes.data.data
              : [];
        }

        var subledgerMap = {};
        subledgers.map(function (subledger) {
          if (!subledgerMap[subledger.pr_part_id]) {
            subledgerMap[subledger.pr_part_id] = [];
          }
          subledgerMap[subledger.pr_part_id].push(subledger);
        });

        self.subledgerReviseGroups = parts.map(function (part) {
          return Object.assign({}, part, {
            subledgers: subledgerMap[part.id] || [],
          });
        });
      } catch (e) {
        App.errorMsg();
      }
      self.subledgerReviseLoading = false;
    },
    resetEditAllocationRemainingBudget: function () {
      var self = this;
      self.editAllocationRemainingBudgetValue = null;
      self.editAllocationObj.remaining_budget.form = false;
      self.editAllocationObj.remaining_budget.data = null;
      self.editAllocationForm = App.updateObject(self.editAllocationForm);
    },
    resetEditAllocationTotalHarga: function () {
      var self = this;
      self.editAllocationTotalHargaValue = null;
      self.editAllocationObj.total_harga.form = false;
      self.editAllocationObj.total_harga.data = null;
      self.editAllocationForm = App.updateObject(self.editAllocationForm);
    },
    toEditAllocationNumber: function (value) {
      if (value === null || value === undefined || value === "") return 0;
      if (typeof value === "number") return value;
      var normalized = String(value).replace(/,/g, "");
      var parsed = parseFloat(normalized);
      return isNaN(parsed) ? 0 : parsed;
    },
    applyEditAllocationTotalHarga: function () {
      var self = this;
      if (!self.editAllocationTarget) {
        self.resetEditAllocationTotalHarga();
        return 0;
      }
      var qty = self.toEditAllocationNumber(self.editAllocationTarget.qty);
      var unitPrice = self.toEditAllocationNumber(
        self.editAllocationTarget.unit_price,
      );
      var currency = String(self.editAllocationTarget.currency || "IDR")
        .trim()
        .toUpperCase();
      var exchangeRate = self.toEditAllocationNumber(
        self.editAllocationTarget.exchange_rate,
      );
      var effectiveRate = currency === "IDR" ? 1 : exchangeRate;
      var totalHarga = qty * unitPrice * effectiveRate;
      var formattedTotalHarga = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(Number(totalHarga || 0));
      self.editAllocationTotalHargaValue = Number(totalHarga || 0);
      self.editAllocationObj.total_harga.form = true;
      self.editAllocationObj.total_harga.data = formattedTotalHarga;
      self.editAllocationForm = App.updateObject(self.editAllocationForm);
      return totalHarga;
    },
    validateEditAllocationRemainingBudget: function (projectType) {
      var self = this;
      var totalHarga = self.applyEditAllocationTotalHarga();
      if (
        self.editAllocationRemainingBudgetValue === null ||
        self.editAllocationRemainingBudgetValue === undefined
      ) {
        App.errorMsg({
          message: "Remaining Budget belum berhasil dimuat. Silakan cek allocation yang dipilih.",
        });
        return false;
      }
      if (self.editAllocationRemainingBudgetValue >= totalHarga) {
        return true;
      }
      if (projectType === "Operational") {
        App.errorMsg({ message: "Sisa Budget Operational Tidak Cukup" });
        return false;
      }
      if (projectType === "Project") {
        App.errorMsg({ message: "Sisa Budget Project Tidak Cukup" });
        return false;
      }
      if (projectType === "Persediaan") {
        App.errorMsg({ message: "Sisa Budget Persediaan Tidak Cukup" });
        return false;
      }
      App.errorMsg({ message: "Sisa Budget Tidak Cukup" });
      return false;
    },
    setEditAllocationRemainingBudgetMessage: function (message, isError) {
      var self = this;
      if (isError) {
        self.editAllocationRemainingBudgetValue = null;
      }
      self.editAllocationObj.remaining_budget.form = true;
      self.editAllocationObj.remaining_budget.data = message;
      self.editAllocationForm = App.updateObject(self.editAllocationForm);
    },
    applyEditAllocationRemainingBudget: function (remaining) {
      var self = this;
      var formattedRemaining = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(Number(remaining || 0));
      self.editAllocationRemainingBudgetValue = Number(remaining || 0);
      self.editAllocationObj.remaining_budget.form = true;
      self.editAllocationObj.remaining_budget.data =
        "Remaining Budget : " + formattedRemaining;
      self.editAllocationForm = App.updateObject(self.editAllocationForm);
    },
    fetchEditAllocationProjectRemainingBudget: function (projectId, budgetId) {
      var self = this;
      self.setEditAllocationRemainingBudgetMessage(
        "Calculate Remaining Budget...",
        false,
      );
      return axios
        .get(
          "https://panel.buanamultiteknik.com/api/budget/project-budget/index",
          {
            params: {
              project_id: projectId,
              budget_id: budgetId,
            },
          },
        )
        .then(function (response) {
          var remaining = response?.data?.budget?.remaining;
          if (remaining === undefined || remaining === null) {
            self.setEditAllocationRemainingBudgetMessage(
              "Error : Failed to load remaining budget",
              true,
            );
            return;
          }
          self.applyEditAllocationRemainingBudget(remaining);
        })
        .catch(function () {
          self.setEditAllocationRemainingBudgetMessage(
            "Error : Failed to load remaining budget",
            true,
          );
        });
    },
    fetchEditAllocationOperationalRemainingBudget: function (
      deptId,
      typeOperationalId,
      subTypeOperationalId,
    ) {
      var self = this;
      self.setEditAllocationRemainingBudgetMessage(
        "Calculate Remaining Budget...",
        false,
      );
      return axios
        .get(
          "https://panel.buanamultiteknik.com/api/budget/operational-budget/index",
          {
            params: {
              dept_id: deptId,
              type_operational_id: typeOperationalId,
              sub_type_operational_id: subTypeOperationalId,
            },
          },
        )
        .then(function (response) {
          var remaining = response?.data?.budget?.remaining;
          if (remaining === undefined || remaining === null) {
            self.setEditAllocationRemainingBudgetMessage(
              "Error : Failed to load remaining budget",
              true,
            );
            return;
          }
          self.applyEditAllocationRemainingBudget(remaining);
        })
        .catch(function () {
          self.setEditAllocationRemainingBudgetMessage(
            "Error : Failed to load remaining budget",
            true,
          );
        });
    },
    fetchEditAllocationPersediaanRemainingBudget: function () {
      var self = this;
      self.setEditAllocationRemainingBudgetMessage(
        "Calculate Remaining Budget...",
        false,
      );
      return axios
        .get("https://panel.buanamultiteknik.com/api/budget/persediaan/summary")
        .then(function (response) {
          var remaining =
            response?.data?.budget?.remaining ??
            response?.data?.remaining ??
            response?.data?.data?.remaining ??
            response?.data?.summary?.remaining;
          if (remaining === undefined || remaining === null) {
            self.setEditAllocationRemainingBudgetMessage(
              "Error : Failed to load remaining budget",
              true,
            );
            return;
          }
          self.applyEditAllocationRemainingBudget(remaining);
        })
        .catch(function () {
          self.setEditAllocationRemainingBudgetMessage(
            "Error : Failed to load remaining budget",
            true,
          );
        });
    },
    refreshEditAllocationRemainingBudget: function () {
      var self = this;
      var projectType = self.editAllocationObj.project_type.data;
      if (projectType === "Project") {
        var projectId = self.editAllocationObj.project_id.data;
        var budgetId = self.editAllocationObj.budget_id.data;
        if (projectId && budgetId) {
          return self.fetchEditAllocationProjectRemainingBudget(
            projectId,
            budgetId,
          );
        } else {
          self.resetEditAllocationRemainingBudget();
        }
        return Promise.resolve();
      }
      if (projectType === "Operational") {
        var deptId = self.editAllocationObj.dept_id.data;
        var typeOperationalId = self.editAllocationObj.type_operational_id.data;
        var subTypeOperationalId =
          self.editAllocationObj.sub_type_operational_id.data;
        if (deptId && typeOperationalId && subTypeOperationalId) {
          return self.fetchEditAllocationOperationalRemainingBudget(
            deptId,
            typeOperationalId,
            subTypeOperationalId,
          );
        } else {
          self.resetEditAllocationRemainingBudget();
        }
        return Promise.resolve();
      }
      if (projectType === "Persediaan") {
        return self.fetchEditAllocationPersediaanRemainingBudget();
      }
      self.resetEditAllocationRemainingBudget();
      return Promise.resolve();
    },
    handleEditAllocationTypeChange: function (projectType) {
      var self = this;
      self.editAllocationObj.project_id.form = false;
      self.editAllocationObj.budget_id.form = false;
      self.editAllocationObj.dept_id.form = false;
      self.editAllocationObj.type_operational_id.form = false;
      self.editAllocationObj.sub_type_operational_id.form = false;

      if (projectType === "Project") {
        self.editAllocationObj.project_id.form = true;
        self.editAllocationObj.budget_id.form = true;
      } else if (projectType === "Operational") {
        self.editAllocationObj.dept_id.form = true;
        self.editAllocationObj.type_operational_id.form = true;
        self.editAllocationObj.sub_type_operational_id.form = true;
      } else if (projectType === "Persediaan") {
        self.editAllocationObj.project_id.data = null;
        self.editAllocationObj.project_id.update = null;
        self.editAllocationObj.budget_id.data = null;
        self.editAllocationObj.budget_id.update = null;
        self.editAllocationObj.dept_id.data = null;
        self.editAllocationObj.dept_id.update = null;
        self.editAllocationObj.type_operational_id.data = null;
        self.editAllocationObj.type_operational_id.update = null;
        self.editAllocationObj.sub_type_operational_id.data = null;
        self.editAllocationObj.sub_type_operational_id.update = null;
      }

      if (projectType !== "Project") {
        self.editAllocationObj.project_id.data = null;
        self.editAllocationObj.project_id.update = null;
        self.editAllocationObj.budget_id.data = null;
        self.editAllocationObj.budget_id.update = null;
        self.editAllocationObj.budget_id.options.filter.project_id = -1;
      }
      if (projectType !== "Operational") {
        self.editAllocationObj.dept_id.data = null;
        self.editAllocationObj.dept_id.update = null;
        self.editAllocationObj.type_operational_id.data = null;
        self.editAllocationObj.type_operational_id.update = null;
        self.editAllocationObj.sub_type_operational_id.data = null;
        self.editAllocationObj.sub_type_operational_id.update = null;
        delete self.editAllocationObj.type_operational_id.options.filter
          .department_id;
        delete self.editAllocationObj.sub_type_operational_id.options.filter
          .department_id;
        delete self.editAllocationObj.sub_type_operational_id.options.filter
          .type_operational_id;
      }

      self.editAllocationForm = App.updateObject(self.editAllocationForm);
      self.refreshEditAllocationRemainingBudget();
    },
    onEditAllocationDepartmentChange: function (deptId) {
      var self = this;
      self.editAllocationObj.type_operational_id.data = null;
      self.editAllocationObj.type_operational_id.update = null;
      self.editAllocationObj.sub_type_operational_id.data = null;
      self.editAllocationObj.sub_type_operational_id.update = null;
      if (deptId) {
        self.editAllocationObj.type_operational_id.options.filter.department_id =
          deptId;
        self.editAllocationObj.sub_type_operational_id.options.filter.department_id =
          deptId;
      } else {
        delete self.editAllocationObj.type_operational_id.options.filter
          .department_id;
        delete self.editAllocationObj.sub_type_operational_id.options.filter
          .department_id;
      }
      delete self.editAllocationObj.sub_type_operational_id.options.filter
        .type_operational_id;
      self.editAllocationForm = App.updateObject(self.editAllocationForm);
      self.refreshEditAllocationRemainingBudget();
    },
    onEditAllocationTypeDepartmentChange: function (typeOperationalId) {
      var self = this;
      self.editAllocationObj.sub_type_operational_id.data = null;
      self.editAllocationObj.sub_type_operational_id.update = null;
      if (typeOperationalId) {
        self.editAllocationObj.sub_type_operational_id.options.filter.type_operational_id =
          typeOperationalId;
      } else {
        delete self.editAllocationObj.sub_type_operational_id.options.filter
          .type_operational_id;
      }
      self.editAllocationForm = App.updateObject(self.editAllocationForm);
      self.refreshEditAllocationRemainingBudget();
    },
    openEditAllocation: async function (subledger) {
      var self = this;
      self.editAllocationTarget = subledger;
      self.editAllocationLoading = true;
      self.dialogEditAllocation = true;
      await self.$nextTick();

      self.editAllocationObj.project_id.data_value = subledger.project_id
        ? [
            {
              value: subledger.project_id,
              text:
                [subledger.project_no, subledger.project_name]
                  .filter(Boolean)
                  .join(" - ") || subledger.project_no || "-",
              category_item: subledger.categoryitem_name || null,
            },
          ]
        : [];
      self.editAllocationObj.budget_id.data_value = subledger.budget_id
        ? [
            {
              value: subledger.budget_id,
              text: subledger.budget_name || "-",
            },
          ]
        : [];
      self.editAllocationObj.dept_id.data_value = subledger.dept_id
        ? [
            {
              value: subledger.dept_id,
              text: subledger.dept_name || "-",
            },
          ]
        : [];
      self.editAllocationObj.type_operational_id.data_value =
        subledger.type_operational_id
          ? [
              {
                value: subledger.type_operational_id,
                text: subledger.type_operational || "-",
              },
            ]
          : [];
      self.editAllocationObj.sub_type_operational_id.data_value =
        subledger.sub_type_operational_id
          ? [
              {
                value: subledger.sub_type_operational_id,
                text: subledger.sub_type_operational || "-",
              },
            ]
          : [];

      self.editAllocationObj.project_type.data = subledger.project_type || null;
      self.editAllocationObj.project_type.update =
        subledger.project_type || null;
      self.editAllocationObj.project_id.data = subledger.project_id || null;
      self.editAllocationObj.project_id.update = subledger.project_id || null;
      self.editAllocationObj.budget_id.data = subledger.budget_id || null;
      self.editAllocationObj.budget_id.update = subledger.budget_id || null;
      self.editAllocationObj.dept_id.data = subledger.dept_id || null;
      self.editAllocationObj.dept_id.update = subledger.dept_id || null;
      self.editAllocationObj.type_operational_id.data =
        subledger.type_operational_id || null;
      self.editAllocationObj.type_operational_id.update =
        subledger.type_operational_id || null;
      self.editAllocationObj.sub_type_operational_id.data =
        subledger.sub_type_operational_id || null;
      self.editAllocationObj.sub_type_operational_id.update =
        subledger.sub_type_operational_id || null;
      self.editAllocationObj.remaining_budget.data = null;
      self.editAllocationObj.remaining_budget.form = false;
      self.editAllocationObj.total_harga.data = null;
      self.editAllocationObj.total_harga.form = false;
      self.editAllocationObj.budget_id.options.filter.project_id =
        subledger.project_id || -1;

      if (subledger.dept_id) {
        self.editAllocationObj.type_operational_id.options.filter.department_id =
          subledger.dept_id;
        self.editAllocationObj.sub_type_operational_id.options.filter.department_id =
          subledger.dept_id;
      } else {
        delete self.editAllocationObj.type_operational_id.options.filter
          .department_id;
        delete self.editAllocationObj.sub_type_operational_id.options.filter
          .department_id;
      }
      if (subledger.type_operational_id) {
        self.editAllocationObj.sub_type_operational_id.options.filter.type_operational_id =
          subledger.type_operational_id;
      } else {
        delete self.editAllocationObj.sub_type_operational_id.options.filter
          .type_operational_id;
      }

      self.handleEditAllocationTypeChange(subledger.project_type || null);

      if (subledger.project_type === "Project") {
        self.editAllocationObj.project_id.data = subledger.project_id || null;
        self.editAllocationObj.project_id.update = subledger.project_id || null;
        self.editAllocationObj.budget_id.data = subledger.budget_id || null;
        self.editAllocationObj.budget_id.update = subledger.budget_id || null;
      }
      if (subledger.project_type === "Operational") {
        self.editAllocationObj.dept_id.data = subledger.dept_id || null;
        self.editAllocationObj.dept_id.update = subledger.dept_id || null;
        self.editAllocationObj.type_operational_id.data =
          subledger.type_operational_id || null;
        self.editAllocationObj.type_operational_id.update =
          subledger.type_operational_id || null;
        self.editAllocationObj.sub_type_operational_id.data =
          subledger.sub_type_operational_id || null;
        self.editAllocationObj.sub_type_operational_id.update =
          subledger.sub_type_operational_id || null;
      }
      self.editAllocationForm = App.updateObject(self.editAllocationForm);
      self.applyEditAllocationTotalHarga();
      try {
        await self.refreshEditAllocationRemainingBudget();
      } finally {
        self.editAllocationLoading = false;
      }
    },
    saveEditAllocation: async function () {
      var self = this;
      if (!self.editAllocationTarget) return;
      var payload = {
        pk: self.editAllocationTarget.id,
        pr_part_id: self.editAllocationTarget.pr_part_id,
        description: self.editAllocationTarget.description,
        qty: self.editAllocationTarget.qty,
        allocation: self.editAllocationTarget.allocation,
        unit_price: self.editAllocationTarget.unit_price,
        currency: self.editAllocationTarget.currency,
        exchange_rate: self.editAllocationTarget.exchange_rate,
        rate_date: self.editAllocationTarget.rate_date,
        requirement: self.editAllocationTarget.requirement || "-",
        year_budget:
          self.editAllocationTarget.year_budget ||
          new Date().getFullYear().toString(),
        project_type: self.editAllocationObj.project_type.data,
        project_id:
          self.editAllocationObj.project_type.data === "Project"
            ? self.editAllocationObj.project_id.data
            : "",
        budget_id:
          self.editAllocationObj.project_type.data === "Project"
            ? self.editAllocationObj.budget_id.data
            : "",
        dept_id:
          self.editAllocationObj.project_type.data === "Operational"
            ? self.editAllocationObj.dept_id.data
            : "",
        type_operational_id:
          self.editAllocationObj.project_type.data === "Operational"
            ? self.editAllocationObj.type_operational_id.data
            : "",
        sub_type_operational_id:
          self.editAllocationObj.project_type.data === "Operational"
            ? self.editAllocationObj.sub_type_operational_id.data
            : "",
        category_item_id: self.editAllocationTarget.category_item_id,
        alokasi_pembelian: self.editAllocationTarget.alokasi_pembelian,
        rnd_id: self.editAllocationTarget.rnd_id,
      };

      var projectType = payload.project_type;
      if (!projectType) {
        App.errorMsg({ message: "Type is required" });
        return;
      }
      if (projectType === "Project" && (!payload.project_id || !payload.budget_id)) {
        App.errorMsg({ message: "Project No dan Budget wajib diisi" });
        return;
      }
      if (
        projectType === "Operational" &&
        (!payload.dept_id ||
          !payload.type_operational_id ||
          !payload.sub_type_operational_id)
      ) {
        App.errorMsg({
          message: "Department, Type Department, dan Sub Type Department wajib diisi",
        });
        return;
      }
      if (!self.validateEditAllocationRemainingBudget(projectType)) {
        return;
      }

      try {
        var res = await axios.put(App.url + "bom/prsubledger", payload);
        if (!res.data.status) {
          App.errorMsg(res.data);
          return;
        }
        App.successMsg();
        self.dialogEditAllocation = false;
        await self.getSubledgerReviseData(self.selected.id);
      } catch (e) {
        App.errorMsg();
      }
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
    formatMoney: function (value, currency) {
      var amount = Number(value || 0);
      if (!amount) return "0";
      try {
        return new Intl.NumberFormat("en-US", {
          style: "currency",
          currency: currency || "IDR",
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        }).format(amount);
      } catch (e) {
        return amount.toLocaleString("en-US");
      }
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
