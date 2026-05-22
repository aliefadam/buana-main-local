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

            <template v-slot:item.design_date="props">
                <template v-if="props.item.design_date">
                    <!-- date: {{props.item.design_date}}<br />
                    ref. design no: {{props.item.ref_design_no}}<br /> -->
                    <!-- <v-btn small color="primary" outlined @click="add('design', props.item)">Edit</v-btn> -->
					<v-icon color="success" @click="add('design', props.item)">mdi-check-bold</v-icon>
                </template>
                <template v-else>
					<v-icon color="error" @click="add('design', props.item)">mdi-close-thick</v-icon>
                    <!-- <v-btn small color="primary" outlined @click="add('design', props.item)">Add</v-btn> -->
                </template>
            </template>

            <template v-slot:item.rfq_date="props">
                <template v-if="props.item.rfq_date">
                    <!-- date: {{props.item.rfq_date}}<br />
                    RFQ no: {{props.item.rfq_no}}<br /> -->
                    <!-- <v-btn small color="primary" outlined @click="add('rfq', props.item)">Edit</v-btn> -->
					<v-icon color="success" @click="add('rfq', props.item)">mdi-check-bold</v-icon>
                </template>
                <template v-else>
					<v-icon color="error" @click="add('rfq', props.item)">mdi-close-thick</v-icon>
                    <!-- <v-btn small color="primary" outlined @click="add('rfq', props.item)">Add</v-btn> -->
                </template>
            </template>

            <template v-slot:item.po="props">
                <template v-if="props.item.po">
                    <!-- date: {{props.item.po_date}}<br />
                    PO no: {{props.item.po_no}}<br /> -->
                    <!-- <v-btn small color="success" outlined @click="add('po', props.item)" icon><v-icon>mdi-check-bold</v-icon></v-btn> -->
					<v-icon color="success" @click="add('po', props.item)">mdi-check-bold</v-icon>
                </template>
                <template v-else>
					<v-icon color="error" @click="add('po', props.item)">mdi-close-thick</v-icon>
                    <!-- <v-btn small color="error" outlined @click="add('po', props.item)" icon><v-icon>mdi-close-thick</v-icon></v-btn> -->
                </template>
            </template>
            
            <template v-slot:item.pr_no="props">
                <template v-if="props.item.pr_no">
                    <!-- date: {{props.item.po_date}}<br />
                    PO no: {{props.item.po_no}}<br /> -->
                    <!-- <v-btn small color="success" outlined @click="add('po', props.item)" icon><v-icon>mdi-check-bold</v-icon></v-btn> -->
					<v-icon color="success" @click="add('pr', props.item)">mdi-check-bold</v-icon>
                </template>
                <template v-else>
					<v-icon color="error" @click="add('pr', props.item)">mdi-close-thick</v-icon>
                    <!-- <v-btn small color="error" outlined @click="add('po', props.item)" icon><v-icon>mdi-close-thick</v-icon></v-btn> -->
                </template>
            </template>

            <template v-slot:item.delivery_finished="props">
                <template v-if="props.item.delivery_finished==1">
                    <!-- date: {{props.item.delivery_date}}<br /> -->
                    <!-- <v-btn small color="primary" outlined @click="add('delivery', props.item)">Edit</v-btn> -->
					<v-icon color="success" @click="add('delivery', props.item)">mdi-check-bold</v-icon>
                </template>
                <template v-else>
					<v-icon color="error" @click="add('delivery', props.item)">mdi-close-thick</v-icon>
                    <!-- <v-btn small color="primary" outlined @click="add('delivery', props.item)">Add</v-btn> -->
                </template>
            </template>

            <template v-slot:item.complete_installation_date="props">
                <template v-if="props.item.complete_installation_date">
                    <!-- date: {{props.item.complete_installation_date}}<br /> -->
                    <!-- <v-btn small color="primary" outlined @click="add('complete installation', props.item)">Edit</v-btn> -->
					<v-icon color="success" @click="add('complete installation', props.item)">mdi-check-bold</v-icon>
                </template>
                <template v-else>
					<v-icon color="error" @click="add('complete installation', props.item)">mdi-close-thick</v-icon>
                    <!-- <v-btn small color="primary" outlined @click="add('complete installation', props.item)">Add</v-btn> -->
                </template>
            </template>
			

            <template v-slot:item.progress="props">
				{{props.item.progress}}%
			</template>

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
        </v-template>

        <v-action-dialog @save="saveInfo" v-model="dialog" :title="target" min-height="75%">
            <v-autoform hide-details v-model="formPart" :valid.sync="valid"></v-autoform>
        </v-action-dialog>

        <v-action-dialog :actions="false" v-model="dialogDelivery" title="Delivery" fullscreen>
            <delivery :data="opt" :key="opt.pm_project_part_id" :delivery="delivery"></delivery>
        </v-action-dialog>

        <v-action-dialog :actions="false" v-model="dialogPo" title="Purchase Order" fullscreen>
            <po :data="opt" :key="opt.pm_project_part_id"></po>
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'part',
        creator: '',
        components: {
            'delivery': 'url:ui/pm/delivery.vue',
            'po': 'url:ui/pm/po.vue'
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
                name: 'part',
                itemKey: 'id',
                url: 'pm/projectpart',
                target: '',
                dialog: false,
                dialogDelivery: false,
                dialogPo: false,
                loading: false,
                delivery: 0,
                valid: false,
				opt: {},
				formPart: [],
                form: {
                    design: [{
                        "text": "Design Date",
                        "value": "design_date",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "date",
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
                        "text": "Ref. Design No",
                        "value": "ref_design_no",
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
                        "text": "Modified Date",
                        "value": "design_modified_date",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}, {
                        "text": "Modified By",
                        "value": "design_modified_by_name",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}],
                    pr: [{
                        "text": "PR",
                        "value": "pr_no",
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
                        "text": "Modified Date",
                        "value": "pr_modified_date",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}, {
                        "text": "Modified By",
                        "value": "pr_modified_by_name",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}],
                    po: [{
                        "text": "PO",
                        "value": "po_no",
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
                        "text": "Po Date",
                        "value": "po_date",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "date",
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
                        "text": "Modified Date",
                        "value": "po_modified_date",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}, {
                        "text": "Modified By",
                        "value": "po_modified_by_name",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}],
                    delivery: [{
                        "text": "Delivery",
                        "value": "delivery_date",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "date",
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
                        "text": "Modified Date",
                        "value": "delivery_modified_date",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}, {
                        "text": "Modified By",
                        "value": "delivery_modified_by_name",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}],
                    "complete installation": [{
                        "text": "Installation",
                        "value": "complete_installation_date",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "date",
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
                        "text": "Modified Date",
                        "value": "complete_modified_date",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}, {
                        "text": "Modified By",
                        "value": "complete_modified_by_name",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}],
                    rfq: [{
                        "text": "Rfq",
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
                        "text": "Rfq Date",
                        "value": "rfq_date",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "date",
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
                        "text": "Modified Date",
                        "value": "rfq_modified_date",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}, {
                        "text": "Modified By",
                        "value": "rfq_modified_by_name",
						"form": true,
                        "type": "varchar",
                        "disabled": true,
                        "readonly": true
					}]
                },
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
                    "text": "Project Id",
                    "value": "project_id",
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
                    "text": "Part Name",
                    "value": "item_id",
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
                    "text": "Internal Part No",
                    "value": "internal_part_no",
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
                    "text": "Design",
                    "value": "design_date",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "date",
                    "disabled": false,
                    "visible": true,
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
                    "text": "Rfq",
                    "value": "rfq_date",
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
                    "text": "PR",
                    "value": "pr_no",
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
                    "text": "PO",
                    "value": "po",
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
                    "text": "Delivery",
                    "value": "delivery_finished",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "date",
                    "disabled": false,
                    "visible": true,
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
                    "text": "Installation",
                    "value": "complete_installation_date",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "date",
                    "disabled": false,
                    "visible": true,
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
                    "text": "% Progress",
                    "value": "progress_done",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "float",
                    "disabled": false,
                    "visible": true,
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
				saveId: false
            }
        },
        computed: {
            designObj: function(){
                var tmp = {}, self = this
                Object.keys(self.form.design).map(key=>{
                    var val = self.form.design[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            prObj: function(){
                var tmp = {}, self = this
                Object.keys(self.form.pr).map(key=>{
                    var val = self.form.pr[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            poObj: function(){
                var tmp = {}, self = this
                Object.keys(self.form.po).map(key=>{
                    var val = self.form.po[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            rfqObj: function(){
                var tmp = {}, self = this
                Object.keys(self.form.rfq).map(key=>{
                    var val = self.form.rfq[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            deliveryObj: function(){
                var tmp = {}, self = this
                Object.keys(self.form.delivery).map(key=>{
                    var val = self.form.delivery[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            "complete installationObj": function(){
                var tmp = {}, self = this
                Object.keys(self.form["complete installation"]).map(key=>{
                    var val = self.form["complete installation"][key]
                    tmp[val.value] = val
                })
                return tmp;
            },
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
        watch: {

        },
        methods: {
            add: function(target, value) {
                var self = this
				if(target=='delivery'){
					self.opt = {
						pm_project_part_id: value.id
					}
					self.delivery = value.delivery_finished
					self.dialogDelivery = true
				}
				else if(target=='po'){
					self.opt = {
						pm_project_part_id: value.id
					}
					self.dialogPo = true
				}
				else{
					self.saveId = value.id
					Object.values(self[target+'Obj']).map(val=>{
						val.data = value[val.value]
					})
					self.formPart = App.updateObject(self.form[target])
					
					self.$nextTick(function(){
						self.target = target
						self.valid = false
						self.dialog = true
					})
				}
            },
            saveInfo: async function() {
                var self = this
                var tmp = {}
                self.form[self.target].map(function(val) {
                    if (val.data != null)
                        tmp[val.value] = val.data
                })
				tmp.pk = self.saveId 
                var res = await axios.put(App.url + 'pm/projectpart', tmp)
                if (!res.data.status) {
                    App.errorMsg()
                } else {
                    App.successMsg()
					self.$refs.template.getItems()
					self.dialog=false
                }
            },
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