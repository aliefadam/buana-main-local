
<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :show-select="false"  :data="data" :items-options="itemsOptions"  @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" table-only :table-fixed-header="tableFixedHeader" :hide-default-footer="true">
			 <template v-slot:title-body v-if="$refs.template">
                <div><b>PR - In Process: </b> {{$refs.template.itemsTotal}}</div><br>
                <!-- <div>Difference from date</div> -->
            </template>
            <template v-slot:item.pr_no="props">
                <a @click="dialogPart=true">{{props.item.pr_no}}</a>
            </template>
            <template v-slot:menu-after-filter>
				<v-btn color="primary" outlined small @click="toExcel">
					Excel
				</v-btn>
			</template>
			<template v-slot:item.status="props">
             {{status(props.item)}}
            </template>
		</v-template>
        <v-action-dialog :actions="false" v-model="dialogPart" title="Purchase Requisition Item" min-height="75%" fullscreen>
            <pr-part :key="selected.id" :data="dataid" :sel="processData" table-only :hide-default-footer="true"></pr-part>
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
            'pr-part': 'url:ui/pr/part.vue',
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
				url: 'monitoring/listprinprocess',
				loading: false,
                processData: {},
                dialogPart: false,
                dataId:{},
				headers: [{
					"text": "#",
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
					"text": "PR No",
					"value": "pr_no",
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
					"text": "Title",
					"value": "pr_subject",
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
					"form": false,
					"filter": true,
					"groupable": false,
                    "default": "New",
					data_value: [{
                            text: "New",
                            value: 0
                        }, {
                            text: "Asking for Approval 1",
                            value: 1
                        }, {
                            text: "Asking for Approval 2",
                            value: 2
                        }],
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
                    "url": App.url + "monitoring/listprinprocess",
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
                
                dataid: {},
				isInDom: false,
				isInViewport: false,
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
			},
		},
		methods: {
            onSelectedRow: function(val) {
				var self = this
					self.selected = val
                    self.processData = {
                        pr_id: val.id
                    }
                    self.dataid = {
                        pr_id: val.id
                    }
			},
			onSelectedRowAll: function(val) {
				var self = this
				self.selectedAll = val
			},
			status: function(item){
                if(item.status==0)
                    return 'New'
				if(item.status==1)
					return 'Asking for Approval 1'
				if(item.status==2)
					return 'Asking for Approval 2'
				if(item.status==3)
					return 'Final Approved'
				if(item.status==-1)
					return 'Rejected'
                if (item.rev_date!=null)
                    return 'New'
			},
            toExcel: function(){
                var self=this
                self.$refs.template.getItems()
                self.$nextTick(()=>{
                    tableToExcel(self.$refs.template.$el.querySelector('.v-data-table__wrapper > table'), 'Document Ready for PO')
                })
            }
		},
		mounted: function() {
            var self=this;

		}
	}

</script>
