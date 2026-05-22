<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly"></v-template>
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
		data: function() {
			return {
				name: 'general',
				itemKey: 'id',
				url: 'admin/general',
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
					"disabled": false,
					"form": false,
					"visible": true,
					"filter": false,
					"groupable": false,
					"cellClass": ""
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
					"groupable": false
				}, {
					"text": "text",
					"value": "text",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false
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
					"visible": false,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": App.url+"admin/general_category",
					"search": ["category"],
					"formatter": ["category", "category"],
					"options": {
						filter: {},
						filterType: {},
						filterCondition: {
						}
					},
					paging: true,
					page: 0,
					limit: 10
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
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}]
			}
		},
		methods: {

		},
		mounted: function() {

		}
	}

</script>
