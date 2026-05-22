<template>
    <v-container v-if="ready" v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template hide-edit-button hide-add-button hide-delete-button @open-edit="onEdit" @after-update="onAfterUpdate" :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
            <template v-slot:expanded-item="props">
                <td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
                    <list-item :table-fixed-header="false" :item-height-as-min-height="true" :table-fill="false" table-only :data="{rfq_id: props.item.id}" :key="props.item.id"></list-item>
                </td>
            </template>
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

            <template v-slot:item.rfq_no="props">
                <a :href="'#/rfq/rfq/'+props.item.id">{{props.item.rfq_no}}</a>
            </template>
            <template v-slot:prepend-menu>
                <v-btn small color="warning" :disabled="disableEdit" v-if="$refs.template" outlined @click="$refs.template.openUpdate">Edit</v-btn>
                <v-btn small color="primary" :disabled="disableEdit" outlined @click="openComment">Comment</v-btn>
            </template>
        </v-template>
        <v-action-dialog fullscreen :actions="false" v-model="dialogItems" :title="selected.rfq_no+' List Items'">
            <list-item :data="dataId" :key="selected.id"></list-item>
        </v-action-dialog>
        <v-action-dialog fullscreen :actions="false" v-model="dialogComment" :title="selected.rfq_no+' Comment'">
            <div style="height: 100%; display: flex; flex-direction: column;">
                <div style="flex: 1; overflow-y: auto;">
                    <template v-for="(item, index) in items" :key="item.id">
                        <v-card outlined style="margin: 8px; background-color: #f6ffc9;">
                            <v-list-item two-line>
                                <v-list-item-content>
                                    <div class="text-overline mb-4">
                                        {{item.created_by_name}}, {{datetimeFormat(item.created_date)}}
                                    </div>
                                    <v-list-item-subtitle v-html="item.comment"></v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </v-card>
                    </template>
                </div>
                <v-card-actions style="flex: 0; align-items: end;">
                    <!-- <v-textarea v-model="comment" auto-grow filled label="Comment" rows="1" dense hide-details></v-textarea> -->
                    <vue-editor-mobile v-model="comment" :height="200" style="flex: 1"></vue-editor-mobile>
                    <v-btn small color="primary" outlined @click="saveComment">Save</v-btn>
                </v-card-actions>
            </div>
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
                        // rfq_group: check_user(['RFQ']) && check_user(['rfq_local']) ? '' : App.userData.groups.filter(function(val) {
                        //     return val.match('rfq_overseas') != null
                        // }).map(val=>"'"+val.replace('rfq_', '').replace(/_/ig, ' ')+"'").join()
                    },
                    filterType: {
                        // rfq_group: check_user(['RFQ']) && check_user(['rfq_local']) ? '%' : 'in'
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
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
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
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
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
					"form": true,
					"filter": true,
					"groupable": false,
				}, {
					"text": "Item Desc",
					"value": "item_description",
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
				}, {
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
                    "visible": true,
                    "required": true,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "default": "New RFQ",
                    "data_value": ["New RFQ", "RFQ Submitted", "Clarification", "Quote Received", "Final RFQ", "Final PO", "Pending", "Cancel", "Close"],
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
                    "visible": true,
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
                    "visible": true,
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "data_value": ["Overseas Beijing", "Overseas Germany", "Local", "Overseas Others"],
                    "input": function(data) {
                        var self = App.$get('rfqList'), value = data.data
						if(value!=null && value != undefined){
							if(value.match("Overseas")!=null){
								self.headersObj['assigned_to'].options.filter.groups = 'rfq_'+value.unCamelCase().slugify("_");
								self.headersObj['assigned_to'].options.filterType.groups = '%';
							}
							else{
								self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas_others';
								self.headersObj['assigned_to'].options.filterType.groups = '%';
							}
						}
						else{
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
                    "visible": true,
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
            saveComment: async function() {
                var self = this
                var res = await axios.post(App.url + 'rfq/rfqcomment', {
                    rfq_id: self.selected.id,
                    comment: self.comment,
                })
                if (!res.data.status)
                    App.errorMsg()
                else {
                    self.comment = ''
                    App.successMsg()
                }
                self.getComment()
            },
            openComment: async function() {
                var self = this
                App.loadWithBase('/rfq/rfq/' + self.selected.id + '/comment')
                /*  self.items = []
                 self.dialogComment = true
                 self.getComment() */
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
            getComment: async function() {
                var self = this
                var res = await axios.get(App.url + 'rfq/rfqcomment', {
                    params: {
                        filter: {
                            rfq_id: self.selected.id
                        },
                        limit: -1
                    }
                })
                if (!res.data.status)
                    App.errorMsg()
                else {
                    self.items = res.data.data
                }
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
				self.headersObj['assigned_to'].options.filter.groups = 'rfq_overseas_others';
				self.headersObj['assigned_to'].options.filterType.groups = '%';
            },
        },
        mounted: function() {
            var self = this
            self.ready = true

        }
    }
</script>