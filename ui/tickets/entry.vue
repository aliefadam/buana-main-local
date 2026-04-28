<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
			<!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					
				</td>
			</template> -->
			<template v-slot:after-header>
			<v-row>
				<v-col v-for="(item, index) in monitorSummary" :key="index" cols="3">
					<v-card class="mx-auto" max-width="344" outlined>
						<v-list-item three-line>
							<v-list-item-content>
								<div class="text-overline mb-4" style="font-weight: bold">
									{{ item.status }}
								</div>
								<v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
									{{ item.status_count }}
								</v-list-item-title>
							</v-list-item-content>
						</v-list-item>
					</v-card>
				</v-col>
			</v-row>
			</template>

			<template v-slot:item.assigned_agent="props">
				{{ props.item.assigned_name }}
			</template>
			<template v-slot:item.category_id="props">
				{{ props.item.category_name }}
			</template>
			<template v-slot:item.status="props">
				<div style="justify-content: left; display: flex;">
					<v-alert dense color="#ffcc99" v-if="props.item.status=='Closed'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Closed
					</v-alert>
					<v-alert dense color="#f88686" v-if="props.item.status=='In_progress'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						In_progress
					</v-alert>
					<v-alert dense color="#bfdda8" v-if="props.item.status=='Open'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Open
					</v-alert>
					<v-alert dense color="#f5e699" v-if="props.item.status=='New'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						New
					</v-alert>
				</div>
            </template>
			<template v-slot:item.priority="props">
				<div style="justify-content: left; display: flex;">
					<v-alert dense color="#ffcc99" v-if="props.item.priority=='Urgent'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Urgent
					</v-alert>
					<v-alert dense color="#f88686" v-if="props.item.priority=='High'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						High
					</v-alert>
					<v-alert dense color="#bfdda8" v-if="props.item.priority=='Medium'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Medium
					</v-alert>
					<v-alert dense color="#f5e699" v-if="props.item.priority=='Low'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Low
					</v-alert>
				</div>
            </template>
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
		name: '',
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
				default: "flag"
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
				name: 'tickets',
				itemKey: 'id',
				url: 'tickets/tickets',
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
					"text": "Ticket No",
					"value": "ticket_no",
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
					"searchby": [],
					"formatter": [],
				}, {
					"text": "User Id",
					"value": "user_id",
					"align": "start",
					"sortable": false,
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
					"limit": "10"
				}, {
					"text": "Subject",
					"value": "subject",
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
					"text": "Description",
					"value": "description",
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
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"data_value": ['New','Open','In_progress','Closed'],
					"searchby": [],
					"formatter": [],
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
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"data_value": ['Low','Medium','High','Urgent'],
				}, {
					"text": "Assigned Agent",
					"value": "assigned_agent",
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
                    "url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				},  {
					"text": "Category",
					"value": "category_id",
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
                    "url": App.url + "tickets/categories",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
					"options": {
						"filter": {},
						"filterType": {},
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
				monitorSummary: [],
				
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
			async getData() {
				var self = this
				url = await axios.get(App.url+'monitor/tickets/getsummary')
				self.monitorSummary = url.data.data
				console.log(self.monitorSummary)
			},
			
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
		},
		mounted: function() {
			var self = this
			self.getData()
		}
	}

</script>
