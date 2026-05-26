<template>
  <v-container
    class="po-dashboard-page"
    :class="{ 'is-mobile-view': isMobileView }"
    v-observe-visibility="onVisible"
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
      class="po-dashboard-template"
      custom-sort
      :show-expand="true"
      :single-expand="true"
      v-if="check_user('po_validasi')"
      v-model="value"
      ref="template"
      :item-key="itemKey"
      :url="url"
      :headers="headers"
      :items-options="itemsOptions"
      :name="name"
      @update:selected-row="onSelectedRow"
      @update:selected-row-all="onSelectedRowAll"
      :table-only="tableOnly"
    >
      <template v-slot:title-body v-if="$refs.template">
        <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
      </template>
      <template v-slot:menu-after-filter>
        <v-btn
          color="primary"
          outlined
          small
          @click="revisionHistory"
          :disabled="!allowRevisiHistory"
        >
          Revise History
        </v-btn>

        <!-- <div>onlyApproveRevision: {{ onlyApproveRevision }}</div> -->
        <template v-if="onlyApproveRevision">
          <v-btn
            color="primary"
            outlined
            small
            @click="dialogNotesApprovalRevision = true"
            :disabled="!selected"
          >
            Approve Revision
          </v-btn>
        </template>
        <!-- <v-btn color="primary" outlined small @click="dialogItemGroup = true" :disabled="!selected">
                    Show Items
                </v-btn> -->
        <v-btn
          small
          color="primary"
          outlined
          :disabled="selected == false"
          @click="dialogNotes = true"
          >Notes</v-btn
        >

        <template v-if="!nointeraction">
          <v-btn
            color="red"
            :loading="loading"
            outlined
            small
            @click="openAskDraft"
            :disabled="
              !selected ||
              Number(selected.approved) === -2 ||
              Number(selected.approved) === -3
            "
            v-if="btnAskDraft"
          >
            Asking for Draft PO
          </v-btn>
          <v-btn
            color="red"
            :loading="loading"
            outlined
            small
            @click="approveDraft"
            :disabled="!allowApproveDraft"
            v-if="btnApproveDraft"
          >
            Approve as Draft PO
          </v-btn>
          <v-btn
            color="success"
            :loading="loading"
            outlined
            small
            @click="openApprove"
            :disabled="!selected"
          >
            {{
              selected !== false
                ? selected.approved > 0
                  ? "Approve"
                  : selected.approved == -2
                    ? "Asking For Cancel PO"
                    : "Approve Cancel"
                : "Approve"
            }}
          </v-btn>
          <v-btn
            color="warning"
            :loading="loading"
            outlined
            small
            @click="openComment"
            :disabled="!selected"
          >
            Revise
          </v-btn>

          <v-btn
            color="red"
            :loading="loading"
            outlined
            small
            @click="openReject"
            :disabled="
              !selected ||
              Number(selected.approved) === -2 ||
              Number(selected.approved) === -3
            "
          >
            Reject
          </v-btn>
        </template>
        <v-btn
          color="primary"
          outlined
          small
          @click="openRemainingBudgetDialog(selected)"
          :disabled="!selected"
        >
          Show Remaining Budget
        </v-btn>
        <v-btn
          color="primary"
          outlined
          small
          @click="openReport2"
          :disabled="!selected"
        >
          <v-icon small left>mdi-printer</v-icon>Preview
        </v-btn>
        <!-- <v-btn color="primary" outlined small @click="openReport2" :disabled="!selected">
                    <v-icon small left>mdi-printer</v-icon>Preview (new)
                </v-btn> -->
      </template>
      <template v-slot:expanded-item="props">
        <td
          ref="expanded"
          :colspan="props.headers.length"
          style="height: auto"
          :key="props.item[itemKey]"
        >
          <purchase-item-group
            :table-fixed-header="false"
            :item-height-as-min-height="true"
            :table-fill="false"
            is-dashboard
            table-only
            :key="props.item[itemKey]"
            :sel="{
              purchase_order_id: props.item.id,
              supplier_id: props.item.supplier_id,
              currency: props.item.currency,
              charge1: props.item.charge1,
              charge2: props.item.charge2,
            }"
            name=""
            :data="{
              purchase_order_id: props.item[itemKey],
            }"
          ></purchase-item-group>
        </td>
      </template>
      <template v-slot:item.po_details="props">
        <div style="">
          <b>PO No:</b> {{ props.item.po_no }}<br />
          <b>PO Title:</b> {{ props.item.title }}<br />
          <b>PO Date:</b> {{ props.item.po_date }}<br />
          <b>Department:</b> {{ props.item.dept_name }}<br />
          <b>Order Type:</b> {{ props.item.order_type }}<br />

          <b>Created by:</b> {{ props.item.created_by_name }}<br />
          <b>Created Date:</b>
          {{ modifDate(props.item.created_date, props.item) }}<br />
          <b>Status:</b> {{ approvedStatus(props.item.approved) }}
        </div>
      </template>
      <template v-slot:item.payment="props">
        <div style="white-space: nowrap">
          <b>Currency:</b> {{ props.item.currency }}<br />
          <b>Exchange Rate:</b>
          {{ Number(props.item.exchange_rate).format(2, 3) }}<br />
          <b>Rate Date:</b> {{ props.item.rate_date }}<br />
          <b>PO Charge:</b> {{ Number(props.item.charge1).format(2, 3) }}<br />
          <b>Other Charge:</b> {{ Number(props.item.other_charge).format(2, 3)
          }}<br />
          <b>Discount:</b> {{ Number(props.item.discount).format(2, 3) }}<br />
          <b>Payment Term:</b>
          <v-truncate :text="props.item.payment_term"></v-truncate>
        </div>
      </template>
      <template v-slot:item.internal_reference="props">
        <div style="">
          <b>RFQ No:</b><br /><a
            :href="'#/rfq/rfq/' + props.item.rfq_id"
            v-if="props.item.rfq_no"
            target="_blank"
            >{{ props.item.rfq_no }}<br /></a
          ><span v-else>{{ props.item.rfq_no }}</span> <b>PR No:</b>
          <a
            :href="props.item.signed_pr_url"
            v-if="props.item.signed_pr_url"
            target="_blank"
            ><br />{{ props.item.pr_no }}</a
          ><span v-else>{{ props.item.pr_no }}</span
          ><br />
          <b>Offer No:</b><br />{{ props.item.ref_offer_no }}<br />
          <b>Final Quote URL:</b>
          <a
            :href="props.item.final_quote_url"
            v-if="props.item.final_quote_url"
            target="_blank"
            >Open Link</a
          ><span v-else>-</span><br />
          <b>Signed PO URL:</b>
          <span @click="openReport2"><a>Open Link</a></span>
          <br />
        </div>
      </template>
      <template v-slot:item.currency_desc="props">
        <div style="white-space: nowrap">
          <b>Currency:</b> {{ props.item.currency }}<br />
          <b>Exchange Rate:</b>
          {{ Number(props.item.exchange_rate).format(2, 3) }}<br />
          <b>Rate Date:</b> {{ props.item.rate_date }}
        </div>
      </template>
      <template v-slot:item.supplier="props">
        <div style="">
          <b>Supplier:</b> {{ props.item.supplier_name }}<br />
          <b>Promised Delivery Date:</b> {{ props.item.promised_delivery_date }}
        </div>
      </template>
      <template v-slot:item.final_quote_url="props">
        <div style="white-space: nowrap">
          <b>Final Quote URL:</b>
          <a
            :href="props.item.final_quote_url"
            v-if="props.item.final_quote_url"
            target="_blank"
            >Open Link</a
          ><span v-else>-</span><br />
          <b>Signed PO URL:</b>
          <a
            :href="props.item.signed_po_url"
            v-if="props.item.signed_po_url"
            target="_blank"
            >Open Link</a
          ><span v-else>-</span><br />
          <b>Signed PR URL:</b>
          <a
            :href="props.item.signed_pr_url"
            v-if="props.item.signed_pr_url"
            target="_blank"
            >Open Link</a
          ><span v-else>-</span>
        </div>
      </template>
      <template v-slot:item.approved="props">
        {{ approvedStatus(props.item.approved) }}
      </template>
      <template v-slot:item.charge1="props">
        <div style="white-space: nowrap">
          <b>Charge 1:</b> {{ Number(props.item.charge1).format(2, 3) }}<br />
          <b>Charge 1 Desc:</b> {{ props.item.charge1_desc }}<br />
          <b>Charge 2:</b> {{ Number(props.item.charge2).format(2, 3) }}<br />
          <b>Charge 2 Desc:</b> {{ props.item.charge2_desc }}
        </div>
      </template>
      <template v-slot:item.shipping="props">
        <div style="">
          <b>Despatch:</b> {{ props.item.despatch }}<br />
          <b>Shipping Address:</b>
          <v-truncate :text="props.item.shipping_address"></v-truncate>
        </div>
      </template>
      <template v-slot:item.last_revised_comment="props">
        <b> Created By: {{ props.item.comment_creator }}</b
        ><br />
        <b> Created Date: {{ props.item.comment_date }}</b
        ><br /><br />
        <v-truncate :text="props.item.comment"></v-truncate>
      </template>
      <template v-slot:item.approval_note="props">
        <b> Created By: {{ props.item.approved_by_name }}</b
        ><br />
        <b> Created Date: {{ props.item.approval_date }}</b
        ><br /><br />
        <v-truncate :text="props.item.approval_note"></v-truncate>
      </template>
      <template v-slot:item.grand_total_price="props">
        <div style="white-space: nowrap">
          <b>Grand Total</b><br />
          {{ props.item.currency }}
          {{ Number(props.item.grand_total).format(2, 3) }}<br /><br />
          <b>Total Item</b> <br />
          {{ props.item.currency }}
          {{ Number(props.item.grand_total_price).format(2, 3) }}
        </div>
      </template>
      <template v-slot:item.approval_history="props">
        <b v-if="props.item.approved > 0">
          Created By: {{ props.item.created_by_name }}</b
        ><b v-else> Created By: {{ props.item.created_by_name }}</b
        ><br />
        <b v-if="props.item.approved > 0">
          Created Date: {{ props.item.ask_approval_date }}</b
        >
        <b v-else> Created Date: {{ props.item.ask_canceled_date }}</b
        ><br />
        <!--<span><b>Notes: </b>-->
        <!--<v-truncate v-if="props.item.approved > 0"-->
        <!--        :text=props.item.askapproval_note></v-truncate><v-truncate v-else-->
        <!--        :text=props.item.ask_canceled_note></v-truncate><br /></span>-->

        <span>
          <b>Notes: </b>

          <v-truncate
            v-if="props.item.approved > 0"
            :text="convertLinks(props.item.askapproval_note)"
            v-html="convertLinks(props.item.askapproval_note)"
          >
          </v-truncate>

          <v-truncate
            v-else
            :text="convertLinks(props.item.ask_canceled_note)"
            v-html="convertLinks(props.item.ask_canceled_note)"
          >
          </v-truncate>

          <br />
        </span>

        <span v-if="props.item.ask_draft_by && props.item.approved > 0"
          ><b>Asking for Approval Draft By:</b> {{ props.item.ask_draft_by_name
          }}<br
        /></span>
        <span v-if="props.item.ask_draft_date && props.item.approved > 0"
          ><b>Asking for Apporoval Date:</b> {{ props.item.ask_draft_date }}<br
        /></span>
        <span v-if="props.item.ask_draft_note && props.item.approved > 0"
          ><b>Asking for Approval Draft Note:</b><br /><v-truncate
            :text="props.item.ask_draft_note"
          ></v-truncate
          ><br /><br
        /></span>

        <span
          v-if="props.item.approval_draft_by_name && props.item.approved > 0"
          ><b>Approved Draft By:</b> {{ props.item.approval_draft_by_name }}<br
        /></span>
        <span v-if="props.item.approval_draft_date && props.item.approved > 0"
          ><b>Approved Draft Date:</b>
          {{ props.item.approval_draft_date }}</span
        >

        <span v-if="props.item.approved_by_name && props.item.approved > 0"
          ><b>Approved 1 By:</b> {{ props.item.approved_by_name }}<br
        /></span>
        <span v-if="props.item.approval_date && props.item.approved > 0"
          ><b>Approved 1 Date:</b> {{ props.item.approval_date }}<br
        /></span>
        <span v-if="props.item.approval_note && props.item.approved > 0"
          ><b>Approval 1 Note:</b><br /><v-truncate
            :text="props.item.approval_note"
          ></v-truncate
          ><br /><br
        /></span>

        <span
          v-if="
            props.item.approved2_by_name &&
            props.item.approved > 0 &&
            props.item.approved > 0
          "
          ><b>Approved 2 By:</b> {{ props.item.approved2_by_name }}<br
        /></span>
        <span
          v-if="
            props.item.approval_2_date &&
            props.item.approved > 0 &&
            props.item.approved > 0
          "
          ><b>Approved 2 Date:</b> {{ props.item.approval_2_date }}</span
        >

        <span v-if="props.item.canceled_by_name"
          ><b>Approved Canceled 1 By:</b> {{ props.item.canceled_by_name }}<br
        /></span>
        <span v-if="props.item.canceled_date"
          ><b>Approved Canceled 1 Date:</b> {{ props.item.canceled_date }}<br
        /></span>
        <span v-if="props.item.canceled_note"
          ><b>Approved Canceled 1 Note:</b><br /><v-truncate
            :text="props.item.canceled_note"
          ></v-truncate
          ><br /><br
        /></span>

        <span v-if="props.item.canceled2_by_name"
          ><b>Approved Canceled 2 By:</b> {{ props.item.canceled_2_by_name
          }}<br
        /></span>
        <span v-if="props.item.canceled_2_date"
          ><b>Approved Canceled 2 Date:</b>
          {{ props.item.canceled_2_date }}</span
        >

        <span v-if="props.item.approved == -1"
          ><b>Rejected By:</b> {{ props.item.rejected_by_name }}<br /><br
        /></span>
        <span v-if="props.item.approved == -1"
          ><b>Rejected Date:</b> {{ props.item.rejected_date }}</span
        >
      </template>
      <template v-slot:item.all_notes="props">
        <b>Ask Approval Note:</b><br /><v-truncate
          v-if="props.item.approved > 0"
          :text="props.item.askapproval_note"
        ></v-truncate
        ><v-truncate v-else :text="props.item.canceled_note"></v-truncate
        ><br /><br />
        <b>Approval 1 Note:</b><br /><v-truncate
          :text="props.item.approval_note"
        ></v-truncate
        ><br /><br />
        <b>Approval 2 Note:</b><br /><br />
      </template>
      <template v-slot:append-body="slotProps">
        <div v-if="isMobileView" class="po-mobile-cards">
          <div v-if="!slotProps.items || slotProps.items.length === 0" class="po-mobile-empty">
            No data available
          </div>
          <v-card
            v-for="item in slotProps.items"
            :key="'mobile-dashboard-po-' + item.id"
            outlined
            class="po-mobile-card"
            :class="{ 'po-mobile-card--selected': isSelectedRow(item) }"
            @click="selectMobileRow(item)"
          >
            <div class="po-mobile-card__head">
              <div>
                <div class="po-mobile-card__po">{{ item.po_no || "-" }}</div>
                <div class="po-mobile-card__title">{{ item.title || "-" }}</div>
              </div>
              <v-chip x-small label :color="statusChipColor(item.approved)" text-color="#1f1f1f">
                {{ approvedStatus(item.approved) }}
              </v-chip>
            </div>

            <div class="po-mobile-card__grid">
              <div><b>Supplier:</b> {{ item.supplier_name || "-" }}</div>
              <div><b>PO Date:</b> {{ item.po_date || "-" }}</div>
              <div><b>Promised:</b> {{ item.promised_delivery_date || "-" }}</div>
              <div><b>Grand Total:</b> {{ item.currency || "-" }} {{ Number(item.grand_total || 0).format(2, 3) }}</div>
              <div><b>Total Item:</b> {{ item.currency || "-" }} {{ Number(item.grand_total_price || 0).format(2, 3) }}</div>
              <div><b>Department:</b> {{ item.dept_name || "-" }}</div>
              <div><b>Created By:</b> {{ item.created_by_name || "-" }}</div>
              <div><b>Created Date:</b> {{ modifDate(item.created_date, item) }}</div>
            </div>

            <div class="po-mobile-card__actions">
              <v-btn small color="primary" outlined @click.stop="openItemsByRow(item)">
                <v-icon small left>mdi-format-list-bulleted</v-icon>Items
              </v-btn>
              <v-btn small color="primary" outlined @click.stop="openNotesByRow(item)">
                <v-icon small left>mdi-note-text</v-icon>Notes
              </v-btn>
              <template v-if="!nointeraction">
                <v-btn small color="success" outlined @click.stop="openApproveByRow(item)">
                  Approve
                </v-btn>
                <v-btn small color="warning" outlined @click.stop="openCommentByRow(item)">
                  Revise
                </v-btn>
                <v-btn
                  small
                  color="red"
                  outlined
                  @click.stop="openRejectByRow(item)"
                  :disabled="Number(item.approved) === -2 || Number(item.approved) === -3"
                >
                  Reject
                </v-btn>
              </template>
            </div>
          </v-card>
        </div>
      </template>
    </v-template>
    <v-action-dialog
      :actions="false"
      v-model="dialogItemGroup"
      title="Purchase Order Item"
      min-height="75%"
      fullscreen
    >
      <purchase-item-group
        is-dashboard
        table-only
        :key="selected.id"
        :sel="processData"
        name=""
        :data="dataid"
      ></purchase-item-group>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogComment"
      title="Comment PO"
      min-height="75%"
      @save="comment"
    >
      <v-textarea
        label="Comment PO"
        v-model="po_comment"
        hint="Comment"
      ></v-textarea>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogAskDraft"
      title="Asking for Approval Draft Note"
      min-height="75%"
      @save="askDraft"
    >
      <v-textarea label="Note" v-model="ask_draft_note"></v-textarea>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogNote"
      title="Approval Note"
      min-height="75%"
      @save="approve"
    >
      <v-textarea label="Note" v-model="approval_note"></v-textarea>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogReject"
      title="Reject Note"
      min-height="75%"
      @save="reject"
    >
      <v-textarea label="Note" v-model="reject_note"></v-textarea>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      fullscreen
      v-model="dialogRevisiHistory"
      title="Revision History"
    >
      <po-rev-history
        v-if="selected"
        table-only
        :items-options="revData"
        :key="selected.id"
      ></po-rev-history>
    </v-action-dialog>
    <v-action-dialog
      :actions="false"
      v-model="dialogNotes"
      title="PO Comment"
      min-height="75%"
      fullscreen
    >
      <po-comment
        :key="selected.id"
        :data="dataid"
        :table-only="tableOnly"
      ></po-comment>
    </v-action-dialog>
    <v-action-dialog
      v-model="dialogNotesApprovalRevision"
      title="Approval Revision Note"
      min-height="75%"
      @save="approvalRevision"
    >
      <v-textarea label="Note" v-model="approval_revision_note"></v-textarea>
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
              <th
                style="padding: 5px; width: 150px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Charged To
              </th>
              <!-- <th style="padding: 5px; width: 150px">Project (Budget)</th> -->
              <th
                style="padding: 5px; width: 150px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Item No
              </th>
              <th
                style="padding: 5px; width: 150px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Part
              </th>
              <th
                style="padding: 5px; width: 150px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Subledger
              </th>
              <th
                style="padding: 5px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                QTY
              </th>
              <th
                style="padding: 5px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Price
              </th>
              <th
                style="padding: 5px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                SubTotal
              </th>
              <!-- <th style="padding: 5px">Discount</th>
              <th style="padding: 5px">Charge 2</th>
              <th style="padding: 5px">Charge 1</th> -->
              <th
                style="padding: 5px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Currency
              </th>
              <th
                style="padding: 5px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Grand Total
              </th>
              <th
                style="padding: 5px"
                v-if="showOperationalRemainingBreakdown"
                colspan="2"
              >
                Remaining Budget
              </th>
              <th
                style="padding: 5px"
                v-else
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Remaining Budget
              </th>
              <th
                style="padding: 5px"
                :rowspan="showOperationalRemainingBreakdown ? 2 : 1"
              >
                Force Reason Input Minus Budget
              </th>
            </tr>
            <tr v-if="showOperationalRemainingBreakdown">
              <th style="padding: 5px">Sub Operational</th>
              <th style="padding: 5px">Department</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="remainingBudgetLoading">
              <td
                :colspan="showOperationalRemainingBreakdown ? 12 : 11"
                style="text-align: center; padding: 5px"
              >
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
              <td
                style="padding: 5px"
                :colspan="showOperationalRemainingBreakdown ? 12 : 11"
              >
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
              <td style="padding: 5px">{{ item.item_no }}</td>
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
              <!-- <td
                style="padding: 5px"
                :style="{ color: Number(item.discount || 0) > 0 ? '#ff5252' : '' }"
              >
                {{ Number(item.discount || 0).format(2, 3) }}
              </td>
              <td style="padding: 5px">
                {{ Number(item.charge2 || 0).format(2, 3) }}
              </td>
              <td style="padding: 5px">
                Rp. {{ Number(item.charge1 || 0).format(2, 3) }} 
              </td>-->
              <td style="padding: 5px">
                {{ item.currency || "-" }}
              </td>
              <td style="padding: 5px">
                {{ "Rp. " + Number(item.grand_total_idr || 0).format(2, 3) }}
              </td>
              <td
                style="padding: 5px"
                v-if="item.show_remaining"
                :rowspan="item.remaining_rowspan"
                :colspan="
                  showOperationalRemainingBreakdown && !item.is_operational
                    ? 2
                    : 1
                "
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
                v-if="
                  showOperationalRemainingBreakdown &&
                  item.is_operational &&
                  item.show_remaining
                "
                :rowspan="item.remaining_rowspan"
              >
                <div v-if="remainingBudgetCalcLoading">
                  Calculate Remaining Budget...
                </div>
                <div
                  v-if="
                    !remainingBudgetCalcLoading &&
                    item.remaining_bugdet_total_department !== undefined &&
                    item.remaining_bugdet_total_department !== null
                  "
                  :style="{
                    color:
                      Number(item.remaining_bugdet_total_department) < 0
                        ? '#ff5252'
                        : '#000',
                  }"
                >
                  {{
                    "Rp. " +
                    Number(item.remaining_bugdet_total_department).format(2, 3)
                  }}
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

<style scoped>
.v-data-table__wrapper > table > tbody > tr > td {
  font-size: 0.775rem;
}

.po-mobile-cards {
  flex: 1;
  min-height: 0;
  padding: 10px;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.po-mobile-empty {
  padding: 14px;
  text-align: center;
  color: #666;
}

.po-mobile-card {
  margin-bottom: 10px;
  padding: 10px;
  border-radius: 10px;
}

.po-mobile-card--selected {
  border-color: #1976d2 !important;
  box-shadow: 0 0 0 1px #1976d2 inset;
}

.po-mobile-card__head {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.po-mobile-card__po {
  font-weight: 700;
}

.po-mobile-card__title {
  font-size: 0.82rem;
  color: #333;
}

.po-mobile-card__grid {
  margin-top: 10px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 4px;
  font-size: 0.82rem;
}

.po-mobile-card__actions {
  margin-top: 10px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
</style>

<style>
.po-dashboard-page.is-mobile-view .po-dashboard-template .table-container,
.po-dashboard-page.is-mobile-view .po-dashboard-template .template-table,
.po-dashboard-page.is-mobile-view .po-dashboard-template .v-data-footer,
.po-dashboard-page.is-mobile-view .po-dashboard-template .v-data-table,
.po-dashboard-page.is-mobile-view .po-dashboard-template .v-data-table__wrapper {
  display: none !important;
}

.po-dashboard-page.is-mobile-view {
  overflow-y: auto !important;
  -webkit-overflow-scrolling: touch;
}

.po-dashboard-page.is-mobile-view .po-dashboard-template {
  min-height: 0;
  height: auto !important;
}

.po-dashboard-page.is-mobile-view .po-dashboard-template .v-card {
  min-height: 0;
}
</style>

<script>
module.exports = {
  name: "",
  props: {
    value: undefined,
    data: {
      type: Object,
    },
    tableOnly: {
      type: Boolean,
      default: false,
    },
    nointeraction: {
      type: Boolean,
      default: false,
    },
    onlyApproveRevision: {
      type: Boolean,
      default: false,
    },
    approval1: {
      type: Boolean,
      default: false,
    },
    btnAskDraft: {
      type: Boolean,
      default: false,
    },
    btnApproveDraft: {
      type: Boolean,
      default: false,
    },
    itemsOptions: {
      type: Object,
      default: {
        filter: {
          // approved: (check_user(['administrator', 'rdarmagiri']) ? '1,3' : 1),
        },
        filterType: {
          // approved: 'in'
        },
      },
    },
  },
  components: {
    "purchase-item": "url:ui/bom/purchasing/purchaseorderitem.vue",
    "purchase-item-group": "url:ui/bom/purchasing/purchaseorderitemgroup.vue",
    "po-rev-history": "url:ui/bom/purchasing/purchaseorder_revisi.vue",
    "po-comment": "url:ui/bom/purchasing/comment.vue",
  },
  data: function () {
    return {
      //name: 'Purchase Order Approval',
      processData: {},
      itemKey: "id",
      po_comment: "",
      approval_note: "",
      reject_note: "",
      approval_revision_note: "",
      ask_draft_note: "",
      revData: {},
      dialogNotes: false,
      dialogNotesApprovalRevision: false,
      loading: false,
      url: "bom/purchaseorder",
      headers: [
        {
          text: "id",
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
          text: "PO Details",
          value: "po_details",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "200px",
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
          text: "PO No",
          value: "po_no",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
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
          page: "0",
          limit: "10",
        },
        {
          text: "PO Title",
          value: "title",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "varchar",
          disabled: false,
          visible: false,
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
          page: "0",
          limit: "10",
        },
        {
          text: "Status",
          value: "approved",
          align: "center",
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
          data_value: [
            { text: "Asking Approval 2", value: 2 },
            { text: "Asking Cancellation 1", value: -2 },
            { text: "Asking Cancellation 2", value: -3 },
          ],
        },
        {
          text: "PO Date",
          value: "po_date",
          align: "start",
          sortable: false,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "date",
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
          text: "Department",
          value: "dept_id",
          align: "start",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "list",
          disabled: false,
          data_value: [],
          visible: false,
          required: true,
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "bom/department",
          searchby: ["id"],
          formatter: ["id", "dept_name"],
          pk: "id",
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "dept_name",
        },
        {
          text: "Order Type",
          value: "order_type",
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
          filter: false,
          groupable: false,
          default: "Lokal",
          data_value: ["Lokal", "Import"],
        },
        {
          text: "Supplier",
          value: "supplier",
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
          data_value: [],
          filter: false,
          groupable: false,
          url: App.url + "bom/supplier",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "supplier_name",
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
          type: "text",
          disabled: false,
          visible: false,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Ref. Offer No",
          value: "ref_offer_no",
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
          form: true,
          filter: true,
          groupable: false,
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
          clearable: "true",
          type: "list",
          disabled: false,
          visible: false,
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
          text: "PR No",
          value: "pr_id",
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
          clearable: true,
          required: false,
          clearable: "true",
          form: true,
          filter: true,
          groupable: false,
          url: App.url + "bom/pr",
          searchby: ["pr_no", "selectdesc"],
          formatter: ["pr_id", "selectdesc"], //['pr_no', 'pr_subject']],
          options: {
            filter: {
              peminta_setuju: null,
              penyetuju_setuju: null,
            },
            filterType: {
              peminta_setuju: "notnull",
              penyetuju_setuju: "notnull",
            },
            filterCondition: {
              pr_no: "or",
              pr_subject: "or",
            },
          },
          paging: true,
          page: "1",
          limit: "10",
        },
        {
          text: "Supplier",
          value: "supplier_id",
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
          data_value: [],
          filter: true,
          groupable: false,
          url: App.url + "bom/supplier",
          searchby: ["name"],
          formatter: ["id", "name"],
          options: {
            filter: {},
            filterType: {},
            filterCondition: {},
          },
          paging: true,
          page: "1",
          limit: "10",
          alias: "supplier_name",
        },
        {
          text: "Promised Delivery Date",
          value: "promised_delivery_date",
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
          form: true,
          filter: false,
          groupable: false,
          default: "IDR",
          data_value: ["IDR", "CNY", "EUR", "USD"],
          input: function (data) {
            var self = App.page();
            if (data.data.trim().toLowerCase() != "idr") {
              self.headersObj.exchange_rate.required = true;
              self.headersObj.rate_date.required = true;
            } else {
              self.headersObj.exchange_rate.required = false;
              self.headersObj.rate_date.required = false;
            }

            self.headers = App.updateObject(self.headers);
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
          text: "PO Title",
          value: "title",
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
          filter: true,
          groupable: false,
        },

        {
          text: "Charge 1",
          value: "charge1",
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
          form: true,
          filter: false,
          groupable: false,
        },
        {
          text: "Charge 1 Desc",
          value: "charge1_desc",
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
        },
        {
          text: "Charge 2",
          value: "charge2",
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
          form: true,
          filter: false,
          groupable: false,
        },
        {
          text: "Charge 2 Desc",
          value: "charge2_desc",
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
        },
        {
          text: "Price",
          value: "grand_total_price",
          align: "center",
          sortable: true,
          filterable: false,
          divider: false,
          class: "",
          width: "auto",
          type: "float",
          disabled: false,
          visible: true,
          required: false,
          form: false,
          filter: false,
          groupable: false,
        },
        {
          text: "Grand Total Price",
          value: "grand_total",
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
        },
        {
          text: "Payment",
          value: "payment",
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
          text: "Other Charge",
          value: "other_charge",
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
          form: true,
          filter: false,
          precision: 2,
          groupable: false,
        },
        {
          text: "Discount",
          value: "discount",
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
          form: true,
          filter: false,
          precision: 2,
          groupable: false,
        },
        {
          text: "Payment Term",
          value: "payment_term",
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
        },
        {
          text: "Despatch",
          value: "despatch",
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
        },
        {
          text: "Shipping Address",
          value: "shipping_address",
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
        },
        {
          text: "Miscellaneous",
          value: "miscellaneous",
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
        },
        {
          text: "Documents",
          value: "final_quote_url",
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
          text: "Currency",
          value: "currency",
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
          filter: false,
          groupable: false,
          default: "IDR",
          data_value: ["IDR", "CNY", "EUR", "USD"],
          input: function (data) {
            var self = App.page();
            if (data.data.trim().toLowerCase() != "idr") {
              self.headersObj.exchange_rate.required = true;
              self.headersObj.rate_date.required = true;
            } else {
              self.headersObj.exchange_rate.required = false;
              self.headersObj.exchange_rate.data = 0;
              self.headersObj.rate_date.required = false;
            }

            if (data.data.trim().toUpperCase() != "IDR") {
              axios
                .get(App.url + "bom/payment/exchange", {
                  params: {
                    currency: data.data.trim().toUpperCase(),
                  },
                })
                .then(function (response) {
                  var data = response.data;
                  self.headersObj.exchange_rate.data =
                    data.match(/\d+(.\d+)/)[0];
                  self.headers = App.updateObject(self.headers);
                })
                .catch(function (error) {});
            } else {
              self.headers = App.updateObject(self.headers);
            }
          },
        },
        {
          text: "Currency",
          value: "currency_desc",
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
          data_value: ["IDR", "CNY", "EUR", "USD"],
          input: function (data) {
            var self = App.page();
            if (data.data.trim().toLowerCase() != "idr") {
              self.headersObj.exchange_rate.required = true;
              self.headersObj.rate_date.required = true;
            } else {
              self.headersObj.exchange_rate.required = false;
              self.headersObj.rate_date.required = false;
            }

            self.headers = App.updateObject(self.headers);
          },
        },
        {
          text: "PO Charge",
          value: "charge1",
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
        },
        {
          text: "Final Quote URL",
          value: "final_quote_url",
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
          text: "Signed PO URL",
          value: "signed_po_url",
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
          text: "Signed PR URL",
          value: "signed_pr_url",
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
          text: "Shipping",
          value: "shipping",
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
          text: "Internal Reference",
          value: "internal_reference",
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
          text: "Last Revised Comment",
          value: "last_revised_comment",
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
        },
        {
          text: "Notes Approval",
          value: "all_notes",
          sortable: false,
          type: "text",
          form: false,
          visible: false,
        },
        {
          text: "Reject Note From Approval 1",
          value: "reject_note1",
          sortable: false,
          type: "text",
          form: false,
          visible: false,
        },
        {
          text: "Reject Note From Approval 2",
          value: "reject_note2",
          sortable: false,
          type: "text",
          form: false,
          visible: false,
        },
        {
          text: "Approval History",
          value: "approval_history",
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
          text: "Notes",
          value: "note",
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
          text: "",
          value: "data-table-expand",
        },
      ],
      dialogReject: false,
      dialogAskDraft: false,
      dialogNote: false,
      dialogComment: false,
      dialogItem: false,
      dialogItemGroup: false,
      dialogReport: false,
      dialogRevisiHistory: false,
      dialogRemainingBudget: false,
      remainingBudgetItems: [],
      remainingBudgetLoading: false,
      remainingBudgetCalcLoading: false,
      showOperationalRemainingBreakdown: false,
      remainingBudgetPoId: null,
      selected: false,
      dataid: {},
      dialogCharge: false,
      total_item_price: 0,
      grand_total_price: 0,
    };
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
    allowRevisiHistory: function () {
      var self = this;

      if (!self.selected) return false;
      if (self.selected.po_no.match(/-rev/i) != null) return true;
      return false;
    },
    allowApproveDraft: function () {
      var self = this;
      if (!self.selected) return false;
      if (self.selected.approved == 5) return true;
      false;
    },
    isMobileView: function () {
      return !!(
        this.$vuetify &&
        this.$vuetify.breakpoint &&
        this.$vuetify.breakpoint.smAndDown
      );
    },
  },
  watch: {
    dialogComment: function () {
      this.po_comment = "";
    },
  },
  methods: {
    isSelectedRow: function (item) {
      return !!(this.selected && item && this.selected.id === item.id);
    },
    selectMobileRow: function (item) {
      this.onSelectedRow(item);
    },
    openItemsByRow: function (item) {
      this.onSelectedRow(item);
      this.dialogItemGroup = true;
    },
    openNotesByRow: function (item) {
      this.onSelectedRow(item);
      this.dialogNotes = true;
    },
    openApproveByRow: function (item) {
      this.onSelectedRow(item);
      this.openApprove();
    },
    openCommentByRow: function (item) {
      this.onSelectedRow(item);
      this.openComment();
    },
    openRejectByRow: function (item) {
      this.onSelectedRow(item);
      this.openReject();
    },
    statusChipColor: function (approved) {
      if (approved == 0) return "#f5e699";
      if (approved == 4) return "#f88686";
      if (approved == 1 || approved == 2 || approved == -2 || approved == -3)
        return "#ffcc99";
      return "#e0e0e0";
    },
    convertLinks: function (text) {
      if (!text) return "";

      const urlRegex = /(https?:\/\/[^\s]+)/g;

      return text.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" class="text-blue-500 underline">${url}</a>`;
      });
    },
    modifDate: function (date, item) {
      date = new Date(date);
      //if([3,8,59,11].includes(item.supplier_id) && Number(date.getDate())<15)
      //date.setDate(15)
      return date.formatDate("YYYY-MM-DD HH:mm:ss");
    },
    openReject: function () {
      this.reject_note = "";
      this.dialogReject = true;
    },
    openAskDraft: function () {
      this.draft_note = "";
      this.dialogAskDraft = true;
    },
    approvedStatus: function (f) {
      if (f == 0) {
        return "New";
      }
      if (f == 1) {
        return "Asking for Approval 1";
      }
      if (f == 2) {
        return "Asking for Approval 2";
      }
      if (f == 3) {
        return "Approval 2 Approved";
      }
      if (f == 3) {
        return "Approval 2 Approved";
      }
      if (f == 5) {
        return "Asking for Draft PO";
      }
      if (f == 6) {
        return "Approval 2 Approved Draft";
      }
      if (f == 4) {
        return "Approval 2 approved";
      }
      if (f == -1) {
        return "Rejected";
      }
      if (f == -2) {
        return "Asking for Cancelation 1";
      }
      if (f == -3) {
        return "Asking for Cancelation 2";
      }
      if (f == -4) {
        return "Canceled";
      }
    },
    openComment: function () {
      var self = this;
      self.po_comment = "";
      self.dialogComment = true;
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
          purchase_order_id: val.id,
          supplier_id: val.supplier_id,
          currency: val.currency,
          charge1: val.charge1,
          charge2: val.charge2,
        };
        self.dataid = {
          purchase_order_id: val.id,
        };
        self.revData = {
          filter: {
            purchase_order_id: val.id,
            flag: 1,
          },
        };
      }
    },
    onSelectedRowAll: function (val) {
      var self = this;
      self.selectedAll = val;
    },
    openApprove: function () {
      var self = this;
      this.approval_note = "";

      if (self.selected.approved == 1 || self.selected.approved == -2) {
        this.dialogNote = true;
      } else {
        this.approve();
      }
    },
    approve: async function () {
      var self = this;
      if (self.selected.approved == -2 || self.selected.approved == -3)
        var c = confirm("Are you sure want to Approve Cancel this PO?");
      else var c = confirm("Are you sure want to Approve this PO?");
      if (c) {
        self.loading = true;
        if (self.selected.approved > 0)
          var approved = self.selected.approved == 1 ? 2 : 3;
        if (approved === 3) {
          try {
            // Fetch PO items from the API
            const poNo = self.selected.po_no;
            const filter = encodeURIComponent(
              JSON.stringify({ po_no: [poNo] }),
            );
            const url = `https://main.buanamultiteknik.com/api/bom/pobudget/poitem?filter=${filter}&limit=-1`;
            const response = await axios.get(url);
            const header = `https://main.buanamultiteknik.com/api/bom/monitoring?filter=${filter}`;
            const headerResponse = await axios.get(header);

            if (
              headerResponse.data &&
              Array.isArray(headerResponse.data.data)
            ) {
              // Prepare data as parent-child structure
              const parent = headerResponse.data.data[0];
              const children = response.data.data;

              // Combine parent and children into one object
              const postData = {
                header: parent,
                items: children,
              };

              // Post the combined data to your API

              // const postUrl = 'https://panel.buanamultiteknik.com/api/transaction/purchase-order/store';
              // await axios.post(postUrl, postData);

              //return;
            }
          } catch (err) {
            console.error("Error posting PO items:", err);
            //return;
          }
        } else if (self.selected.approved < 0)
          var approved = self.selected.approved == -2 ? -3 : -4;
        var params = {
          po_no: self.selected.po_no,
          approved: approved,
          pk: self.selected.id,
        };
        if (self.selected.approved == 1 || self.selected.approved == -2) {
          if (self.approval_note.trim() == "") {
            App.errorMsg("Note cannot be empty!");
            return false;
          }
          params.approval_note = self.approval_note;
        }
        var r = await axios.put(App.url + "bom/purchaseorder", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.loading = false;
      self.dialogNote = false;
    },
    comment: async function () {
      var self = this;

      self.loading = true;
      var r = await axios.post(App.url + "bom/comment", {
        notes: self.po_comment,
        purchase_order_id: self.selected.id,
      });
      if (!r.data.status) App.errorMsg(r.data);
      else {
        self.$refs.template.getItems();
        App.successMsg();
        self.dialogComment = false;
        self.$refs.template.getItems();
      }

      self.loading = false;
    },
    askDraft: async function () {
      var self = this;
      var c = confirm("Are you sure want to Ask for Draft PO?");
      if (c) {
        self.loading = true;
        var params = {
          approved: 5,
          pk: self.selected.id,
        };
        params.ask_draft_note = self.ask_draft_note;
        var r = await axios.put(App.url + "bom/purchaseorder", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.loading = false;
      self.dialogAskDraft = false;
    },
    approveDraft: async function () {
      var self = this;
      var c = confirm("Are you sure want to Approve for Draft PO?");
      if (c) {
        self.loading = true;
        var params = {
          approved: 6,
          pk: self.selected.id,
        };
        var r = await axios.put(App.url + "bom/purchaseorder", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.loading = false;
    },
    reject: async function () {
      var self = this;
      var c = confirm("Are you sure want to reject this PO?");
      if (c) {
        self.loading = true;
        var params = {
          approved: -1,
          pk: self.selected.id,
        };
        if (self.selected.approved == 2 || self.selected.approved == -3)
          params.reject_note2 = self.reject_note;
        if (self.selected.approved == 1 || self.selected.approved == -2)
          params.reject_note1 = self.reject_note;
        var r = await axios.put(App.url + "bom/purchaseorder", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.loading = false;
      self.dialogReject = false;
    },
    approvalRevision: async function () {
      var self = this;
      var c = confirm("Are you sure want to approve revision this PO?");
      if (c) {
        self.loading = true;
        var params = {
          revision_approval: 2,
          pk: self.selected.id,
        };
        params.approval_revision_note = self.approval_revision_note;
        var r = await axios.put(App.url + "bom/purchaseorder", params);
        if (!r.data.status) App.errorMsg(r.data);
        else {
          self.$refs.template.getItems();
          App.successMsg();
        }
      }
      self.loading = false;
      self.dialogReject = false;
    },
    openReport: function () {
      var self = this;
      var name = self.selected.po_no.replace(/\//g, "_").replace(/\-/g, "_");
      var randomid = randomId();
      //dialogReport=true
      window.open(
        "https://main.buanamultiteknik.com/api/data/reportpo?id=" +
          self.selected.id +
          "&filename=" +
          name +
          "&idx=" +
          randomid,
      );
    },
    openReport2: function () {
      var self = this;
      //dialogReport=true
      var name = self.selected.po_no.replace(/\//g, "_").replace(/\-/g, "_");
      var randomid = randomId();
      window.open(
        "https://main.buanamultiteknik.com/api/data/reportpo2?id=" +
          self.selected.id +
          "&filename=" +
          name +
          "&idx=" +
          randomid,
      );

      //window.open('https://main.buanamultiteknik.com/api/report/po/index?po_id='+self.selected.id)
    },
    fetchRemainingBudgetItems: async function (poId) {
      var self = this;
      self.remainingBudgetLoading = true;
      self.remainingBudgetCalcLoading = false;
      try {
        var r = await axios.get(
          App.url + "bom/purchase_order_item_group/subledger",
          {
            params: {
              purchase_order_id: poId,
            },
          },
        );
        if (!r.data.status) {
          App.errorMsg(r.data);
          self.remainingBudgetItems = [];
          self.remainingBudgetLoading = false;
          return;
        }
        var items = r.data.data || [];
        var groupMap = {};
        var totalSubtotal = 0;
        var charge1Total = 0;
        var charge2Total = 0;
        var discountTotal = 0;
        items.map((item) => {
          var qty = Number(item.qty || 0);
          var price = Number(item.price || 0);
          var subtotal = qty * price;
          item.subtotal = subtotal;
          totalSubtotal += subtotal;
          var key =
            (item.project_id || "") + "|" + (item.project_budget_id || "");
          if (!groupMap[key]) {
            groupMap[key] = { total: 0, count: 0 };
          }
          groupMap[key].total += subtotal;
          groupMap[key].count += 1;
        });
        if (items.length > 0) {
          charge1Total = Number(items[0].charge1 || 0);
          charge2Total = Number(items[0].charge2 || 0);
          discountTotal = Number(items[0].discount || 0);
        }
        var seen = {};
        self.remainingBudgetItems = items.map((item) => {
          var key =
            (item.project_id || "") + "|" + (item.project_budget_id || "");
          var showGroup = !seen[key];
          if (showGroup) seen[key] = true;
          var projectLabel = "";
          if (item.project_no) {
            projectLabel += item.project_no;
          }
          if (item.project_name) {
            projectLabel += (projectLabel ? " - " : "") + item.project_name;
          }
          if (!projectLabel) projectLabel = "-";
          if (item.budget_name) projectLabel += " (" + item.budget_name + ")";
          var subtotal = Number(item.subtotal || 0);
          var pct = totalSubtotal > 0 ? subtotal / totalSubtotal : 0;
          var allocatedCharge1 = charge1Total * pct;
          var allocatedCharge2 = charge2Total * pct;
          var allocatedDiscount = discountTotal * pct;
          var exchangeRate = Number(item.exchange_rate || 0);
          if (!exchangeRate || exchangeRate <= 0) exchangeRate = 1;
          var subtotalIdr = subtotal * exchangeRate;
          var grandTotalIdr =
            subtotalIdr +
            allocatedCharge1 +
            allocatedCharge2 -
            allocatedDiscount;
          return Object.assign({}, item, {
            project_budget_label: projectLabel,
            show_project_budget: showGroup,
            project_budget_rowspan: groupMap[key].count,
            show_remaining: showGroup,
            remaining_rowspan: groupMap[key].count,
            charge1: allocatedCharge1,
            charge2: allocatedCharge2,
            discount: allocatedDiscount,
            grand_total_idr: grandTotalIdr,
          });
        });
        if (self.remainingBudgetItems.length > 0) {
          await self.calculateRemainingBudget();
        }
      } catch (e) {
        App.errorMsg(e);
        self.remainingBudgetItems = [];
      }
      self.remainingBudgetLoading = false;
    },
    calculateRemainingBudget: async function () {
      var self = this;
      if (self.remainingBudgetItems.length === 0) {
        self.showOperationalRemainingBreakdown = false;
        return;
      }
      var groupedIds = {};
      self.remainingBudgetItems.map((item) => {
        var price = Number(item.price || 0);
        if (!groupedIds[price]) groupedIds[price] = [];
        groupedIds[price].push(item.subledger_id);
      });
      var subledgerIds = self.remainingBudgetItems
        .map((item) => item.subledger_id)
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
      });
      self.remainingBudgetCalcLoading = true;
      try {
        var remainingMap = {};
        var remainingTotalDepartmentMap = {};
        var warningMap = {};
        var operationalLabelMap = {};
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
                console.log("Remaining budget API result:", budgetRes.data);
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
                  warningMap[d.subledger_id] = !!d.is_warning;
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
                console.log("Remaining budget API result:", budgetRes.data);
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
                  warningMap[d.subledger_id] = !!d.is_warning;
                  var department = d.department_name || "-";
                  var typeOperational = d.type_operational_name || "-";
                  var subTypeOperational = d.sub_type_operational_name || "-";
                  operationalLabelMap[d.subledger_id] =
                    department +
                    " ➝ " +
                    typeOperational +
                    " ➝ " +
                    subTypeOperational;
                });
              }),
          );
        });
        await Promise.all(requests);
        self.remainingBudgetItems = self.remainingBudgetItems.map((item) => {
          var projectType = projectTypeMap[item.subledger_id];
          var label = operationalLabelMap[item.subledger_id];
          var isOperational =
            String(projectType || "").toLowerCase() === "operational";
          var projectBudgetLabel =
            isOperational && label ? label : item.project_budget_label;
          return Object.assign({}, item, {
            remaining_budget: remainingMap[item.subledger_id],
            remaining_bugdet_total_department:
              remainingTotalDepartmentMap[item.subledger_id],
            is_operational: isOperational,
            is_warning:
              warningMap[item.subledger_id] !== undefined
                ? warningMap[item.subledger_id]
                : item.is_warning,
            project_budget_label: projectBudgetLabel,
          });
        });
        // For operational items, re-group consecutive items that share the same
        // Charged To label so the cell merges correctly (rowspan by label).
        var opItems = self.remainingBudgetItems.slice();
        var i = 0;
        while (i < opItems.length) {
          if (!opItems[i].is_operational) { i++; continue; }
          var label = opItems[i].project_budget_label;
          var j = i + 1;
          while (j < opItems.length && opItems[j].is_operational && opItems[j].project_budget_label === label) j++;
          var count = j - i;
          opItems[i] = Object.assign({}, opItems[i], {
            show_project_budget: true,
            project_budget_rowspan: count,
            show_remaining: true,
            remaining_rowspan: count,
          });
          for (var k = i + 1; k < j; k++) {
            opItems[k] = Object.assign({}, opItems[k], {
              show_project_budget: false,
              project_budget_rowspan: 1,
              show_remaining: false,
              remaining_rowspan: 1,
            });
          }
          i = j;
        }
        self.remainingBudgetItems = opItems;
        self.showOperationalRemainingBreakdown = self.remainingBudgetItems.some(
          (item) => !!item.is_operational,
        );
      } catch (e) {
        console.log("Remaining budget API error:", e);
        self.showOperationalRemainingBreakdown = false;
      }
      self.remainingBudgetCalcLoading = false;
    },
    getProjectTypeMap: async function (subledgerIds) {
      if (!Array.isArray(subledgerIds) || subledgerIds.length === 0) {
        return {};
      }
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
          if (d && d.id !== undefined) {
            map[d.id] = d.project_type;
          }
        });
        return map;
      } catch (e) {
        console.log("Remaining budget project_type fetch error:", e);
        return {};
      }
    },
    openRemainingBudgetDialog: function (item) {
      var self = this;
      self.dialogRemainingBudget = true;
      self.remainingBudgetItems = [];
      self.showOperationalRemainingBreakdown = false;
      self.remainingBudgetPoId = item.id;
      axios
        .get(
          "https://panel.buanamultiteknik.com/api/transaction/purchase-order/get-remaining-budget/" +
            item.id,
        )
        .then((res) => {
          console.log("Remaining budget API result:", res.data);
        })
        .catch((err) => {
          console.error("Remaining budget API error:", err);
        });
      self.fetchRemainingBudgetItems(item.id);
    },
    onVisible: function (isVisible, e) {
      var self = this;
      var isInViewport = !!isVisible;
      var isInDom = !!e.target.isConnected;
      if (isInViewport) {
        //self.headersObj.approval_note.visible = self.approval1 == false
      }
    },
    revisionHistory: function () {
      var self = this;
      self.dialogRevisiHistory = true;
    },
  },
  mounted: function () {
    console.log("onlyApproveRevision:", this.onlyApproveRevision);
  },
};
</script>
