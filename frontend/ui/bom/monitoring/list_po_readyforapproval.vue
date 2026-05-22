
<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template  :show-select="false"  :data="data" :items-options="itemsOptions"   @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" table-only :hide-default-footer="true" >
			 <template v-slot:title-body v-if="$refs.template">
                <div><b>PO - Ready for Approval : </b> {{$refs.template.itemsTotal}} - <b>Summary Currency : </b>{{summary}}</div><br>
                <!-- <div>Difference from date</div> -->
            </template>
            <template v-slot:menu-after-filter>
				<v-btn color="primary" outlined small @click="toExcel">
					Excel
				</v-btn>
			</template>
			<template v-slot:item.approved="props">
                {{approvedStatus(props.item.approved)}}
			</template>
            <template v-slot:item.po_no="props">
                <a @click="dialogPart=true">{{props.item.po_no}}</a>
            </template>
		</v-template>
        <v-action-dialog :actions="false" v-model="dialogPart" title="Purchase Order Item" min-height="75%" fullscreen>
            <purchase-item-group :key="selected.id" :sel="processData" name="" :data="dataid" is-dashboard></purchase-item-group>
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
            'purchase-item-group': 'url:ui/bom/purchasing/purchaseorderitemgroup.vue',
		},
		props: {
			value: undefined,
            processData: {},
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
            history: {
				type: Boolean,
				default: false
			},
			itemsOptions: {
				type: Object,
				default: {
					filter: {
                    },
					filterType: {
                    },
				}
			}
		},
		data: function() {
			return {
				name: '',
				itemKey: 'id',
				url: 'monitoring/listporeadyforapproval',
				loading: false,
                dialogPart: false,
                dataid: {},
				headers: [{
					"text": "#",
					"value": "id",
					"align": "start",
					"sortable": false,
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
				},  {
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
                    }, {
					"text": "No",
					"value": "no",
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
                        "text": "PO No",
                        "value": "po_no",
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
					"text": "Title",
					"value": "title",
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
					    "text": "Status",
					    "value": "approved",
					    "align": "start",
					    "sortable": false,
					    "filterable": false,
					    "divider": false,
					    "class": "",
					    "width": "auto",
					    "type": "list",
					    "disabled": false,
					    "visible": true,
					    "required": false,
					    "form": false,
					    "filter": true,
					    "groupable": false,
					    "data_value": [{ text:"New", value:0}]
				    }, {
                        "text": "Currency",
                        "value": "currency",
                        "align": "center",
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
                    },{
                        "text": "Item Price",
                        "value": "grand_total_price",
                        "align": "center",
                        "sortable": false,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "float",
                        "disabled": false,
                        "visible": true,
                        "required": false,
                        "form": false,
                        "filter": false,
                        "groupable": false
                    },{
					"text": "Created By",
					"value": "created_by",
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
                    "clearable": true,
					"form": false,
					"filter": true,
					"groupable": false,
                    "url": App.url + "monitoring/listporeadyforapproval",
					"searchby":  ["created_by_name"],
					"formatter": ["created_by", "created_by_name"],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {},
                        optionUser:true
					},
                    "paging": true,
					"page": "1",
					"limit": "10",
					"alias": "created_by_name"
				}],
                    //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
			}
		},
		watch: {
            dialogPart: function(val){
				if(!val)
					this.$refs.template.getItems()
			},

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
			},
		},
		methods: {
            getSummary: async function() {
                var self = this
                var res = await axios.get(App.url + 'monitor/summarycurr', {
                    // params: {
                    //     filter: {
                    //         pr_no: self.hash[2]
                    //     },
                    //     limit: -1
                    // }
                })
                if (!res.data.status)
                    App.errorMsg()
                else {
                    self.summary = res.data.data
                    console.log(self.summary)
                }
            },		    
            onSelectedRow: function(val) {
                var self = this
                    self.selected = val
                    self.processData = {
                        purchase_order_id: val.id,
                    }
                    self.dataid = {
                        purchase_order_id: val.id
                    }
            },
            onSelectedRowAll: function(val) {
                var self = this
                self.selectedAll = val
            },
			approvedStatus: function(f, new_po_no){
				if(new_po_no!=null){
					if(new_po_no.trim()!='')
						return 'Revised Final PO'
				}
				if(f == 0){
					return 'New'
				}
				if(f == 1){
					return 'Asking for Approval 1'
				}
				if(f == 2){
					return 'Asking for Approval 2'
				}
				if(f == 3){
					return 'Approval 2 Approved'
				}
				/* if(f == 4){
					return 'Approval 2 approved'
				} */
				if(f == -1){
					return 'Rejected'
				}
			},
            toExcel: function(){
				var self = this
				self.$refs.template.getItems()
				self.$nextTick(()=>{
					tableToExcel(self.$refs.template.$el.querySelector('.v-data-table__wrapper > table'), 'List PO Ready for Approval')
				})
			},
		},
		mounted: function() {
            var self=this
            self.getSummary()

		}
	}

</script>
