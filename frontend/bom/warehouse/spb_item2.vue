<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-row no-gutters style="flex: 1" v-if="App.page().selected.approved <= 0 && App.page().selected.bom_receive_id != null">
			<v-cell cols="12" style="width: 100%">
				<bom-item :data="data2" ref="remaining_stock"></bom-item>
			</v-cell>
		</v-row>
		<v-row no-gutters style="flex: 1">
			<v-cell cols="12" style="width: 100%">
				<v-template hide-add-button @before-save="beforeSave" @after-save="$refs.remaining_stock.$refs.template.getItems()" @after-delete="$refs.remaining_stock.$refs.template.getItems()"  @after-update="$refs.remaining_stock.$refs.template.getItems()" hide-add-button @after-delete="afterDelete" :hide-delete-button="hideDelete" :hide-edit-button="hideAddButton" :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
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
					<template v-slot:item.qty="props">
						{{parseFloat(props.item.qty).format(2,3)}}
					</template>
					<template v-slot:item.photo="props">
						<a :href="props.item.photo" v-if="props.item.photo!=''" target="_blank">Photo</a>
						<span v-else>-</span>
					</template>
					<template v-slot:title-body>
						SPB Items
					</template>
				</v-template>
			</v-cell>
		</v-row>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'spb_item2',
        creator: '',
        components: {
            'bom-item': 'url:ui/bom/transaction/spb_bom_item.vue'
        },
        props: {
            value: undefined,
            data: {
                type: Object
            },
            data2: {
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
                name: '',
                itemKey: 'id',
                url: 'warehouse/spbitem',
                loading: false,
                headers: [{
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
                    "url": App.url + 'warehouse/spb',
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
                }, {
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
                    "required": true,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "alias": "item_no",
                    "url": App.url + "transaction/bomitem",
                    "searchby": ['full'],
                    "formatter": ['item_id', 'full'],
                    "options": {
                        "filter": {
                        },
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "alias": "item_no"
                }, {
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
                    "limit": "10"
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
                    "text": "Notes",
                    "value": "notes",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "text",
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
                    "limit": "10"
                }, {
                    "text": "PO",
                    "value": "po_id",
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
                    "text": "Po No",
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
					"text": "Photo",
					"value": "photo",
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
					"form": false,
					"filter": false,
					"groupable": false,
                }],
                //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
                selected: false,
                //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
                selectedAll: [],
                isInDom: false,
                isInViewport: false,
                maxQty: 0,
            }
        },
        watch: {

        },
        computed: {
            hideDelete: function() {
                var $self = this
                if (App.page().selected.approved > 0)
                    return true
                return false
            },
            hideAddButton: function() {
                var $self = this
                if (App.page().selected.po_id == null && App.page().selected.approved < 1)
                    return false
                return true
            },
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
                if (self.isInViewport) {
                    self.$refs.template.defaultItemsOptions.filter.spb_id = self.data.spb_id
                    self.$refs.template.getItems()
					self.data2 = {
						bom_receive_id: App.page().selected.bom_receive_id
					}
					window.spbitem2 = self
                }
            },
            afterDelete: function() {
                var self = this
                self.$emit('after-delete')
            },
			beforeSave: function(opt){
				var self = this
				/*if(opt.params.qty > self.maxQty)
					opt.params.qty = self.maxQty*/
			}
        },
        mounted: function() {

		}
    }
</script>