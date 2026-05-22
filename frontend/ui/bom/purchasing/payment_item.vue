<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :item-height-as-min-height="itemHeightAsMinHeight" :table-fill="tableFill" :table-fixed-header="tableFixedHeader" :show-expand="showExpand" :single-expand="singleExpand" :data="data" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly">
			<template v-slot:item.invoice_total_price="props">
				{{Number(props.item.invoice_total_price).format(2,3)}}
			</template>
			<template v-slot:item.invoice_id="props">
				<b>Invoice no:</b> {{props.item.invoice_no}}<br/>
				<b>Invoice date:</b> {{props.item.invoice_date}}
			</template>
			<template v-slot:item.currency="props">
				<b>Currency:</b> {{props.item.currency}}<br/>
				<b>Total Price:</b> {{Number(props.item.invoice_total_price).format(2,3)}}
			</template>
			<template v-slot:item.title="props">
				<span>{{props.item.as_reference == 0 ? props.item.title : props.item.uraian}}</span>
			</template>
			<template v-slot:item.bank_account_no="props">
				<b>Bank name:</b> {{props.item.bank}}<br/>
				<b>Account no/IBAN:</b> {{props.item.bank_account_no}}<br/>
				<b>Account name:</b> {{props.item.bank_account_name}}<br/>
				<b>BIC/Swift Code:</b> {{props.item.bic_swift_code}}
			</template>
			<template v-slot:item.proof_url="props">
				<a :href="props.item.proof_url" v-if="props.item.proof_url" target="_blank">Open Link</a><span v-else>-</span>
			</template>
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
            showExpand: {
                type: Boolean,
                default: false
            },
            singleExpand: {
                type: Boolean,
                default: false
            },
			tableFixedHeader: {
                type: Boolean,
                default: true
            },
            itemHeightAsMinHeight: {
                type: Boolean,
                default: false
            },
            tableFill: {
                type: Boolean,
                default: true
            },
        },
        data: function() {
            return {
                name: 'Payment Item',
                itemKey: 'id',
                url: 'bom/paymentitem',
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
                    },{
                        "text": "payment id",
                        "value": "payment_id",
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
                    },
                    {
                        "text": "Invoice",
                        "value": "invoice_id",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "list",
						"data_value": [],
                        "disabled": false,
                        "visible": true,
                        "required": false,
                        "form": true,
                        "filter": true,
                        "groupable": false,
                        "url": App.url+"bom/invoice",
                        "searchby": ["invoice_no"],
                        "formatter": ["id", "invoice_no"],
                        "options": {
                            "filter": {
                                "payment_id": null
                            },
                            "filterType": {
                                "payment_id": "isnull"
                            },
                            "filterCondition": {}
                        },
                        "paging": true,
                        "page": "1",
                        "limit": "10",
                        "alias": "invoice_no"
                    },{
						"text": "Title",
                        "value": "title",
						"type": "varchar",
						"form": false
					}, 
                    {
						"text": "invoice date",
                        "value": "invoice_date",
						"type": "varchar",
						"form": false,
						"visible": false
					},{
						"text": "Supplier",
                        "value": "supplier_name",
						"type": "varchar",
						"form": false
					},
					{
						"text": "Bank Info",
                        "value": "bank_account_no",
						"type": "varchar",
						"form": false
					}, {
						"text": "Amount",
                        "value": "currency",
						"type": "varchar",
						"form": false
					}, {
						"text": "account name",
                        "value": "bank_account_name",
						"type": "varchar",
						"form": false,
						"visible": false
					},  {
						"text": "invoice total price",
                        "value": "invoice_total_price",
						"type": "varchar",
						"form": false,
						"visible": false
					}, {
                        "text": "proof url",
                        "value": "proof_url",
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
						"text": "Invoice payment notes",
                        "value": "invoice_payment_notes",
						"type": "text",
						"form": true,
                        "visible": true,
                        "filter": false,
					}
                ],
				selected: false,
				selectedAll: false,
            }
        },
        methods: {
            onSelectedRow: function(val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                } else {
                    self.selected = val
                }
            },
            onSelectedRowAll: function(val) {
                var self = this
                self.selectedAll = val
            },
        },
        mounted: function() {
			console.log(this.data)
        }
    }
</script>