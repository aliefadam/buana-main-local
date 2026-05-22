<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
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
            
            <template v-slot:prepend-menu>
				<v-btn small color="primary" outlined @click="bomDialog=true"><v-icon small left></v-icon>Import From BOM Receive</v-btn>
            </template>
			
            <!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
            <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
			<template v-slot:item.qty="props">
				{{parseFloat(props.item.qty).format(2,3)}}
			</template>
			<v-action-dialog @save="importBOM" v-model="bomDialog" title="BOM receive" min-height="75%" fullscreen >
				<v-autoform hide-details :cols="null" v-model="formBom" :valid.sync="valid"></v-autoform>
			</v-action-dialog>
        </v-template>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'npb_item',
        creator: '',
        components: {
            /* 'document-form': 'url:ui/ewis/test/document_form.vue' */
        },
        props: {
            value: undefined,
            data: {
                type: Object
            },
            tableOnly: {
                type: Boolean,
                default: false
            },
            tableFixedHeader: {
                type: Boolean,
                default: true
            },
            disableTable: {
                type: Boolean,
                default: false
            },
            activeColumn: {
                type: Boolean,
                default: false
            },
            showExpand: {
                type: Boolean,
                default: false
            },
            singleExpand: {
                type: Boolean,
                default: true
            },
            itemsOptions: {
                type: Object,
                default: {
                    filter: {},
                    filterType: {}
                }
            }
        },
        data: function() {
            return {
                name: 'NPB Item',
                itemKey: 'id',
                url: 'transaction/npbitem',
                loading: false,
                bomDialog: false,
                valid: false,
				formBom: [{
                        text: "BOM Receive No",
                        value: "bom_id",
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
                        form: true,
                        filter: true,
                        groupable: false,
                        url: App.url + "transaction/bom", //"bom/payment/complete",
                        searchby: ["name"],
                        formatter: ["id", "name"],
                        options: {
                            filter: {
                                
                            },
                            filterType: {},
                            filterCondition: {},
                        },
                        paging: true,
                        page: "1",
                        limit: "10"
                    }],
                headers: [{
                    "text": "Id",
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
                    "required": true,
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
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Npb Id",
                    "value": "npb_id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "int",
                    "disabled": false,
                    "visible": false,
                    "required": true,
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
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Item No",
                    "value": "item_no",
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
                    "text": "Item Name",
                    "value": "item_id",
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
                    "url": App.url + "transaction/stock",
                    "searchby": ['full'],
                    "formatter": ['item_id', 'full'],
                    "options": {
                        "filter": {
							flag: 1
						},
                        "filterType": {},
                        "filterCondition": {},
                        "distinct": true
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "input": function(data) {
                        try{
                            var self = App.$get('npb_item')
                            var reload = false
							console.log(self.headersObj.po_id.options.filter.item_id, data.data)
                            if (self.headersObj.po_id.options.filter.item_id != data.data)
                                reload = true
                            self.headersObj.po_id.options.filter.item_id = data.data
                            self.headersObj.allocation.options.filter.item_id = data.data
                            if (reload) {
                                self.headersObj.po_id.data = false
                                self.headersObj.allocation.data = false
                                self.headersObj.po_id.update = false
                                self.headersObj.allocation.update = false
                            }
                            self.headers = App.updateObject(self.headers)
                            self.checkQty()
                        }
                        catch(e){
                            console.log(e)
                        }
                    },
                    "alias": "item_name"
                }, {
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
                    }, {
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
                    }, {
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
                    "text": "PO No",
                    "value": "po_id",
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
                    "filter": false,
                    "groupable": false,
                    "url": App.url + "transaction/stock",
                    "searchby": ['po_no'],
                    "formatter": ['po_id', 'po_no'],
                    "options": {
                        "filter": {
							flag: 1
						},
                        "filterType": {},
                        "filterCondition": {},
                        "distinct": true,
                        "fields": ['po_id', 'po_no']
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "input": function(data) {
                        var self = App.$get('npb_item')
                        var reload = false
                        if (self.headersObj.allocation.options.filter.po_id != data.data)
                            reload = true
                        self.headersObj.allocation.options.filter.po_id = data.data
                        if (reload) {
                            self.headersObj.allocation.data = null
                            self.headersObj.allocation.update = null
                        }
                        self.headers = App.updateObject(self.headers)
                        self.checkQty()

                    },
                    alias: "po_no"
                }, 
                // {
                //     "text": "PO No",
                //     "value": "",
                //     "align": "start",
                //     "sortable": true,
                //     "filterable": false,
                //     "divider": false,
                //     "class": "",
                //     "width": "auto",
                //     "type": "varchar",
                //     "disabled": false,
                //     "visible": true,
                //     "required": true,
                //     "form": false,
                //     "filter": false,
                //     "groupable": false,
                //     "alias": "po_no_item"
                // }, 
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
                    "url": App.url + "transaction/stock",
                    "searchby": ['allocation'],
                    "formatter": ['allocation', 'allocation'],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {},
                        "distinct": true,
                        "fields": ['allocation']
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "input": function(data) {
                        var self = App.$get('npb_item')
                        self.checkQty()
                    }
                }, {
                    "text": "Qty",
                    "value": "qty",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "float",
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
                    "limit": "10",
					"input": function(data){
                        var self = App.$get('npb_item')
						if(Number(data.data) > self.maxQty){
							self.headersObj.qty.data = self.maxQty
                        	self.headers = App.updateObject(self.headers)
						}
					}
                }, {
                    "text": "Stock Qty",
                    "value": "stock_qty",
					"form": true,
					"type": "float",
                    "required": false,
                    "readonly": true,
                    "visible": false,
				}, {
					"text": "Subassembly",
					"value": "subassembly_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
                    "clearable": true,
					"url": App.url + "maintenance/msubassembly",
					"searchby": ["subassy"],
					"formatter": ["id", "subassy"],
					"options": {
						"filter": {
                        },
						"filterType": {},
						"filterCondition": {
                        }
					},
					"paging": true,
					"page": "1",
					"limit": "10",
                    "alias": "subassembly"
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
                }],
                //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
                selected: false,
                //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
                selectedAll: [],
                isInDom: false,
                isInViewport: false,
                maxQty: 0
            }
        },
        watch: {

        },
        computed: {
            headersObj: function() {
                var tmp = {},
                    self = this
                Object.keys(self.headers).map(key => {
                    var val = self.headers[key]
                    tmp[val.value] = val
                })
                return tmp;
            }
        },
        methods: {
			importBOM: async function(val){
				var self = this
				console.log(self.formBom[0].data, self.data)
				 try {
					var res = await axios.get(App.url+'transaction/npbitem/create_from_bom', {
						params: {
						    npb_id: self.data.npb_id,
						    bom_id: self.formBom[0].data
						}
					})
					if(res.data.status==false) 
						throw res.data
					else
						App.successMsg()
				} catch (error) {
					App.errorMsg(error)
				} 
			},
            onSelectedRow: function(val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                } else {
                    self.selected = val
					self.checkQty2()
                }
            },
            onSelectedRowAll: function(val) {
                var self = this
                self.selectedAll = val
            },
            onVisible: function(isVisible, e) {
                var self = this
                self.isInViewport = !!isVisible
                self.isInDom = !!e.target.isConnected
            },
            checkQty: async function() {
                var self = this
                if (self.headersObj.allocation.data != null && self.headersObj.item_id.data != null) {
                    var res = await axios.get(App.url + "transaction/stock", {
                        params: {
                            filter: {
                                po_id: self.headersObj.po_id.data,
                                allocation: self.headersObj.allocation.data,
                                item_id: self.headersObj.item_id.data
                            }
                        }
                    })
					if(res.data.status == false){
                    	self.maxQty = 0
						self.headersObj.stock_qty.data = 0
					}else{
						self.maxQty = Number(res.data.data[0].qty)
						self.headersObj.stock_qty.data = self.maxQty
					}
                } else{
                    self.maxQty = 0
					self.headersObj.stock_qty.data = 0
				}
                //self.headers = App.updateObject(self.headers)
            },
            checkQty2: async function() {
                var self = this
				var res = await axios.get(App.url + "transaction/stock", {
					params: {
						filter: {
							po_id: self.selected.po_id,
							allocation: self.selected.allocation,
							item_id: self.selected.item_id
						}
					}
				})
				if(res.data.status == false){
					self.maxQty = 0
					self.selected.stock_qty = 0
				}else{
					self.maxQty = Number(res.data.data[0].qty)
					self.selected.stock_qty = self.maxQty
				}
                //self.headers = App.updateObject(self.headers)
            }
        },
        mounted: function() {

        }
    }
</script>