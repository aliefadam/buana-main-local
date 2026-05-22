<template>
    <v-container v-observe-visibility="onVisible"
        style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template show-expand="showExpand" :item-height-as-min-height="itemHeightAsMinHeight" :table-fill="tableFill" :table-fixed-header="tableFixedHeader" :items-options="itemsOptions" :data="data" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name"
            @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll">

        <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
            
         <template v-slot:item.created="props">
			<span>Created By: </span>{{ props.item.created_by_name }}<br/>
			<span>Created Date: </span>{{ props.item.created_date }}<br/>
		</template>
		<template v-slot:item.modified="props">
			<span>Modified By: </span>{{ props.item.modified_by_name }}<br/>
			<span>Modified Date: </span>{{ props.item.modified_date }}
		</template>
           
        <template v-slot:menu-after-filter>
        	<v-btn small color="green darken-2" outlined @click="uploadSubassyDialog=true"><v-icon small left>mdi-cloud-upload</v-icon>Upload Subassembly (Excel)</v-btn>
        	<v-btn small color="green darken-2" outlined @click="uploadPartlistDialog=true"><v-icon small left>mdi-cloud-upload</v-icon>Upload Partlist (Excel)</v-btn>
        </template>
        <template v-slot:expanded-item="props">
                <td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
                    <partlist-sub title="Part List" :table-fixed-header="false" :item-height-as-min-height="true" :table-fill="false" :key="props.item[itemKey]" :sel="{
						sub_assembly: props.item.sub_assembly,
                        section: props.item.section,
                        sub_assembly_desc: props.item.sub_assembly_desc
					}" name="" :data="{
						sub_assembly: props.item.sub_assembly,
                        section: props.item.section,
                        sub_assembly_desc: props.item.sub_assembly_desc
					}"></partlist-sub>
                </td>
            </template>
    </v-template>
    <v-action-dialog title="Upload Part List Rebuild" v-model="uploadPartlistDialog" fullscreen @save="savePartlist">
				<v-excel-reader v-model="file" ref="file"></v-excel-reader>
				<table class="simple-table default-table">
					<thead>
						<tr>
							<th></th>
                            <th>Sub Assembly</th>
							<th>Sub Assembly Description</th>
							<th>Part Number</th>
							<th>Part Description</th>
							<th>Section</th>
							<th>Article No</th>
							<th>Index No</th>
							<th>Qty</th>
							<th>UOM</th>
							<th>Brand</th>
							<th>Type</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in data" :key="index">
							<td><v-icon small v-if="item[0]===true" color="success">mdi-check</v-icon><v-icon small v-else color="error">mdi-close</v-icon></td>
							<td>{{item[1]}}</td>
							<td>{{item[2]}}</td>
							<td>{{item[3]}}</td>
							<td>{{item[4]}}</td>
							<td>{{item[5]}}</td>
							<td>{{item[6]}}</td>
              				<td>{{item[7]}}</td>
							<td>{{item[8]}}</td>
							<td>{{item[9]}}</td>
              				<td>{{item[10]}}</td>
              				<td>{{item[11]}}</td>
						</tr>
					</tbody>
				</table>
			 </v-action-dialog>
			 <v-action-dialog title="Upload Subassembly Rebuild" v-model="uploadSubassyDialog" fullscreen @save="saveSubassy">
				<v-excel-reader v-model="file" ref="file"></v-excel-reader>
				<table class="simple-table default-table">
					<thead>
						<tr>
							<th></th>
                            <th>Index No</th>
							<th>Sub Assembly</th>
							<th>Sub Assembly Description</th>
							<th>Section</th>
							<th>Brand</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in data" :key="index">
							<td><v-icon small v-if="item[0]===true" color="success">mdi-check</v-icon><v-icon small v-else color="error">mdi-close</v-icon></td>
							<td>{{item[1]}}</td>
							<td>{{item[2]}}</td>
							<td>{{item[3]}}</td>
							<td>{{item[4]}}</td>
                            <td>{{item[5]}}</td>
						</tr>
					</tbody>
				</table>
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
            itemHeightAsMinHeight: {
                type: Boolean,
                default: false
            },
            tableFill: {
                type: Boolean,
                default: true
            },
            showExpand: {
                type: Boolean,
                default: false
            },
			itemsOptions: {
				type: Object,
				default: {
					filter: {},
					filterType: {},
                    sortBy: ['section', 'index_no'],
					sortDesc: [false, false]
				}
			}
        },
        components: {
            'partlist-sub': 'url:ui/rebuildmachine/partlistrebuildsub.vue'
        },
        data: function () {
            return {
                file: false,
                uploadSubassyDialog: false,
                uploadPartlistDialog: false,
                valid: false,
                name: '',
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
				},{
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
					"form": false,
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
				},  {
					"text": "Brand",
					"value": "brand",
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
					"groupable": false,
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
					"visible": true,
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
				}],
                dialogItem: false,
                selected: false,
                dataid: {}
            }
        },
        watch: {
            dialogItem: function(val){
                var self = this
				if(!val){
					self.$refs.template.getItems()
				}
            },

			uploadSubassyDialog: function(val){
				if(!val)
					this.$refs.template.getItem()
			},
			file: function(val){
				var self = this, data
				if(val){
					data = val[0].data.filter(val=>val.length>2).slice(1)
					//console.log(this.data)
					var wb = new ExcelJS.Workbook();
					var reader = new FileReader()
					reader.readAsArrayBuffer(val[0].info)
					var tmp = []
					reader.onload = () => {

						var buffer = reader.result;
					}
					self.data = data
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
            }
        },
        methods: {

			savePartlist: function(){
				var self = this
				setTimeout(async () => {
					var tmp = self.data.filter(val=>{return val[0]!==true}).slice(0, 5)
					var str = []
						tmp.map(val=>{
							str.push([
                                (val[1]||'').toString().trim().length == 0 ? '-' : val[1],
								(val[2]||'').toString().trim().length == 0 ? '-' : val[2], 
                                (val[3]||'').toString().trim().length == 0 ? '-' : val[3],
								(val[4]||'').toString().trim().length == 0 ? '-' : val[4], 
                                (val[5]||'').toString().trim().length == 0 ? '-' : val[5], 
								(val[6]||'').toString().trim().length == 0 ? '-' : val[6],
                                (val[7]||'').toString().trim().length == 0 ? '-' : val[7],
                                (val[8]||'').toString().trim().length == 0 ? '-' : val[8],
                                (val[9]||'').toString().trim().length == 0 ? '-' : val[9],
                                (val[10]||'').toString().trim().length == 0 ? '-' : val[10],
                                (val[11]||'').toString().trim().length == 0 ? '-' : val[11],
							].join(';;'))
						})
					str = str.join(';;;')
					var res = await axios.post(App.url+'/rebuildmachine/partlistrebuild', {
					    batch: str
					})
					if(res.data.status==false){

					}
					else{
						var tmpVal = self.data.filter(val=>{return val[0]!==true})
						for(var i=0; i<5;i++){
							if(tmpVal[i])
								tmpVal[i][0] = true
						}
						if(self.data.filter(val=>{return val[0]!==true}).length > 0){
							self.data = App.updateObject(self.data)
							self.savePartlist()
                            App.succesMsg()
						}else{
							self.data = App.updateObject(self.data)
						}
					}
				}, 100);
			},
			saveSubassy: function(){
				var self = this
				setTimeout(async () => {
					var tmp = self.data.filter(val=>{return val[0]!==true}).slice(0, 5)
					var str = []
						tmp.map(val=>{
							str.push([
							    (val[1]||'').toString().trim().length == 0 ? '-' : val[1],
								(val[2]||'').toString().trim().length == 0 ? '-' : val[2], 
                                (val[3]||'').toString().trim().length == 0 ? '-' : val[3],
                                (val[4]||'').toString().trim().length == 0 ? '-' : val[4],
                                (val[5]||'').toString().trim().length == 0 ? '-' : val[5],
							].join(';;'))
						})
					str = str.join(';;;')
					var res = await axios.post(App.url+'/rebuildmachine/subassemblyrebuild', {
					    batch: str
					})
					if(res.data.status==false){

					}
					else{
						var tmpVal = self.data.filter(val=>{return val[0]!==true})
						for(var i=0; i<5;i++){
							if(tmpVal[i])
								tmpVal[i][0] = true
						}
						if(self.data.filter(val=>{return val[0]!==true}).length > 0){
							self.data = App.updateObject(self.data)
							self.saveSubassy()
                            App.succesMsg()
						}else{
							self.data = App.updateObject(self.data)
						}
					}
				}, 100);
			},
            onSelectedRow: function (val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                    self.processData = {}
                    self.dataid = {}
                } else {
                    self.selected = val
                    self.processData = {
                        id: self.data.id,
                    }
                    self.dataid = {
                        id: self.data.id
                    }
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
			onVisible: function(isVisible, e){
				var self = this
				self.isInViewport = !!isVisible
			    self.isInDom = !!e.target.isConnected
				if(isVisible){
					self.itemsOptions.filter = {
					}
					self.$refs.template.defaultItemsOptions.filter = {
					}
					self.$refs.template.getItems()
				}
				 if (self.isInViewport) {
                    self.$refs.template.getItems()
                }
			},

        },
        mounted: function () {
        }
    }
</script>