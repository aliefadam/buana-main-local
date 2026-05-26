<template>
    <v-container
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template hide-add-button :data="data" :items-options="itemsOptions" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" :table-only="tableOnly">
            <template v-slot:menu-after-filter>
				<v-btn small color="primary" outlined @click="dialogStock=true" :disabled="!allowInsert">Insert Into Stock</v-btn>
			</template>
            <template v-slot:title-body>
                Available Item
            </template>
			<template v-slot:item.price_per_item="props">
				{{props.item.currency}} {{parseFloat(props.item.price_per_item).format(2,3)}}
			</template>
			<template v-slot:item.total_price_per_item="props">
				{{props.item.currency}} {{parseFloat(props.item.total_price_per_item).format(2,3)}}
			</template>
			<template v-slot:item.qty="props">
				{{parseFloat(props.item.qty).format(2,3)}}
			</template>
        </v-template>
        <v-action-dialog v-model="dialogStock" title="Purchase Order Item" min-height="75%" @save="saveStock">
            <v-autoform v-model="stock"></v-autoform>
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'completeitem',
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
                default: true
            },
			itemsOptions: {
				type: Object,
				default: {
					filter: {},
					filterType: {},
					sortDesc: [false]
				}
			}
        },
        components: {},
        data: function () {
            return {
                name: 'Available Item',
                processData: {},
                itemKey: 'id',
                url: 'bom/pocompleteitem',
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
                    },{
                        "text": "SPB No",
                        "value": "spb_id",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "list",
                        "disabled": false,
                        "readonly": true,
                        "visible": false,
                        "required": false,
                        "form": false,
                        "filter": false,
                        "groupable": false,
                        "url": App.url+'warehouse/spb',
                        "searchby": ["id"],
                        "formatter": ["id", "spb_no"],
                        "options": {
                            "filter": {},
                            "filterType": {},
                            "filterCondition": {}
                        },
                        "paging": true,
                        "page": "0",
                        "limit": "10",
                    },
                    {
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
                        "visible": false,
                        "required": false,
                        "form": false,
                        "filter": false,
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
                        "url": App.url+"transaction/stock",
                        "searchby": ['item_name'],
                        "formatter": ['item_id', 'item_name'],
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
                        "disabled": false,
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
                    }, {
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
                        
                    }, {
                        "text": "Complete Qty",
                        "value": "complete_qty",
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
                    }, {
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
                        "visible": false,
                        "required": false,
                        "form": false,
                        "filter": true,
                        "groupable": false,
                        
                    }, {
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
                stock: [{
					"text": "QTY",
					"value": "qty",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "float",
					"data_value": [],
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"precision": 3
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
				}, {
					"text": "Notes",
					"value": "notes",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"data_value": [],
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
                }, {
					"text": "Photo",
					"value": "photo",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"default": "",
					"width": "auto",
					"type": "text",
					"data_value": [],
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
                }],
                dialogStock: false,
                selected: false,
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
            stockObj: function(){
                var tmp = {}, self = this
                Object.keys(self.stock).map(key=>{
                    var val = self.stock[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
			allowInsert: function(){
				var self = this
				if(!self.selected){
					return false
				}
				else{
					return Number(self.selected.order_qty) != Number(self.selected.complete_qty)
				}
			}
        },
        methods: {
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
					qty = Number(self.selected.order_qty) - Number(self.selected.complete_qty)
					self.stockObj.qty.default = qty
					self.stockObj.allocation.default = self.selected.allocation
                }
            },
            onSelectedRowAll: function (val) {
                var self = this
                self.selectedAll = val
            },
			saveStock: function(){
				var self = this
				var qty = Number(self.selected.order_qty) - Number(self.selected.complete_qty)
				if(Number(self.stockObj.qty.data) > qty){
					self.stockObj.qty.data = null
					self.$nextTick(()=>{
						self.stockObj.qty.data = qty
						self.saveStockData()
					})
				}
				else{
					self.saveStockData()
				}
				
			},
			saveStockData: async function(){
				var self = this
				var q = confirm("Insert "+self.stockObj.qty.data+" "+self.selected.item_name+" into stock?")
				if(q){
					var res = await axios.get(App.url + 'bom/payment/transferstock', {
						params: {
							po_id: self.selected.purchase_order_id,
							id: self.selected.id,
							qty: self.stockObj.qty.data,
							allocation: self.stockObj.allocation.data,
							notes: self.stockObj.notes.data,
							photo: self.stockObj.photo.data,
							spb_id: self.sel.spb_id
						}
					})
					if(!res.data.status)
						App.errorMsg()
					else
						App.successMsg()
					self.$refs.template.getItems();
					self.$emit('after-insert-stock')
				}
			},
        },
        mounted: function () {
            var self = this
        }
    }
</script>