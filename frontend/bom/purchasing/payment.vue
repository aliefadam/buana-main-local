<template>
    <v-container
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :disable-delete-button="disableDeleteButton" :hide-delete-button="hideDeleteButton" :hide-add-button="hideAddButton" @open-edit="onOpenEdit" @open-add="onOpenEdit(true)" :items-options="itemsOptions" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" :table-only="tableOnly">
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
            <template v-slot:menu-after-filter>
                <v-btn color="primary" v-if="!hideAddInvoice" outlined small @click="dialogItem = true" :disabled="!selected">
                    Add Invoice
                </v-btn>
                <v-btn color="primary" outlined small @click="openReport" :disabled="!selected ? true : selected.item_count == 0">
                    <v-icon small left>mdi-printer</v-icon>Print
                </v-btn>
                <!-- <v-btn color="primary" outlined small @click="askApproval" :disabled="!selected">
                    Ask Approval
                </v-btn> -->
                <v-btn color="primary" v-if="!hideApproval" outlined small @click="askApproval" :disabled="checkApproval1">
                    Ask Approval 1
                </v-btn>
                <v-btn color="primary" v-if="!hideApproval" outlined small @click="askApproval" :disabled="checkApproval2">
                    Ask Approval 2
                </v-btn>
                <!--<v-btn color="primary" v-if="history" outlined small @click="toggleDone" :disabled="!selected ? true : selected.item_count == 0">-->
                <!--    Toggle Done-->
                <!--</v-btn>-->
				<!-- <v-btn small color="primary" outlined v-if="showCompletePo" @click="dialogComplete = true">Complete PO</v-btn> -->
            </template>
			<template v-slot:item.signed_payment_doc_url="props">
				<a :href="props.item.signed_payment_doc_url" v-if="props.item.signed_payment_doc_url" target="_blank">Open Link</a><span v-else>-</span>
			</template>
			<template v-slot:item.payment_no="props">
				<b>Payment no:</b> {{props.item.payment_no}}<br/>
				<b>Payment date:</b> {{props.item.payment_date}}<!-- <br/>
				<b>Department:</b> {{props.item.dept_name}} -->
			</template>
			<template v-slot:item.done="props">
				{{props.item.done==1 ? 'Done' : '-'}}
			</template>
			<template v-slot:item.approved="props">
				<b>Status:</b> {{approvedStatus(props.item.approved)}}<br/>
				<b>Validated date:</b> {{props.item.approved1_date}}<br/>
				<b>Validated by:</b> {{props.item.approved1}}<br/>
				<b>Approved date:</b> {{props.item.approved2_date}}<br/>
				<b>Approved by:</b> {{props.item.approved2}}
			</template>
        </v-template>
        <v-action-dialog :actions="false" v-model="dialogItem" title="Detail" min-height="75%" fullscreen
            :key="selected.id">
            <payment-item :data="processData" :key="selected.id" :table-only="selected.approved != 0"></payment-item>
        </v-action-dialog>
        <!-- <v-action-dialog :actions="false" v-model="dialogComplete" title="Complete PO" min-height="75%" fullscreen>
            <complete-po></complete-po>
        </v-action-dialog> -->
    </v-container>
</template>

<style scoped>
	.v-data-table__wrapper > table > tbody > tr > td {
		font-size: .775rem;
	}
</style>

<script>
    module.exports = {
        name: '',
        props: {
            value: undefined,
            data: {
                type: Object
            },
            tableOnly: {
                type: Boolean,
                default: false
            },
            showCompletePo: {
                type: Boolean,
                default: false
            },
            disableDeleteButton: {
                type: Boolean,
                default: false
            },
            hideDeleteButton: {
                type: Boolean,
                default: false
            },
            hideAddButton: {
                type: Boolean,
                default: false
            },
            hideApproval: {
                type: Boolean,
                default: false
            },
            history: {
                type: Boolean,
                default: false
            },
            hideAddInvoice: {
                type: Boolean,
                default: false
            },
			itemsOptions: {
				type: Object,
				default: {
					filter: {
						approved: '0,3'
					},
					filterType: {
						approved: 'btw'
					},
					sortBy: ["payment_date"],
					sortDesc: ["desc"],
				}
			}
        },
        components: {
            'payment-item': 'url:ui/bom/purchasing/payment_item.vue',
            'complete-po': 'url:ui/bom/purchasing/complete_po.vue'
        },
        data: function () {
            return {
                name: 'Payment',
                processData: {},
                itemKey: 'id',
                url: 'bom/payment',
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
                        "text": "Payment",
                        "value": "payment_no",
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
                        "filter": true,
                        "groupable": false,
                        "url": "",
                        "sea