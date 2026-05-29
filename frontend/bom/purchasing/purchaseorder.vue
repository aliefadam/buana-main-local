<template>
    <v-container
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template export-excel :show-expand="true" :single-expand="true" :hide-delete-button="hideDeleteButton"
            :hide-add-button="hideAddButton" :items-options="itemsOptions" @open-edit="onOpenEdit"
            @open-add="onOpenEdit(true)" :disable-edit-button="disableEdit" :disable-delete-button="disableDelete"
            v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" :table-only="tableOnly">
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
            <template v-slot:menu-after-filter>
                <!-- <v-btn color="primary" outlined small @click="dialogItem = true" :disabled="!selected">
                    <v-icon small left>mdi-plus</v-icon>Add Item
                </v-btn> -->
                <v-btn color="primary" outlined small @click="dialogItemGroup = true" :disabled="allowItems">
                    <!-- <v-icon small left>mdi-plus</v-icon> -->Items
                </v-btn>

                <v-btn small color="primary" outlined :disabled="selected == false"
                    @click="dialogNotes = true">Notes</v-btn>

                <!-- <div>allowRevisi: {{ allowRevisi }}</div> -->
                <v-btn color="primary" outlined small @click="openDialogNote" :disabled="allowRevisi()"
                    v-if="!tableOnly && history">
                    Revise
                </v-btn>

                <v-btn color="primary" outlined small @click="openDialogNoteApprovalRevision"
                    :disabled="allowAskApprovalRevision()" v-if="!tableOnly && history">
                    Ask Approval
                </v-btn>

                <v-btn color="primary" outlined small @click="revisionHistory" :disabled="!allowRevisiHistory">
                    Revise History
                </v-btn>
                <v-btn color="orange" outlined small @click="createFakeData" :disabled="selected == false">
                    Create Fake Data
                </v-btn>
                <v-btn color="red" outlined small @click="dialogAskCancelNote = true" :disabled="allowRevisi()"
                    v-if="!tableOnly && history">
                    Cancel
                </v-btn>
                <!-- <v-btn color="primary" outlined small @click="openReport" :disabled="allowPrint">
                    <v-icon small left>mdi-printer</v-icon><template v-if="!hideAddButton">Preview</template><template
    v-else>Print</template>
</v-btn> -->
                <v-btn color="primary" outlined small @click="openReport2" :disabled="allowPrint">
                    <v-icon small left>mdi-printer</v-icon><template v-if="!hideAddButton">Preview</template><template
                        v-else>Print</template>
                </v-btn>
                <v-btn color="primary" outlined small @click="openRemainingBudgetDialog(selected)"
                    :disabled="selected == false">
                    Show Remaining Budget
                </v-btn>
                <v-btn color="primary" outlined small @click="openReportNoPrice" :disabled="allowPrint"
                    v-if="hideAddButton">
                    <v-icon small left>mdi-printer</v-icon>Print Without price
                </v-btn>
                <v-btn color="primary" v-if="!hideApproval" outlined small
                    @click="action = 'askApproval'; dialogAskApprovalNote = true" :disabled="allowAskApproval">
                    {{ txtApproval }}
                </v-btn>
                <!--<v-btn v-if="history" color="primary" outlined small @click="dialogEmail=true" :disabled="!selected">Send Email</v-btn>-->
                <!--<v-btn v-if="history" color="primary" outlined small @click="sendEmail2()" :disabled="!selected">Send Email (test)</v-btn>-->
                <!-- <v-btn small color="primary" outlined v-if="hideAddButton" @click="dialogComplete = true">Complete PO</v-btn> -->
            </template>
            <template v-slot:item.po_details="props">
                <b>PO No:</b> {{ props.item.po_no }}<br />
                <b>PO Date:</b> {{ props.item.po_date }}<br />
                <b>Department:</b> {{ props.item.dept_name }}<br />
                <b>Order Type:</b> {{ props.item.order_type }}<br />
                <b>Created By:</b> {{ props.item.created_by_name }}<br />
                <b>Created Date:</b> {{ modifDate(props.item.created_date, props.item) }}<br />
                <b style="color: red; font-weight: bold" v-if="props.item.complete == 1">Completed</b>
            </template>
            <template v-slot:item.shipping="props">
                <div style="">
                    <b>Despatch:</b> {{ props.item.despatch }}<br />
                    <b>Shipping Address:</b> <v-truncate :text="props.item.shipping_address"></v-truncate>
                    <b>Miscellaneous Term:</b> {{ props.item.miscellaneous }}
                    <b>Notes:</b> <v-truncate :text="props.item.note"></v-truncate>
                </div>
            </template>
            <template v-slot:item.payment="props">
                <div style="white-space: nowrap;">
                    <b>Exchange Rate:</b> {{ Number(props.item.exchange_rate).format(2, 3) }}<br />
                    <b>Rate Date:</b> {{ props.item.rate_date }}<br />
                    <b>Payment Term:</b> <v-truncate :text="props.item.payment_term"></v-truncate>
                </div>
            </template>
            <template v-slot:item.internal_reference="props">
                <div style="">
                    <b>RFQ No:</b><br><a :href="'#/rfq/rfq/' + props.item.rfq_id" v-if="props.item.rfq_no"
                        target="_blank">{{ props.item.rfq_no }}<br></a><span v-else>{{ props.item.rfq_no }}</span>
                    <b>PR No:</b> <a :href="props.item.signed_pr_url" v-if="props.item.signed_pr_url"
                        target="_blank"><br />{{ props.item.pr_no }}</a><span v-else>{{ props.item.pr_no }}</span><br />
                    <b>Offer No:</b> <br />{{ props.item.ref_offer_no }}<br />
                    <!--<b>Ref. Internal BMT:</b> <br />{{props.item.ref_internal_bmt}}<br/><br />-->
                    <b>Final Quote URL:</b> <a :href="props.item.final_quote_url" v-if="props.item.final_quote_url"
                        target="_blank">Open Link</a><span v-else>-</span><br />
                    <span v-if="history"><b>Signed PO URL:</b> <a :href="props.item.signed_po_url"
                            v-if="props.item.signed_po_url" target="_blank">Open Link</a><span v-else>-</span></span>
                </div>
            </template>
            <!--        <template v-slot:item.internal_reference="props">-->
            <!--<div style="white-space: nowrap;">-->
            <!--	<b>Ref. Offer No:</b> <br />{{props.item.ref_offer_no}}-->
            <!--	<b>Ref. Internal BMT:</b> <br />{{props.item.ref_internal_bmt}}-->
            <!--</div>-->
            <!--        </template>-->
            <template v-slot:item.currency_desc="props">
                <b>Currency:</b> {{ props.item.currency }}<br />
                <b>Exchange Rate:</b> {{ Number(props.item.exchange_rate).format(2, 3) }}<br />
                <b>Rate Date:</b> {{ props.item.rate_date }}
            </template>
            <template v-slot:item.supplier="props">
                <b>Supplier:</b> {{ props.item.supplier_name }}<br />
                <b>Promised Delivery Date:</b> {{ props.item.promised_delivery_date }}<br />
                <b>Brand:</b> {{ props.item.brand || '-' }}<br />
                <b>Jenis Barang:</b> {{ props.item.jenis_barang || '-' }}
            </template>
            <template v-slot:item.final_quote_url="props">
                <b>Final Quote URL:</b> <a :href="props.item.final_quote_url" v-if="props.item.final_quote_url"
                    target="_blank">Open Link</a><span v-else>-</span><br />
                <b>Signed PO URL:</b> <a :href="props.item.signed_po_url" v-if="props.item.signed_po_url"
                    target="_blank">Open Link</a><span v-else>-</span><br />
                <b>Signed PR URL:</b> <a :href="props.item.signed_pr_url" v-if="props.item.signed_pr_url"
                    target="_blank">Open Link</a><span v-else>-</span>
            </template>
            <template v-slot:item.status="props">
                <div style="justify-content: center; display: flex;">
                    <v-alert dense color="#ffcc99"
                        v-if="props.item.approved == 1 || props.item.approved == 2 || props.item.approved == -2 || props.item.approved == -3"
                        style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
                        Pending
                    </v-alert>
                    <v-alert dense color="#f5e699" v-if="props.item.approved == 0"
                        style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
                        New
                    </v-alert>
                    <v-alert dense color="#f88686" v-if="props.item.approved == 4"
                        style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
                        Clarification
                    </v-alert>
                </div>
                <div>
                    <span style="text-align:center">
                        <br>
                        {{ approvedStatus(props.item.approved, props.item.new_po_no) }}<br>
                        <span v-if="props.item.approval_date">
                            <b>Approved</b><br />
                            <span>By:</span> {{ props.item.approved_by_name }}<br />
                            <span>Date:</span> {{ props.item.approval_date }}<br /><br />
                        </span>
                        <span v-else></span>
                    </span>
                </div>

            </template>
            <template v-slot:item.approved="props">
                {{ approvedStatus(props.item.approved, props.item.new_po_no) }}<br><br>
                <span v-if="props.item.approval_date">
                    <b>Approved</b><br />
                    <span>By:</span> {{ props.item.approved_by_name }}<br />
                    <span>Date:</span> {{ props.item.approval_date }}<br /><br />
                </span>
                <span v-else></span>
            </template>
            <template v-slot:item.charge1="props">
                <b>Charge 1:</b> {{ Number(props.item.charge1).format(2, 3) }}<br />
                <b>Charge 1 Desc:</b> <v-truncate :text=props.item.charge1_desc></v-truncate><br />
                <b>Charge 2:</b> {{ Number(props.item.charge2).format(2, 3) }}<br />
                <b>Charge 2 Desc:</b> <v-truncate :text=props.item.charge2_desc></v-truncate><br />
                <b>Discount:</b> {{ Number(props.item.discount).format(2, 3) }}<br />
                <b>Discount Desc:</b> <v-truncate :text=props.item.discount_desc></v-truncate>
            </template>
            <template v-slot:item.comment="props">
                {{ props.item.comment }}<br />
                Created by {{ props.item.comment_creator }}
            </template>
            <template v-slot:item.grand_total_price="props">
                <div style="white-space: nowrap;">
                    <b>Grand Total</b><br />
                    {{ props.item.currency }} {{ Number(props.item.grand_total).format(2, 3) }}<br /><br />
                    <b>Total Item</b> <br />
                    {{ props.item.currency }} {{ Number(props.item.grand_total_price).format(2, 3) }}
                </div>
            </template>
            <template v-slot:item.send_po="props">
                <span>
                    <b>Send Status:</b>
                    <p v-if="props.item.send_po == null">
                        <v-icon color="error">mdi-close-thick</v-icon>
                    </p>
                    <p v-else>
                        <v-icon color="success">mdi-check-bold</v-icon><br>
                        <b>Send by:</b>
                    <p>{{ props.item.sp_name }}</p>
                    <b>Send Date:</b>
                    <p>{{ props.item.send_po }}</p>
                    </p>
                </span>

                <span v-if="props.item.approved > 0">
                    <v-btn color="warning" outlined small @click="openSendForm"
                        :disabled="!allowSendPo(props.item)"><v-icon small left>mdi-send</v-icon>
                        PO Send
                    </v-btn>
                </span>
            </template>
            <template v-slot:item.approval_history="props">
                <!-- <b v-if="props.item.approved>0"> Created By: {{props.item.created_by_name}}</b><b v-else> Created By: {{props.item.created_by_name}}</b><br/>
                <b v-if="props.item.approved>0"> Created Date: {{props.item.ask_approval_date}}</b> <b v-else> Created Date: {{props.item.ask_canceled_date}}</b><br/>
                <span><b>Notes: </b><v-truncate v-if="props.item.approved>0" :text=props.item.askapproval_note></v-truncate><v-truncate v-else :text=props.item.ask_canceled_note></v-truncate><br/></span> -->

                <span v-if="props.item.ask_draft_by && props.item.approved > 0"><b>Asking for Approval Draft By:</b>
                    {{ props.item.ask_draft_by_name }}<br /></span>
                <span v-if="props.item.ask_draft_date && props.item.approved > 0"><b>Asking for Apporoval Date:</b>
                    {{ props.item.ask_draft_date }}<br /></span>
                <span v-if="props.item.ask_draft_note && props.item.approved > 0"><b>Asking for Approval Draft
                        Note:</b><br /><v-truncate :text=props.item.ask_draft_note></v-truncate><br /><br /></span>

                <span v-if="props.item.approval_draft_by_name && props.item.approved > 0"><b>Approved Draft By:</b>
                    {{ props.item.approval_draft_by_name }}<br /></span>
                <span v-if="props.item.approval_draft_date && props.item.approved > 0"><b>Approved Draft Date:</b>
                    {{ props.item.approval_draft_date }}</span>

                <span v-if="props.item.approved_by_name && props.item.approved > 0"><b>Approved 1 By:</b>
                    {{ props.item.approved_by_name }}<br /></span>
                <span v-if="props.item.approval_date && props.item.approved > 0"><b>Approved 1 Date:</b>
                    {{ props.item.approval_date }}<br /></span>
                <span v-if="props.item.approval_note && props.item.approved > 0"><b>Approval 1
                        Note:</b><br /><v-truncate :text=props.item.approval_note></v-truncate><br /><br /></span>

                <span v-if="props.item.approved2_by_name && props.item.approved > 0"><b>Approved 2 By:</b>
                    {{ props.item.approved2_by_name }}<br /></span>
                <span v-if="props.item.approval_2_date && props.item.approved > 0"><b>Approved 2 Date:</b>
                    {{ props.item.approval_2_date }}</span>

                <span v-if="props.item.canceled_by_name"><b>Approved Canceled 1 By:</b>
                    {{ props.item.canceled_by_name }}<br /></span>
                <span v-if="props.item.canceled_date"><b>Approved Canceled 1 Date:</b>
                    {{ props.item.canceled_date }}<br /></span>
                <span v-if="props.item.canceled_note"><b>Approved Canceled 1 Note:</b><br /><v-truncate
                        :text=props.item.canceled_note></v-truncate><br /><br /></span>

                <span v-if="props.item.canceled2_by_name"><b>Approved Canceled 2 By:</b>
                    {{ props.item.canceled_2_by_name }}<br /></span>
                <span v-if="props.item.canceled_2_date"><b>Approved Canceled 2 Date:</b>
                    {{ props.item.canceled_2_date }}</span>

                <span v-if="props.item.approved == -1"><b>Rejected By:</b>
                    {{ props.item.rejected_by_name }}<br /><br /></span>
                <span v-if="props.item.approved == -1"><b>Rejected Date:</b> {{ props.item.rejected_date }}</span>
            </template>
            <!-- <template v-slot:item.charge2="props">
				{{Number(props.item.charge2).format(3,3)}}
			</template> -->
            <template v-slot:expanded-item="props">
                <td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
                    <purchase-item-group :table-fixed-header="false" :item-height-as-min-height="true"
                        :table-fill="false" is-dashboard table-only :key="props.item[itemKey]" :sel="{
                            purchase_order_id: props.item.id,
                            supplier_id: props.item.supplier_id,
                            pr_id: props.item.pr_id,
                            pr_no: props.item.pr_no,
                            currency: props.item.currency,
                            charge1: props.item.charge1,
                            charge2: props.item.charge2
                        }" name="" :data="{
                            purchase_order_id: props.item[itemKey]
                        }"></purchase-item-group>
                </td>
            </template>
        </v-template>
        <!-- <v-action-dialog :actions="false" v-model="dialogItem" title="Purchase Order Item" min-height="75%" fullscreen>
            <purchase-item :key="selected.id" :sel="processData" name="" :data="dataid"></purchase-item>
        </v-action-dialog> -->
        <v-action-dialog :actions="false" v-model="dialogItemGroup" title="Purchase Order Item" min-height="75%"
            fullscreen>
            <purchase-item-group :key="selected.id" :sel="processData" name="" :data="dataid"
                :is-dashboard="!allowPrint2"></purchase-item-group>
        </v-action-dialog>
        <v-action-dialog :actions="false" v-model="dialogReport" title="Purchase Order Report" min-height="75%"
            fullscreen>
            <report :key="selected.id" :sel="processData" name="" :data="dataid"></report>
        </v-action-dialog>
        <!-- <v-action-dialog :actions="false" v-model="dialogComplete" title="Complete PO" min-height="75%" fullscreen>
            <complete-po></complete-po>
        </v-action-dialog> -->
        <v-action-dialog v-model="dialogEmail" title="Send Email to Supplier" fullscreen @save="sendEmail">
            <v-text-field filled hide-details dense label="To" v-model="to" @keyup.enter.native="addTo"></v-text-field>
            <div style="padding: 8px;">
                <v-chip v-for="(item, index) in toSelected" :key="index" close @click:close="removeTo(item)">
                    <strong>{{ item }}</strong>
                </v-chip>
            </div>
            <v-text-field filled hide-details dense label="CC" v-model="cc" @keyup.enter.native="addCC"></v-text-field>
            <div style="padding: 8px;">
                <v-chip v-for="(item, index) in ccSelected" :key="index" close @click:close="remove(item)">
                    <strong>{{ item }}</strong>
                </v-chip>
            </div>
            <!-- <v-select-paging url="api/bom/supplier" :searchby="['email']" :formatter="['email', 'email']" paging hide-details v-model="ccSelected" :items="ccItems" chips clearable multiple filled label="CC">
        		
				<template v-slot:selection="props">
					<v-chip v-bind="props.attrs" :input-value="props.selected" close @click:close="remove(props.item)">
					<strong>{{ props.item }}</strong>
					</v-chip>
				</template>
        	</v-select-paging> -->
        </v-action-dialog>
        <v-action-dialog :actions="false" fullscreen v-model="dialogRevisiHistory" title="Revision History">
            <po-rev-history v-if="selected" table-only :items-options="revData" :key="selected.id"></po-rev-history>
        </v-action-dialog>
        <v-action-dialog v-model="dialogAskApprovalNote" title="Ask Approval Note" min-height="75%" @save="askApproval">
            <v-textarea label="Note" v-model="askapproval_note"></v-textarea>
        </v-action-dialog>
        <v-action-dialog v-model="dialogNote" title="Revisi Note" min-height="75%" @save="revision">
            <v-textarea label="Note" v-model="revisi_note"></v-textarea>

            <v-alert type="danger" v-if="hasPayment" class="mt-2" dense border="start" color="red" text>
                PO ini sudah memiliki invoice dan sudah masuk daftar pembayaran. Apakah Anda yakin ingin revisi?
            </v-alert>

        </v-action-dialog>

        <v-action-dialog v-model="dialogAskRevisionNote" title="Ask Approval Revisi Note" min-height="75%"
            @save="askAprpovalRevision">
            <v-textarea label="Note" v-model="ask_approval_revision_note"></v-textarea>

            <v-alert type="danger" v-if="hasPayment" class="mt-2" dense border="start" color="red" text>
                PO ini sudah memiliki invoice dan sudah masuk daftar pembayaran. Apakah Anda yakin ingin revisi?
            </v-alert>

        </v-action-dialog>

        <v-action-dialog v-model="dialogAskCancelNote" title="Ask Cancel Note" min-height="75%" @save="cancel">
            <v-textarea label="Note" v-model="askcancel_note"></v-textarea>
        </v-action-dialog>
        <v-action-dialog :actions="false" v-model="dialogNotes" title="PO Comment" min-height="75%" fullscreen>
            <po-comment :key="selected.id" :data="dataid" :table-only="tableOnly"></po-comment>
        </v-action-dialog>
        <v-action-dialog :actions="false" v-model="dialogRemainingBudget" title="Remaining Budget" min-height="75%"
            fullscreen>
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
                                <v-progress-circular indeterminate size="18" width="2" style="margin-right: 8px"></v-progress-circular>
                                Loading...
                            </td>
                        </tr>
                        <tr v-else-if="remainingBudgetItems.length === 0">
                            <td style="padding: 5px" :colspan="showOperationalRemainingBreakdown ? 12 : 11">No data</td>
                        </tr>
                        <tr v-else v-for="(item, index) in remainingBudgetItems" :key="index">
                            <td style="padding: 5px" v-if="item.show_project_budget" :rowspan="item.project_budget_rowspan">{{ item.project_budget_label || "-" }}</td>
                            <td style="padding: 5px">{{ item.item_no }}</td>
                            <td style="padding: 5px">{{ item.part_name || "-" }}</td>
                            <td style="padding: 5px">{{ item.subledger_name || "-" }}</td>
                            <td style="padding: 5px">{{ Number(item.qty || 0).format(2, 3) }}</td>
                            <td style="padding: 5px">{{ Number(item.price || 0).format(2, 3) }}</td>
                            <td style="padding: 5px">{{ Number(item.subtotal || 0).format(2, 3) }}</td>
                            <td style="padding: 5px">{{ item.currency || "-" }}</td>
                            <td style="padding: 5px">{{ "Rp. " + Number(item.grand_total_idr || 0).format(2, 3) }}</td>
                            <td style="padding: 5px" v-if="item.show_remaining" :rowspan="item.remaining_rowspan"
                                :colspan="showOperationalRemainingBreakdown && !item.is_operational ? 2 : 1">
                                <div v-if="remainingBudgetCalcLoading">Calculate Remaining Budget...</div>
                                <div v-else :style="{ color: Number(item.remaining_budget) < 0 ? '#ff5252' : '#000' }">
                                    {{ item.remaining_budget !== undefined ? "Rp. " + Number(item.remaining_budget).format(2, 3) : "-" }}
                                </div>
                            </td>
                            <td style="padding: 5px"
                                v-if="showOperationalRemainingBreakdown && item.is_operational && item.show_remaining"
                                :rowspan="item.remaining_rowspan">
                                <div v-if="remainingBudgetCalcLoading">Calculate Remaining Budget...</div>
                                <div v-if="!remainingBudgetCalcLoading && item.remaining_bugdet_total_department !== undefined && item.remaining_bugdet_total_department !== null"
                                    :style="{ color: Number(item.remaining_bugdet_total_department) < 0 ? '#ff5252' : '#000' }">
                                    {{ "Rp. " + Number(item.remaining_bugdet_total_department).format(2, 3) }}
                                </div>
                                <div v-else-if="!remainingBudgetCalcLoading">-</div>
                            </td>
                            <td style="padding: 5px">{{ item.force_budget_minus_reason || "-" }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </v-action-dialog>

        <v-action-dialog @save=saveFile title="Sent Info" v-model="dialogFile">
            <v-autoform v-model="formFile" :valid="valid"></v-autoform>
        </v-action-dialog>

    </v-container>
</template>

<style scoped>
.v-data-table__wrapper>table>tbody>tr>td {
    font-size: .775rem;
}
</style>

<script>
module.exports = {
    name: 'po',
    props: {
        name: {
            default: 'Entry'
        },
        value: undefined,
        data: {
            type: Object
        },
        tableOnly: {
            type: Boolean,
            default: false
        },
        history: {
            type: Boolean,
            default: false
        },
        hideApproval: {
            type: Boolean,
            default: false
        },
        hideAddButton: {
            type: Boolean,
            default: false
        },
        hideDeleteButton: {
            type: Boolean,
            default: false
        },
        itemsOptions: {
            type: Object,
            default: {
                filter: {
                    approved: '0,1,2,4,5,6,-2,-3'
                },
                filterType: {
                    approved: 'in'
                }
            }
        }
    },
    components: {
        'purchase-item': 'url:ui/bom/purchasing/purchaseorderitem.vue',
        'report': 'url:ui/bom/report/purchaseorder.vue',
        'purchase-item-group': 'url:ui/bom/purchasing/purchaseorderitemgroup.vue',
        'po-rev-history': 'url:ui/bom/purchasing/purchaseorder_revisi.vue',
        'po-comment': 'url:ui/bom/purchasing/comment.vue',
        /* 'complete-po': 'url:ui/bom/purchasing/complete_po.vue' */
    },
    data: function () {
        return {
            exchangeData: {},
            exchangeRateRequest: 0,
            dialogFile: false,
            valid: false,
            askapproval_note: "",
            approval_note: "",
            askcancel_note: "",
            ask_approval_revision_note: "",
            revisi_note: "",
            dialogNote: false,
            dialogAskRevisionNote: false,
            hasPayment: false,
            isLoadingPayment: false,
            dialogAskApprovalNote: false,
            dialogAskCancelNote: false,
            showPaymentWarning: false,
            ccItems: [],
            cc: "",
            to: "",
            revData: {},
            ccSelected: [],
            toSelected: [],
            disableDelete: false,
            disableEdit: false,
            dialogRevisiHistory: false,
            name: '',
            txtApproval: 'Ask Approval',
            processData: {},
            dialogEmail: false,
            dialogNotes: false,
            formFile: [
                {
                    text: "Sent Date",
                    value: "send_po",
                    align: "start",
                    type: "date",
                    required: false
                },
            ],
            itemKey: 'id',
            url: 'bom/purchaseorder',
            headers: [{
                "text": "id",
                "value": "id",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "int",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            }, {
                "text": "purchase_order_id",
                "value": "purchase_order_id",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "int",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            }, {
                "text": "PO Details",
                "value": "po_details",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "200px",
                "type": "varchar",
                "disabled": false,
                "visible": true,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            }, {
                "text": "PO No",
                "value": "po_no",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": false,
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
                "page": "0",
                "limit": "10"
            }, {
                "text": "PO Date",
                "value": "po_date",
                "default": new Date().formatDate('YYYY-MM-DD'),
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
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
                "page": "0",
                "limit": "10"
            }, {
                "text": "Title",
                "value": "title",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": true,
                "required": true,
                "form": true,
                "filter": true,
                "groupable": false,
                "clearable": true,
                "hint": "Type to search title",
                "data_value": [],
            }, {
                "text": "Brand",
                "value": "brand",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": true,
                "filter": false,
                "groupable": false
            }, {
                "text": "Jenis Barang",
                "value": "jenis_barang",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": true,
                "filter": false,
                "groupable": false
            }, {
                "text": "Status",
                "value": "approved",
                "align": "center",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": true,
                "groupable": false,
                "data_value": [{ text: "New", value: "0" }, { text: "Asking Approval 1", value: 1 }, { text: "Asking Approval 2", value: 2 }, { text: "Asking Cancellation 1", value: -2 }, { text: "Asking Cancellation 2", value: -3 }, { text: "Clarification", value: 4 }]
            }, {
                "text": "Status",
                "value": "status",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": true,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false
            },
            {
                "text": "Supplier",
                "value": "supplier",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": true,
                "required": true,
                "form": false,
                "data_value": [],
                "filter": false,
                "groupable": false,
                "url": App.url + "bom/supplier",
                "searchby": ['name'],
                "formatter": ['id', 'name'],
                "options": {
                    "filter": {},
                    "filterType": {},
                    "filterCondition": {}
                },
                "paging": true,
                "page": "1",
                "limit": "10",
                "alias": "supplier_name"
            },
            {
                "text": "Supplier",
                "value": "supplier_id",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": true,
                "data_value": [],
                "filter": true,
                "groupable": false,
                "url": App.url + "bom/supplier",
                "searchby": ['name'],
                "formatter": ['id', 'name'],
                "options": {
                    "filter": {
                        flag: 1
                    },
                    "filterType": {},
                    "filterCondition": {}
                },
                "paging": true,
                "page": "1",
                "limit": "10",
            }, {
                "text": "Price",
                "value": "grand_total_price",
                "align": "center",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": true,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false
            }, {
                "text": "Grand Total Price",
                "value": "grand_total",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false
            },
            {
                "text": "Charge",
                "value": "charge1",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": true,
                "required": false,
                "form": false,
                "filter": false,
                "precision": 2,
                "groupable": false
            }, {
                "text": "Ask Approval Notes",
                "value": "askapproval_note",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false,
            }, {
                "text": "Department",
                "value": "dept_id",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "data_value": [],
                "visible": false,
                "required": true,
                "form": true,
                "filter": true,
                "groupable": false,
                "url": App.url + "bom/department",
                "searchby": ['dept_name'],
                "formatter": ['id', 'dept_name'],
                "pk": "id",
                "options": {
                    "filter": {},
                    "filterType": {},
                    "filterCondition": {}
                },
                "paging": true,
                "page": "1",
                "limit": "10",
                "alias": "dept_name"
            },
            {
                "text": "Ref. Offer No",
                "value": "ref_offer_no",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": true,
                "filter": true,
                "groupable": false,
            }, {
                "text": "RFQ No",
                "value": "rfq_id",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "clearable": "true",
                "type": "list",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": true,
                "filter": true,
                "groupable": false,
                "url": App.url + "rfq/rfq",
                "searchby": ['rfq_no'],
                "formatter": ['id', 'rfq_no'],
                "options": {
                    "filter": {
                        status: 'Final Quotation'
                    },
                    "filterType": {},
                    "filterCondition": {}
                },
                "paging": true,
                "page": "1",
                "limit": "10",
                "alias": "rfq_no"
            }, {
                "text": "PR No",
                "value": "pr_id",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "data_value": [],
                "disabled": false,
                "visible": false,
                "clearable": true,
                "required": false,
                "clearable": "true",
                "form": true,
                "filter": false,
                "groupable": false,
                "url": App.url + "bom/pr",
                "searchby": ['pr_no', 'pr_subject'],
                "formatter": ['id', ['pr_no', 'pr_subject']],
                "options": {
                    "filter": {
                        "peminta_setuju": null,
                        "penyetuju_setuju": null,
                    },
                    "filterType": {
                        "peminta_setuju": 'notnull',
                        "penyetuju_setuju": 'notnull',
                    },
                    "filterCondition": {
                        "pr_no": 'or',
                        "pr_subject": 'or'
                    }
                },
                "paging": true,
                "page": "1",
                "limit": "10",
                "alias": "pr_no"
            }, {
                "text": "PR No",
                "value": "prr_id",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "data_value": [],
                "disabled": false,
                "visible": false,
                "clearable": true,
                "required": true,
                "clearable": "true",
                "form": false,
                "filter": true,
                "groupable": false,
                "url": App.url + "bom/pr",
                "searchby": ['selectdesc'],
                "formatter": ['id', 'selectdesc'],
                "options": {
                    "filter": {
                        "peminta_setuju": null,
                        "penyetuju_setuju": null,
                    },
                    "filterType": {
                        "peminta_setuju": 'notnull',
                        "penyetuju_setuju": 'notnull',
                    },
                    "filterCondition": {
                    }
                },
                "paging": true,
                "page": "1",
                "limit": "10",
                "alias": "pr_no"
            },
            {
                "text": "Ref. Internal BMT",
                "value": "ref_internal_bmt",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": false,
                "filter": false,
                "groupable": false,
            }, {
                "text": "Internal Reference",
                "value": "internal_reference",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": true,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Order Type",
                "value": "order_type",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": true,
                "filter": true,
                "groupable": false,
                "default": "Lokal",
                "data_value": ["Lokal", "Import"]
            },
            {
                "text": "Promised Delivery Date",
                "value": "promised_delivery_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Requested Delivery Date",
                "value": "requested_delivery_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Delivery time",
                "value": "delivery_time",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "ETA date",
                "value": "eta_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Received date",
                "value": "received_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Currency",
                "value": "currency",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "groupable": false,
                "default": "IDR",
                "data_value": ["IDR", "CNY", "EUR", "USD","BDT","SGD"],
                "input": function (item) {
                    var self = App.page()
                    var template = self.$refs.template || {}

                    var currentFormItems = function () {
                        if (template.dialogEdit && template.formModel) return template.formModel
                        return template.formModelAdd || self.headers
                    }
                    var currentField = function (value) {
                        return currentFormItems().find(function (field) {
                            return field.value == value
                        }) || self.headersObj[value]
                    }
                    var refreshFormItems = function () {
                        if (template.dialogEdit && template.formModel)
                            template.formModel = App.updateObject(template.formModel)
                        else if (template.formModelAdd)
                            template.formModelAdd = App.updateObject(template.formModelAdd)
                        else
                            self.headers = App.updateObject(self.headers)
                    }
                    var currency = (item.data || '').trim().toUpperCase()
                    var exchangeRateItem = currentField('exchange_rate')
                    var rateDateItem = currentField('rate_date')
                    var requestId = ++self.exchangeRateRequest

                    exchangeRateItem.required = false
                    if (currency != 'IDR') {
                        exchangeRateItem.required = true
                        rateDateItem.required = true
                    }
                    else {
                        exchangeRateItem.required = false
                        exchangeRateItem.data = 0
                        rateDateItem.required = false
                        refreshFormItems()
                    }

                    if (currency != 'IDR') {
                        var ex = currency
                        if (self.exchangeData[ex]) {
                            exchangeRateItem.data = self.exchangeData[ex]
                            exchangeRateItem.update = self.exchangeData[ex]
                            refreshFormItems()
                        }
                        else {
                            exchangeRateItem.data = 'Loading...'
                            refreshFormItems()
                            axios.get(App.url + 'bom/payment/exchange', {
                                params: {
                                    currency: currency
                                }
                            })
                            .then(function (response) {
                                if (
                                    requestId != self.exchangeRateRequest ||
                                    (item.data || '').trim().toUpperCase() != currency
                                )
                                    return
                                var data = response.data == null ? '' : String(response.data)
                                var rate = (
                                    data.match(/<jual_subkurslokal>(.*?)<\/jual_subkurslokal>/i) || []
                                )[1] || (data.match(/[\d.,]+/) || [''])[0]

                                if (rate.lastIndexOf(',') > rate.lastIndexOf('.'))
                                    rate = rate.replace(/\./g, '').replace(',', '.')
                                else
                                    rate = rate.replace(/,/g, '')

                                exchangeRateItem = currentField('exchange_rate')
                                exchangeRateItem.data = isNaN(Number(rate)) ? null : Number(rate).format(2, 3)
                                refreshFormItems()
                            })
                            .catch(function (error) {
                                if (requestId != self.exchangeRateRequest) return
                                exchangeRateItem = currentField('exchange_rate')
                                exchangeRateItem.data = null
                                refreshFormItems()
                            })
                        }
                    }
                    else {
                        refreshFormItems()
                    }
                }
            },
            {
                "text": "Exchange Rate",
                "value": "exchange_rate",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
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
                "precision": "4",
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Rate Date",
                "value": "rate_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Charge 1",
                "value": "charge1",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "Charge 1 Desc",
                "value": "charge1_desc",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "groupable": false
            },
            {
                "text": "Charge 2",
                "value": "charge2",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "Charge 2 Desc",
                "value": "charge2_desc",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "groupable": false
            },
            {
                "text": "PPN (%)",
                "value": "ppn",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "PPH (%)",
                "value": "pph",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "Admin Charge",
                "value": "admin_charge",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "Shipping Cost Charge",
                "value": "shipping_cost_charge",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "Service Charge",
                "value": "service_charge",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "Other Charge",
                "value": "other_charge",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "Discount",
                "value": "discount",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "float",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "precision": 2,
                "groupable": false
            },
            {
                "text": "Discount Desc",
                "value": "discount_desc",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "groupable": false
            },
            {
                "text": "Payment Term",
                "value": "payment_term",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "groupable": false
            },
            {
                "text": "Despatch",
                "value": "despatch",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "groupable": false
            },

            {
                "text": "Shipping Address",
                "value": "shipping_address",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "groupable": false
            },
            {
                "text": "Miscellaneous",
                "value": "miscellaneous",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
                "groupable": false
            },
            {
                "text": "Documents",
                "value": "final_quote_url",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Currency",
                "value": "currency_desc",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false,
                "default": "IDR",
                "data_value": ["IDR", "CNY", "EUR", "USD","SGD","BDT"],
                "input": function (data) {
                    var self = App.page()
                    if (data.data.trim().toLowerCase() != 'idr') {
                        self.headersObj.exchange_rate.required = true
                        self.headersObj.rate_date.required = true
                    }
                    else {
                        self.headersObj.exchange_rate.required = false
                        self.headersObj.rate_date.required = false
                    }

                    self.headers = App.updateObject(self.headers)
                }
            },
            {
                "text": "Final Quote URL",
                "value": "final_quote_url",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Signed PO URL",
                "value": "signed_po_url",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            },
            {
                "text": "Signed PR URL",
                "value": "signed_pr_url",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": true,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            }, {
                "text": "Approval History",
                "value": "approval_history",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
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
                "page": "0",
                "limit": "10"
            }, {
                "text": "Sender Name",
                "value": "sp_name",
                "type": "varchar",
                "visible": false,
                "form": false
            },
            {
                "text": "Send PO",
                "value": "send_po",
                "align": "Start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": true,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false,


            },
            {
                "text": "Notes",
                "value": "note",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": true,
                "required": false,
                "form": true,
                "filter": true,
                "groupable": false,
            },
            {
                text: 'Shipping',
                value: 'shipping',
                "type": "text",
                form: false,
                visible: true
            }, {
                "text": "Payment",
                "value": "payment",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": true,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false
            },
            {
                "text": "Last Revised Comment",
                "value": "comment",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false,
            }, {
                text: 'Approval 1 date',
                value: 'approval_date',
                form: false,
                visible: false,
                "alias": "approval_date"
            }, {
                text: 'Approval 2 date',
                value: 'approval2date',
                form: false,
                visible: false,
                "type": "date",
                "alias": "approval_2_date",
                "filter": true
            },
            {
                "text": "New PO No (Revised Existing)",
                "value": "new_po_no",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "text",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false,
            }, {
                "text": "Created",
                "value": "created",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false
            }, {
                "text": "Created By",
                "value": "created_by",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": true,
                "groupable": false,
                "url": App.url + "users/info",
                "searchby": ["name"],
                "formatter": ["id", "name"],
                "options": {
                    "filter": {
                        group_name: "po_admin",
                        sub_group_name: "po_admin"
                    },
                    "filterType": {
                    },
                    "filterCondition": {
                        group_name: 'or',
                        sub_group_name: 'or'
                    }
                },
                paging: true,
                page: "1",
                limit: "10",
                alias: "created_by_name",
            }, {
                "text": "Created Date",
                "value": "created_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false
            }, {
                "text": "Created Date",
                "value": "crea_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": false,
                "filter": true,
                "groupable": false,
                "url": "",
                "search": [],
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
                "text": "Modified",
                "value": "modified",
                "align": "start",
                "sortable": false,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "varchar",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false
            }, {
                "text": "Modified By",
                "value": "modified_by",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "list",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": true,
                "groupable": false,
                "url": App.url + "users/info",
                "searchby": ["name"],
                "formatter": ["id", "name"],
                "options": {
                    "filter": {
                        group_name: "po_admin",
                        sub_group_name: "po_admin"
                    },
                    "filterType": {
                    },
                    "filterCondition": {
                        group_name: 'or',
                        sub_group_name: 'or'
                    }
                },
                paging: true,
                page: "1",
                limit: "10",
                alias: "modified_by_name",
            }, {
                "text": "Modified Date",
                "value": "modified_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": false,
                "form": false,
                "filter": false,
                "groupable": false
            }, {
                "text": "Modified Date",
                "value": "mod_date",
                "align": "start",
                "sortable": true,
                "filterable": false,
                "divider": false,
                "class": "",
                "width": "auto",
                "type": "date",
                "disabled": false,
                "visible": false,
                "required": true,
                "form": false,
                "filter": true,
                "groupable": false,
                "url": "",
                "search": [],
                "formatter": [],
                "options": {
                    "filter": {
                    },
                    "filterType": {
                    },
                    "filterCondition": {}
                },
                "paging": true,
                "page": "1",
                "limit": "10"
            }, {
                text: '',
                value: 'data-table-expand'
            }
            ],
            dialogItem: false,
            dialogItemGroup: false,
            dialogReport: false,
            dialogRemainingBudget: false,
            remainingBudgetItems: [],
            remainingBudgetLoading: false,
            remainingBudgetCalcLoading: false,
            showOperationalRemainingBreakdown: false,
            remainingBudgetPoId: null,
            selected: false,
            dialogComplete: false,
            dataid: {},
            defaultForm: [],
            titleOptionsCache: []
        }
    },
    watch: {
        dialogItemGroup: function (val) {
            if (!val)
                this.$refs.template.getItems()
        },
        dialogNote: function (val) {
            if (val == false)
                this.revisi_note = ""
        },
        dialogAskRevisionNote: function (val) {
            if (val == false)
                this.ask_approval_revision_note = ""
        },
        dialogAskApprovalNote: function (val) {
            if (val == false)
                this.approval_note = ""
        },
        dialogAskCancelNote: function (val) {
            if (val == false)
                this.askcancel_note = ""
        }
    },
    computed: {
        headersObj: function () {
            var tmp = {}, self = this
            Object.keys(self.headers).map(key => {
                var val = self.headers[key]
                tmp[val.value] = val
            })
            return tmp;
        },
        // allowRevisi: function(){
        // 	var self = this

        // 	if(!self.selected)
        // 		return true
        // 	if(self.selected.approved < 3)
        // 		return true
        //     if(self.selected.revision_approval == 1)
        // 		return true
        //     if (self.selected.revision_approval == 0 && self.hasPayment) 
        // 		return true

        // 	return false
        // },
        allowItems: function () {
            var self = this

            if (!self.selected)
                return true
            if (self.selected.approved > 0)
                return true
            return false
        },
        allowRevisiHistory: function () {
            var self = this

            if (!self.selected)
                return false
            if (self.selected.po_no.match(/-rev/i) != null)
                return true
            return false
        },
        allowPrint: function () {
            var self = this
            if (self.selected.new_po_no != null) {
                if (self.selected.new_po_no.trim() != '')
                    return true
            }
            if (!self.selected)
                return true
            if (self.selected.approved == -1)
                return true
            /* if(self.selected.approved == 2)
                return false */
            return false
        },
        allowPrint2: function () {
            var self = this
            if (!self.selected)
                return true
            if (self.selected.approved == 2)
                return false
            return true
        },
        allowAskApproval: function () {
            var self = this
            if (self.selected.item_count < 1)
                return true
            if (self.selected.approved < 0)
                return true
            if (!self.selected)
                return true
            if (self.selected.approved == 0 || self.selected.approved == -1)
                return false
            return true
        },
    },
    methods: {
        applyTitleOptions: function () {
            var self = this
            var opts = Array.isArray(self.titleOptionsCache) ? self.titleOptionsCache : []
            if (self.headersObj.title) {
                self.headersObj.title.data_value = opts
            }
            if (Array.isArray(self.defaultForm)) {
                self.defaultForm.map(function (item) {
                    if (item.value === 'title') {
                        item.data_value = JSONfn.parse(JSONfn.stringify(opts))
                    }
                })
            }
        },
        loadTitleOptions: async function () {
            var self = this
            try {
                var r = await axios.get(App.url + 'bom/purchaseorder/titleoptions')
                if (r.data && r.data.status) {
                    self.titleOptionsCache = Array.isArray(r.data.data) ? r.data.data : []
                    self.applyTitleOptions()
                }
            } catch (err) {
                console.log(err)
            }
        },
        async checkPaymentStatus() {
            try {
                const response = await fetch(`api/bom/purchaseorder/getdataexistpaymentlist?id=${this.selected.id}`);
                const data = await response.json();
                this.hasPayment = data.status;
            } catch (error) {
                console.error('Gagal memuat status pembayaran:', error);
                this.hasPayment = false;
            }
        },

        allowRevisi() {
            if (!this.selected) return true;
            if (this.isLoadingPayment) return true; // Nonaktifkan saat loading
            if (this.selected.approved < 3) return true;
            if (this.selected.revision_approval == 1) return true;
            if (this.selected.revision_approval == 0 && this.hasPayment) return true;
            return false;
        },

        allowAskApprovalRevision() {
            if (this.selected.revision_approval == 0 && this.hasPayment) return false;
            return true;
        },

        async openDialogNote() {
            await this.checkPaymentStatus();
            this.dialogNote = true;
        },

        async openDialogNoteApprovalRevision() {
            await this.checkPaymentStatus();
            this.dialogAskRevisionNote = true;
        },
        saveFile: async function () {
            var self = this
            const formData = new FormData()
            self.formFile.map(function (val) {
                var key = val.value
                formData.append(key, val.data)
            })
            formData.append("pk", self.selected.id)
            var res = await axios.post(App.url + 'bom/purchaseorder/sendpo', formData,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                }
            )
            if (!res.data.status) {
                App.errorMsg()
            } else {
                self.$refs.template.getItems()
                App.successMsg()
            }
            self.dialogFile = false
        },
        openSendForm: function () {
            var self = this
            // if(confirm('are u sure ?'))
            self.dialogFile = true
        },
        allowSendPo: function (item) {
            if (item == undefined)
                return false
            if (item.approved < 3)
                return false
            return true
        },
        modifDate: function (date, item) {
            date = new Date(date)
            //if([3,8,59,11].includes(item.supplier_id) && Number(date.getDate())<15) 
            //date.setDate(15)
            return date.formatDate('YYYY-MM-DD HH:mm:ss')
        },
        revision: async function () {
            var self = this
            var q = confirm("Are you sure you want to revise this PO?")
            if (!q)
                return true
            else {
                try {
                    var r = await axios.get(App.url + 'bom/purchaseorder/revisi', {
                        params: {
                            id: self.selected.id,
                            revisi_note: self.revisi_note
                        }
                    })
                    if (!r.data.status)
                        App.errorMsg(r.data)
                    else {
                        self.$refs.template.getItems()
                        App.successMsg()
                    }
                } catch (err) {
                    App.errorMsg()
                }
            }
            self.dialogNote = false
        },
        askAprpovalRevision: async function () {
            var self = this
            var q = confirm("Are you sure you want ask approval to revise this PO?")
            if (!q)
                return true
            else {
                try {
                    var r = await axios.get(App.url + 'bom/purchaseorder/askapprovalrevisi', {
                        params: {
                            id: self.selected.id,
                            ask_approval_revision_note: self.ask_approval_revision_note
                        }
                    })
                    if (!r.data.status)
                        App.errorMsg(r.data)
                    else {
                        self.$refs.template.getItems()
                        App.successMsg()
                    }
                } catch (err) {
                    App.errorMsg()
                }
            }
            self.dialogAskRevisionNote = false
        },
        cancel: async function () {
            var self = this
            var q = confirm("Are you sure you want to cancel this PO?")
            if (!q)
                return true
            else {
                try {
                    var r = await axios.get(App.url + 'bom/purchaseorder/cancel', {
                        params: {

                            id: self.selected.id,
                            ask_canceled_note: self.askcancel_note
                        }
                    })
                    if (!r.data.status)
                        App.errorMsg(r.data)
                    else {
                        self.$refs.template.getItems()
                        App.successMsg()
                    }
                } catch (err) {
                    App.errorMsg()
                }
            }
            self.dialogAskCancelNote = false
        },
        onOpenEdit: function (add) {
            var self = this
            if (self.selected.approved >= 3 && self.selected.approved != 4 && add != true) {
                self.headers.map(function (val) {
                    val.form = false
                })
                self.headersObj.signed_po_url.form = true
                self.headersObj.eta_date.form = true
                self.headersObj.requested_delivery_date.form = true
                self.headersObj.delivery_time.form = true
                self.headersObj.received_date.form = true
                self.headersObj.promised_delivery_date.form = true
                self.headersObj.new_po_no.form = true
            }
            else {
                self.headers = JSONfn.parse(JSONfn.stringify(self.defaultForm))
                self.applyTitleOptions()
            }
            if (add != true) {
                self.headers.map(function (val) {
                    if (self.selected[val.value] !== undefined)
                        val.data = self.selected[val.value]
                })
                self.headersObj.dept_id.readonly = true
            }


        },
        onSelectedRow: async function (val) {
            var self = this
            if (val === undefined) {
                self.selected = false
                self.processData = {}
                self.dataid = {}
                self.disableDelete = false
                self.disableEdit = false
            } else {
                self.selected = val
                var email = (val.email || "").split(';').map(val => {
                    return val.trim()
                }).filter(val => {
                    return val.trim() != ''
                })
                self.toSelected = [email[0]]
                self.ccSelected = email.slice(1)
                if (App.userData.data[0].email)
                    self.ccSelected.push(App.userData.data[0].email)
                self.processData = {
                    purchase_order_id: val.id,
                    supplier_id: val.supplier_id,
                    pr_id: val.pr_id,
                    pr_no: val.pr_no,
                    currency: val.currency,
                    charge1: val.charge1,
                    charge2: val.charge2,
                }

                self.txtApproval = 'Ask Approval'
                if (val.approved == 0)
                    self.txtApproval = 'Ask Approval'
                self.disableEdit = false
                /* if(val.approved == 2)
                    self.txtApproval = 'Ask Approval 2' */
                if (val.approved == 2 || val.approved == 1) {
                    self.disableDelete = true
                    self.disableEdit = true
                }
                if (val.approved < 0) {
                    self.disableEdit = true
                }
                if (val.approved > 2) {
                    self.disableEdit = false
                }
                else {
                    // self.disableDelete = false
                    // self.disableEdit = false
                }
                self.dataid = {
                    purchase_order_id: val.id
                }
                self.revData = {
                    filter: {
                        purchase_order_id: val.id,
                        flag: 1
                    }
                }

                try {
                    self.isLoadingPayment = true;
                    const response = await fetch(`api/bom/purchaseorder/getdataexistpaymentlist?id=${val.id}`);
                    const data = await response.json();
                    self.hasPayment = data.status;
                } catch (error) {
                    console.error('Gagal memuat status pembayaran:', error);
                    self.hasPayment = false;
                } finally {
                    self.isLoadingPayment = false;
                }
            }
        },
        onSelectedRowAll: function (val) {
            var self = this
            self.selectedAll = val
        },
        askApproval: async function (val) {

            var self = this
            var c = confirm("Are you sure?");
            if (!c) return true;
            if (self.selected.approved == 0) {
                var r = await axios.put(App.url + 'bom/purchaseorder', {
                    approved: 1,
                    pk: self.selected.id,
                    askapproval_note: self.askapproval_note
                })
                if (!r.data.status)
                    App.errorMsg(r.data)
                else {
                    self.$refs.template.getItems()
                    App.successMsg()
                }
            }
            if (self.selected.approved == 1) {
                App.errorMsg('Telah ada permintaan approve 1!')
            }
            /* if(self.selected.approved==3){
                App.errorMsg('Telah ada permintaan approve 2!')
            } */
            if (self.selected.approved == 3) {
                App.errorMsg('Telah di approve!')
            }
            if (self.selected.approved == -1) {
                App.errorMsg('Telah di reject!')
            }
            self.dialogAskApprovalNote = false
        },
        approvedStatus: function (f, new_po_no) {
            if (new_po_no != null) {
                if (new_po_no.trim() != '')
                    return 'Revised Final PO'
            }
            // if(f == 0){
            // 	return 'New'
            // }
            if (f == 1) {
                return 'Asking for Approval 1'
            }
            if (f == 2) {
                return 'Asking for Approval 2'
            }
            if (f == 3) {
                return 'Approval 2 Approved'
            }
            if (f == 4) {
                return 'Clarification'
            }
            if (f == 5) {
                return 'Asking for Draft PO'
            }
            if (f == 6) {
                return 'Approval 2 Approved Draft'
            }
            if (f == -1) {
                return 'Rejected'
            }
            if (f == -2) {
                return 'Asking for Canceled 1'
            }
            if (f == -3) {
                return 'Asking for Canceled 2'
            }
            if (f == -4) {
                return 'Canceled'
            }
        },

        //Button preview (procurement) button comment
        openReport: function () {
            var self = this
            var name = self.selected.po_no.replace(/\//g, '_').replace(/\-/g, '_')
            var randomid = randomId()
            // window.open('https://decafet.com/api/report?type=pdf&file=po&filename=' + name + '&engine=easytemplate&po_id=' + self.selected.id)
            window.open('https://main.buanamultiteknik.com/api/data/reportpo?id=' + self.selected.id + '&filename=' + name + '&idx=' + randomid)

        },

        //button preview (procurement)
        openReport2: function () {
            var self = this
            var name = self.selected.po_no.replace(/\//g, '_').replace(/\-/g, '_')
            var randomid = randomId()
            window.open('https://main.buanamultiteknik.com/api/data/reportpo2?id=' + self.selected.id + '&filename=' + name + '&idx=' + randomid)


            // window.open('https://decafet.com/api/report?type=pdf&file=po2&filename='+name+'&engine=easytemplate&po_id='+self.selected.id)
        },

        //button preview without price (procurement & Logistik)
        openReportNoPrice: function () {
            var self = this
            var name = self.selected.po_no.replace(/\//g, '_').replace(/\-/g, '_')
            var randomid = randomId()
            window.open('https://main.buanamultiteknik.com/api/data/reportponoprice?id=' + self.selected.id + '&filename=' + name + '&idx=' + randomid)
        },
        fetchRemainingBudgetItems: async function (poId) {
            var self = this
            self.remainingBudgetLoading = true
            self.remainingBudgetCalcLoading = false
            try {
                var r = await axios.get(App.url + 'bom/purchase_order_item_group/subledger', {
                    params: {
                        purchase_order_id: poId
                    }
                })
                if (!r.data.status) {
                    App.errorMsg(r.data)
                    self.remainingBudgetItems = []
                    self.remainingBudgetLoading = false
                    return
                }
                var items = r.data.data || []
                var groupMap = {}
                var totalSubtotal = 0
                var charge1Total = 0
                var charge2Total = 0
                var discountTotal = 0
                items.map((item) => {
                    var qty = Number(item.qty || 0)
                    var price = Number(item.price || 0)
                    var subtotal = qty * price
                    item.subtotal = subtotal
                    totalSubtotal += subtotal
                    var key = (item.project_id || '') + '|' + (item.project_budget_id || '')
                    if (!groupMap[key]) groupMap[key] = { total: 0, count: 0 }
                    groupMap[key].total += subtotal
                    groupMap[key].count += 1
                })
                if (items.length > 0) {
                    charge1Total = Number(items[0].charge1 || 0)
                    charge2Total = Number(items[0].charge2 || 0)
                    discountTotal = Number(items[0].discount || 0)
                }
                var seen = {}
                self.remainingBudgetItems = items.map((item) => {
                    var key = (item.project_id || '') + '|' + (item.project_budget_id || '')
                    var showGroup = !seen[key]
                    if (showGroup) seen[key] = true
                    var projectLabel = ''
                    if (item.project_no) projectLabel += item.project_no
                    if (item.project_name) projectLabel += (projectLabel ? ' - ' : '') + item.project_name
                    if (!projectLabel) projectLabel = '-'
                    if (item.budget_name) projectLabel += ' (' + item.budget_name + ')'
                    var subtotal = Number(item.subtotal || 0)
                    var pct = totalSubtotal > 0 ? subtotal / totalSubtotal : 0
                    var allocatedCharge1 = charge1Total * pct
                    var allocatedCharge2 = charge2Total * pct
                    var allocatedDiscount = discountTotal * pct
                    var exchangeRate = Number(item.exchange_rate || 0)
                    if (!exchangeRate || exchangeRate <= 0) exchangeRate = 1
                    var subtotalIdr = subtotal * exchangeRate
                    var grandTotalIdr = subtotalIdr + allocatedCharge1 + allocatedCharge2 - allocatedDiscount
                    return Object.assign({}, item, {
                        project_budget_label: projectLabel,
                        show_project_budget: showGroup,
                        project_budget_rowspan: groupMap[key].count,
                        show_remaining: showGroup,
                        remaining_rowspan: groupMap[key].count,
                        charge1: allocatedCharge1,
                        charge2: allocatedCharge2,
                        discount: allocatedDiscount,
                        grand_total_idr: grandTotalIdr
                    })
                })
                if (self.remainingBudgetItems.length > 0) {
                    await self.calculateRemainingBudget()
                }
            } catch (e) {
                App.errorMsg(e)
                self.remainingBudgetItems = []
            }
            self.remainingBudgetLoading = false
        },
        calculateRemainingBudget: async function () {
            var self = this
            if (self.remainingBudgetItems.length === 0) {
                self.showOperationalRemainingBreakdown = false
                return
            }
            var groupedIds = {}
            self.remainingBudgetItems.map((item) => {
                var price = Number(item.price || 0)
                if (!groupedIds[price]) groupedIds[price] = []
                groupedIds[price].push(item.subledger_id)
            })
            var subledgerIds = self.remainingBudgetItems
                .map((item) => item.subledger_id)
                .filter((id) => id !== undefined && id !== null && id !== '')
            var projectTypeMap = await self.getProjectTypeMap(subledgerIds)
            var groupedProjectIds = {}
            var groupedOperationalIds = {}
            Object.keys(groupedIds).map((price) => {
                groupedProjectIds[price] = []
                groupedOperationalIds[price] = []
                groupedIds[price].map((subledgerId) => {
                    var projectType = projectTypeMap[subledgerId]
                    if (String(projectType || '').toLowerCase() === 'operational') groupedOperationalIds[price].push(subledgerId)
                    else groupedProjectIds[price].push(subledgerId)
                })
            })
            self.remainingBudgetCalcLoading = true
            try {
                var remainingMap = {}
                var remainingTotalDepartmentMap = {}
                var warningMap = {}
                var operationalLabelMap = {}
                var requests = []
                Object.keys(groupedProjectIds).map((price) => {
                    if (groupedProjectIds[price].length === 0) return
                    requests.push(
                        axios.get('https://panel.buanamultiteknik.com/api/budget/project-budget/subledger', {
                            params: { price: price, subledger_id: groupedProjectIds[price].join(',') }
                        }).then((budgetRes) => {
                            var budgetData = budgetRes && budgetRes.data && budgetRes.data.data && Array.isArray(budgetRes.data.data) ? budgetRes.data.data : []
                            budgetData.map((d) => {
                                remainingMap[d.subledger_id] = d.remaining
                                remainingTotalDepartmentMap[d.subledger_id] = d.remaining_bugdet_total_department
                                warningMap[d.subledger_id] = !!d.is_warning
                            })
                        })
                    )
                })
                Object.keys(groupedOperationalIds).map((price) => {
                    if (groupedOperationalIds[price].length === 0) return
                    requests.push(
                        axios.get('https://panel.buanamultiteknik.com/api/budget/operational-budget/subledger', {
                            params: { price: price, subledger_id: groupedOperationalIds[price].join(',') }
                        }).then((budgetRes) => {
                            var budgetData = budgetRes && budgetRes.data && budgetRes.data.data && Array.isArray(budgetRes.data.data) ? budgetRes.data.data : []
                            budgetData.map((d) => {
                                remainingMap[d.subledger_id] = d.remaining
                                remainingTotalDepartmentMap[d.subledger_id] = d.remaining_bugdet_total_department
                                warningMap[d.subledger_id] = !!d.is_warning
                                var department = d.department_name || '-'
                                var typeOperational = d.type_operational_name || '-'
                                var subTypeOperational = d.sub_type_operational_name || '-'
                                operationalLabelMap[d.subledger_id] = department + ' -> ' + typeOperational + ' -> ' + subTypeOperational
                            })
                        })
                    )
                })
                await Promise.all(requests)
                self.remainingBudgetItems = self.remainingBudgetItems.map((item) => {
                    var projectType = projectTypeMap[item.subledger_id]
                    var label = operationalLabelMap[item.subledger_id]
                    var isOperational = String(projectType || '').toLowerCase() === 'operational'
                    var projectBudgetLabel = isOperational && label ? label : item.project_budget_label
                    return Object.assign({}, item, {
                        remaining_budget: remainingMap[item.subledger_id],
                        remaining_bugdet_total_department: remainingTotalDepartmentMap[item.subledger_id],
                        is_operational: isOperational,
                        is_warning: warningMap[item.subledger_id] !== undefined ? warningMap[item.subledger_id] : item.is_warning,
                        project_budget_label: projectBudgetLabel
                    })
                })
                var opItems = self.remainingBudgetItems.slice()
                var i = 0
                while (i < opItems.length) {
                    if (!opItems[i].is_operational) {
                        i++
                        continue
                    }
                    var label = opItems[i].project_budget_label
                    var j = i + 1
                    while (j < opItems.length && opItems[j].is_operational && opItems[j].project_budget_label === label) j++
                    var count = j - i
                    opItems[i] = Object.assign({}, opItems[i], {
                        show_project_budget: true, project_budget_rowspan: count, show_remaining: true, remaining_rowspan: count
                    })
                    for (var k = i + 1; k < j; k++) {
                        opItems[k] = Object.assign({}, opItems[k], {
                            show_project_budget: false, project_budget_rowspan: 1, show_remaining: false, remaining_rowspan: 1
                        })
                    }
                    i = j
                }
                self.remainingBudgetItems = opItems
                self.showOperationalRemainingBreakdown = self.remainingBudgetItems.some((item) => !!item.is_operational)
            } catch (e) {
                console.log('Remaining budget API error:', e)
                self.showOperationalRemainingBreakdown = false
            }
            self.remainingBudgetCalcLoading = false
        },
        getProjectTypeMap: async function (subledgerIds) {
            if (!Array.isArray(subledgerIds) || subledgerIds.length === 0) return {}
            try {
                var r = await axios.get(App.url + 'bom/prsubledger', {
                    params: {
                        filter: { id: subledgerIds },
                        filterType: { id: 'in' },
                        limit: -1
                    }
                })
                var rows = r && r.data && Array.isArray(r.data.data) ? r.data.data : []
                var map = {}
                rows.map((d) => {
                    if (d && d.id !== undefined) map[d.id] = d.project_type
                })
                return map
            } catch (e) {
                console.log('Remaining budget project_type fetch error:', e)
                return {}
            }
        },
        openRemainingBudgetDialog: function (item) {
            var self = this
            if (!item) return
            self.dialogRemainingBudget = true
            self.remainingBudgetItems = []
            self.showOperationalRemainingBreakdown = false
            self.remainingBudgetPoId = item.id
            axios
                .get('https://panel.buanamultiteknik.com/api/transaction/purchase-order/get-remaining-budget/' + item.id)
                .then((res) => {
                    console.log('Remaining budget API result:', res.data)
                })
                .catch((err) => {
                    console.error('Remaining budget API error:', err)
                })
            self.fetchRemainingBudgetItems(item.id)
        },
        remove(item) {
            this.ccSelected.splice(this.ccSelected.indexOf(item), 1)
        },
        removeTo(item) {
            this.toSelected.splice(this.toSelected.indexOf(item), 1)
        },
        addCC: function () {
            let regex = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}')
            if (regex.test(this.cc)) {
                this.ccSelected.push(this.cc)
                this.cc = ''
            }
            else {
                App.errorMsg("Email address invalid!")
            }
        },
        addTo: function () {
            let regex = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}')
            if (regex.test(this.to)) {
                this.toSelected.push(this.to)
                this.to = ''
            }
            else {
                App.errorMsg("Email address invalid!")
            }
        },
        sendEmail: async function () {
            var self = this
            try {
                if (self.toSelected.length == 0)
                    App.errorMsg("Please insert recipient!")
                else {
                    var r = await axios.post(App.url + 'bom/purchaseorder/emailsupplier', {
                        to: self.toSelected.join(';;'),
                        po_id: self.selected.id,
                        supplier_id: self.selected.supplier_id,
                        cc: self.ccSelected.join(';;')
                    })
                    if (!r.data.status)
                        App.errorMsg(r.data)
                    else {
                        self.$refs.template.getItems()
                        App.successMsg()
                        self.dialogEmail = false
                    }
                }
            } catch (error) {
                App.errorMsg()
            }
        },
        sendEmail2: async function () {
            var self = this
            try {
                if (self.toSelected.length == 0)
                    App.errorMsg("Please insert recipient!")
                else {
                    var r = await axios.post(App.url + 'bom/purchaseorder/autoemailsupplier', {
                        to: self.toSelected.join(';;'),
                        po_id: self.selected.id,
                        supplier_id: self.selected.supplier_id,
                        cc: self.ccSelected.join(';;')
                    })
                    if (!r.data.status)
                        App.errorMsg(r.data)
                    else {
                        self.$refs.template.getItems()
                        App.successMsg()
                        self.dialogEmail = false
                    }
                }
            } catch (error) {
                App.errorMsg()
            }
        },
        revisionHistory: function () {
            var self = this
            self.dialogRevisiHistory = true
        },
		async createFakeData(){
			var self = this
            try {
                var r = await axios.post(App.url + 'bom/purchaseorder/fakedata', {
					po_id: self.selected.id
				})
				if (!r.data.status)
					App.errorMsg(r.data)
				else {
					App.successMsg()
				}
            } catch (error) {
                App.errorMsg()
            }
		}
    },
    mounted: function () {
        var self = this
		App.searchControl = true
		App.onSearch = function(){
			App.page().$refs.template.defaultItemsOptions.filter.po_no = App.search
			App.page().$refs.template.defaultItemsOptions.filterType.po_no = "%"
			App.page().$refs.template.getItems()

		}
        self.defaultForm = JSONfn.parse(JSONfn.stringify(self.headers))
        self.loadTitleOptions()
        axios.get(App.url + 'bom/payment/exchange', {
            params: {
                currency: 'CNY'
            }
        })
        .then(function (response) {
            var data = response.data
            self.exchangeData['CNY'] = data.match(/\d+(.\d+)/)[0]
        })
        .catch(function (error) {
        })
        
        axios.get(App.url + 'bom/payment/exchange', {
            params: {
                currency: 'EUR'
            }
        })
        .then(function (response) {
            var data = response.data
            self.exchangeData['EUR'] = data.match(/\d+(.\d+)/)[0]
        })
        .catch(function (error) {
        })
        
        axios.get(App.url + 'bom/payment/exchange', {
            params: {
                currency: 'USD'
            }
        })
        .then(function (response) {
            var data = response.data
            self.exchangeData['USD'] = data.match(/\d+(.\d+)/)[0]
        })
        .catch(function (error) {
        })

        if (self.history) {
            self.headersObj.approval_history.visible = true
            /*self.headersObj.approval_date.visible=true
            self.headersObj.approval_2_date.visible=true*/
            self.headersObj.approved.visible = false
            self.headersObj.status.visible = false
        }
    },
    beforeDestroy: function () {
        var self = this
		App.searchControl = false
		App.search = ''
		App.onSearch = function(){}
	}
}
</script>
