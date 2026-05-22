<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template export-excel :disable-edit-button="disableEdit" :disable-delete-button="disableEdit" @open-add="onOpenAdd" @open-edit="onEdit" @after-update="onAfterUpdate" :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
            <!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					<list-item table-only :data="{rfq_id: props.item.id}" :key="props.item.id"></list-item>
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
			 -->
            <!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
            <!-- 
            <template v-slot:append-menu>
            </template>
			 -->

			<template v-slot:item.priority_show="props">
				<div style="justify-content: center; display: flex;">
					<v-alert dense color="#ffcc99" v-if="props.item.priority=='Medium'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						{{props.item.priority}}
					</v-alert>
					<v-alert dense color="#f88686" v-if="props.item.priority=='High'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						{{props.item.priority}}
					</v-alert>
					<v-alert dense color="#bfdda8" v-if="props.item.priority=='Low'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						{{props.item.priority}}
					</v-alert>
				</div>
			</template>

            <template v-slot:after-header>
                <v-row>
                    <v-col cols="3">
                        <v-card class="mx-auto" max-width="344" outlined>
                            <v-list-item three-line>
                                <v-list-item-content>
                                    <div class="text-overline mb-4" style="font-weight: bold">
                                        New RFQ
                                    </div>
                                    <v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
                                        {{total_new_rfq}}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card>
                    </v-col>
                    <v-col cols="3">
                        <v-card class="mx-auto" max-width="344" outlined>
                            <v-list-item three-line>
                                <v-list-item-content>
                                    <div class="text-overline mb-4" style="font-weight: bold">
                                        RFQ Submitted
                                    </div>
                                    <v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
                                        {{total_submitted}}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card>
                    </v-col>
                    <v-col cols="3">
                        <v-card class="mx-auto" max-width="344" outlined>
                            <v-list-item three-line>
                                <v-list-item-content>
                                    <div class="text-overline mb-4" style="font-weight: bold">
                                        Clarification Needed
                                    </div>
                                    <v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
                                        {{total_need_clarification}}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card>
                    </v-col>
                    <v-col cols="3">
                        <v-card class="mx-auto" max-width="344" outlined>
                            <v-list-item three-line>
                                <v-list-item-content>
                                    <div class="text-overline mb-4" style="font-weight: bold">
                                        Quotation Received
                                    </div>
                                    <v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
                                        {{total_quotation_received}}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card>
                    </v-col>
                </v-row>
            </template>
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b> {{$refs.template.itemsTotal}}
            </template>
            <template v-slot:menu-after-filter>
                <v-btn small color="primary" :disabled="!selected" outlined @click="dialogItems = true">Items List</v-btn>
            </template>

            <template v-slot:item.rfq_no="props">
                <a :href="'#/rfq/rfq/'+props.item.id">{{props.item.rfq_no}}</a>
            </template>
            <!-- <template v-slot:item.id="props">
				<a :href="'#/rfq/rfq/'+props.item.id">#{{props.item.id}}</a>
			</template> -->

            <template v-slot:item.created="props">
                <span>BY:</span> {{props.item.created_by_name}}<br />
                <span>DATE:</span> {{props.item.created_date}}
            </template>
            <template v-slot:item.modified="props">
                <span>BY:</span> {{props.item.modified_by_name}}<br />
                <span>DATE:</span> {{props.item.modified_date}}
            </template>
            <template v-slot:item.reference="props">
                <span>PROJECT NO:</span> {{ props.item.mr_task_no }}<br>
                <span>RAGIC NO:</span> {{props.item.reference_no}}
            </template>
            <template v-slot:item.status="props">
				<div style="justify-content: center; display: flex;">
					<v-alert dense color="#ffcc99" v-if="props.item.status=='Quotation Received'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Received
					</v-alert>
					<v-alert dense color="#f88686" v-if="props.item.status=='Clarification'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Clarification
					</v-alert>
					<v-alert dense color="#bfdda8" v-if="props.item.status=='RFQ Submitted'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Submitted
					</v-alert>
					<v-alert dense color="#f5e699" v-if="props.item.status=='New RFQ'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						New
					</v-alert>
				</div>
            </template>
            <!-- <template v-slot:item.all_status="props">
                <span>RFQ: </span>{{props.item.status}}<br />
                <span v-if="props.item.reference_no">RAGIC:</span> {{props.item.ragic_status}}
            </template> -->
            <template v-slot:item.detail="props">
                <!-- <span>TITLE:</span> {{ props.item.title }}<br> -->
                <!-- <span>PRIORITY:</span> {{ props.item.priority }}<br> -->
                <!-- <span>GROUP:</span> {{props.item.rfq_group}}<br> -->
                <!-- <span>ASSIGNED TO:</span> {{ props.item.assigned_to_name }} -->
            </template>
            <template v-slot:item.item="props">
                <span>NO:</span> {{ props.item.item_no }}<br>
                <span>DESCRIPTION:</span> {{ props.item.item_description }}<br>
            </template>
        </v-template>


        <v-action-dialog fullscreen :actions="false" v-model="dialogItems" :title="selected.rfq_no+' List Items'">
            <list-item :data="dataId" :key="selected.id" :table-only="disableEdit"></list-item>
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'rfqList',
        creator: '',
        components: {
            'list-item': 'url:ui/rfq/list.vue'
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
            history: {
                type: Boolean,
                default: false
            },
            itemsOptions: {
                type: Object,
                default: {
                    filter: {
                        status: "'New RFQ', 'RFQ Submitted', 'Clarification', 'Quotation Received', 'Pending'"
                    },
                    filterType: {
                        status: 'in'
                    }
                }
            }
        },
        data: function() {
            return {
                name: 'rfq',
                itemKey: 'id',
                url: 'rfq/rfq',
                total_new_rfq: 0,
                total_submitted: 0,
                total_need_clarification: 0,
                total_quotation_received: 0,
                loading: false,
                dialogItems: false,
                dataId: {},
                headers: [{
                    "text": "#",
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
                    "text": "Rfq No",
                    "value": "rfq_no",
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
                    "text": "Title",
                    "value": "title",
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
                    "text": "Detail",
                    "value": "detail",
                    "align": "start",
                    "sortable": false,
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
                    "limit": "10",
                }, {
                    "text": "Allocation",
                    "value": "allocation",
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
                    "data_value": ["Primary", "Secondary", "Utility", "RND", "Tools", "Electric", "Others"],
                }, {
                    "text": "Priority",
                    "value": "priority_show",
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
                    "filter": false,
                    "groupable": false
                }, {
                    "text": "Group",
                    "value": "rfq_group",
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
                    "filter": false,
                    "groupable": false
                }, {
                    "text": "Assigned To",
                    "value": "assigned_to_name",
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
                    "filter": false,
                    "groupable": false
                }, {
                    "text": "Reference No",
                    "value": "reference",
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
                    "text": "Ragic No",
                    "value": "reference_no",
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
                }, {
                    "text": "Status",
                    "value": "all_status",
                    "align": "start",
                    "sortable": false,
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
                    "groupable": false,
                    "default": "",
                }, {
                    "text": "RFQ Status",
                    "value": "status",
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
                    "filter": false,
                    "groupable": false,
                },  {
                    "text": "Project No",
                    "value": "mr_task_no",
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
                    "groupable": false,
                },  {
                    "text": "Project",
                    "value": "project_id",
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
                    "url": App.url+"project/project",
                    "searchby": ["full"],
                    "formatter": ["id", "full"],
                    "options": {
                        "filter": {
                            flag:1
                        },
                        "filterType": {},
                        "filterCondition": {},
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "alias": "project_name"
                }, {
                    "text": "Item",
                    "value": "item",
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
                    "default": "",
                }, {
                    "text": "Item No",
                    "value": "item_no",
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
                    "form": true,
                    "filter": true,
                    "groupable": false,
                }, {
                    "text": "Item Description",
                    "value": "item_description",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "text",
                    "disabled": false,
                    "visible": false,
                    "required": false,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                }, {
                    "text": "Status RFQ",
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
                    "required": true,
                    "form": true,
                    "filter": false,
                    "groupable": false,
                    "default": "",
                    "data_value": ["New RFQ", "RFQ Submitted", "Clarification", "Quotation Received", "Final Quotation", "Cancel"],
                    "input": function(data) {
                        var self = App.$get('rfqList'),
                            value = data.data
                        if (value != null && value != undefined) {
                            if (value.match("Overseas") != null) {
                                self.headersObj['assigned_to'].options.filter.groups = 'rfq_' + value.unCamelCase().slugify("_");
                                self.headersObj['assigned_to'].options.filterType.groups = '%';
                            } else {
                                self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas_others';
                                self.headersObj['assigned_to'].options.filterType.groups = '%';
                            }
                        } else {
                            self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas';
                            self.headersObj['assigned_to'].options.filterType.groups = '!%';
                        }
                        self.headersObj['assigned_to'].update = null
                        self.headers = App.updateObject(self.headers)
                    }
                }, {
                    "text": "Status RFQ",
                    "value": "status_s",
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
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "default": "",
                    "data_value": ["New RFQ", "RFQ Submitted", "Clarification", "Quotation Received"],
                }, {
                    "text": "Status Ragic",
                    "value": "ragic_status",
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
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "default": "",
                    "data_value": ["New", "RFQ", "Clarification RFQ", "Offer Sent to BMT", "Order Confirmed by PO", "Order Placed to Vendor by OBH", "Clarification After PO", "Ready for Shipment - Germany", "Shipped to Indonesia", "Cancelled", "Complete", "Quotation Submitted", "Order Confirmed", "Waiting for Arrival - China", "Ready for Shipment - China", "Shipped to Indonesia", "Arrived in Indonesia", "Custom Cleared", "Partial Arrival", "Combined to Other Task"]
                }, {
                    "text": "Priority",
                    "value": "priority",
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
                    "data_value": ["High", "Medium", "Low"]
                }, {
                    "text": "Group",
                    "value": "rfq_group",
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
                    "default": "Local",
                    "data_value": ["Overseas Beijing", "Overseas Germany", "Local", "Overseas Others"],
                    "input": function(data) {
                        var self = App.$get('rfqList'),
                            value = data.data
                        if (value != null && value != undefined) {
                            if (value.match("Overseas") != null) {
                                self.headersObj['assigned_to'].options.filter.groups = 'rfq_' + value.unCamelCase().slugify("_");
                                self.headersObj['assigned_to'].options.filterType.groups = '%';
                            } else {
                                self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas_others';
                                self.headersObj['assigned_to'].options.filterType.groups = '%';
                            }
                        } else {
                            self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas';
                            self.headersObj['assigned_to'].options.filterType.groups = '!%';
                        }
                        self.headersObj['assigned_to'].update = null
                        self.headers = App.updateObject(self.headers)
                    }
                }, {
                    "text": "Assigned to",
                    "value": "assigned_to",
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
                    "url": App.url + "users",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "pk": "id",
                    "options": {
                        "filter": {
                            groups: 'rfq_overseas_others'
                        },
                        "filterType": {
                            groups: '%'
                        },
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "alias": "assigned_to_name"
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
                    "limit": "10",
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
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": App.url + "users",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            groups: ['RFQ', 'rfq_overseas_germany', 'rfq_overseas_beijing', 'rfq_overseas_others', 'rfq_local'],
                            is_group: '0'
                        },
                        "filterType": {
                            groups: '%',
                        },
                        "filterCondition": {
                            groups: 'OR'
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
                    "text": "Last Modified",
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
                    "required": true,
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
                    "limit": "10",
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
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": App.url + "users",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            groups: ['RFQ', 'rfq_overseas_germany', 'rfq_overseas_beijing', 'rfq_overseas_others', 'rfq_local'],
                            is_group: '0'
                        },
                        "filterType": {
                            groups: '%',
                        },
                        "filterCondition": {
                            groups: 'OR'
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
                    "required": true,
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
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Flag",
                    "value": "flag",
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
            },
            disableEdit: function() {
                var self = this
                if (!self.selected)
                    return true
                if (self.selected.status.toLowerCase() == 'close')
                    return true
                return false
            }
        },
        methods: {
            onSelectedRow: function(val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                    self.dataId = {}
                } else {
                    self.selected = val
                    self.dataId = {
                        rfq_id: val.id
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
            onEdit: function() {
                var self = this,
                    value = self.selected.rfq_group
                if (value != null && value != undefined) {
                    if (value.match("Overseas") != null) {
                        self.headersObj['assigned_to'].options.filter.groups = 'rfq_' + value.unCamelCase().slugify("_");
                        self.headersObj['assigned_to'].options.filterType.groups = '%';
                    } else {
                        self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas_others';
                        self.headersObj['assigned_to'].options.filterType.groups = '%';
                    }
                } else {
                    self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas';
                    self.headersObj['assigned_to'].options.filterType.groups = '!%';
                }
            },
            onAfterUpdate: function() {
                var self = this
                delete self.headersObj['assigned_to'].options.filter.groups
                delete self.headersObj['assigned_to'].options.filterType.groups
            },
            onOpenAdd: function() {
                var self = this
                delete self.headersObj['assigned_to'].options.filter.groups
                delete self.headersObj['assigned_to'].options.filterType.groups
                self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas_others';
                self.headersObj['assigned_to'].options.filterType.groups = '%';
            },
			async getData(){
                try {
                    var self = this;
                    var data = await axios.get(App.url+'pr/monitoring/monitoring_rfq')
					self.total_new_rfq = data.data.data[0].rfq_new
					self.total_submitted = data.data.data[0].rfq_submitted
					self.total_need_clarification = data.data.data[0].rfq_clarification
					self.total_quotation_received = data.data.data[0].rfq_quotation
				}
				catch(e){

				}
			}
        },
        mounted: function() {
            var self = this;
            if (self.history) {
                self.headersObj.status.data_value = ["Final Quotation", "Pending", "Cancel"]
                self.headersObj.status_s.filter=["Final Quotation", "Pending", "Cancel"]
            }
			self.getData()
        }
    }
</script>