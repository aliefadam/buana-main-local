<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
			<!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					
				</td>
			</template> -->
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

             <template v-slot:title-body v-if="$refs.template">
				<b>Count Rows: </b> {{$refs.template.itemsTotal}}
            </template>
            <template v-slot:item.created="props">
				<span>Created By: </span>{{ props.item.created_by_name }}<br/>
				<span>Created Date: </span>{{ props.item.created_date }}<br/>
			</template>
			<template v-slot:item.modified="props">
				<span>Modified By: </span>{{ props.item.modified_by_name }}<br/>
				<span>Modified Date: </span>{{ props.item.modified_date }}
			</template>
		</v-template>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: '',
		creator: '',
		components: {
			/* 'document-form': 'url:ui/ewis/test/document_form.vue' */
		},
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
				type: Boolean,
				default: false
			},
			showExpand: {
				type: Boolean,
				default: false
			},
			singleExpand: {
				type: Boolean,
				default: true
			},
			itemsOptions: {
				type: Object,
				default: {
					filter: {},
					filterType: {}
				}
			}
		},
		data: function() {
			return {
				name: 'List Users Group',
				itemKey: 'id',
				url: 'admin/user_group',
				loading: false,
				headers: [{
					"text": "User Name",
					"value": "user_id",
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
					"url": App.url + "users",
					"searchby": ["name"],
					"formatter": ["id", "name"],
					"options": {
						"filter": {
                            is_group: 0,
                            flag:1
                        },
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "user_name"
				}, {
					"text": "Group Name",
					"value": "group_id",
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
					"url": App.url + "users",
					"searchby": ["name"],
					"formatter": ["id", "name"],
					"options": {
						"filter": {
                            is_group: 1,
                            flag:1
                        },
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "group_name"
				}, {
					"text": "Id",
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
					"filter": false,
					"groupable": false,
					"url": "",
					"search": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
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
                    "required": true,
                    "form": false,
                    "filter": false,
                    "groupable": false,
				}, {
					"text": "Created By",
					"value": "created_by",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": App.url + "users",
                        "searchby": ["name"],
                        "formatter": ["id", "name"],
                        "options": {
                            "filter": {
                                "groups": "admin",
							},
                            "filterType": {
                                "groups": "%",
                            },
                            "filterCondition": {},
                    },
                    paging: true,
                    page: "1",
                    limit: "10",
                    alias: "created_by_name",
				}, {
					"text": "Created Date",
					"value": "created_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Created Date",
					"value": "crea_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": true
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
					"type": "list",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false,
                    "url": App.url + "users",
                        "searchby": ["name"],
                        "formatter": ["id", "name"],
                        "options": {
                            "filter": {
                                "groups": "admin",
							},
                            "filterType": {
                                "groups": "%",
                            },
                            "filterCondition": {},
                    },
                    paging: true,
                    page: "1",
                    limit: "10",
                    alias: "modified_by_name",
				}, {
					"text": "Modified Date",
					"value": "modified_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Modified Date",
					"value": "mod_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": true,
				}],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
			}
		},
		watch: {

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
		},
		mounted: function() {

		}
	}

</script>
