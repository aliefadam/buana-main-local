<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
	    <v-template show-expand single-expand @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" @close-edit="closeEdit" @open-edit="openEdit" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly">
	        <template v-slot:item.datasheet="props">
	            <a :href="props.item.datasheet" v-if="props.item.datasheet" target="_blank">Open Link</a><span v-else>-</span>
	       </template>
	       <template v-slot:item.price_per_item="props">
				{{parseFloat(props.item.price_per_item).format(2,3)}}
			</template>
				<template v-slot:item.created="props">
				<b>By:</b> {{props.item.created_by_name}}<br/>
				<b>Date:</b> {{props.item.created_date}}<br/>
			</template>
			<template v-slot:item.modified="props">
				<b>By:</b> {{props.item.modified_by_name}}<br/>
				<b>Date:</b> {{props.item.modified_date}}<br/>
			</template>
			<template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					<item-in-po table-only :data="{
						item_id: props.item.id
					}" :key="props.item[itemKey]" :table-fixed-header="false" :item-height-as-min-height="true" :table-fill="false"></item-in-po>
				</td>
			</template>
	   </v-template>
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
				default: check_user(["viewonly"])
			}
		},
        components: {
            'item-in-po': 'url:ui/bom/purchasing/iteminpo.vue',
		},
		data: function() {
			return {
				poData: {},
				name: 'Item Price',
				itemKey: 'id',
				url: 'bom/item',
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
					"visible": false,
					"filter": false,
					"groupable": false,
					"cellClass": ""
				}, {
					"text": "Item No",
					"value": "item_no",
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
					"groupable": false
				}, {
					"text": "Item Name",
					"value": "item_name",
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
				}, {
					"text": "Item Unit",
					"value": "unit",
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
					"text": "Price",
					"value": "price_per_item",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "float",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Currency",
					"value": "currency",
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
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Item Type",
					"value": "item_type",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"data_value": ["Part", "Tool", "Jasa"]
				}, {
					"text": "Original Manufacture",
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
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Manufacture PN",
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
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Specification",
					"value": "specification",
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
                        "text": "Datasheet",
                        "value": "datasheet",
                        "align": "start",
                        "sortable": false,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "text",
                        "disabled": false,
						"visible": true,
                        "required": false,
                        "form": true,
                        "filter": false,
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
                    }, {
					"text": "is active",
					"value": "is_active",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "switch",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
					"filter": false,
					"groupable": false,
					"default": 1,
					"data_value": [0, 1,]
				}, {
					"text": "Created",
					"value": "created",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": false,
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
					"visible": false,
					"required": false,
					"form": false,
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
					"visible": false,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "Modified",
					"value": "modified",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": false,
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
					"visible": false,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false
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
					"visible": false,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					text: '',
					value: 'data-table-expand'
				}]
			}
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
			closeEdit: function(){
				var self = this
				self.headersObj.unit.readonly = false
				self.headersObj.item_type.readonly = false
			},
			openEdit: function(){
				var self = this
				self.headersObj.unit.readonly = true
				self.headersObj.item_type.readonly = true
			},
            onSelectedRow: function(val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                    self.poData = {}
					//self.disableEdit = false
                } else {
                    self.selected = val
					
                }
            },
            onSelectedRowAll: function(val) {
                var self = this
                self.selectedAll = val
            },
		},
		mounted: function() {

		}
	}

</script>
