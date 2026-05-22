<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template export-excel :disable-edit-button="disableEdit" :disable-delete-button="disableEdit" :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"  :table-fixed-header="tableFixedHeader" table-only>
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
                        <v-card class="mx-auto" max-width="344" outlined title="Total RFQ with Status Final Quotation">
                            <v-list-item three-line>
                                <v-list-item-content>
                                    <div class="text-overline mb-4" style="font-weight: bold">
                                    Final Quotation 
                                    </div>
                                    <v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
                                        {{total_final_quotation}}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card>
                    </v-col>
                    <v-col cols="3">
                        <v-card class="mx-auto" max-width="344" outlined title="Total RFQ with Status Cancel">
                            <v-list-item three-line>
                                <v-list-item-content>
                                    <div class="text-overline mb-4" style="font-weight: bold">
                                        Cancel
                                    </div>
                                    <v-list-item-title class="text-h5 mb-1" style="font-weight: bold">
                                        {{ total_cancel }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card>
                    </v-col>
                </v-row>
            </template>
            <!-- <template v-slot:title-body>
                <b>RFQ History: </b> {{total_rfq}}
            </template> -->
            <template v-slot:title-body v-if="$refs.template">
                <b>RFQ History: </b> {{$refs.template.itemsTotal}}
            </template>

            <template v-slot:item.rfq_no="props">
                <a :href="'#/rfq/rfq/'+props.item.id">{{props.item.rfq_no}}</a>
            </template>

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
					<v-alert dense color="#f88686" v-if="statusRfq(props.item.status, props.item.ragic_status, props.item.rfq_group)=='Cancel'" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Cancel
					</v-alert>
					<v-alert dense color="#f5e699" v-if="statusRfq(props.item.status, props.item.ragic_status, props.item.rfq_group)=='Final Quotation'"  style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						Final Quotation
					</v-alert>
                    <v-alert v-if="statusRfq(props.item.status, props.item.ragic_status, props.item.rfq_group)==props.item.ragic_status" style="margin: 0 !important; padding: 4px 8px; width: auto; display: inline-block; font-weight: bold; font-size: .875rem;">
						{{props.item.ragic_status}}
					</v-alert>
				</div>
            </template>
            <template v-slot:item.detail="props">
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
                        status: "'Final Quotation', 'Cancel'",
                        ragic_status:"'Order Confirmed', 'Order Confirmed by PO', 'Order Placed to Vendor by OBH', 'Clarification After PO', 'Ready for Shipment - Germany', 'Shipped to Indonesia', 'Cancelled', 'Complete', 'Order Confirmed', 'Waiting for Arrival - China', 'Ready for Shipment - China', 'Shipped to Indonesia', 'Arrived in Indonesia', 'Custom Cleared', 'Partial Arrival', 'Combined to Other Task'"
                    },
                    filterType: {
                        status: 'in',
                        ragic_status: 'in'
                    },
                    filterCondition:{
                        ragic_status:'AND'
                    }
                }
            }
        },
        data: function() {
            return {
                name: 'rfq',
                itemKey: 'id',
                url: 'rfq/rfq',
                total_cancel: 0,
                total_final_quotation:0,
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
                    "data_value": ["Primary", "Secondary", "Tools", "Electronic", "Others"],
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
                    "text": "Status",
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
                    "groupable": false
                }, {
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
                    "required": true,
                    "form": true,
                    "filter": true,
                    "groupable": false,
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
                },  {
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
                    "visible": false,
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "default": "",
                    "data_value": ["New", "RFQ", "Clarification RFQ", "Offer Sent to BMT", "Quotation Submitted"]
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
                    "data_value": ["Overseas Beijing", "Overseas Germany", "Local", "Overseas Others"]
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
                            groups: 'RFQ'
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
                            groups: 'RFQ',
                            is_group: '0'
                        },
                        "filterType": {
                            groups: '%',
                        },
                        "filterCondition": {
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
                            groups: 'RFQ',
                            is_group: '0'
                        },
                        "filterType": {
                            groups: '%',
                        },
                        "filterCondition": {
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
            statusRfq: function(f, g, h){
                
				if(h=='Local' && f == 'Final Quotation'){
					return 'New'
				}
				else if(h=='Overseas Germany' && g == 'New' || g=='RFQ'){
					return 'New'
				}
                else if(h=='Overseas Beijing' && g == 'New'){
					return 'New'
				}
                else if(h=='Local' && f=='Clarification'){
                    return 'Clarification'
                }
                else if(h=='Overseas Germany' && g == 'Clarification RFQ'){
					return 'Clarification'
				}
                else if(h=='Overseas Beijing' && g == 'Clarification'){
					return 'Clarification'
				}
                else if(h=='Local' && f=='Quotation Received'){
                    return 'Received'
                }
                else if(h=='Overseas Germany' && g == 'Offer Sent to BMT'){
					 return 'Received'
				}
                else if(h=='Overseas Beijing' && g == 'Quotation Submitted'){
					 return 'Received'
				}
                else {
                    return g
                }
				
			},
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
			async getData(){
                try {
                    var self = this;
                    var data = await axios.get(App.url+'rfq/rfq/total_rfq')
                    self.total_rfq = parseInt(data.data.data[0].rfq_new)+parseInt(data.data.data[0].rfq_clarification)+parseInt(data.data.data[0].rfq_quotation)
					self.total_new_rfq = data.data.data[0].rfq_new
					self.total_need_clarification = data.data.data[0].rfq_clarification
					self.total_quotation_received = data.data.data[0].rfq_quotation
				}
				catch(e){

				}
			}
        },
        mounted: function() {
            var self = this;
			self.getData()
        }
    }
</script>