<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template hide-delete-button  :data="data"  @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly">
            <template v-slot:menu-after-filter>
				<v-btn small color="primary" outlined :disabled="selected == false" @click="dialogPart=true">Parts</v-btn>
            </template>
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
            <template v-slot:item.info="props">
                {{props.item.name}}<br/>
            </template>
			<template v-slot:item.pic_info="props">
				<span>CATEGORY:</span> {{props.item.category}}<br/>
				<span>PROJECT NAME:</span> {{props.item.project_name}}<br/>
			</template>
		    <template v-slot:item.detail_info="props">
				<span>DESCRIPTION:</span> {{props.item.description}}<br/>
				<span>CLIENT NAME:</span> {{props.item.client_name}}<br/>
				<span>CATEGORY:</span> {{props.item.category}}<br/>
				<span>PLAN START DATE:</span> {{props.item.plan_start_date}}<br/>
				<span>PLAN END DATE:</span>  {{props.item.plan_end_date}}<br/>
			</template>
            <template v-slot:item.created="props">
			    <b>Created</b><br/>
				<span>BY:</span> {{props.item.created_by_name}}<br/>
				<span>DATE:</span> {{props.item.created_date}}<br/><br/>
				<b>Modified</b><br/>
				<span>BY:</span> {{props.item.modified_by_name}}<br/>
				<span>DATE:</span> {{props.item.modified_date}}
			</template>
		</v-template>
        <v-action-dialog :actions="false" v-model="dialogPart" title="Purchase Requisition Item" min-height="70%" fullscreen>
            <project-part :key="selected.id" :data="dataid" :sel="processData" :table-only="tableOnly"></project-part>
        </v-action-dialog>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: '',
        components: {
			'project-part': 'url:ui/administration/project/projectitem.vue',
		},
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
				name: 'Project',
				itemKey: 'id',
				url: 'administration/project/project',
                dialogPart: false,
                dataid: {},
                selected: false,
                processData: {},
				headers: [
                {
					"text": "Id",
					"value": "id",
					"align": "start",
					"sortable": false,
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
					"cellClass": "",
					
				},                
                {
					"text": "Project No",
					"value": "project_no",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"type": "auto",
					"disabled": false,
					"form": true,
                    "required": true,
					"filter": false,
					"groupable": false,
					"cellClass": "",
					
				}, 
                {
                    "text": "Project No",
                    "value": "project_no",
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
                },
                {
                    "text": "Project Name",
                    "value": "project_name",
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
                },
                {
                    "text": "Category",
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
                    "data_value": [{
                        text: "Project",
                        value: "Project"
                    },{
                        text: "Aset",
                        value: "Aset"
                    }],
                },
                {
                    "text": "Description",
                    "value": "description",
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
                },
                {
                    "text": "Client Name",
                    "value": "client_name",
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
                    "filter": true,
                    "groupable": false
                },
                {
                    "text": "Project Year",
                    "value": "prj_year",
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
                    "filter": true,
                    "groupable": false
                },
                {
                    "text": "Plan Start Date",
                    "value": "plan_start_date",
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
                    "form": true,
                    "filter": true,
                    "groupable": false
                },
                {
                    "text": "Plan End Date",
                    "value": "plan_end_date",
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
                    "form": true,
                    "filter": true,
                    "groupable": false
                },
                {
                    "text": "Status",
                    "value": "status",
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
                    "data_value": [{
                        text: "Not Started",
                        value: "0"
                    },{
                        text: "In Progress",
                        value: "1"
                    },{
                        text: "Completed",
                        value: "2"
                    }]
                },
                {
                    "text": "PIC",
                    "value": "pic",
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
                    "filter": true,
                    "groupable": false
                },
                {
                    "text": "no",
                    "value": "no",
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
                },
                {
					"text": "Project Info",
					"value": "pic_info",
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
				},
                {
					"text": "Detail",
					"value": "detail_info",
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
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "Created/Modified",
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
				}]
			}
		},
		methods: {
            onSelectedRow: function(val) {
				var self = this
				if (val === undefined) {
					self.selected = false
                    self.processData = {}
					self.dataid = {}
				} else {
					self.selected = val
                    self.processData = {
                        project_id: val.id,
                    }
                    self.dataid = {
                        project_id: val.id,
                    }
				}
			},
			onSelectedRowAll: function(val) {
				var self = this
				self.selectedAll = val
			},
			category: function(f){
				if(f=="Project"){
					return 'Project'
				}
				if(f=="Aset"){
					return 'Aset'
				}
			},
			bank_account_residence: function(f){
				if(f==1){
					return 'Residence / Penduduk'
				}
				if(f==2){
					return 'Non Residence / Bukan Penduduk'
				}
			},
            status: function(f){
                if (f==0) {
                    return 'Not Started'
                }
                if (f==1) {
                    return 'In Progress'
                }
                if (f==3) {
                    return 'Completed'
                }
            }
		},
		mounted: function() {

		}
	}

</script>
