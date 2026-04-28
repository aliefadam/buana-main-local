<template>
    <v-container v-observe-visibility="onVisible"
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :item-height-as-min-height="itemHeightAsMinHeight" :table-only="tableOnly" :table-fill="tableFill" :table-fixed-header="tableFixedHeader" :items-options="itemsOptions" :data="data" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll">
            
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
         <template v-slot:item.add_activity="props">
            <br/>
                <v-btn color="primary" outlined small @click="dialogActivity = true"><v-icon small left>mdi-plus</v-icon>
                    Activity
                </v-btn><br/><br/>
        </template>
        <template v-slot:item.is_part="props">
            <b>{{isPart(props.item.is_part)}}</b>
        </template>
    </v-template>
    <v-action-dialog :actions="false" v-model="dialogActivity" title="Report Rebuild" min-height="75%" fullscreen :key="selected.id">
            <report-rebuild-2 :key="selected.part_id"  :data="dataid" ></report-rebuild-2> 
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'Detail',
        props: {
            value: undefined,
            data: {
                type: Object
            },
            sel: {
                type: Object
            },
            tableOnly: {
                type: Boolean,
                default: true
            },
            isDashboard: {
                type: Boolean,
                default: false
            },
            tableFixedHeader: {
                type: Boolean,
                default: true
            },
            showExpand: {
                type: Boolean,
                default: false
            },
            itemHeightAsMinHeight: {
                type: Boolean,
                default: false
            },
            tableFill: {
                type: Boolean,
                default: true
            },
			itemsOptions: {
				type: Object,
				default: {
					filter: {
					},
					filterType: {
					},
					sortBy: ['is_part', 'index_no'],
					sortDesc: [ false, false]
				}
			}
        },
        components: {
             'report-rebuild-2': 'url:ui/rebuildmachine/report_rebuild_2.vue',
        },
        data: function () {
            return {
                valid: false,
                name: 'Activity',
                processData: {},
                itemKey: 'id',
                url: 'rebuildmachine/partlistrebuild',
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
				},
				{
					"text": "Part No",
					"value": "part_number",
					"align": "start",
					"sortable": false,
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
					"url": App.url + "rebuildmachine/partlistrebuild",
                    "searchby": ['partno_info'],
                    "formatter": ['part_number', 'partno_info'],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {},
                        partno: true
                    },
					"paging": true,
					"page": "1",
					"limit": "10"
				},  
				{
					"text": "Part No",
					"value": "part_number",
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
					"form": true,
					"filter": false,
					"groupable": false
				}, {
					"text": "Part No Description",
					"value": "part_number_desc",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": false,
					"groupable": false
				}, 
				{
					"text": "Sub Assembly",
					"value": "sub_assembly",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": false,
					"groupable": false,
					"url": App.url + "rebuildmachine/listprotos80creg1",
                    "searchby": ['subassy_info'],
                    "formatter": ['sub_assembly', 'subassy_info'],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {},
                        subassy: true
                    },
					"paging": true,
					"page": "1",
					"limit": "10"
				}, 
				{
					"text": "Sub Assembly",
					"value": "sub_assembly",
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
					"form": true,
					"filter": false,
					"groupable": false
				}, {
					"text": "Sub Assembly Description",
					"value": "sub_assembly_desc",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
					"filter": false,
					"groupable": false,
				},{
					"text": "Section",
					"value": "section",
					"align": "start",
					"sortable": false,
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
					"data_value": ["SE", "VE", "MAX", "HCF"],
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Article No",
					"value": "article_number",
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
					"text": "Application",
					"value": "application",
					"align": "start",
					"sortable": false,
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
					"text": "QTY",
					"value": "qty",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "float",
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
					"text": "UOM",
					"value": "uom",
					"align": "start",
					"sortable": false,
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
					"data_value": ["PC", "KIT", "M", "L", "KG", "ROL", "PCK", "M²", "SET", "PS", "PR"],
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Type",
					"value": "is_part",
					"align": "center",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": false,
					"groupable": false
				},
				{
				    text: "Action",
                    value: "add_activity",
                    type: "varchar",
                    align:"center",
                    sortable:false,
                    form: false,
                    visible:true
                }, 
				{
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
					"filter": false,
					"groupable": false,
					"url": App.url + "users",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            "groups": "rebuild_report_page",
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
					"filter": false,
					"groupable": false,
                    "url": App.url + "users",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            "groups": "rebuild_report_page",
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
					"filter": false,
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
				}],
                dialogActivity: false,
                selected: false,
                dataid: {}
            }
        },
        watch: {
            dialogActivity: function(val){
                var self = this
				if(!val){
					self.$refs.template.getItems()
				}
            }
        },
        computed: {
            headersObj: function(){
                var tmp = {}, self = this
                Object.keys(self.headers).map(key=>{
                    var val = self.headers[key]
                    tmp[val.value] = val
                })
                return tmp;
            },
            allowActivity: function(){
				var self = this
				if(!self.selected)
					return true
				return false
			},
        },
        methods: {
            onSelectedRow: function (val) {
                var self = this
                self.selected = val
                self.dataid = {
                    part_id: val.id,
                    is_part: val.is_part
                }
            },
            
            isPart: function(f){
				if(f == 0){
					return 'Subassembly'
				}
				if(f == 1){
					return 'Part'
				}
			},


            onSelectedRowAll: function (val) {
                var self = this
                self.selectedAll = val
            },
			onAddItem: function(){
				var self = this
				self.$refs.sjItem.$refs.template.openAdd()
			},
			onAfterSave: function(){
				var self = this
				setTimeout(() => {
					self.$refs.template.getItems()
					self.$refs.sjItem.$refs.template.getItems()
				}, 100);
			},
			onVisible: function(isVisible){
				var self = this
				if(isVisible){
					self.itemsOptions.filter = {
						sub_assembly: self.data.sub_assembly,
					}
					self.$refs.template.defaultItemsOptions.filter = {
						sub_assembly: self.data.sub_assembly,
					}
					self.$refs.template.getItems()
				}
			}
        },
        mounted: function () {
        }
    }
</script>