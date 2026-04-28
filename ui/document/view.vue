<template>
    <v-container style="padding-top: 0 !important; padding-bottom: 0 !important; height: 100%;">
        <v-row>
            <v-col order-xs="1">
                <v-sheet rounded="md">
					<v-container style="padding-right: 0; padding-bottom: 0;">
						<v-row dense>
							<template v-for="(item, index) in headers">
								<template v-if="item.origin!=undefined">
									<v-col :cols="item.origin.width_xs" :xs="item.origin.width_xs" :sm="item.origin.width_sm" :md="item.origin.width_m" :lg="item.origin.width_l" :xl="item.origin.width_xl" :key="index">
										<div class="docsheet" :style="(item.style||'')">
											<div style="border-bottom: 1px solid #FAC858; font-weight: bold;" v-if="item.inline==0">{{item.text}}</div>
											<span style="font-weight: bold;" v-else>{{item.text}}</span>
											{{values[item.value+'_text'] || values[item.value]}}
										</div>
										
									</v-col>
								</template>
							</template>
						</v-row>
						<v-row>
							<v-col cols="12">
								<v-autoform v-models="headers"></v-autoform>
							</v-col>
						</v-row>
					</v-container>
                </v-sheet>
            </v-col>
            <v-col cols="12" md="2" xs="12" order-xs="2">
                <v-sheet rounded="md" style="padding: 12px;">
					<v-row>
						<v-btn small color="warning" outlined @click="updateDialog=true"><v-icon left>mdi-pencil</v-icon>Update</v-btn>
					</v-row>
                </v-sheet>
            </v-col>
        </v-row>
		<v-action-dialog v-model="updateDialog" fullscreen :title="'Update Task #'+values.identifier" @save="save">
			<v-expansion-panels v-if="!expansion">
				<v-expansion-panel>
					<v-expansion-panel-header>
					More Options...
					</v-expansion-panel-header>
					<v-expansion-panel-content>
						<v-autoform v-model="headers" hide-details></v-autoform>
					</v-expansion-panel-content>
				</v-expansion-panel>
			</v-expansion-panels>
			<v-autoform v-model="headers" hide-details v-else></v-autoform>
			<v-autoform v-model="timeline" hide-details></v-autoform>
		</v-action-dialog>
		<list :local-hash="item" v-for="(item, index) in children" :key="index"></list>
    </v-container>
</template>

<style scoped>
	.docsheet{
		border: 1px solid #D3DCE3;
		/* background-color: #ECECEC; */
		padding: 8px;
		margin-bottom: 0;
		margin-right: 12px;
		border-radius: 4px;
		font-family: Verdana, sans-serif;
		font-size: 12px;
		color: #333;
	}
</style>

<script>
    module.exports = {
        name: '',
		components: {
			'list': 'url:ui/document/list.vue'
		},
        data: function() {
            return {
				children: [],
				docIdentifier: "",
				note_id: -1,
				updateDialog: false,
				hash: [],
				values: [],
				headers: [],
				timeline: [],
				expansion: false,
				defaultHeaders: [{
					"text": "#",
					"value": "note_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
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
					"limit": "10",
					"alias": "modified_by_name"
				}],
            }
        },
        methods: {
			getForm: async function(){
				try {
					var self = this
					var res = await axios.get(App.url+'document/notes/'+self.hash[self.hash.length-2], {
						params: {
							filter: {
								identifier: self.hash[self.hash.length-1],
								doc_identifier: self.hash[self.hash.length-2]
							}
						}
					})
					self.values = res.data.data[0]
					
					self.getContainerInfo(self.values.document_id)
					var res = await axios.get(App.url+'document/fields/'+self.hash[self.hash.length-2], {
						params: {
							filter: {
								flag: 1
							},
							sortBy: ['sort']
						}
					})
					var headers = [].concat(self.defaultHeaders)
					var timeline = []
					var datatype = {
						"decimal": "float",
						"list": "list",
						"listurl": "list",
						"document": "list",
						"note": "list",
					}
					var dbdatatype = {
						"decimal": "numeric",
						"list": "list",
						"listurl": "list",
						"document": "document",
						"note": "note",
						"date": "datetime",
						"datetime": "datetime",
						"time": "datetime",
						"listurl": "list",
					}
					res.data.data.map((val,i)=>{
						if(val.width_sm == null)
							val.width_sm = val.width_xs
						if(val.width_m == null)
							val.width_m = val.width_sm
						if(val.width_l == null)
							val.width_l = val.width_m
						if(val.width_xl == null)
							val.width_xl = val.width_l
						var url = "", 
						options = {
							"filter": {},
							"filterType": {},
							"filterCondition": {}
						}, searchby = [], formatter = []
						var alias = null
						if(["list"].includes(val.datatype)){
							url = App.url+"document/listvalues"
							options.filter.document_list_id = val.list
							searchby = ["text"]
							formatter = ["value", "text"]
							alias = `field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}_text`
						}
						var obj = {
							"text": val.name,
							"value": `field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}`,
							"align": "start",
							"sortable": true,
							"filterable": false,
							"divider": false,
							"class": "",
							"width": "auto",
							"type": datatype[val.datatype] || val.datatype,
							"disabled": false,
							"visible": true,
							"required": val.required,
							"form": true,
							"filter": true,
							"groupable": false,
							"url": url,
							"searchby": searchby,
							"formatter": formatter,
							"options": options,
							"paging": true,
							"page": "1",
							"limit": "10",
							"origin": val,
							"xs": val.width_xs,
							"sm": val.width_sm,
							"md": val.width_m,
							"lg": val.width_l,
							"xl": val.width_xl,
							"default": self.values[`field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}`],
							"formwidth": '100%'
						}
						if(alias!=null)
							obj.alias = alias
						
						if(val.is_timeline==0){
							headers.push(obj)
						}
						else{
							timeline.push(obj)
						}
					})
					if(timeline.length == 0)
						self.expansion = true
					self.headers = App.updateObject(headers);
				} catch (error) {
					App.errorMsg()
				}
			},
			getContainerInfo: async function(document_id){
				try {
					var self = this
					var res = await axios.get(App.url+'document/container', {
						params: {
							parent_document_id: document_id
						}
					})
					res.data.data.map(val=>{
						self.children.push('#/'+location.hash.split('/').slice(1, -1).join('/')+'/'+val.children_identifier+'/__')
						App.items.push({
							name: val.children_alias || val.children_name,
							loadWithBase: '/'+location.hash.split('/').slice(1, -1).join('/')+'/'+val.children_identifier
						})
					})
				} catch (error) {
					console.log(error)
					App.errorMsg()
				}
			},
			save: async function(){
				var self = this
				try {
					var values = {}
					self.headers.map(val=>{
						if(val.data!=undefined && self.values[val.value] != val.data)
							values[val.value] = val.data
					})
					self.timeline.map(val=>{
						if(val.data!=undefined && self.values[val.value] != val.data)
							values[val.value] = val.data
					})
					values.pk = self.values.note_id
					var res = await axios.put(App.url+'document/notes/'+self.hash[self.hash.length-2], values)
					App.successMsg()
					self.updateDialog = false
				} catch (err) {
					App.errorMsg()
				}
			}
        },
        mounted: function() {
			var self = this
			self.hash = location.hash.split('/').slice(1, -1)
			self.url = 'document/notes/'+self.hash[self.hash.length-2]
			App.title = self.hash[self.hash.length-2]
			self.docIdentifier = self.hash[self.hash.length-2]
			self.note_id = self.hash[self.hash.length-1]
			self.getForm()
        }
    }
</script>