<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :data="data" :item-height-as-min-height="itemHeightAsMinHeight" :table-fill="tableFill" :table-fixed-header="tableFixedHeader" export-excel :hide-delete-button="hideDeleteButton" :hide-add-button="hideAddButton" :items-options="itemsOptions" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" table-only>
            
			<template v-slot:item.po_details="props">
				<b>PO No:</b> {{props.item.po_no}}<br/>
				<b>PO Date:</b> {{props.item.po_date}}<br/>
				<b>Department:</b> {{props.item.dept_name}}<br/>
				<b>Order Type:</b> {{props.item.order_type}}<br/>
				<b>Created By:</b> {{props.item.created_by_name}}<br/>
				<b>Created Date:</b> {{modifDate(props.item.created_date, props.item)}}
			</template>
            <template v-slot:item.shipping="props">
				<div style="">
					<b>Despatch:</b> {{props.item.despatch}}<br />
					<b>Shipping Address:</b> <v-truncate :text="props.item.shipping_address"></v-truncate>
				</div>
            </template>
            <template v-slot:item.payment="props">
				<div style="white-space: nowrap;">
					<b>Currency:</b> {{props.item.currency_desc}}<br />
					<b>Exchange Rate:</b> {{Number(props.item.exchange_rate).format(2,3)}}<br />
					<b>Rate Date:</b> {{props.item.rate_date}}<br />
					<b>PO Charge:</b> {{props.item.charge1}}<br />
					<b>Other Charge:</b> {{props.item.other_charge}}<br />
					<b>Discount:</b> {{props.item.discount}}<br />
					<b>Payment Term:</b> {{props.item.payment_term}}
				</div>
            </template>
             <template v-slot:item.internal_reference="props">
				<div style="">
					<b>Ref. Offer No:</b> <br />{{props.item.ref_offer_no}}<br />
					<b>Ref. Internal BMT:</b> <br />{{props.item.ref_internal_bmt}}<br/><br />
					<b>Final Quote URL:</b> <a :href="props.item.final_quote_url" v-if="props.item.final_quote_url" target="_blank">Open Link</a><span v-else>-</span><br />
					<b>Signed PO URL:</b> <a :href="props.item.signed_po_url" v-if="props.item.signed_po_url" target="_blank">Open Link</a><span v-else>-</span><br />
					<b>Signed PR URL:</b> <a :href="props.item.signed_pr_url" v-if="props.item.signed_pr_url" target="_blank">Open Link</a><span v-else>-</span>
				</div>
            </template>
			<template v-slot:item.currency_desc="props">
				<b>Currency:</b> {{props.item.currency}}<br/>
				<b>Exchange Rate:</b> {{Number(props.item.exchange_rate).format(2,3)}}<br/>
				<b>Rate Date:</b> {{props.item.rate_date}}
			</template>
			<template v-slot:item.supplier="props">
				<b>Supplier:</b> {{props.item.supplier_name}}<br/>
				<b>Promised Delivery Date:</b> {{props.item.promised_delivery_date}}
			</template>
			<template v-slot:item.final_quote_url="props">
				<b>Final Quote URL:</b> <a :href="props.item.final_quote_url" v-if="props.item.final_quote_url" target="_blank">Open Link</a><span v-else>-</span><br/>
				<b>Signed PO URL:</b> <a :href="props.item.signed_po_url" v-if="props.item.signed_po_url" target="_blank">Open Link</a><span v-else>-</span><br />
				<b>Signed PR URL:</b> <a :href="props.item.signed_pr_url" v-if="props.item.signed_pr_url" target="_blank">Open Link</a><span v-else>-</span>
			</template>
			<template v-slot:item.approved="props">
				{{approvedStatus(props.item.approved, props.item.new_po_no)}}
			</template>
			<template v-slot:item.charge1="props">
				<b>Charge 1:</b> {{Number(props.item.charge1).format(2,3)}}<br/>
				<b>Charge 1 Desc:</b> {{props.item.charge1_desc}}<br/>
				<b>Charge 2:</b> {{Number(props.item.charge2).format(2,3)}}<br/>
				<b>Charge 2 Desc:</b> {{props.item.charge2_desc}}<br/>
				<b>Discount:</b> {{Number(props.item.discount).format(2,3)}}<br/>
			</template>
			<template v-slot:item.comment="props">
				{{props.item.comment}}<br/>
				Created by {{props.item.comment_creator}}
			</template>
			<template v-slot:item.price_per_item="props">
				<div style="white-space: nowrap;">
				{{props.item.currency}} {{Number(props.item.price_per_item).format(2,3)}}<br />
				</div>
            </template>
            <template v-slot:item.approval_history="props">
				<b>Approved 1 By:</b> {{props.item.approved_by_name}}<br/>
				<b>Approved 1 Date:</b> {{props.item.approval_date}}<br/>
				<b>Approved 2 By:</b> {{props.item.approved2_by_name}}<br/>
				<b>Approved 2 Date:</b> {{props.item.approval_2_date}}<br/>
				<b>Rejected By:</b> {{props.item.rejected_by_name}}<br/>
				<b>Rejected Date:</b> {{props.item.rejected_date}}<br/>
			</template>
			<!-- <template v-slot:item.charge2="props">
				{{Number(props.item.charge2).format(3,3)}}
			</template> -->
        </v-template>
    </v-container>
</template>

<style scoped>
	.v-data-table__wrapper > table > tbody > tr > td {
		font-size: .775rem;
	}
</style>

<script>
    module.exports = {
        name: 'po',
        props: {
            value: undefined,
            data: {
                type: Object
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
				}
			},
            tableFixedHeader: {
                type: Boolean,
                default: true,
            },
            itemHeightAsMinHeight: {
                type: Boolean,
                default: false,
            },
            tableFill: {
                type: Boolean,
                default: true,
            },
        },
        components: {
            /* 'complete-po': 'url:ui/bom/purchasing/complete_po.vue' */
        },
        data: function() {
            return {
				revisi_note: "",
				dialogNote: false,
            	ccItems: [],
            	cc: "",
            	to: "",
				revData: {},
            	ccSelected: [],
            	toSelected: [],
				disableDelete: false,
				disableEdit: false,
				dialogRevisiHistory: false,
                name: 'Purchase Order',
                txtApproval: 'Ask Approval',
                processData: {},
                dialogEmail: false,
                itemKey: 'id',
                url: 'bom/poitem',
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
                        "default": new Date().formatDate('YYYY-MM-DD'),
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
                        "sortable": true,
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
                    }, {
                        "text": "Item Price",
                        "value": "price_per_item",
                        "align": "center",
                        "sortable": true,
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
                    },  {
                        "text": "Status",
                        "value": "approved",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "text",
                        "disabled": false,
                        "visible": true,
                        "required": false,
                        "form": false,
                        "filter": true,
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
                        "url": App.url+"bom/department",
                        "searchby": ['id'],
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
					/*{
                        text: 'Internal Reference',
                        value: 'ref_offer_no',
						"type": "text",
                        form: false,
                        visible: true
                    },*/
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
                        "form": true,
                        "filter": true,
                        "groupable": false,
                    },{
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
                        "text": "Supplier",
                        "value": "supplier_id",
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
						"data_value": [],
                        "filter": true,
                        "groupable": false,
                        "url": App.url+"bom/supplier",
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
                    },
                    {
                        "text": "Promised Delivery Date",
                        "value": "promised_delivery_date",
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
                        "data_value": ["IDR", "CNY", "EUR", "USD"],
                        "input": function(data){
                            var self = App.page()
                            if(data.data.trim().toLowerCase()!='idr'){
                                self.headersObj.exchange_rate.required = true
                                self.headersObj.rate_date.required = true
                            }
                            else{
                                self.headersObj.exchange_rate.required = false
                                self.headersObj.rate_date.required = false
                            }

                            self.headers = App.updateObject(self.headers)
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
                        text: 'Shipping',
                        value: 'shipping',
						"type": "text",
                        form: false,
                        visible: true
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
                        "url": App.url+"bom/supplier",
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
                        "data_value": ["IDR", "CNY", "EUR", "USD"],
                        "input": function(data){
                            var self = App.page()
                            if(data.data.trim().toLowerCase()!='idr'){
                                self.headersObj.exchange_rate.required = true
                                self.headersObj.rate_date.required = true
                            }
                            else{
                                self.headersObj.exchange_rate.required = false
                                self.headersObj.rate_date.required = false
                            }

                            self.headers = App.updateObject(self.headers)
                        }
                    },
                    {
                        "text": "Charge",
                        "value": "charge1",
                        "align": "start",
                        "sortable": true,
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
                        value: 'approval_2_date',
                        form: false,
                        visible: false,
						"alias": "approval_2_date"
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
                    }
                ],
                dialogItem: false,
                dialogItemGroup: false,
                dialogReport: false,
                selected: false,
				dialogComplete: false,
                dataid: {},
				defaultForm: []
            }
        },
		watch: {
			dialogItemGroup: function(val){
				if(!val)
					this.$refs.template.getItems()
			},
			dialogNote: function(val){
				if(val==false)
					this.revisi_note = ""
			}
		},
        computed: {
            headersObj: function(){
                var tmp = {}, self = this
                Object.keys(self.headers).map(key=>{
                    var val = self.headers[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
			allowRevisi: function(){
				var self = this
				
				if(!self.selected)
					return true
				if(self.selected.approved < 3)
					return true
				return false
			},
			allowRevisiHistory: function(){
				var self = this
				
				if(!self.selected)
					return false
				if(self.selected.po_no.match(/-rev/i)!=null)
					return true
				return false
			},
			allowPrint: function(){
				var self = this
				if(self.selected.new_po_no!=null){
					if(self.selected.new_po_no.trim()!='')
						return true
				}
				if(!self.selected)
					return true
				if(self.selected.approved == -1)
					return true
				/* if(self.selected.approved == 2)
					return false */
				return false
			},
			allowPrint2: function(){
				var self = this
				if(!self.selected)
					return true
				if(self.selected.approved == 2)
					return false
				return true
			},
			allowAskApproval: function(){
				var self = this
				if(self.selected.item_count < 1)
					return true
				if(self.selected.approved < 0)
					return true
				if(!self.selected)
					return true
				if(self.selected.approved == 0 || self.selected.approved == -1)
					return false
				return true
			},
        },
        methods: {
            modifDate: function(date, item){
                date = new Date(date)
                //if([3,8,59,11].includes(item.supplier_id) && Number(date.getDate())<15)
                    //date.setDate(15)
                return date.formatDate('YYYY-MM-DD HH:mm:ss')
            },
			onSelectedRow: function(val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                    self.processData = {}
                    self.dataid = {}
					self.disableDelete = false
					//self.disableEdit = false
                } else {
                    self.selected = val
					var email = (val.email||"").split(';').map(val=>{
						return val.trim()
					}).filter(val=>{
						return val.trim()!=''
					})
					self.toSelected = [email[0]]
					self.ccSelected = email.slice(1)
					if(App.userData.data[0].email)
						self.ccSelected.push(App.userData.data[0].email)
                    self.processData = {
                        purchase_order_id: val.id,
                        supplier_id: val.supplier_id,
                        currency: val.currency,
                        charge1: val.charge1,
                        charge2: val.charge2,
                    }
					
					self.txtApproval = 'Ask Approval'
					if(val.approved == 0)
						self.txtApproval = 'Ask Approval'
					/* if(val.approved == 2)
						self.txtApproval = 'Ask Approval 2' */
					if(val.approved == 2 || val.approved == 1){
						self.disableDelete = true
						//self.disableEdit = true
					}
					else{
						self.disableDelete = false
						//self.disableEdit = false
					}
					if(val.approved == 1){
						self.disableEdit = true
					}
					if(val.approved < 0){
						self.disableEdit = true
					}
					if(val.approved >= 2){
						self.disableEdit = false
					}
                    self.dataid = {
                        purchase_order_id: val.id,
                    }
                    self.revData = {
						filter: {
							purchase_order_id: val.id,
							flag: 1
						}
                    }
                }
            },
            onSelectedRowAll: function(val) {
                var self = this
                self.selectedAll = val
            },
			approvedStatus: function(f, new_po_no){
				if(new_po_no!=null){
					if(new_po_no.trim()!='')
						return 'Revised Final PO'
				}
				if(f == 0){
					return 'New'
				}
				if(f == 1){
					return 'Asking for approval 1'
				}
				if(f == 2){
					return 'Asking for approval 2'
				}
				if(f == 3){
					return 'Approval 2 approved'
				}
				/* if(f == 4){
					return 'Approval 2 approved'
				} */
				if(f == -1){
					return 'Rejected'
				}
			},
			addCC: function(){
				let regex = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}')
				if(regex.test(this.cc)){
					this.ccSelected.push(this.cc)
					this.cc = ''
				}
				else{
					App.errorMsg("Email address invalid!")
				}
			},
			addTo: function(){
				let regex = new RegExp('[a-z0-9]+@[a-z]+\.[a-z]{2,3}')
				if(regex.test(this.to)){
					this.toSelected.push(this.to)
					this.to = ''
				}
				else{
					App.errorMsg("Email address invalid!")
				}
			}
        },
        mounted: function() {
			var self = this
			self.defaultForm = JSONfn.parse(JSONfn.stringify(self.headers))
			if(self.history){
			    self.headersObj.approval_history.visible=true
				self.headersObj.new_po_no.visible=true
			}

			self.itemsOptions = {
				filter: {
					item_id: self.data.item_id
				}
			}
        }
    }
</script>