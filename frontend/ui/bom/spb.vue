<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :disable-delete-button="disableDelete" :disable-edit-button="disableEdit" :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">

			
			 <template v-slot:menu-after-filter>
                <!--<v-btn small color="primary" outlined @click="openPrint" :disabled="!selected">Print SPB</v-btn>-->
                <v-btn color="primary" v-if="!hideApproval" outlined small @click="openNote('askApproval')" :disabled="disableAskApproval">
                    Ask Approval
                </v-btn>
            </template>
             <template v-slot:item.approved="props">
                {{ status[props.item.approved] }}<br><br>
                <span v-if="props.item.approved_date">
                <b>Validated</b><br/>
                <span>By:</span> {{props.item.approved_by_name}}<br/>
                <span>Date:</span> {{props.item.approved_date}}<br/><br/>
                </span>
                <span v-else></span>
				<!-- <b v-if="props.item.approval_date">Approved 1 Date:  {{props.item.approval_date}}</b> -->

			</template>
            
            <template v-slot:append-menu>
                <v-btn small color="primary" outlined @click="openItem" :disabled="!selected">
                    Items
                </v-btn>
            </template>
             <template v-slot:item.spb_details="props">
                <span>Notes:</span> {{ props.item.notes }}<br />
                <span>Created By:</span> {{ props.item.created_by_name }}<br />
                <span>Created date:</span> {{ props.item.created_date }}<br />
            </template>
            
            
		</v-template>
        <v-action-dialog fullscreen :actions="false" v-model="dialogItem" title="SPB Item">
            <item :data="stock"></item>
        </v-action-dialog>
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
            'item': 'url:ui/bom/spbitem.vue'
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
			hideApproval: {
                type: Boolean,
                default: false,
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
				default: "flag"
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
					filter: {
					    approved: "0, 3",
					},
					filterType: {
					     approved: "btw",
					}
				}
			}
		},
		data: function() {
			return {
				name: 'spb',
                stock: {},
				itemKey: 'id',
				url: 'bom/spb',
				loading: false,
				status: {
                    "-1": "Rejected",
                    0: "New",
                    1: "Asking for Approval Logistik",
                    2: "Asking for Approval Kepala Bagian",
                    4: "Approved by Kepala Bagian",
                },
                dialogNote: false,
				action: '',
				dialogItem: false,
				headers: [{
                        text: "Receive Details",
                        value: "spb_details",
                        align: "start",
                        sortable: false,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "varchar",
                        disabled: false,
                        visible: true,
                        required: true,
                        form: false,
                        filter: false,
                        groupable: false,
                        url: "",
                        searchby: [],
                        formatter: [],
                        options: {
                            filter: {},
                            filterType: {},
                            filterCondition: {},
                        },
                        paging: true,
                        page: "1",
                        limit: "10",
                    },{
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
					"text": "Notes",
					"value": "notes",
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
				},  {
                        text: "Arrival Date",
                        value: "arrival_date",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "date",
                        disabled: false,
                        visible: true,
                        required: true,
                        form: true,
                        filter: true,
                        groupable: false,
                        url: "",
                        searchby: [],
                        formatter: [],
                        options: {
                            filter: {},
                            filterType: {},
                            filterCondition: {},
                        },
                        paging: true,
                        page: "1",
                        limit: "10",
                    },  {
                        text: "Logistik",
                        value: "kelog_id",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "list",
                        disabled: false,
                        visible: true,
                        required: true,
                        form: true,
                        filter: true,
                        groupable: false,
                        url: App.url + "users",
                        searchby: ["name"],
                        formatter: ["id", "name"],
                        options: {
                            filter: {
                                groups: "kepala_logistik",
                            },
                            filterType: {
                                groups: "%",
                            },
                            filterCondition: {},
                        },
                        paging: true,
                        page: "1",
                        limit: "10",
                        alias: "kelog_name",
                    },{
                        text: "Kepala Bagian",
                        value: "kabag_id",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "list",
                        disabled: false,
                        visible: true,
                        required: true,
                        form: true,
                        filter: true,
                        groupable: false,
                        url: App.url + "users",
                        searchby: ["name"],
                        formatter: ["id", "name"],
                        options: {
                            filter: {
                                groups: "spb_kabag",
                            },
                            filterType: {
                                groups: "%",
                            },
                            filterCondition: {},
                        },
                        paging: true,
                        page: "1",
                        limit: "10",
                        alias: "kabag_name",
                    }, {
					"text": "Status",
					"value": "approved",
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
					"data_value": [{
                            text: "New",
                            value: 0
                        }, {
                            text: "Asking for Approval Logistik",
                            value: 1
                        }, {
                            text: "Asking for Approval Kepala Bagian",
                            value: 2
                        }],
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
			disableAskApproval: function() {
                var self = this;
                if (self.selected == false) return true;
                if (self.selected.approved <= 0) return false;
                return true;
            },
            disableEdit: function() {
                var self = this;
                if (self.selected == false) return true;
                if (self.selected.approved <= 0) return false;
                return true;
            },
            disableDelete: function() {
                var self = this;
                if (self.selected == false) return true;
                if (self.selected.approved < 2) return false;
                return true;
            },
		},
		methods: {
			openItem() {
				this.dialogItem = true
			},
			openNote: function(action){
				var self = this
				try {
					self.action = action
					self.dialogNote = true
					self.ask_approval_notes = ''

				} catch (err) {
					App.errorMsg()
				}
			},
			onSelectedRow: function(val) {
				var self = this
				if (val === undefined) {
					self.selected = false
					self.stock = {}
				} else {
					self.selected = val
					self.stock = {
						bom_spb_id: val.id
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
			}
		},
		mounted: function() {

		}
	}

</script>
