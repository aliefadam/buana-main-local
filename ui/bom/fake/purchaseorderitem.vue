<template>
    <v-container
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template hide-add-button @after-save="onAfterSave" @open-add="onOpenAdd" @close-add="onCloseAdd" :data="data" :items-options="itemsOptions" @after-get-items="afterGetItems" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll">
            <template v-slot:menu-after-filter>
                <!-- <v-btn color="primary" outlined small @click="dialogItem = true">
                    <v-icon small left>mdi-plus</v-icon>Add BOM
                </v-btn> -->
                <!-- <v-btn color="primary" outlined small @click="dialogCharge = true">
                    <v-icon small left>mdi-plus</v-icon>Add Charge
                </v-btn> -->
                <!-- <v-btn color="warning" :disabled="!selected" outlined small @click="dialogSubledger=true">
                    <v-icon small left>mdi-pencil</v-icon>Edit Subledger
                </v-btn> -->
            </template>
            <!-- <template v-slot:prepend-body>
                <div style="font-weight: bold">
                    Total Item Price: {{total_item_price}}<br/>
                    Grand Total Price: {{grand_total_price}}
                </div>
            </template> -->
			<template v-slot:item.price_per_item="props">
				{{parseFloat(props.item.price_per_item).format(2,3)}}
			</template>
			<template v-slot:item.total_price_per_item="props">
				{{parseFloat(props.item.total_price_per_item).format(2,3)}}
			</template>
        </v-template>
        <v-action-dialog v-model="dialogItem" title="Purchase Order Item" min-height="75%" @save="addBom">
            <v-autoform v-model="formBom"></v-autoform>
        </v-action-dialog>
        <v-action-dialog v-model="dialogCharge" title="Purchase Order Item" min-height="75%" @save="addCharge">
            <v-autoform v-model="formCharge"></v-autoform>
        </v-action-dialog>
        <v-action-dialog v-model="dialogSubledger" title="Edit Subledger" min-height="75%" @save="saveSubledger">
            <v-autoform v-model="formSubledger"></v-autoform>
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'pcitem',
        props: {
            value: undefined,
            data: {
                type: Object
            },
            sel: {
                type: Object
            },
            tableOnly: {
                type: Boolean,
                default: false
            },
			itemsOptions: {
				type: Object,
				default: {
					filter: {},
					filterType: {},
					sortBy: ['order_no','id'],
					sortDesc: [false, false]
				}
			}
        },
        components: {},
        data: function () {
            return {
                name: 'Purchase Order',
                processData: {},
                itemKey: 'id',
                url: 'fake/purchase_order_item',
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
                        "text": "BOM/PR",
                        "value": "bom_pr",
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
                        "form": false,
                        "filter": true,
                        "groupable": false,
                        "url": App.url+"bom/bom_header",
                        "searchby": ['project_name'],
                        "formatter": ['id', 'project_name'],
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
                        "text": "Item No",
                        "value": "item_id",
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
                        "form": false,
                        "filter": true,
                        "groupable": false,
                        "alias": "item_no",
                        "url": App.url+"bom/item",
                        "searchby": ['full'],
                        "formatter": ['id', 'full'],
                        "options": {
                            "filter": {
								is_active: 1
							},
                            "filterType": {},
                            "filterCondition": {}
                        },
                        "paging": true,
                        "page": "1",
                        "limit": "10",
						"alias": "item_no"
                    },
                    {
                        "text": "Item Name",
                        "value": "item_name",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "varchar",
                        "disabled": true,
                        "visible": true,
                        "required": false,
                        "form": false,
                        "filter": true,
                        "groupable": false,
                        
                    },
                    {
                        "text": "Original Manufacture",
                        "value": "original_manufacture",
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
                    },
                    {
                        "text": "Manufacture PN",
                        "value": "manufacture_pn",
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
                        
                    },
                    {
                        "text": "Specification",
                        "value": "specification",
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
                        
                    }, {
                        "text": "Unit",
                        "value": "unit",
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
                        
                    },
                    {
                        "text": "Order Qty",
                        "value": "order_qty",
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
                        "form": true,
                        "filter": true,
                        "groupable": false,
                        
                    },
                    {
                        "text": "Allocation",
                        "value": "allocation",
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
                        "form": true,
                        "filter": true,
                        "groupable": false,
                        "data_value": ["NI", "NIS", "SC"]
                    },
                    {
                        "text": "Subledger",
                        "value": "subledger_id",
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
                        "type": "varchar",
                        "disabled": false,
                        "visible": true,
                        "required": false,
                        "form": false,
                        "filter": true,
                        "groupable": false,
                        
                    },
                    {
                        "text": "Price per Item",
                        "value": "price_per_item",
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
                        "precision": "4",
                        "page": "0",
                        "limit": "10"
                    },
                    {
                        "text": "Total Price per Item",
                        "value": "total_price_per_item",
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
                        
                    }
                ],
                formBom: [{
                        "text": "BOM",
                        "value": "bom_id",
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
                        "url": App.url+"bom/bom_header",
                        "searchby": ['project_name'],
                        "formatter": ['id', 'project_name'],
                        "options": {
                            "filter": {
                                "supplier_id": App.$get('po').selected.supplier_id,
                                "status": 'Final'
                            },
                            "filterType": {},
                            "filterCondition": {}
                        },
                        "paging": true,
                        "page": "1",
                        "limit": "10"
                }],
                formCharge: [{
                        "text": "Charge1",
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
                        "form": true,
                        "filter": true,
                        "groupable": false,
                }, {
                        "text": "Charge2",
                        "value": "charge2",
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
                        "form": true,
                        "filter": true,
                        "groupable": false,
                       
                }],
                dialogItem: false,
                dialogCharge: false,
                dialogSubledger: false,
                selected: false,
                total_item_price: 0,
                grand_total_price: 0,
				formSubledger: [{
					"text": "PR",
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
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": App.url+"bom/pr",
					"searchby": ['pr_no', 'pr_subject'],
					"formatter": ['id', ['pr_no', 'pr_subject']],
					"options": {
						"filter":{
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
					"input": function(data){
						var self= App.$get('pcitem')
						if(data.data)
							self.subledgerObj.subledger_id.form = true
						else
							self.subledgerObj.subledger_id.form = false
						self.subledgerObj.subledger_id.options.filter = {
							pr_id: data.data,
							item_id: self.selected.item_id
						}
						self.subledgerObj.subledger_id.data = null
						self.subledgerObj.subledger_id.update = null
						self.formSubledger = App.updateObject(self.formSubledger)
					}
                }, {
					"text": "Subledger",
					"value": "subledger_id",
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
					"required": true,
					"form": false,
					"filter": true,
					"data": null,
					"groupable": false,
					"url": App.url+"bom/prsubledger",
					"searchby": ['text'],
					"formatter": ['id', 'text'],
					"options": {
						"filter":{
						},
						"filterType": {
						},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}]
            }
        },
        computed: {
            subledgerObj: function(){
                var tmp = {}, self = this
                Object.keys(self.formSubledger).map(key=>{
                    var val = self.formSubledger[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            headersObj: function(){
                var tmp = {}, self = this
                Object.keys(self.headers).map(key=>{
                    var val = self.headers[key]
                    tmp[val.value] = val
                })
                return tmp;
            }
        },
        methods: {
			saveSubledger: async function(){
                var self = this
				try{
                    var res = await axios.put(App.url+self.url,{
                        pk: self.selected.id,
						subledger_id: self.subledgerObj.subledger_id.data
                    })
                    if(!res.data.status) throw res.data
                    App.successMsg()
                    self.$refs.template.getItems()
                    self.dialogItem = false
                }
                catch(e){
                    App.errorMsg(e)
                }
                self.dialogSubledger=false
			},
            onSelectedRow: function (val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                    self.processData = {}
                } else {
                    self.selected = val
					if(val.bom_id!=null)
						self.headersObj.allocation.form = false
					else
						self.headersObj.allocation.form = true
                    self.processData = {
                        purchase_order_id: val.id
                    }
                }
            },
            onSelectedRowAll: function (val) {
                var self = this
                self.selectedAll = val
            },
            addBom: async function(){
                var self = this
                try{
                    var res = await axios.post(App.url+self.url+"/addbom",{
                        bom_id: self.formBom[0].data,
                        purchase_order_id: self.sel.purchase_order_id,
                        currency: self.sel.currency
                    })
                    if(!res.data.status) throw res.data
                    App.successMsg()
                    self.$refs.template.getItems()
                    self.dialogItem = false
                }
                catch(e){
                    App.errorMsg(e)
                }
            },
            addCharge: async function(){
                var self = this
                try{
                    var res = await axios.post(App.url+"fake/purchaseorder/addcharge",{
                        charge1: self.formCharge[0].data,
                        charge2: self.formCharge[1].data,
                        purchase_order_id: self.sel.purchase_order_id
                    })
                    if(!res.data.status) throw res.data
                    App.successMsg()
                    self.$refs.template.getItems()
                    App.$get('po').$refs.template.getItems()
                    self.dialogCharge = false
                }
                catch(e){
                    App.errorMsg(e)
                }
            },
            afterGetItems: function(val){
                var self = this
                var tmp = 0;
				
				val.data.map(function(v){
					v.order_qty = numberFormat(v.order_qty, 2)
					v.price_per_item = numberFormat(v.price_per_item, 2)
				})
                if(val.data[0]){
                    self.total_item_price = val.data[0].total_item_price
                    self.grand_total_price = val.data[0].grand_total
                }
                else{
                    self.total_item_price = 0
                    self.grand_total_price = 0
                }
            },
            onOpenAdd: function(){
                var self = this
                self.$nextTick(()=>{
                    setTimeout(()=>{
                        var tmp = {}
                        self.$refs.template.formModelAdd.map(val=>{
                            tmp[val.value] = val
                        })
                        tmp.item_id.form = true
                        tmp.item_id.required = true
                        tmp.order_qty.form = true
                        tmp.order_qty.required = true
                        tmp.currency.form = true
                        tmp.currency.required = true
                        tmp.currency.readonly = true
                        tmp.allocation.form = true
                        tmp.currency.data = self.sel.currency
                    }, 100)
                })
            },
            onCloseAdd: function(){
                var self = this
                self.$nextTick(()=>{
					self.$emit('close-add')
                    setTimeout(()=>{
                        var tmp = {}
                        self.$refs.template.formModelAdd.map(val=>{
                            tmp[val.value] = val
                        })
                        tmp.item_id.form = false
                        tmp.item_id.required = false
                        tmp.order_qty.form = false
                        tmp.order_qty.required = false
                        tmp.currency.form = false
                        tmp.currency.required = false
                    }, 100)
                })
            },
			onAfterSave: function(){
				this.$emit('after-save')
			}
        },
        mounted: function () {
            var self = this

            self.formCharge[0].data = self.sel.charge1
            self.formCharge[1].data = self.sel.charge2
			if(self.sel)
				self.formBom[0].options = {
					"filter": {
						"supplier_id": self.sel.supplier_id
					},
					"filterType": {},
					"filterCondition": {}
				}
        }
    }
</script>