<template>
    <v-container v-observe-visibility="onVisible" class="" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column;">
        <v-template :show-select="false" hide-default-header :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
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

            <template v-slot:menu-after-edit>
                <v-btn color="primary" outlined small @click="dialogPart = true" :disabled="!selected">
                    <!-- <v-icon small left>mdi-plus</v-icon> -->Parts
                </v-btn>
            </template>
            <template v-slot:item.name="props">
                <v-list-item three-line>
                    <v-list-item-content>
                        <v-list-item-title class="mb-1 flex text-bold">
                            {{props.item.name}}<v-spacer></v-spacer>{{props.item.project_status}}
                        </v-list-item-title>
                        <v-list-item-subtitle>
							<v-row dense no-gutter>
								<v-col cols="6" sm="4" md="2">
									% Design<br/>
									<v-progress-linear rounded height="22px" color="#7BC6FF" style="width: 100px" :value="props.item.design">{{numberFormat(props.item.design, 2)}}</v-progress-linear>
								</v-col>
								<v-col cols="6" sm="4" md="2">
									% RFQ<br/>
									<v-progress-linear rounded height="22px" color="#7BC6FF" style="width: 100px" :value="props.item.rfq">{{numberFormat(props.item.rfq, 2)}}</v-progress-linear>
								</v-col>
								<v-col cols="6" sm="4" md="2">
									% PR<br/>
									<v-progress-linear rounded height="22px" color="#7BC6FF" style="width: 100px" :value="props.item.pr">{{numberFormat(props.item.pr, 2)}}</v-progress-linear>
								</v-col>
								<v-col cols="6" sm="4" md="2">
									% Procurement<br/>
									<v-progress-linear rounded height="22px" color="#7BC6FF" style="width: 100px" :value="props.item.procurement">{{numberFormat(props.item.procurement, 2)}}</v-progress-linear>
								</v-col>
								<v-col cols="6" sm="4" md="2">
									% Installation<br/>
									<v-progress-linear rounded height="22px" color="#7BC6FF" style="width: 100px" :value="props.item.installation">{{numberFormat(props.item.installation, 2)}}</v-progress-linear>
								</v-col>
								<v-col cols="6" sm="4" md="2">
									% Delivery<br/>
									<v-progress-linear rounded height="22px" color="#7BC6FF" style="width: 100px" :value="props.item.delivery">{{numberFormat(props.item.delivery, 2)}}</v-progress-linear>
								</v-col>
								<!--v-col cols="6" sm="4" md="2">
									% Project Progress<br/>
									<v-progress-linear rounded height="22px" color="success" style="width: 100px" :value="props.item.progress">{{numberFormat(props.item.progress, 2)}}</v-progress-linear>
								</v-col-->
								<!-- <v-col cols="12">
									<table class="tpl">
										<tr>
											<td>% Design</td>
											<td>% RFQ</td>
										</tr>
										<tr>
											<td>
												<v-progress-linear rounded height="22px" color="#7BC6FF" style="width: 100px" v-model="props.item.design">{{numberFormat(props.item.design, 2)}}</v-progress-linear>
											</td>
											<td>
												<v-progress-linear rounded height="22px" color="#7BC6FF" style="width: 100px" v-model="props.item.rfq">{{numberFormat(props.item.rfq, 2)}}</v-progress-linear>
											</td>
										</tr>
									</table>
									<v-chip label small class="nowrap inline-block mb-1"><b>% Design:</b> {{numberFormat(props.item.design, 2)}}</v-chip>
									<v-chip label small class="nowrap inline-block mb-1"><b>% RFQ:</b> {{numberFormat(props.item.rfq, 2)}}</v-chip>
									<v-chip label small class="nowrap inline-block mb-1"><b>% Procurement:</b> {{numberFormat(props.item.procurement, 2)}}</v-chip>
									<v-chip label small class="nowrap inline-block mb-1"><b>% Delivery:</b> {{numberFormat(props.item.delivery, 2)}}</v-chip>
									<v-chip label small class="nowrap inline-block mb-1"><b>% Installation:</b> {{numberFormat(props.item.installation, 2)}}</v-chip>
									<v-chip label small class="nowrap inline-block mb-1" color="primary" outlined><b>% Project Progress:</b> {{numberFormat(props.item.progress, 2)}}</v-chip>
								</v-col> -->
							</v-row>
							<v-row dense no-gutter>
								<v-col cols="12" md="10" v-html="props.item.description">
								</v-col>
								<v-col cols="12" md="2">
									% Project Progress<br/>
									<v-progress-linear rounded height="22px" color="success" style="width: 100px" :value="props.item.progress">{{numberFormat(props.item.progress, 2)}}</v-progress-linear>
								</v-col>
							</v-row>
							<v-row dense no-gutter>
								<v-col cols="12" sm="6">
									<b>Author:</b> {{props.item.created_by_name.split(' ')[0]}}, {{dateFormat(props.item.created_date).split(' ')[0]}}
								</v-col>
								<v-col cols="12" sm="6">
									<b>Ref Project No:</b> {{props.item.ref_project_no}}
								</v-col>
								<!-- <v-col cols="12" md="4" v-if="props.item.modified_by_name">
									Modified: props.item.modified_by_name, {{dateFormat(props.item.modified_date)}}
								</v-col> -->
							</v-row>
						</v-list-item-subtitle>
                    </v-list-item-content>
                </v-list-item>
            </template>

            <!-- 
            <template v-slot:append-menu>
            </template>
			 -->
        </v-template>
        <v-action-dialog :actions="false" v-model="dialogPart" title="Project Part" min-height="75%" fullscreen>
            <part :data="dataid" :key="selected.id"></part>
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
	.nowrap{
		white-space: nowrap;
	}
	.inline-block{
		display: inline-block;
	}
	.text-bold{
		font-weight: bold;
	}
	table.tpl {
        table-layout: auto;
        margin-top: 1em;
        min-width: 100%;
    }

    table.tpl th,
    table.tpl td {
        border: 1px solid #333;
        padding: 4px;
    }

    table.tpl th{
        background: #FFFF00;
    }

    table.tpl tr.first td {
        font-weight: bold;
    }

	table.tpl .top-header{
		border-top: 2px solid #333;
	}

	table.tpl .no-border{
		border-top: 0;
	}
</style>

<script>
    module.exports = {
        name: '',
        creator: '',
        components: {
            'part': 'url:ui/pm/part.vue'
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
                name: 'project',
                itemKey: 'id',
                url: 'pm/project',
                dataid: {},
                dialogPart: false,
                loading: false,
                headers: [{
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
                    "required": true,
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
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "No. Item",
                    "value": "no_item",
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
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
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
                    "page": "1",
                    "limit": "10"
                },  {
                    "text": "Description",
                    "value": "description",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "text",
                    "disabled": false,
                    "visible": false,
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
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Ref Project No",
                    "value": "ref_project_no",
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
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Project Status",
                    "value": "project_status",
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
                    "data_value": ["New", "In Progress", "Done"],
                    "default": "New",
                }, {
                    "text": "% Design",
                    "value": "design",
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
                    "page": "1",
                    "limit": "10",
                    "default": "0",
                }, {
                    "text": "% Rfq",
                    "value": "rfq",
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
                    "page": "1",
                    "limit": "10",
                    "default": "0",
                }, {
                    "text": "% PR",
                    "value": "pr",
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
                    "page": "1",
                    "limit": "10",
                    "default": "0",
                },	{
                    "text": "% Procurement",
                    "value": "procurement",
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
                    "page": "1",
                    "limit": "10",
                    "default": "0",
                }, {
                    "text": "% Delivery",
                    "value": "delivery",
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
                    "page": "1",
                    "limit": "10",
                    "default": "0",
                }, {
                    "text": "% Installation",
                    "value": "installation",
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
                    "page": "1",
                    "limit": "10",
                    "default": "0",
                }, {
                    "text": "% Project Progress",
                    "value": "progress",
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
                    "page": "1",
                    "limit": "10",
                    "default": "0",
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
                    "required": true,
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
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Created By",
                    "value": "created_by",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "int",
                    "disabled": false,
                    "visible": false,
                    "required": true,
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
                    "page": "1",
                    "limit": "10",
                    "alias": "created_by_name"
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
                    "required": true,
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
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Modified By",
                    "value": "modified_by",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "int",
                    "disabled": false,
                    "visible": false,
                    "required": true,
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
                    "page": "1",
                    "limit": "10",
                    "alias": "modified_by_name"
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
			dialogPart: function(val){
				var self = this
				if(!val)
					self.$refs.template.getItems()
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
            onSelectedRow: function(val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                    self.dataid = {}
                } else {
                    self.selected = val
                    self.dataid = {
                        project_id: val.id,
                    }
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