<template>
	<page-template :hide-add-button="hideAddButton" :hide-edit-button="hideEditButton" :hide-delete-button="hideDeleteButton" :table-height="tableHeight" @after-get-items="onAfterGetItems" form-max-width="75%" form-width="700px" form-class="task-form" v-model="value" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :items-options="itemsOptions" @update:selected-row="onSelectedRow">
		<template v-slot:item.id="props">
			<a :href="location.href.split('/').slice(0,4).concat(['planner', 'planner', 'task', props.item.id]).join('/')">{{props.item.id.toString().padStart(4, 0)}}</a>
		</template>
		<template v-slot:item.subject="props">
			<a :href="location.href.split('/').slice(0,4).concat(['planner', 'planner', 'task', props.item.id]).join('/')">{{props.item.subject}}</a>
		</template>
		<template v-slot:item.description="props">
			<div style="min-width: 150px" v-html="App.md.render(props.item.description||'').replace(/(<([^>]+)>)/gi, '').substring(0, 100)">
				<!-- {{App.md.render(props.item.description||'').replace(/(<([^>]+)>)/gi, '').substring(0, 100)}} -->
				<!-- <v-truncate :text="App.md.render(props.item.description||'').replace(/(<([^>]+)>)/gi, '')"></v-truncate> -->
				<!-- <div v-html="App.md.render(props.item.description||'')"></div> -->
			</div>
			
		</template>
		<template v-slot:item.priority__1="props">
			<v-chip small flat class="immediate" v-if="props.item.alias_Priority == 'Immediate'"><v-icon left>mdi-alert-decagram-outline</v-icon>{{props.item.alias_Priority}}</v-chip>
			<v-chip small flat class="urgent" v-else-if="props.item.alias_Priority == 'Urgent'"><v-icon left>mdi-alert-circle-outline</v-icon>{{props.item.alias_Priority}}</v-chip>
			<v-chip small flat class="high" v-else-if="props.item.alias_Priority == 'High'"><v-icon left>mdi-chevron-up-circle-outline</v-icon>{{props.item.alias_Priority}}</v-chip>
			<v-chip small flat class="normal" v-else-if="props.item.alias_Priority == 'Normal'"><v-icon left>mdi-circle-outline</v-icon>{{props.item.alias_Priority}}</v-chip>
			<v-chip small flat class="low" v-else-if="props.item.alias_Priority == 'Low'"><v-icon left>mdi-chevron-down-circle-outline</v-icon>{{props.item.alias_Priority}}</v-chip>
		</template>
		<!-- <template slot="form.append.description">
			<div style="flex: 0; align-items: center; display: flex;"><v-icon @click="App.openWindow('http://'+location.host+'/apps/#/sa/md', '_blank', 400, 500)" color="error" style=" margin-right: 8px;">mdi-help-circle-outline</v-icon></div>
		</template> -->
	</page-template>
</template>

<style scoped>
	.markdown-body ol li{
		list-style: decimal !important;
	}
</style>

<script>
	module.exports = {
		name: '',
		creator: 'admin',
		components: {},
		props: {
			value: undefined,
			data: {
				type: Object
			},
			tableOnly: {
				type: Boolean,
				default: true
			},
			hideAddButton: {
				type: Boolean,
				default: true
			},
			hideDeleteButton: {
				type: Boolean,
				default: true
			},
			hideEditButton: {
				type: Boolean,
				default: true
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
				default: 'false'
			},
			tableHeight: {
				default: 'auto'
			},
			itemsOptions: {
				type: Object,
				default: {
					
				}
			}
		},
		components: {
			'page-template': 'url:ui/admin/page-template.vue',
		},
		data: function() {
			return {
				name: 'task',
				itemKey: 'id',
				url: 'planner/task',
				headersDef: [{
					"text": "#",
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
					"visible": true,
					"filter": false,
					"groupable": false
				}, {
					"text": "Task ID",
					"value": "id",
					"align": "start",
					"type": "int",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"disabled": false,
					"form": false,
					"visible": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "Subject",
					"value": "subject",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
                    "formWidth": "100%",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"paging": true,
					"page": 0,
					"limit": 10,
					"data_value": [],
					"url": "",
					"searchby": [],
					"formatter": [null, null],
					"pk": null,
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					}
				}, {
					"text": "Desciption",
					"value": "description",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
                    "formWidth": "100%",
					"type": "ckeditor",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"paging": true,
					"page": 0,
					"limit": 10,
					"data_value": [],
					"url": "",
					"searchby": [],
					"formatter": [null, null],
					"pk": null,
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					}
				}, {
					"text": "Parent Task",
					"value": "parent_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"formWidth": "calc(50% - 8px)",
					"type": "int",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
				}, {
					"text": "Project",
					"value": "project_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": false,
					"groupable": false,
					"paging": true,
					"page": 0,
					"limit": 10,
					"data_value": [],
					"url": "",
					"searchby": [],
					"formatter": [null, null],
					"pk": null,
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"alias": "project"
				}, {
					"text": "Project",
					"value": "project",
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
					"filter": true,
					"groupable": false,
					"paging": false,
					"page": 0,
					"limit": 10,
					"data_value": [],
					"url": "",
					"searchby": [],
					"formatter": [null, null],
					"pk": null,
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					}
				}, {
					"text": "Tracker",
					"value": "category_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"formWidth": "calc(50% - 8px)",
					"type": "list",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": false,
					"groupable": false,
					"paging": true,
					"page": 0,
					"default": 0,
					"limit": 10,
					"data_value": [],
					"url": App.url+"planner/category",
					"searchby": ['name'],
					"formatter": ['id', 'name'],
					"options": {
						"filter": {
							flag: 1,
						},
						"filterType": {
							name: '%'
						},
						"filterCondition": {}
					},
					"alias": "category"
				}, {
					"text": "Tracker",
					"value": "category_id",
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
					"paging": true,
					"page": 0,
					"default": 0,
					"limit": 10,
					"data_value": [],
					"url": App.url+"planner/category",
					"searchby": ['name'],
					"formatter": ['id', 'name'],
					"options": {
						"filter": {
							flag: 1,
						},
						"filterType": {
							name: '%'
						},
						"filterCondition": {}
					},
					"alias": "category"
				}, {
					"text": "% Done",
					"value": "percent_done",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"formWidth": "calc(50% - 8px)",
					"type": "list",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"paging": true,
					"page": 0,
					"default": 0,
					"limit": 10,
					"data_value": [0, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
				}, {
					"text": "Created By",
					"value": "created_by",
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
					"paging": true,
					"page": 0,
					"limit": 10,
					"data_value": [],
					"url": "",
					"searchby": [],
					"formatter": [null, null],
					"pk": null,
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					}
				}, {
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
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "Parent Task",
					"value": "parent_id",
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
					"paging": true,
					"page": 0,
					"limit": 10,
					"data_value": [],
					"url": "",
					"searchby": [],
					"formatter": [null, null],
					"pk": null,
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					}
				}],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
				headersTask: []
			}
		},
		computed: {
			headers: function(){
				var self = this
				var tmp = [].concat(self.headersDef, self.headersTask)
				return tmp
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
			onAfterGetItems: function(data){
				data.data.map(val=>{
					Object.keys(val).forEach(function (key) {
						var v = val[key]
						if(key)
							if(key.match('__')){
								var keys = key.split('__')
								var value = v != undefined ? v.split('=__=')[1] : null
								var alias = v != undefined ? v.split('=__=')[0] : null
								val['real_'+keys[0]] = v
								val['alias_'+keys[0]] = alias
								val[key] = value
								val[key.toLowerCase()] = value
							}
					});
				})
			},
			getTaskField: async function(id){
				try {
					var self = this
					self.loading = true
					var res = await axios.get(App.url+'planner/field/task_category', {
						params: {
							category_id: id
						}
					})
					var tmp = []
					
					res.data.data.map(function(val){
						try {
							var values = val.task_value.slice(2).split(';;').map((val)=>{
								var s = val.split('$$')
								return {
									text: s[1],
									value: val.field_type == 'int' ? Number(s[0]) : s[0].toString()
								}
							})
						} catch (err) {
							var values = []		
						}

						var def = null;
						if(val.default_value!=null){
							if(val.default_value.trim() != ''){
								if(val.field_type == 'int')
									def = Number(val.default_value)
								else
									def = val.default_value.toString()
							}
						}

						var url = (val.url||'').trim()
						
						var params = JSONfn.parse((val.params||`
							{
								"options": {
									"filter": {},
									"filterType": {},
									"filterCondition": {}
								},
								"searchby": [],
								"formatter": [null, null]
							}
						`).trim())
						
						var field = {
							"text": val.name,
							"value": val.name.toLowerCase()+'__'+val.id,
							"align": "start",
							"sortable": true,
							"filterable": false,
							"divider": false,
							"class": "",
							"width": "auto",
							"formWidth": "calc(50% - 8px)",
							"type": val.field_type,
							"disabled": false,
							"visible": true,
							"required": false,
							"form": true,
							"filter": false,
							"groupable": false,
							"default": def,
							"paging": true,
							"page": 0,
							"limit": 10,
							"data_value":values,
							"alias": "alias_"+val.name,
							"url": url == '' ? false : App.url + url,
							"options": params.options,
							"searchby": params.searchby,
							"formatter": params.formatter
						}

						if(self.itemsOptions.project_id!=undefined){
							field.options.project_id = self.itemsOptions.project_id
						}
						
						tmp.push(field)

						var field2 = {
							"text": val.name,
							"value": 'search__'+val.name+'__'+val.id,
							"align": "start",
							"sortable": true,
							"filterable": false,
							"divider": false,
							"class": "",
							"width": "auto",
							"type": val.field_type,
							"disabled": false,
							"visible": false,
							"required": false,
							"form": false,
							"filter": true,
							"groupable": false,
							"data": null,
							"default": def,
							"paging": true,
							"page": 0,
							"limit": 10,
							"data_value":values,
							"alias": "alias_"+val.name,
							"url": url == '' ? false : App.url + url,
							"options": params.options,
							"searchby": params.searchby,
							"formatter": params.formatter
						}
						
						if(location.hash.match(/\/planner\/planner\/task\//gi)!= null && val.id == 4){
							field2.options = {
								filter: {
									//groups: ';dept-seg;'
								},
								filterType: {
									//groups: '%'
								},
								filterCondition: {
									email: 'OR',
									username: 'OR',
									//groups: 'and'
								}
							}

							field2.url = App.url + "admin/users"
							field2.searchby = ['username', 'email']
							field2.formatter = function(val) {
								return {
									text: val.name,
									value: val.username
								}
							}
						}
						
						tmp.push(field2)
					})
					self.headersTask = tmp
				} catch (err) {
					console.log(err)
				}
				self.loading = false
			}
		},
		mounted: function() {
			var self = this
			self.getTaskField()
		}
	}

</script>
