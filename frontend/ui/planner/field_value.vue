<template>
	<page-template v-model="value" delete-mode="active" active-column="flag" delete-mode="active" active-column="flag" :name="name" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :items-options="itemsOptions" @update:selected-row="onSelectedRow">
	</page-template>
</template>

<style>
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
				default: 'false'
			},
			itemsOptions: {
				type: Object,
				default: {}
			}
		},
		components: {
			'page-template': 'url:ui/admin/page-template.vue',
		},
		data: function() {
			return {
				name: 'task field value',
				itemKey: 'id',
				url: 'planner/field_value',
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
					"form": false,
					"disabled": false,
					"visible": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Field",
					"value": "field_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"type": "auto",
					"form": false,
					"disabled": false,
					"visible": false,
					"filter": false,
					"groupable": false,
					"alias": "field"
				}, {
					"text": "Value",
					"value": "value",
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
					"text": "Sort",
					"value": "sort",
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
					"default": 1,
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
					"text": "Flag",
					"value": "flag",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "switch",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"paging": true,
					"default": 1,
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
				}],
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
