<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader" :disable-edit-button="disableEdit" :disable-delete-button="disableDelete" >
             <template v-slot:title-body v-if="$refs.template">
				<b>Count Rows: </b> {{$refs.template.itemsTotal}}
            </template>
			 <template v-slot:menu-after-filter>

			 </template>
              <template v-slot:item.detail="props">
				<span>Created By: </span>{{ props.item.created_by_name }}<br/>
				<span>Created Date: </span>{{ props.item.created_date }}<br/>
				<span>Modified By: </span>{{ props.item.modified_by_name }}<br/>
				<span>Modified Date: </span>{{ props.item.modified_date }}
			</template>
			<template v-slot:item.file_upload="props">
				<div style=""><a :href="props.item.file_upload" v-if="props.item.file_upload" target="_blank">Open Link</a><span v-else>-</span><br />
				</div>
            </template>
            <template v-slot:item.status="props">
                {{status(props.item.status)}}
            </template>
            <template v-slot:item.client_id="props">
                {{clientInfo(props.item.client_id)}}
            </template>
		</v-template>
        <v-action-dialog v-model="dialogNote" :title="titleNote" min-height="75%" @save="act">
            <v-textarea label="Note" v-model="approval_notes"></v-textarea>
        </v-action-dialog>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: '',
		creator: '',
		components: {
		},
		props: {
			value: undefined,
			data: {
				type: Object
			},
			history: {
				type: Boolean,
				default: false
			},
			revise: {
				type: Boolean,
				default: false
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
            showColumn: {
                type: Boolean,
                default: false,
            },
			itemsOptions: {
				type: Object,
				default: {
					filter: {
						flag: 0
					},
					filterType: {
						flag: "!="
					}
				}
			}
		},
		data: function() {
			return {
                valid: false,
                dialogNote: false,
				action: '',
				name: '',
                approval_notes: '',
				dataid: {},
				processData: {},
                disableDelete: false,
				disableEdit: false,
				url: 'nomenclature/bast',
				itemKey: 'id',
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
				},  
                {
					"text": "No Surat",
					"value": "bast_no",
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
                    "text": "Date",
                    "value": "bast_date",
                    "default": new Date().formatDate('YYYY-MM-DD'),
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "date",
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
                    "page": "0",
                    "limit": "10"
                }, {     
				    text: "PO No",
                    value: "po_no",
                    align: "start",
                    sortable: true,
                    filterable: false,
                    divider: false,
                    class: "",
                    width: "auto",
                    type: "varchar",
                    data_value: [],
                    disabled: false,
                    visible: true,
                    required: false,
                    form: true,
                    filter: true,
                    groupable: false,
                    // url: App.url + "bom/purchaseorder",
                    // searchby: ["po_no"],
                    // formatter: ["id", "po_no"],
                    // options: {
                    //     filter: {
                    //         approved: 3,
                    //     },
                    //     filterType: {
                    //     },
                    //     filterCondition: {},
                    //         invoice: true,
                    //     },
                    // paging: true,
                    // page: "1",
                    // limit: "10",
                    // alias: "po_no",
                    }, 	
                    {
                    "text": "Client",
                    "value": "client_id",
                    "align": "start",
                    "sortable": false,
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
                    "data_value": [{text:"1. PT. GBB", value:1}, {text:"2. PT. BSI", value:2}, {text:"3. PT. Nojorono Tobacco International", value:3}],
                    "paging": true,
                    "page": "0",
                    "limit": "10"
				},  {
					"text": "Status",
					"value": "status",
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
					"filter": false,
					"groupable": false,
					"data_value": [{text:"Open", value:1}, {text:"Close", value:2}],
				// 	"url": "",
				// 	"searchby": [],
				// 	"formatter": [],
				// 	"options": {
				// 		"filter": {},
				// 		"filterType": {},
				// 		"filterCondition": {}
				// 	},
					"paging": true,
					"page": "1",
					"limit": "10"
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
					"filter": false,
				},  {
					"text": "Link Document",
					"value": "file_upload",
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
					"filter": false,
					"groupable": false,
                }, {
					"text": "Created/Modified",
					"value": "detail",
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
					"filter": false,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					}
				},  
				{
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
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                    "filter": {
                        // "group_name": "logistic_admin, surat_jalan",
                        // "sub_group_name": "logistic_admin, surat_jalan"
                    },
                    "filterType": {

                    },
                    "filterCondition": {
                        // "group_name":'or',
                        // "sub_group_name":'or'
                    }
                },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "alias": "created_by_name",
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
                    required: false,
                    form: false,
                    filter: true,
                    groupable: false,
                    "url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                    "filter": {
                        // "group_name": "logistic_admin, surat_jalan",
                        // "sub_group_name": "logistic_admin, surat_jalan"
                    },
                    "filterType": {

                    },
                    "filterCondition": {
                        // "group_name":'or',
                        // "sub_group_name":'or'}
                    }},
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
				}],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
				dataid: {},
			}
		},
		watch: {

		},
		computed: {
			titleNote: function(){
				var self = this
                if(self.action == 'delete'){
                    return 'Cancel Notes'
                }
			},
			headersObj: function() {
				var tmp = {},
					self = this
				Object.keys(self.headers).map(key => {
					var val = self.headers[key]
					tmp[val.value] = val
				})
				return tmp;
			},
		},
		methods: {
			 clientInfo: function(f){
				if(f == 1){
					return 'PT. GBB'
				}
				if(f == 2){
					return 'PT. BSI'
				}
				if(f == 3){
					return 'PT. Nojorono Tobacco International'
				}
			},
            status: function(f){
				if(f == 1){
					return 'Open'
				}
				if(f == 2){
					return 'Close'
				}
			},
			act: function(){
				this[this.action]()
			},
			onSelectedRow: function(val) {
				var self = this
				if (val === undefined) {
					self.selected = false
					self.processData = {}
					self.dataid = {}
                    self.disableDelete = false
				} else {
					self.selected = val

					if(val.status == 2){
						self.disableDelete = true
						self.disableEdit = true
					} else{
                        self.disableDelete = false
                        self.disableEdit = false
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
			},          
             delete: async function() {
                var self = this
                var c = confirm('Are you sure?')
                if (!c) return true;
                if (c) {
                    var params = {
                        flag:0,
                        pk: self.selected.id,
                    }
                    var r = await axios.put(App.url + 'nomenclature/bast', params)
                    if (!r.data.status)
                        App.errorMsg(r.data)
                    else {
                        self.$refs.template.getItems()
                        App.successMsg()
                    }
                }
            }
		},
		mounted: function() {
          
		}
	}
</script>
