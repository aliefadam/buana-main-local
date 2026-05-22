<template>
    <v-container
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template hide-add-button :data="data" :items-options="itemsOptions" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" :table-only="tableOnly">
            <template v-slot:menu-after-filter>
				<v-btn small color="primary" outlined @click="dialogStock=true" :disabled="!allowInsert" v-if="!isDashboard">Insert Into Stock</v-btn>
                <!-- <v-btn small color="primary" outlined @click="dialogScan=true">Scan Items</v-btn> -->
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
        <v-action-dialog v-model="dialogScan" title="Scanner Receive Page" min-height="75%" @save="saveScan">
            <!-- <v-autoform v-model="scanStock"></v-autoform> -->
            <scan-page ref="scanner"></scan-page>
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
            isDashboard: {
				type: Boolean,
				default: false
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
        components: {
                 'scan-page': 'url:ui/bom/transaction/scanner.vue'
        },
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
                scanStock: [{
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
                }],                stock: [{
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
                dialogScan: false,
                selected: false,
				scanner: false
            }
        },
		watch: {
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
			saveStock: function(sel, params){
				var self = this
				var selected = self.selected
				var stockObj = self.stockObj
				if(sel){
					if(sel.item_id){
						selected = sel
					}
				}
				var qty = Number(selected.order_qty) - Number(selected.complete_qty)
                if(params){
                    if(Number(params.qty) > qty){
                        self.$nextTick(()=>{
                            params.qty = qty
                            self.saveStockData(params)
                        })
                    }
                    else{
                        self.saveStockData(params)
                    }
                }
                else{
                    if(Number(self.stockObj.qty.data) > qty){
                        self.stockObj.qty.data = null
                        self.$nextTick(()=>{
                            self.stockObj.qty.data = qty
                            self.saveStockData(params)
                        })
                    }
                    else{
                        self.saveStockData(params)
                    }
                }
				
			},
            saveScan: async function(){
				var self = this
				var qty = Number(self.selected.order_qty) - Number(self.selected.complete_qty)
				var $scanner = self.$refs.scanner
				self.scanner = $scanner
				//checking item
                var itemType = 'po'
				var item_id = false;//$scanner.headersObj.item.data.match(/\d+$/)
                if($scanner.headersObj.item.data.match(/^\d+.\d+.1$/i) != null){
                    item_id = $scanner.headersObj.item.data.split('.')[1]
                    itemType = 'bom'
                }
                else if($scanner.headersObj.item.data.match(/^bmt.item/i) != null){
                    item_id = $scanner.headersObj.item.data.match(/\d+$/)[0]
                }
                else{
                    item_id = false
                }
				/* if($scanner.headersObj.item.data.match(/^bmt.item/i) == null && $scanner.headersObj.item.data.match(/^\d+.\d+.1$/i) == null)
					item_id = false */

				if(item_id!==false && itemType == 'po'){
					var res = await axios.get(App.url + 'bom/pocompleteitem', {
						params: {
							filter: {
								item_id: item_id,
                                purchase_order_id: App.page().selected.po_id
							},
                            filterType: {},
                            filterCondition: {},
                            limit: -1
						}
					})
					if(!res.data.status) throw res.data
					if($scanner.headersObj.item==0)
						alert("Item tidak ada di PO ini!")
					else{
						if($scanner.selected === false && res.data.data.length > 1){
							$scanner.items = res.data.data
							App.successMsg("Please select item!")
						}
						else{
							var selectedIdx = res.data.data.length == 1 ? 0 : $scanner.selected
							self.saveStock(res.data.data[selectedIdx], {
								item_id: item_id,
								barcode: $scanner.headersObj.item.data,
								item_name: res.data.data[selectedIdx].item_name,
								allocation: $scanner.headersObj.allocation.data,
								qty:  $scanner.headersObj.qty.data,
								img: $scanner.srcImg,
								id: res.data.data[selectedIdx].id
							})
						}
					}
				}    
				else if(item_id!==false && itemType == 'bom'){
                    var dataItem = $scanner.headersObj.item.data.split('.')
					var res = await axios.get(App.url + 'transaction/bomitemspb', {
						params: {
							filter: {
								item_id: item_id,
                                po_id: App.page().selected.po_id,
                                bom_header_id: dataItem[0]
							},
                            filterType: {},
                            filterCondition: {},
                            limit: -1
						}
					})
					if(!res.data.status) throw res.data
					if($scanner.headersObj.item==0)
						alert("Item tidak ada di BOM dengan PO ini!")
					else{
                        if($scanner.selected === false && res.data.data.length > 1){
							$scanner.items = res.data.data
							App.successMsg("Please select item!")
						}
						else{
							var selectedIdx = res.data.data.length == 1 ? 0 : $scanner.selected
							
                        //save item from bom
                            self.saveStock(res.data.data[selectedIdx], {
                                item_id: item_id,
                                barcode: $scanner.headersObj.item.data,
                                item_name: res.data.data[selectedIdx].part_number,
                                bom_receive_id: res.data.data[selectedIdx].bom_receive_id,
                                allocation: $scanner.headersObj.allocation.data,
                                qty:  $scanner.headersObj.qty.data,
                                img: $scanner.srcImg,
                                id: res.data.datas[selectedIdx].id
                            })
                        }
					}
				}
				else{
					alert('Item tidak sesuai format')
				}
				/* if(Number(self.stockObj.qty.data) > qty){
					self.stockObj.qty.data = null
					self.$nextTick(()=>{
						self.stockObj.qty.data = qty
						self.saveStockData()
					})
				}
				else{
					self.saveStockData()
				} */
				
			},
			saveStockData: async function(params){
				var self = this
				//console.log(params)
				if(params)
					var q = confirm("Insert "+params.qty+" "+params.item_name+" into stock?")
				else
					var q = confirm("Insert "+self.stockObj.qty.data+" "+self.selected.item_name+" into stock?")
				if(q){
					if(params){
						/* console.log({
							po_id: App.page().selected.po_id,
							id: params.id,
							qty: params.qty,
							allocation: params.allocation,
							//notes: encodeURIComponent(self.stockObj.notes.data),
							photo: params.img == false ? "" : params.img,
							spb_id: self.sel.spb_id
						}) */
                        if(params.bom_receive_id != undefined){
                            var res = await axios.post(App.url + 'bom/payment/transferstock3', {
                                po_id: App.page().selected.po_id,
                                barcode: params.barcode,
                                id: params.id,
                                item_id: params.item_id,
                                qty: params.qty,
                                allocation: params.allocation,
                                bom_receive_id: params.bom_receive_id,
                                notes: '',
                                photo: params.img == false ? "" : params.img,
                                spb_id: self.sel.spb_id
                            })
                        }
                        else{
                            var res = await axios.post(App.url + 'bom/payment/transferstock2', {
                                po_id: App.page().selected.po_id,
                                barcode: params.barcode,
                                id: params.id,
                                item_id: params.item_id,
                                qty: params.qty,
                                allocation: params.allocation,
                                notes: '',
                                photo: params.img == false ? "" : params.img,
                                spb_id: self.sel.spb_id
                            })
                        }
                        
                        /* var res = await axios.get(App.url + 'bom/payment/transferstock2', {
							params: {
                                po_id: App.page().selected.po_id,
                                id: params.id,
                                qty: params.qty,
                                allocation: params.allocation,
                                //notes: encodeURIComponent(self.stockObj.notes.data),
                                photo: params.img == false ? "" : params.img,
                                spb_id: self.sel.spb_id
                            }
						}) */
					}
					else{
						var res = await axios.get(App.url + 'bom/payment/transferstock', {
							params: {
								po_id: self.selected.purchase_order_id,
								id: self.selected.id,
								qty: self.stockObj.qty.data,
								complete_qty: self.selected.complete_qty,
								allocation: self.stockObj.allocation.data,
								notes: encodeURIComponent(self.stockObj.notes.data),
								photo: encodeURIComponent(self.stockObj.photo.data),
								spb_id: self.sel.spb_id
							}
						})
					}
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
            if(self.isDashboard){
                self.headersObj.price_per_item.visible=false
                self.headersObj.total_price_per_item.visible=false
            }
        }
    }
</script>