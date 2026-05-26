<template>
	<v-container
		style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
			:table-only="tableOnly"></v-template>
	</v-container>
</template>

<style scoped>
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
			}
		},
		data: function () {
			return {
				name: 'project',
				itemKey: 'id',
				url: 'bom/project',
				headers: [{
					"text": "id",
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
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "project no",
					"value": "project_no",
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
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						filter: {},
						filterType: {},
						filterCondition: {}
					},
					"paging": true,
					"page": 0,
					"limit": 10
				}, {
					"text": "project name",
					"value": "project_name",
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
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						filter: {},
						filterType: {},
						filterCondition: {}
					},
					"paging": true,
					"page": 0,
					"limit": 10
				}, {
					"text": "category",
					"value": "category",
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
					"default": "General",
					"data_value": ["General", "Primary", "Secondary", "Civil", "Internal"]
				}, {
					"text": "plan start date",
					"value": "plan_start_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
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
						filter: {},
						filterType: {},
						filterCondition: {}
					},
					"paging": true,
					"page": 0,
					"limit": 10
				}, {
					"text": "plan end date",
					"value": "plan_end_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
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
						filter: {},
						filterType: {},
						filterCondition: {}
					},
					"paging": true,
					"page": 0,
					"limit": 10
				}, {
					"text": "status",
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
					"default": "New",
					"data_value": ["New",
						"BOM Available",
						"Purchasing Process",
						"Pending", "Cancel", "Finish"
					]
				}, {
					"text": "pic",
					"value": "pic",
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
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": App.url + "users",
					"searchby": ["name"],
					"formatter": ["id", "name"],
					"options": {
						filter: {},
						filterType: {},
						filterCondition: {}
					},
					"paging": true,
					"page": 0,
					"limit": 10,
					"alias": "pic_name"
				}, {
					"text": "description",
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
						filter: {},
						filterType: {},
						filterCondition: {}
					},
					"paging": true,
					"page": 0,
					"limit": 10
				}, {
					"text": "client name",
					"value": "client_name",
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
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						filter: {},
						filterType: {},
						filterCondition: {}
					},
					"paging": true,
					"page": 0,
					"limit": 10
				}]
			}
		},
		methods: {

		},
		mounted: function () {

		}
	}
</script>