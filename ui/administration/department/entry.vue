<template>
	<v-container
		style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
			:table-only="tableOnly">
            <template v-slot:title-body v-if="$refs.template" >
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
		   	<template v-slot:item.created="props">
			    <b>Created</b><br/>
				<span>BY:</span> {{props.item.created_by_name}}<br/>
				<span>DATE:</span> {{props.item.created_date}}<br/><br/>
				<b>Modified</b><br/>
				<span>BY:</span> {{props.item.modified_by_name}}<br/>
				<span>DATE:</span> {{props.item.modified_date}}
			</template>
			<template v-slot:item.modified="props">
				<span>BY:</span> {{props.item.modified_by_name}}<br/>
				<span>DATE:</span> {{props.item.modified_date}}<br/>
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
				default: false
			}
		},
		data: function () {
			return {
				name: 'department',
				itemKey: 'id',
				url: 'administration/department/department',
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
					"filter": false,
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
					"text": "Department Code",
					"value": "dept_code",
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
					"text": "Department Name",
					"value": "dept_name",
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
					"text": "Approval",
					"value": "approval_1",
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
					"form": true,
					"filter": true,
					"groupable": false,
					"url": App.url + "users",
					"searchby": ["name"],
					"formatter": ["id", "name"],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "approval_1_name"
				}/* , {
					"text": "Approval 2",
					"value": "approval_2",
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
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "approval_2_name"
				} */, {
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
					"required": false,
					"form": true,
					"filter": false,
					"groupable": false,
					"data_value": ["0", "1"],
					"default": 1
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
                    "url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            group_name: "adm_admin",
                            sub_group_name: "adm_admin"
                        },
                        "filterType": {
                        },
                        "filterCondition": {
                            group_name:'or',
                            sub_group_name:'or'
                        }
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
					"filter": true,
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
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
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
				}, 
				{
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
					"url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            group_name: "adm_admin",
                            sub_group_name: "admm_admin"
                        },
                        "filterType": {
                        },
                        "filterCondition": {
                            group_name:'or',
                            sub_group_name:'or'
                        }
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
					"groupable": false,
					"url": "",
					"search": [],
					"formatter": [],
					"options": {
						"filter": {
                        },
						"filterType": {
                        },
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}
				]
			}
		},
		methods: {

		},
		mounted: function () {

		}
	}
</script>