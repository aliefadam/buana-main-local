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
				name: 'supplier',
				itemKey: 'id',
				url: App.folderRoot+'supplier',
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
					"cellClass": "",
					"alias": "supplier_id"
				}, {
					"text": "Name",
					"value": "name",
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
					"text": "pic name",
					"value": "pic_name",
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
					"text": "email",
					"value": "email",
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
					"groupable": false
				}, {
					"text": "phone",
					"value": "phone",
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
					"text": "address",
					"value": "address",
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
					"groupable": false
				}, {
					"text": "bank",
					"value": "bank",
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
				}, {
					"text": "bank account no",
					"value": "bank_account_no",
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
					"groupable": false
				}, {
					"text": "currency",
					"value": "currency",
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
					"groupable": false
				}, {
					"text": "bank account name",
					"value": "bank_account_name",
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
				}, {
					"text": "bic swift code",
					"value": "bic_swift_code",
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
