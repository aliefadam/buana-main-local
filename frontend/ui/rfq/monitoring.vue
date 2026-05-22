<template>
    <v-container v-if="ready" v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template export-excel hide-edit-button hide-add-button hide-delete-button :data="data"  ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
            <template v-slot:expanded-item="props">
                <td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
                    <list-item :table-fixed-header="false" :item-height-as-min-height="true" :table-fill="false" table-only :data="{rfq_id: props.item.id}" :key="props.item.id"></list-item>
                </td>
            </template>
            <template v-slot:title-body v-if="$refs.template">
				<b>Count Rows: </b> {{$refs.template.itemsTotal}}
            </template>

            <template v-slot:item.created="props">
				<span>BY:</span> {{props.item.created_by_name}}<br/>
				<span>DATE:</span> {{props.item.created_date}}
			</template>
			<template v-slot:item.modified="props">
				<span>BY:</span> {{props.item.modified_by_name}}<br/>
				<span>DATE:</span> {{props.item.modified_date}}
			</template>
            <template v-slot:item.reference="props">
                <span>PROJECT NO:</span> {{ props.item.mr_task_no }}<br>
				<span>RAGIC NO:</span> {{props.item.reference_no}}
			</template>
            <template v-slot:item.all_status="props">
				<span>RFQ: </span>{{props.item.status}}<br/>
				<span v-if="props.item.reference_no">RAGIC:</span> {{props.item.ragic_status}}
			</template>
            <template v-slot:item.detail="props">
                <span>TITLE:</span> {{ props.item.title }}<br>
                <span>PRIORITY:</span> {{ props.item.priority }}<br>
				<span>GROUP:</span> {{props.item.rfq_group}}<br>
                <span>ASSIGNED TO:</span> {{ props.item.assigned_to_name }}
			</template>
            <template v-slot:item.item="props">
                <span>NO:</span> {{ props.item.item_no }}<br>
                <span>DESCRIPTION:</span> {{ props.item.item_description }}<br>
			</template>
        </v-template>
        <v-action-dialog fullscreen :actions="false" v-model="dialogItems" :title="selected.rfq_no+' List Items'">
            <list-item :data="dataId" :key="selected.id"></list-item>
        </v-action-dialog>
    </v-container>
</template>

<style>
    .vue-editor-mobile>.toolbar>ul>li .icon {
        font-size: 16px;
    }
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
                default: true
            },
            singleExpand: {
                type: Boolean,
                default: true
            },
            itemsOptions: {
                type: Object,
                default: {
                    filter: {
                    },
                    filterType: {
                    }
                }
            }
        },
        data: function() {
            return {
                name: 'rfq',
                itemKey: 'id',
                url: 'rfq/dashboard',
                dialogComment: false,
                dialogItems: false,
                loading: false,
                comment: '',
                items: [],
                dataId: {},
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
                    "filter": true,
                    "groupable": false,
                    "default": "New RFQ",
                    "data_value": ['New RFQ', 'RFQ Submitted', 'Clarification', 'Quotation Received', 'Final Quotation', 'Pending', 'Cancel' ],
                },  {
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
					"data_value": ["New", "RFQ", "Clarification RFQ", "Offer Sent to BMT", "Order Confirmed by PO", "Order Placed to Vendor by OBH",  "Clarification After PO", "Ready for Shipment - Germany", "Shipped to Indonesia", "Cancelled", "Complete"]
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
				},  {
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
					"visible": true,
					"required": true,
					"form": false,
					"filter": false,
					"groupable": false,
                    "default": "",
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
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "data_value": ["High", "Medium", "Low"],
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
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "data_value": ["Overseas Beijing", "Overseas Germany", "Local", "Overseas Others"],
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
                    "options": {
                        "filter": {
                            "is_group": "0"
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
                },  {
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
                    "type": "int",
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
                    "limit": "10",
                    "alias": "created_by_name"
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
                },  {
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
                    "type": "int",
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
                    "limit": "10",
                    "alias": "modified_by_name"
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
                    text: '',
                    value: 'data-table-expand'
                }],
                //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
                selected: false,
                //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
                selectedAll: [],
                isInDom: false,
                isInViewport: false,
                ready: false,
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
        },
        mounted: function() {
            var self = this
            self.ready = true

        }
    }
</script>