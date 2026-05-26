<template>
	<page-template v-model="value" delete-mode="active" active-column="flag" :name="name" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :items-options="itemsOptions" @update:selected-row="onSelectedRow">
		<template v-slot:item.id="props">
			<a :href="location.href.split('/').slice(0,4).concat(['planner', 'planner', 'task', props.item.id]).join('/')">{{props.item.id.toString().padStart(4, 0)}}</a>
		</template>
		<template v-slot:item.subject="props">
			<a :href="location.href.split('/').slice(0,4).concat(['planner', 'planner', 'task', props.item.id]).join('/')">{{props.item.subject}}</a>
		</template>
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
	</page-template>
</template>

<style>
</style>

<script>
	module.exports = {
		name: '',
		creator: 'admin',
		components: {
			'page-template': 'url:ui/admin/page-template.vue',
		},
		props: {
			value: undefined,
			data: {
				type: Object
			},
			tableOnly: {
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
			itemsOptions: {
				type: Object,
				default: {}
			}
		},
		data: function() {
			return {
				name: 'My Page',
				itemKey: 'id',
				url: 'planner/dashboard/my_task',
				headers: [{
					"text": "Task Id",
					"value": "id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
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
					"text": "Assigned To",
					"value": "assigned_to_name",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
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
					"type": "list",
                    "data_value": [],
                    "url": App.url + "admin/users",
                    'searchby': ['username', 'email'],
                    'formatter': function(val) {
                        return {
                            text: val.name,
                            value: val.username
                        }
                    },
                    "options": {
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
                    },
				}, {
					"text": "Pic Name",
					"value": "pic_name",
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
					"text": "Assigned To Id",
					"value": "assigned_to_id",
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
					"text": "Pic",
					"value": "pic",
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
					"text": "Id",
					"value": "id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"type": "int",
					"disabled": false,
					"visible": false,
					"filter": false,
					"groupable": false
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
					"text": "Project Id",
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
					"text": "Parent Id",
					"value": "parent_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
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
					"text": "Percent Done",
					"value": "percent_done",
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
					"text": "Category Id",
					"value": "category_id",
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
					"visible": true,
					"required": false,
					"form": false,
					"filter": true,
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
					"type": "varchar",
					"disabled": false,
					"visible": true,
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
				}/*, {
					"text": "Username",
					"value": "username",
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
					"text": "Username(2)",
					"value": "username(2)",
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
				}*/],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
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
			}
		},
		mounted: function() {

		}
	}

</script>
