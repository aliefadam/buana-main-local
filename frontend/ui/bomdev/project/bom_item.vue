<template>
	<v-container
		style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :data="data" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
			:table-only="tableOnly"></v-template>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: 'bom_item',
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
				name: 'BOM Item',
				itemKey: 'id',
				url: App.folderRoot+'bom_item',
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
						"page": "0",
						"limit": "10"
					},
					{
						"text": "bom_header_id",
						"value": "bom_header_id",
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
						"url": "",
						"searchby": [],
						"formatter": [],
						"options": {
							"filter": {},
							"filterType": {},
							"filterCondition": {}
						},
						"paging": true,
						"page": "0",
						"limit": "10"
					},
					{
						"text": "item",
						"value": "item_id",
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
						"data_value": [],
						"form": true,
						"filter": true,
						"groupable": false,
						"url": App.model+"item",
						"searchby": ['full'],
						"formatter": ['id', 'full'],
						"options": {
							"filter": {
								"is_active": 1
							},
							"filterType": {},
							"filterCondition": {}
						},
						"paging": true,
						"page": "1",
						"limit": "10",
						"alias": "item_name"
					},
					{
						"text": "original manufacture",
						"value": "original_manufacture",
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
					}, {
						"text": "manufacture pn",
						"value": "manufacture_pn",
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
					},
					{
						"text": "NI qty",
						"value": "required_qty",
						"align": "start",
						"sortable": true,
						"filterable": false,
						"divider": false,
						"class": "",
						"width": "auto",
						"type": "float",
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
						"page": "0",
						"limit": "10",
						"input": function(data){
							var self = App.$get('bom_item')
							self.headersObj.order_qty.data = Number(self.headersObj.required_qty.data||0) + Number(self.headersObj.required_qty_nis.data||0)
						}
					},
					{
						"text": "NIS qty",
						"value": "required_qty_nis",
						"align": "start",
						"sortable": true,
						"filterable": false,
						"divider": false,
						"class": "",
						"width": "auto",
						"type": "float",
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
						"page": "0",
						"limit": "10",
						"input": function(data){
							var self = App.$get('bom_item')
							self.headersObj.order_qty.data = Number(self.headersObj.required_qty.data||0) + Number(self.headersObj.required_qty_nis.data||0)
						}
					},
					{
						"text": "order qty",
						"value": "order_qty",
						"align": "start",
						"sortable": true,
						"filterable": false,
						"divider": false,
						"class": "",
						"width": "auto",
						"type": "float",
						"disabled": false,
						"visible": true,
						"required": true,
						"readonly": true,
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
						"page": "0",
						"limit": "10"
					},
					{
						"text": "remark",
						"value": "remark",
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
						"url": "",
						"searchby": [],
						"formatter": [],
						"options": {
							"filter": {},
							"filterType": {},
							"filterCondition": {}
						},
						"paging": true,
						"page": "0",
						"limit": "10"
					}
				]
			}
		},
        computed: {
            headersObj: function(){
                var tmp = {}, self = this
                Object.keys(self.headers).map(key=>{
                    var val = self.headers[key]
                    tmp[val.value] = val
                })
                return tmp;
            }
        },
		methods: {

		},
		mounted: function () {

		}
	}
</script>