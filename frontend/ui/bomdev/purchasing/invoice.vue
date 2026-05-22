<template>
    <v-container
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :disable-edit-button="disableEditButton" :disable-delete-button="disableDeleteButton" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" :table-only="tableOnly">
            <template v-slot:menu-after-filter>
                <v-btn color="primary" outlined small @click="dialogItem = true" :disabled="!selected">
                    Details
                </v-btn>
            </template>
        </v-template>
        <!-- <v-action-dialog :actions="false" v-model="dialogItem" title="Detail" min-height="75%" fullscreen :key="selected.id">
            <table style="width: 100%; height: 100%;">
                <tr>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="poInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="supplierInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="billInf" hide-details></v-autoform>
                        
                        <v-btn color="primary" outlined small @click="saveBill">
                            Save
                        </v-btn>
                    </td>
                </tr>
            </table>
        </v-action-dialog> -->
        <v-action-dialog class="long-dialog" :actions="false" v-model="dialogItem" title="Detail" min-height="75%" :key="selected.id">
			<v-card flat>
				<v-card-title primary-title>
					Supplier Information
				</v-card-title>
				<table class="table">
					<thead>
						<tr>
							<th>Supplier</th>
							<th>Bank Info</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								{{supplierfObj.supplier_name.data}}
							</td>
							<td>
								<b>Bank name:</b> {{supplierfObj.bank.data}}<br/>
								<b>Account no/IBAN:</b> {{supplierfObj.bank_account_no.data}}<br/>
								<b>Currency:</b> {{poinfObj.currency.data}}<br/>
								<b>Account name:</b> {{supplierfObj.bank_account_name.data}}<br/>
								<b>BIC/Swift Code:</b> {{supplierfObj.bic_swift_code.data}}
							</td>
						</tr>
					</tbody>
				</table>
			</v-card>
			<v-card flat>
				<v-card-title primary-title>
					Invoice Amount
				</v-card-title>
				<table class="table">
					<thead>
						<tr>
							<th>Currency</th>
							<th>Total Item Price</th>
							<th>PO Charge</th>
							<th>Grand Total Price</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								{{poinfObj.currency.data}}
							</td>
							<td>
								{{poinfObj.total_price.data}}
							</td>
							<td>
								<b>Charge 1:</b> {{poinfObj.charge1.data}}<br/>
								<b>Charge 1 Desc:</b> {{poinfObj.charge1_desc.data}}<br/>
								<b>Charge 2:</b> {{poinfObj.charge2.data}}<br/>
								<b>Charge 2 Desc:</b> {{poinfObj.charge2_desc.data}}
							</td>
							<td>
								{{poinfObj.grand_total.data}}
							</td>
						</tr>
					</tbody>
				</table>
			</v-card>
			<v-autoform v-model="billInf" hide-details></v-autoform>
			<div class="flex">
				<v-spacer></v-spacer>
				<v-btn color="primary" outlined small @click="saveBill">
					Save
				</v-btn>
			</div>
            <!-- <table style="width: 100%; height: 100%;">
                <tr>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="poInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="supplierInf" hide-details></v-autoform>
                    </td>
                    <td style="vertical-align: top;">
                        <v-autoform v-model="billInf" hide-details></v-autoform>
                        
                        <v-btn color="primary" outlined small @click="saveBill">
                            Save
                        </v-btn>
                    </td>
                </tr>
            </table> -->
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
	.v-data-table__wrapper > table > tbody > tr > td {
		font-size: .775rem;
	}
</style>
<style>
	.v-data-table__wrapper > table > tbody > tr > td {
		font-size: .775rem;
	}
	table.table {
        border: 0px;
        border-collapse: collapse;
		font-size: 12px;
		width: 100%;
    }

    table.table tr th {
        border-bottom: 1px solid #C5C5C5;
        margin-bottom: 4px;
		background-color: #DDEBF6;
    }

    table.table tr:last-child th {
        height: 4px;
    }

    table.table th>div {
        padding: 0 4px;
        border-radius: 3px;
        text-align: center;
        white-space: nowrap;
    }

    table.table td>div {
        padding: 0 4px;
        border-radius: 3px;
        white-space: nowrap;
    }

    table.table td {
        padding: 1.5px;
    }
	table.table td,
	table.table th{
		padding: 2px 4px;
		border: 1px solid black !important;
		min-width: 40px;
	}

	table.table td.table-title{
		font-weight: bold;
		text-align: center;
	}
</style>

<script>
    module.exports = {
        name: 'invoice',
        props: {
            value: undefined,
            data: {
                type: Object
            },
            tableOnly: {
                type: Boolean,
                default: false
            },
            disableDeleteButton: {
                type: Boolean,
                default: false
            },
            disableEditButton: {
                type: Boolean,
                default: false
            }
        },
        components: {
            /* 'purchase-item': App.ui+'purchasing/purchaseorderitem.vue' */
        },
        data: function () {
            return {
                name: 'Invoice',
                processData: {},
                itemKey: 'id',
                url: App.folderRoot+'invoice',
                billInf: [{
                    "text": "Payment %",
                    "value": "payment_pct",
                    "readonly": false,
                    "type": "float",
					"default": 100,
					"input": function(data){
						try {
							var self = App.$get('invoice')
							self.$nextTick(function(){
								if(Number(data.data) > 100)
									self.billfObj.payment_pct.data = 100
								if(Number(data.data) < 0)
									self.billfObj.payment_pct.data = 0
								self.billfObj.invoice_total_price.data = Number(self.selected.grand_total_price) * Number(self.billfObj.payment_pct.data/100) + Number(self.billfObj.invoice_charge.data || 0) - Number(self.billfObj.invoice_reduction.data || 0)
								self.billInf = App.updateObject(self.billInf)
							})
						} catch (err) {
							console.log(err)
						}
					}
                },{
                    "text": "Invoice Charge",
                    "value": "invoice_charge",
                    "readonly": false,
                    "type": "float",
					"input": function(data){
						var self = App.$get('invoice')
						self.$nextTick(function(){
							self.billfObj.invoice_total_price.data = Number(self.selected.grand_total_price) * (Number(self.billfObj.payment_pct.data)/100) + Number(self.billfObj.invoice_charge.data || 0) - Number(self.billfObj.invoice_reduction.data || 0)
							self.billInf = App.updateObject(self.billInf)
						})
					}
                }, {
                    "text": "Invoice Reduction",
                    "value": "invoice_reduction",
                    "readonly": false,
                    "type": "float",
					"input": function(data){
						var self = App.$get('invoice')
						self.$nextTick(function(){
							self.billfObj.invoice_total_price.data = Number(self.selected.grand_total_price) * (Number(self.billfObj.payment_pct.data)/100) + Number(self.billfObj.invoice_charge.data || 0) - Number(self.billfObj.invoice_reduction.data || 0)
							self.billInf = App.updateObject(self.billInf)
						})
					}
                }, {
                    "text": "Invoice notes",
                    "value": "notes",
                    "readonly": false,
                    "type": "text"
                }, {
                    "text": "Invoice total price",
                    "value": "invoice_total_price",
                    "readonly": true,
                    "type": "float"
                }],
                supplierInf: [{
                    "text": "Supplier ID",
                    "value": "supplier_id",
                    "readonly": true,
                    "type": "varchar"
                }, {
                    "text": "Supplier Name",
                    "value": "supplier_name",
                    "readonly": true,
                    "type": "varchar"
                }, {
                    "text": "Bank Name",
                    "value": "bank",
                    "readonly": true,
                    "type": "varchar"
                }, {
                    "text": "Account No/IBAN",
                    "value": "bank_account_no",
                    "readonly": true,
                    "type": "varchar"
                }, {
                    "text": "Currency",
                    "value": "supplier_currency",
                    "readonly": true,
                    "type": "varchar"
                }, {
                    "text": "Account Name",
                    "value": "bank_account_name",
                    "readonly": true,
                    "type": "varchar"
                }, {
                    "text": "BIC/SWIFT Code",
                    "value": "bic_swift_code",
                    "readonly": true,
                    "type": "varchar"
                }],
                poInf: [{
                    "text": "Currency",
                    "value": "currency",
                    "readonly": true,
                    "type": "varchar"
                },{
                    "text": "Total Item Price",
                    "value": "total_price",
                    "readonly": true,
                    "type": "varchar"
                },{
                    "text": "Charge 1",
                    "value": "charge1",
                    "readonly": true,
                    "type": "varchar"
                },{
                    "text": "Charge 1 Description",
                    "value": "charge1_desc",
                    "readonly": true,
                    "type": "varchar"
                },{
                    "text": "Charge 2",
                    "value": "charge2",
                    "readonly": true,
                    "type": "varchar"
                },{
                    "text": "Charge 2 Description",
                    "value": "charge2_desc",
                    "readonly": true,
                    "type": "varchar"
                },{
                    "text": "Grand Total Price",
                    "value": "grand_total",
                    "readonly": true,
                    "type": "varchar"
                }],
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
                        "page": "1",
                        "limit": "10"
                    },
                    {
                        "text": "Invoice no",
                        "value": "invoice_no",
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
                    },
                    {
                        "text": "Invoice date",
                        "value": "invoice_date",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "date",
                        "disabled": false,
                        "visible": true,
                        "required": false,
                        "form": true,
                        "default": new Date().formatDate('YYYY-MM-DD'),
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
                    },
                    {
                        "text": "ref invoice no",
                        "value": "ref_invoice_no",
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
                    },
                    {
                        "text": "PO No",
                        "value": "po_id",
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
                        "url": App.model+"purchaseorder",
                        "searchby": ['po_no'],
                        "formatter": ['id', 'po_no'],
                        "options": {
                            "filter": {
                                "total_payment_pct": "100",
								"approved": 2
                            },
                            "filterType": {
                                "total_payment_pct": "<"
                            },
                            "filterCondition": {},
							"invoice": true
                        },
                        "paging": true,
                        "page": "1",
                        "limit": "10",
                        "alias": "po_no"
                    },
                    {
                        "text": "Invoice doc url",
                        "value": "invoice_doc_url",
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
                        "page": "1",
                        "limit": "10"
                    }, {
                        text: '',
                        value: 'data-table-expand'
                    }
                ],
                dialogItem: false,
                selected: false,
                dataid: {}
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
            poinfObj: function(){
                var tmp = {}, self = this
                Object.keys(self.poInf).map(key=>{
                    var val = self.poInf[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            supplierfObj: function(){
                var tmp = {}, self = this
                Object.keys(self.supplierInf).map(key=>{
                    var val = self.supplierInf[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            billfObj: function(){
                var tmp = {}, self = this
                Object.keys(self.billInf).map(key=>{
                    var val = self.billInf[key]
                    tmp[val.value] = val
                })
                return tmp;
            }
        },
        methods: {
            onSelectedRow: function (val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                } else {
					if(val.payment_id!==null){
						self.disableDeleteButton = true
						self.disableEditButton = true
					}
					
					if(val.payment_flag==0){
						self.disableDeleteButton = false
						self.disableEditButton = false
					}
					
					if(val.payment_approved<0){
						self.disableDeleteButton = false
						self.disableEditButton = false
					}
                    self.selected = val
                    self.poinfObj.currency.data = val.currency
                    self.poinfObj.total_price.data = Number(val.total_price).format(2, 3)
                    self.poinfObj.charge1.data = Number(val.charge1).format(2, 3)
                    self.poinfObj.charge1_desc.data = val.charge1_desc
                    self.poinfObj.charge2.data = Number(val.charge2).format(2, 3)
                    self.poinfObj.charge2_desc.data = val.charge2_desc
                    self.poinfObj.grand_total.data = Number(val.grand_total_price).format(2, 3)

                    self.supplierfObj.supplier_id.data = val.supplier_id
                    self.supplierfObj.supplier_name.data = val.supplier_name
                    self.supplierfObj.bank.data = val.bank
                    self.supplierfObj.bank_account_no.data = val.bank_account_no
                    self.supplierfObj.supplier_currency.data = val.supplier_currency
                    self.supplierfObj.bank_account_name.data = val.bank_account_name
                    self.supplierfObj.bic_swift_code.data = val.bic_swift_code

                    self.billfObj.payment_pct.data = Number(val.payment_pct).format(2)
                    self.billfObj.invoice_charge.data = numberFormat(Number(val.invoice_charge), 2)
                    self.billfObj.invoice_reduction.data = numberFormat(Number(val.invoice_reduction), 2)
                    self.billfObj.notes.data = val.notes
                    self.billfObj.invoice_total_price.data = Number(val.invoice_total_price).format(2, 3)
                }
            },
            onSelectedRowAll: function (val) {
                var self = this
                self.selectedAll = val
            },
            saveBill: async function(){
                var self = this
                try{
                    var res = await axios.post(App.url+self.url+"/savebill",{
                        id: self.selected.id,
                        payment_pct: self.billfObj.payment_pct.data,
                        invoice_charge: self.billfObj.invoice_charge.data,
                        invoice_reduction: self.billfObj.invoice_reduction.data,
                        notes: self.billfObj.notes.data,
                    })
                    if(!res.data.status) throw res.data
                    App.successMsg()
                    self.$refs.template.getItems()
					self.dialogItem = false
                }
                catch(e){
                    App.errorMsg(e)
                }
            }
        },
        mounted: function () {

        }
    }
</script>