<template>
	<page-template v-model="value" :name="name" ref="template" :item-key="itemKey" :url="url" :headers="headers" :table-only="tableOnly" :items-options="itemsOptions" @update:selected-row="onSelectedRow">
		<template v-slot:item.name="props">
            <div style="min-width: 300px;">
			    <a :href="location.href.split('/').slice(0,7).concat([props.item.id]).join('/')">{{props.item.name}}</a>
            </div>
		</template>
        <template v-slot:item.id="props">
            <div style="white-space: nowrap;">
			    <a :href="location.href.split('/').slice(0,7).concat([props.item.id]).join('/')">B{{props.item.created_date.substr(2,2)}}-{{props.item.id.toString().padStart(4, 0)}}</a>
            </div>
        </template>
		<template v-slot:item.description="props">
			<div style="min-width: 400px;" v-html="App.md.render(props.item.description||'')"></div>
		</template>
		<template v-slot:prepend-menu>
			<v-btn :disabled="!selected" small color="primary" outlined @click="dialogTask=true">Task</v-btn>
		</template>
		<v-action-dialog v-model="dialogTask" :title="selected.name" fullscreen :actions="false">
			<page-task :items-options="projectOptions" v-model="projectOptions" :table-only="false"></page-task>
		</v-action-dialog>
		<!-- <template slot="form.append.description">
			<div style="flex: 0; align-items: center; display: flex;"><v-icon @click="App.openWindow('http://'+location.host+'/apps/#/sa/md', '_blank', 400, 500)" color="error" style=" margin-right: 8px;">mdi-help-circle-outline</v-icon></div>
		</template> -->
	</page-template>
</template>

<style>
</style>

<script>
	module.exports = {
		name: '',
		props: {
			value: undefined,
			data: {
				type: Object
			},
			tableOnly: {
				type: Boolean,
				default: false
			},
			itemsOptions: {
				type: Object,
				default: {
					filter: {
						created_by: App.username,
						members: App.username
					},
					filterType:{
						created_by: '=',
						members: '%'
					},
					filterCondition:{
						created_by: 'or',
						members: 'or'
					}
				}
			}
		},
		components: {
			'page-template': 'url:ui/admin/page-template.vue',
			'page-task': 'url:ui/planner/task.vue',
		},
		data: function () {
			return {
				itemKey: 'id',
				name: 'Project',
				url: 'planner/project',
				dialogTask: false,
				selected: false,
				headers: [{
                    "text": "ID",
                    "value": "id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "1%",
                    "formWidth": "100%",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": false,
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
                    }
                }, {
                    "text": "Project Name",
                    "value": "name",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "1%",
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
                    "text": "Project Description",
                    "value": "description",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    //"width": "calc(50% - 10px)",
                    "width": "auto",
                    "formWidth": "100%",
                    "type": "ckeditor",
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
                    "text": "Client",
                    "value": "client_id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "1%",
                    "formWidth": "100%",
                    "type": "list",
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
                    "url": App.url+"planner/client",
                    "searchby": ['name', 'npwp'],
                    "formatter": ['id', 'name'],
                    "pk": 'id',
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
					"alias": "client_name"
                }, {
                    "text": "Type",
                    "value": "project_type",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "1%",
                    "formWidth": "100%",
                    "type": "list",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "paging": true,
                    "page": 0,
                    "limit": 10,
					"default": "Primary",
                    "data_value": ["Primary", "Secondary", "Internal", "Civil", "General"],
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
                    "text": "Status",
                    "value": "status",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "1%",
                    "formWidth": "100%",
                    "type": "list",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "paging": true,
                    "page": 0,
                    "limit": 10,
					"default": "Open",
                    "data_value": ["Open", "Pending", "Closed", "Cancelled"],
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
                    "text": "Contract No",
                    "value": "contract_no",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "1%",
                    "formWidth": "100%",
                    "type": "varchar",
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
                    "text": "Location",
                    "value": "location",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "1%",
                    "formWidth": "100%",
                    "type": "varchar",
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
					"text": "Planned Start Date",
					"value": "planned_start_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "datetime",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Planned Commissioning Date",
					"value": "planned_commissioning_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "datetime",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Planned Handover Date",
					"value": "planned_handover_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "datetime",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Planned for Project Close Date",
					"value": "planned_finish_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "datetime",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
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
				}],
			}
		},
		computed: {
			projectOptions: function(){
				var self = this
				if(!self.selected)
					return {}
				return {
					project_id: self.selected.id
				}
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
		},
		mounted: function () {
			var self = this
			if(check_user('administrator') ){
				delete self.itemsOptions.filter.created_by
				delete self.itemsOptions.filter.members
			}
		}
	}
</script>