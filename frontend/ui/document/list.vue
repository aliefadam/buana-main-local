<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template export-excel @save="onSave" @update="onUpdate" @after-get-items="updateData" fullscreen-dialog :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
			<!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					
				</td>
			</template> -->
			<template v-for="(item, index) in custom" :key="index" v-slot:['item.'+item.alias]="props">
				<div v-html="item.custom(props)"></div>
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
			
            <!-- <template v-slot:append-menu>
				<v-btn small outlined color="primary" @click="openReport">Open Report</v-btn>
            </template> -->
			
			 <template v-slot:item.note_id="props">
                <a v-if="hash.length == 4" :href="'#/document/'+hash[1]+'/'+docIdentifier+'/'+props.item.identifier">#{{props.item.identifier}}</a>
                <a v-else :href="'#/document/'+docIdentifier+'/'+props.item.identifier">#{{props.item.identifier}}</a>
            </template>
		</v-template>
	</v-container>
</template>

<style scoped>
	.v-autoform > div > div {
		max-width: auto !important;
	}
</style>

<script>
	module.exports = {
		name: '',
		creator: '',
		components: {
			/* 'document-form': 'url:ui/ewis/test/document_form.vue' */
		},
		props: {
			value: undefined,
			data: {
				type: Object
			},
			localHash: {
				default: false
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
			footerProps: {
			    default: {
			        "items-per-page-options": [10, 50, 100, 200, 500]
			    }
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
				name: '',
				itemKey: 'note_id',
				url: 'document/notes',
				docIdentifier: "",
				loading: false,
				headers: [],
				custom: [],
				timeline: [],
				document_id: -1,
				defaultHeaders: [/* {
					"text": "docid",
					"value": "document_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
					"disabled": false,
					"visible": false,
					"required": false,
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
				},  */{
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
					"type": "list",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": App.url + "users",
					"searchby": ["name"],
					"formatter": ["id", "name"],
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
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
				hash: [],
				containerId: -1,
				dataUrl: []
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
			}
		},
		methods: {
			openReport: function(){
				var self = this
				var params = encodeURIComponent(JSON.stringify(self.$refs.template.defaultItemsOptions))

				window.open()
			},
			onSave: async function(params){
				var self = this
				try {
					const formData = new FormData();
					var fileInfo = [];
					var files = [];
					self.headers.map(function(val){
						if(val.type == 'file')
							files.push(val.value)
					})
					Object.keys(params).forEach(function (key) {
						var val = params[key]
						formData.append(key, val);
						if(files.includes(key)){
							fileInfo.push(key)
						}
					});

					formData.append('fileInfoParams', fileInfo.join(';'))

					var res = await axios.post(App.url + self.url, formData, {
						headers: {
							'Content-Type': 'multipart/form-data'
						}
					})

					if (!res.data.status)
						App.errorMsg()
					else {
						App.successMsg()
						self.$refs.template.dialogAdd = false
						self.$refs.template.getItems()
					}
				} catch (error) {
					
				}
			},
			onUpdate: async function(params){
				var self = this
				try {
					const formData = new FormData();
					var fileInfo = [];
					var files = [];
					self.headers.map(function(val){
						if(val.type == 'file')
							files.push(val.value)
					})
					Object.keys(params).forEach(function (key) {
						var val = params[key]
						formData.append(key, val);
						if(files.includes(key)){
							fileInfo.push(key)
						}
					});

					formData.append('fileInfoParams', fileInfo.join(';'))

					var res = await axios.post(App.url + self.url, formData, {
						headers: {
							'Content-Type': 'multipart/form-data'
						}
					})

					if (!res.data.status)
						App.errorMsg()
					else {
						App.successMsg()
						self.$refs.template.dialogUpdate = false
						self.$refs.template.getItems()
					}
				} catch (error) {
					
				}
			},
			onSelectedRow: function(val) {
				var self = this
				if (val === undefined) {
					self.selected = false
				} else {
					Object.keys(val).forEach(function (key) {
						var value = val[key]
						if(value!==undefined && value!==null && self.headersObj[key]){
							if(self.headersObj[key].formatAs)
								if(self.headersObj[key].formatAs=='int'){
									val[key] = isNaN(value) ? null : parseInt(value)
								}
						}
					});
					self.selected = val
					self.headers.map(function(val){
						if(val.load){
							val.load(self.selected)
						}
					})

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
			toExcel: function(){
				var self = this
				self.$refs.template.getItems()
				self.$nextTick(()=>{
					tableToExcel(self.$refs.template.$el.querySelector('.v-data-table__wrapper > table'), self.name)
				})
			},
			updateData: async function(values){
			    try{
    				var self = this
    			    var proms = []
    			    var data = self.dataUrl
    			    for(var i=0; i< data.length; i++){
    			        var val = data[i]
    			        var ids = values.data.filter(v=>{
    			            return v[val.alias]!=null
    			        }).map(v=>{
    			            return `'${v[val.alias]}'`
    			        })
    			        
    			        var params = {
        						filter: {
        							
        						},
        						filterType: {
        						},
        						filterCondition: {
        						},
        						limit: -1
        				}
        				
        				params.filterType[val.listurlvalue] = 'in'
        				params.filter[val.listurlvalue] = ids.join(',')
        				if(ids.length>0)
            			    proms.push(
            			        new Promise(async (resolve, reject) => {
            			            var alias = val.alias,
            			            listurltext = val.listurltext,
            			            listurlvalue = val.listurlvalue,
            			            aliasText = val.aliasText
    						        var res = await axios.get(val.listurl, {
                    					params: params
                    				})
                    				var tmp = {}
                    				
                    				if(res.data.status){
                        				res.data.data.map(d=>{
                        				    tmp[d[listurlvalue]] = d[listurltext]
                        				})
                        				
                        				self.$refs.template.items.map(m=>{
                        				    m[aliasText] = tmp[m[alias]]
                        				})
                    				    
                    				}
                    				resolve(true)
    					        })
            			    )
    			    }
    			    
    			    var res = await Promise.all(proms)
    			}catch(e){
    			    console.log(e)
			    }
			},
			getForm: async function(){
				try {
					var self = this
					var res = await axios.get(App.url+'document/fields/'+self.docIdentifier, {
						params: {
							filter: {
								flag: 1
							},
							sortBy: ['sort'],
							limit: -1
						}
					})
					
					self.document_id = res.data.data[0].document_id
					if(self.containerId>=0)
						self.sub()
					var headers = [].concat(self.defaultHeaders)
					var timeline = []
					//["varchar", "decimal", "int", "text", "date", "datetime", "time", "list", "listurl", "document", "note", "attachment"]
					var datatype = {
						"decimal": "float",
						"list": "list",
						"listurl": "list",
						"document": "list",
						"note": "list",
						"attachment": "file",
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
					var dataUrl = []
					var custom = []
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
						
						if(["listurl"].includes(val.datatype)){
							val.format_as = "int"
							url = val.listurl
							searchby = [val.listurlsearch]
							formatter = [val.listurlvalue, val.listurltext]
							alias = `field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}_text`
							dataUrl.push({
							    listurl: val.listurl,
							    listurlvalue: val.listurlvalue,
							    listurltext: val.listurltext,
							    alias: `field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}`,
							    aliasText: `field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}_text`
							})
							//alias = `field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}_text`
						}
						self.dataUrl = dataUrl
						var html = false
						if(val.datatype == "attachment"){
						    html = function(value){
						        if(value.value){
						            var split = value.value.split('+++')
						            return `<a href="${App.url}${split[0].split('./')[1]}" download="${split[1]}" target="__blank">${split[1]}</a>`
						        }
						    }
						    
						    custom.push({
								alias: `field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}`,
								custom: html
							})
						}
						
						//custom view
						try {
							if(val.custom.trim()!=''){
								eval(`var fnc = function(props){
										var self = App.page()
										${val.custom}
									}`)
								var c = {
									alias: `field_${dbdatatype[val.datatype] || val.datatype}_${val.document_field_id}`,
									custom: fnc
								}
								custom.push(c)
							}
						} catch (error) {
						}

						var obj = {
							"formatAs": val.format_as,
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
							"visible": val.visible == 1,
							"required": val.required,
							"form": val.formdata == 1,
							"filter": val.filter == 1,
							"groupable": false,
							"url": url,
							"searchby": searchby,
							"formatter": formatter,
							"options": options,
							"paging": true,
							"page": "1",
							"limit": "10",
							"xs": val.width_xs,
							"sm": val.width_sm,
							"md": val.width_m,
							"lg": val.width_l,
							"xl": val.width_xl,
							"formwidth": '100%'
						}
						
						try {
							eval(`var fn = async function(data){
								var self = App.page()
								${val.oninput}
							}`)
							obj.input = fn
						} catch (error) {
							console.log(error)
						}
						try {
							eval(`var fn = async function(data){
								var self = App.page()
								${val.onload}
							}`)
							obj.load = fn
						} catch (error) {
							console.log(error)
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
					self.headers = App.updateObject(headers);
					self.custom = custom
					self.$refs.template.filterModel = self.headers.filter(val=>val.filter==true)
				} catch (error) {
					App.errorMsg()
					console.log(error)
				}
			},
			getContainerInfo: async function(containerId){
				var self = this
				var hash = self.localHash || location.hash
				try {
					var res = await axios.get(App.url+'document/container', {
						params: {
							parent_document_id: containerId
						}
					})
					res.data.data.map(val=>{
						App.items.push({
							name: val.children_alias || val.children_name,
							loadWithBase: '/'+hash.split('/').slice(1, -1).join('/')//+'/'+val.children_identifier
						})
					})
				} catch (error) {
					App.errorMsg()
				}
			},
			sub: function(){
				var self = this
				self.defaultHeaders.push({
					"text": self.docIdentifier,
					"value": "container_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"data_value": [{
						text: "#"+self.containerId,
						value: self.containerId
					}],
					"readonly": true,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"default": self.containerId,
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
					"formwidth": '100%'
				}, )
			}
		},
		mounted: function() {
			var self = this
			var hash = self.localHash || location.hash
			self.hash = hash.split('/').slice(1, -1)
			if(self.hash.length == 4){
				self.url = 'document/notes/'+self.hash[3]
				//console.log('document/notes/'+self.hash[3])
				App.title = self.hash[3]
				self.docIdentifier = self.hash[3]
				self.containerId = self.hash[2]
				self.itemsOptions.filter.container_id = self.containerId
				self.getContainerInfo(self.containerId)
				// self.headers[0].default = document_id
				// self.headers[0].data = document_id
			}
			else{
				self.url = 'document/notes/'+self.hash[1]
				App.title = self.hash[1]
				self.docIdentifier = self.hash[1]
			}
			self.getForm()
		}
	}

</script>
