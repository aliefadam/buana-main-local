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
            <!-- 
            <template v-slot:prepend-menu>
            </template>
			 -->
            <!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
            <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
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
                name: 'npbitem',
                itemKey: 'id',
                url: App.folderRoot+'npbitem',
                loading: false,
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
                    "text": "Item",
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
                    "url": App.model+"stock",
                    "searchby": ['item_name'],
                    "formatter": ['item_id', 'item_name'],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {},
                        "distinct": true,
                        "fields": ['item_id', 'item_name']
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "input": function(data) {
                        var self = App.$get('npb_item')
                        var reload = false
                        if (self.headersObj.po_id.options.filter.item_id != data.data)
                            reload = true
                        self.headersObj.po_id.options.filter.item_id = data.data
                        self.headersObj.allocation.options.filter.item_id = data.data
                        if (reload) {
                            self.headersObj.po_id.data = null
                            self.headersObj.allocation.data = null
                            self.headersObj.po_id.update = null
                            self.headersObj.allocation.update = null
                        }
                        self.headers = App.updateObject(self.headers)
                        self.checkQty()
                    }
                }, {
                    "text": "PO",
                    "value": "po_id",
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
                    "url": App.model+"stock",
                    "searchby": ['po_no'],
                    "formatter": ['po_id', 'po_no'],
                    "options": {
                        "filter": {},
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

                    }
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
                    "url": App.model+"stock",
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
                    "required": true,
                    "readonly": true,
                    "visible": false,
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
            onVisible: function(isVisible, e) {
                var self = this
                self.isInViewport = !!isVisible
                self.isInDom = !!e.target.isConnected
            },
            checkQty: async function() {
                var self = this
                if (self.headersObj.allocation.data != null && self.headersObj.po_id.data != null && self.headersObj.item_id.data != null) {
                    var res = await axios.get(App.model+"stock", {
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
            }
        },
        mounted: function() {

        }
    }
</script>