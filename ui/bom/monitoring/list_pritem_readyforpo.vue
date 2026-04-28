<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
			
             <template v-slot:item.item_name="props">				
            	<a :href="props.item.datasheet" v-if="props.item.datasheet" target="_blank">{{props.item.item_name}}</a><span v-else>{{props.item.item_name}}</span>
	    </template>
			<template v-slot:item.quotation="props">
                <b>No:</b> {{ props.item.quotation_no }}<br/>
                <b>Date:</b> {{props.item.quotation_date}}<br/>
				<a :href="props.item.quotation_doc_url" v-if="props.item.quotation_doc_url" target="_blank">Document</a><span v-else>-</span><br/>
			</template>
			  <template v-slot:item.created="props">
				<span>By:</span> {{props.item.created_by_name}}<br/>
				<span>Date:</span> {{props.item.created_date}}<br/>
			</template>
			<template v-slot:item.modified="props">
				<span>By:</span> {{props.item.modified_by_name}}<br/>
				<span>Date:</span> {{props.item.modified_date}}<br/>
			</template>
			<template v-slot:item.datasheet="props">
	            <a :href="props.item.datasheet" v-if="props.item.datasheet" target="_blank">Open Link</a><span v-else>-</span>
	       </template>
		</v-template>
        <v-action-dialog :actions="false" v-model="dialogSubledger" title="Subledger" min-height="75%" fullscreen>
            <subledger-part :key="selected.id" :data="dataid" :table-only="tableOnly"></subledger-part>
        </v-action-dialog>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: 'prpart',
		creator: '',
		components: {
			'subledger-part': 'url:ui/pr/subledger.vue'
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
            isDashboard: {
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
					filter: {
                        rn:1,
						in_po:0
                    },
					filterType: {},
					sortBy: ['order_no', 'id'],
					sortDesc: [false, false]
				}
			}
		},
		data: function() {
			return {
				name: 'prpart',
				itemKey: 'id',
				dataid: {},
				order_no: 0,
				url: 'monitoring/listpritemreadyforpo',
				dialogSubledger: false,
				dialogBOM: false,
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
					"value": "item_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"data_value": [],
                    // "rules": [v => (v || '' ).trim().length > 10 || 'Link is Invalid'],
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": App.url+"bom/item",
					"searchby": ['full'],
                    "formatter": function(val) {
                            return {
                                text: val.full,
                                value: val.id,
                                datasheet: val.datasheet,
								item_type: val.item_type
                            }
                        },
					// "formatter": ['id', 'full'],
                    "input": function(data) {
                            var self = App.$get("prpart")
                            if (data.data) {
                                var val = data.data_value.filter(val => val.value == data.data)[0]

                                if(val.item_type!='Jasa' && (val.datasheet==null || (val.datasheet||'').trim().length <10)){

                                    self.headersObj.item_id.rules=[val => (val.datasheet || '' ).trim().length > 10 || "Item can't be used. Please make sure the Datasheet is vaild"]
                                }else{
                                    delete self.headersObj.item_id.rules
                                }

                            } 
                            else {
                                delete self.headersObj.item_id.rules
                            }
                            self.headers = App.updateObject(self.headers)
                        },
					"options": {
						"filter": {
							is_active: 1,
                            is_subpart:1
						},
						"filterType": {
                            is_subpart:'!='
                        },
						"filterCondition": {
                            is_active:'or',
                            is_subpart:'or'
                        },
                        full:true
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
					"type": "list",
					"data_value": [],
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
				}, {
					"text": "Item Unit",
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
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "Item Type",
					"value": "item_type",
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
					"filter": true,
					"groupable": false,
					"data_value": ["Part", "Tool", "Jasa"]
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
					"groupable": false
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
					"groupable": false
				}, {
					"text": "Specification",
					"value": "specification",
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
					"groupable": false
				}, {
                        "text": "Datasheet",
                        "value": "datasheet",
                        "align": "start",
                        "sortable": false,
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
                    }, {
					"text": "Supplier",
					"value": "supplier_id",
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
					"required": true,
					"form": false,
					"filter": false,
					"groupable": false,
					"url": App.url+"bom/supplier",
					"searchby": ['name'],
					"formatter": ['id', 'name'],
					"options": {
						"filter": {
							flag: 1
						},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "supplier_name",
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
					"text": "Pr Id",
					"value": "pr_id",
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
					"text": "Created",
					"value": "created",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
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
					"text": "Created By",
					"value": "created_by",
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
					"limit": "10",
					"alias": "created_by_name"
				},  {
					"text": "Created Date",
					"value": "created_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "datetime",
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
					"text": "Modified",
					"value": "modified",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
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
					"text": "Modified By",
					"value": "modified_by",
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
					"limit": "10",
					"alias": "modified_by_name"
				}, {
					"text": "Modified Date",
					"value": "modified_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "datetime",
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
				}, {
                        "text": "Order",
                        "value": "order_no",
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
                        "precision": 2,
                       
                	}],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
				dialogOrderNo: false,
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
					self.dataid = {}
				} else {
				    if(val.quotation_date!=null)
				        val.quotation_date = null
					self.selected = val
                    self.dataid = {
                        pr_part_id: val.id,
                    }
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
                //console.log(self.data.pr_id)
			},
		},
		mounted: function() {
            var self=this
            if(self.tableOnly){
                self.headersObj.order_no.visible=false
            }
            if(self.isDashboard){
                self.headersObj.modified.visible=false
                self.headersObj.created.visible=false
            }
		}
	}

</script>
