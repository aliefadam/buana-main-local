<template>
	<v-container
		style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
			@update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll"
			:table-only="tableOnly">
			<template v-slot:menu-after-filter>
				<v-btn color="primary" outlined small @click="dialogItem = true" :disabled="!selected">
					<v-icon small left>mdi-plus</v-icon>Add Item
				</v-btn>
			</template>
		</v-template>
        <v-action-dialog :actions="false" v-model="dialogItem" title="BOM Item" min-height="75%" fullscreen>
            <bom-item :data="processData" name=""></bom-item>
        </v-action-dialog>
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
        components: {
            'bom-item': App.ui+'project/bom_item.vue'
		},
		data: function () {
			return {
				name: 'BOM',
				processData: {},
				itemKey: 'id',
				url: App.folderRoot+'bom_header',
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
					},
					{
						"text": "BOM no",
						"value": "bom_no",
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
						"form": false,
						"filter": true,
						"groupable": false,
					},{
						"text": "Project No",
						"value": "project_no",
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
						"filter": true,
						"groupable": false,
					},
					{
						"text": "project",
						"value": "project_id",
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
						"url": App.model+"project",
						"searchby": ["project_name"],
						"formatter": ["id", "project_name"],
						"options": {
							"filter": {},
							"filterType": {},
							"filterCondition": {}
						},
						"paging": true,
						"page": "1",
						"limit": "10",
						"alias": "project_name"
					},
					{
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
						"data_value": ["New", "Pending", "Final"],
					},
					{
						"text": "notes",
						"value": "notes",
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
				],
				dialogItem: false,
				selected: false
			}
		},
		methods: {
			onSelectedRow: function(val) {
				var self = this
				if (val === undefined) {
					self.selected = false
					self.processData = {}
				} else {
					self.selected = val
					self.processData = {
						bom_header_id: val.id
					}
				}
			},
			onSelectedRowAll: function(val) {
				var self = this
				self.selectedAll = val
			},
		},
		mounted: function () {

		}
	}
</script>