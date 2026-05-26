
<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :show-select="false"  :data="data" :items-options="itemsOptions"  v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" table-only :table-fixed-header="tableFixedHeader" :hide-default-footer="true">
			 <template v-slot:title-body v-if="$refs.template">
                <div><b>Issue Request - Clarification: </b> {{$refs.template.itemsTotal}}</div><br>
                <!-- <div>Difference from date</div> -->
            </template>
            <template v-slot:menu-after-filter>
				<v-btn color="primary" outlined small @click="toExcel">
					Excel
				</v-btn>
			</template>
		</v-template>
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
				url: 'monitoring/listrfqclarification',
				loading: false,
                dataId: {},
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
					"text": "Rfq No",
					"value": "rfq_no",
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
				},  {
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
                    "data_value": ["Primary", "Secondary", "Utility", "RND", "Tools", "Electric", "Others"],
                }, {
					"text": "Priority",
					"value": "priority",
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
					"data_value": ["High", "Medium", "Low"]
				}, {
					"text": "Assigned To",
					"value": "assigned_to",
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
                    "url": App.url + "monitoring/listrfqclarification",
					"searchby":  ["assigned_to_name"],
					"formatter": ["assigned_to", "assigned_to_name"],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {},
                        optionAssigned:true
					},
                    "paging": true,
					"page": "1",
					"limit": "10",
					"alias": "assigned_to_name"
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
                    "url": App.url + "monitoring/listrfqclarification",
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
            toExcel: function(){
				var self = this
				self.$refs.template.getItems()
				self.$nextTick(()=>{
					tableToExcel(self.$refs.template.$el.querySelector('.v-data-table__wrapper > table'), 'Document Ready for PR')
				})
			},
		},
		mounted: function() {
            var self=this;

		}
	}

</script>
