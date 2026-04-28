<template>
    <v-container v-observe-visibility="onVisible"
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template show-expand="showExpand" :item-height-as-min-height="itemHeightAsMinHeight" :table-only="tableOnly" :table-fill="tableFill" :table-fixed-header="tableFixedHeader" :items-options="itemsOptions" :data="data" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll">
            
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
            
   <!--         <template v-slot:item.created="props">-->
			<!--<span>Created By: </span>{{ props.item.created_by_name }}<br/>-->
			<!--<span>Created Date: </span>{{ props.item.created_date }}<br/>-->
			<!--</template>-->
			
			<!--<template v-slot:item.modified="props">-->
			<!--<span>Modified By: </span>{{ props.item.modified_by_name }}<br/>-->
			<!--<span>Modified Date: </span>{{ props.item.modified_date }}-->
			<!--</template>-->
        <template v-slot:menu-after-filter>
            <!-- <v-btn color="primary" outlined small @click="openReport" :disable="allowReport">
                    <v-icon small left>mdi-printer</v-icon>Report
            </v-btn> -->
        </template>
        	<template v-slot:expanded-item="props">
                <td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
                    <partlist-sub title="Part List" :table-fixed-header="false" :item-height-as-min-height="true" :table-fill="false" :key="props.item[itemKey]" :sel="{
						sub_assembly: props.item.sub_assembly
					}" name="" :data="{
						sub_assembly: props.item.sub_assembly
					}"></partlist-sub>
                </td>
            </template>
        <template v-slot:item.is_part="props">
            <b>{{isPart(props.item.is_part)}}</b>
        </template>
    </v-template>
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
                    sortBy: ['section', 'index_no'],
					sortDesc: [false, false]
				}
			}
        },
        components: {
             'report-rebuild-2': 'url:ui/rebuildmachine/report_rebuild_2.vue',
             'partlist-sub': 'url:ui/rebuildmachine/report_rebuild_2_partlist_sub.vue'
        },
        data: function () {
            return {
                valid: false,
                name: 'Activity',
                processData: {},
                itemKey: 'id',
                url: 'rebuildmachine/subassemblyrebuild',
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
				}, {
                        text: '',
                        value: 'data-table-expand',
                    }, 
					{
					"text": "Index No",
					"value": "index_no",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
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
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": App.url + "rebuildmachine/subassemblyrebuild",
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
					"visible": true,
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
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"data_value": ["SE", "VE", "MAX", "HCF"],
					"paging": true,
					"page": "1",
					"limit": "10"
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
                selected: false,
                dataid: {}
            }
        },
        watch: {
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
			 allowReport: function(){
				var self = this
				if(!self.selected)
					return true
				return false
			},
        },
        methods: {
            openReport: function(){
				var self = this
				var params = JSON.parse(JSON.stringify(self.$refs.template.defaultItemsOptions))
				var w = screen.width*0.75
				var h = screen.height*0.5
				var left = (screen.width/2)-(w/2);
  				var top = (screen.height/2)-(h/2);
				params.limit = -1
				params.rand = randomId()

				var prm = window.btoa(JSON.stringify(params)); 
				window.open('https://internaldev.buanamultiteknik.com/api/report/rebuild/checksubassembly?parameter='+prm,
					`scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=${w},height=${h},left=${left},top=${top}`)
			},
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
			onVisible: function(isVisible, e) {
				var self = this
				self.isInViewport = !!isVisible
				self.isInDom = !!e.target.isConnected
				
				if(self.isVisible){
					self.itemsOptions = {
						filter: self.data
					}
					//console.log(self.isInViewport)
					if(self.isInViewport){
						self.$nextTick(()=>{
							self.$refs.template.getItems()
						})
					}
				}
			},
        },
        mounted: function () {
        }
    }
</script>