<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly">
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
            <template v-slot:item.info_group="props">
                <span v-if="props.item.is_group==1">YES</span><span v-else>NO</span>
			</template>
            <template v-slot:item.hh="props">
                <span v-if="props.item.is_active==1">YES</span><span v-else>NO</span>
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
		data: function() {
			return {
				name: 'List Users and Groups',
				itemKey: 'username',
				url: 'users',
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
				},{
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
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Password",
					"value": "auth",
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
					"form": true,
					"filter": false,
					"groupable": false
				},  {
					"text": "No Handphone",
					"value": "no_hp",
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
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Email",
					"value": "email",
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
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Is Group?",
					"value": "is_group",
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
					"filter": true,
					"groupable": false,
					"default": 0,
					"data_value": [0,1]
				}, {
                    "text": "Group",
                    "value": "info_group",
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
				},{
					"text": "Is Active?",
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
					"filter": true,
					"groupable": false,
					"default": 0,
					"data_value": [0,1]
				}, {
                    "text": "Active",
                    "value": "hh",
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
				}]
			}
		},
		methods: {

		},
		mounted: function() {

		}
	}

</script>
