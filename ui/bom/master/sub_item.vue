<template>
	<v-container v-observe-visibility="onVisible" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
	    <v-template :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @close-edit="closeEdit" @open-edit="openEdit" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly">
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
	        <template v-slot:item.datasheet="props">
	            <a :href="props.item.datasheet" v-if="props.item.datasheet" target="_blank">Open Link</a><span v-else>-</span>
	       </template>
	       <template v-slot:item.price_per_item="props">
				{{parseFloat(props.item.price_per_item).format(2,3)}}
			</template>
			<template v-slot:item.created="props">
			    <b>Created</b><br/>
				<span>BY:</span> {{props.item.created_by_name}}<br/>
				<span>DATE:</span> {{props.item.created_date}}<br/><br/>
				<b>Modified</b><br/>
				<span>BY:</span> {{props.item.modified_by_name}}<br/>
				<span>DATE:</span> {{props.item.modified_date}}
			</template>
			<template v-slot:item.modified="props">
				<span>BY:</span> {{props.item.modified_by_name}}<br/>
				<span>DATE:</span> {{props.item.modified_date}}<br/>
			</template>
			<template v-slot:menu-after-filter>
				<v-btn small color="primary" outlined @click="importBOM"><v-icon small left></v-icon>Import From BOM Receive</v-btn>
			</template>
	   </v-template>
	</v-container>
</template>

<style scoped>
	.no-border,
    .no-border td,
    .no-border th {
        border: 0px !important;
    }
</style>

<script>
	module.exports = {
		name: '',
		props: {
			value: undefined,
			data: {
				type: Object,
				default: {}
			},
			tableOnly: {
				type: Boolean,
				default: false
			},
            subItem: {
                type: Boolean,
                default: false
            },
			itemsOptions: {
				type: Object,
				default: {
					filter: {
					},
					filterType: {
					}
				}
			}
		},
		components: {
        },
		data: function() {
			return {
				name: 'subpart',
				itemKey: 'id',
				url: 'bom/item',
				dialogPrint: false,
				subItemDialog: false,
				isInDom: false,
				isInViewport: false,
                selected: false,
				images: [],
				headers: [{
					"text": "Id",
					"value": "id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"type": "auto",
					"disabled": false,
					"form": false,
					"visible": false,
					"filter": false,
					"groupable": false,
					"cellClass": ""
				}, {
					"text": "parent Id",
					"value": "parent_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"type": "auto",
					"disabled": false,
					"form": false,
					"visible": false,
					"filter": false,
					"groupable": false,
					"cellClass": ""
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
					"groupable": false
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
					"required": true,
					"form": true,
					"filter": true,
				}, {
					"text": "Item Unit",
					"value": "unit",
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
					"data_value": ["SHEET", "TON", "LTR", "KG", "PACK", "BOX", "PCS", "MTR", "DZN", "LOT", "LOG", "SET", "UNIT", "ROLL", "SQM"]
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
					"form": true,
					"filter": true,
					"groupable": false,
					"data_value": ["Jasa", "Material", "Part", "Tool"],
					"paging": true,
                    "page": "0",
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
					"form": true,
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
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Article No",
					"value": "article_no",
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
					"groupable": false
				}, {
					"text": "Specification",
					"value": "specification",
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
					"groupable": false
				},{
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
					"visible": true,
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
					"text": "is active",
					"value": "is_active",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "switch",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
					"filter": false,
					"groupable": false,
					"default": 1,
					"data_value": [0, 1,]
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
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Created By",
					"value": "created_by",
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
					"filter": true,
					"groupable": false,
                    "url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            group_name: "adm_admin",
                            sub_group_name: "adm_admin"
                        },
                        "filterType": {
                        },
                        "filterCondition": {
                            group_name:'or',
                            sub_group_name:'or'
                        }
                    },
                    paging: true,
                    page: "1",
                    limit: "10",
                    alias: "created_by_name",
				}, {
					"text": "Created Date",
					"value": "created_date",
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
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Created Date",
					"value": "crea_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"search": [],
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
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Modified By",
					"value": "modified_by",
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
					"filter": true,
					"groupable": false,
                    "url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            group_name: "adm_admin",
                            sub_group_name: "adm_admin"
                        },
                        "filterType": {
                        },
                        "filterCondition": {
                            group_name:'or',
                            sub_group_name:'or'
                        }
                    },
                    paging: true,
                    page: "1",
                    limit: "10",
                    alias: "modified_by_name",
				}, {
					"text": "Modified Date",
					"value": "modified_date",
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
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Modified Date",
					"value": "mod_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"search": [],
					"formatter": [],
					"options": {
						"filter": {
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
				try {
					var res = await axios.get(App.url+'bom/item/create_from_bom', {
						params: {
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
			onSelectedRow: function(val){
				var self = this
				if (val === undefined) {
                    self.selected = false
                } else {
                    self.selected = val
				}
			},
			closeEdit: function(){
				var self = this
				self.headersObj.unit.readonly = false
				self.headersObj.item_type.readonly = false
			},
			openEdit: function(){
				var self = this
				self.headersObj.unit.readonly = true
				self.headersObj.item_type.readonly = true
			},
			textToBase64Barcode: async function(){
				var self = this
				var res = await axios.get(App.url+self.url, {
					params: Object.assign({
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},self.$refs.template.defaultItemsOptions)
				})

				var canvas = document.createElement("canvas"), images=[], tmp = [];
				self.$refs.template.items.map(function(val, i){
					if(i%4==0&&i!=0){
						images.push(tmp)
						tmp = []
					}
					JsBarcode(canvas, val.item_no, {format: "CODE128"});
					tmp.push(
						canvas.toDataURL("image/png")
					)
				})
				if(tmp.length!=0)
				    images.push(tmp)
				self.images = images
				self.$nextTick(()=>{
					//tableToExcel(document.getElementById('qr'))
					self.dialogPrint=true
				})
			},
			onVisible: function(isVisible, e) {
				var self = this
				self.isInViewport = !!isVisible
				self.isInDom = !!e.target.isConnected
				if(self.data.parent_id != undefined){
					self.itemsOptions = {
						filter: self.data
					}
					//console.log(self.isInViewport)
					if(self.isInViewport){
						self.$nextTick(()=>{
							self.$refs.template.getItems()
						})
					}
				}
			},
		},
		mounted: function() {
			loadMultipleLibrary('https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/barcodes/JsBarcode.code128.min.js')


		}
	}

</script>
