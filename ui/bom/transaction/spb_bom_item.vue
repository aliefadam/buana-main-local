<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :height="height" :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
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
			<!-- 
			<template v-slot:item.item_name="props">
			</template>
			 -->
			<!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->
			
            <template v-slot:prepend-menu>
				<!-- <v-btn small color="primary" outlined @click="uploadDialog=true">Upload data</v-btn>
				<v-btn small color="primary" :disabled="!selected" outlined @click="">Receive</v-btn> -->
				<v-btn small color="primary" outlined @click="add" :disabled="!selected">Insert into Stock</v-btn>
            </template>
			
			<template v-slot:title-body>
				BOM Items
			</template>
			<!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
			<!-- 
            <template v-slot:append-menu>
            </template>
			 -->
			 <!-- <v-action-dialog v-model="uploadDialog" fullscreen>
				<v-excel-reader v-model="file"></v-excel-reader><v-btn small color="primary" outlined @click="save">Save</v-btn>
				<table class="simple-table default-table">
					<thead>
						<tr>
							<th></th>
							<th>No</th>
							<th>Part Number</th>
							<th>BOM Structure</th>
							<th>Unit QTY</th>
							<th>QTY</th>
							<th>Description</th>
							<th>Material</th>
							<th>Mass</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in data" :key="index">
							<td><v-icon small v-if="item[0]===true" color="success">mdi-check</v-icon><v-icon small v-else color="error">mdi-close</v-icon></td>
							<td>{{item[1]}}</td>
							<td>{{item[2]}}</td>
							<td>{{item[4]}}</td>
							<td>{{item[5]}}</td>
							<td>{{item[6]}}</td>
							<td>{{item[8]}}</td>
							<td>{{item[10]}}</td>
							<td>{{item[11]}}</td>
						</tr>
					</tbody>
				</table>
			 </v-action-dialog> -->
		</v-template>
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
		},
		props: {
			value: undefined,
			data: {
				type: Object
			},
			tableOnly: {
				type: Boolean,
				default: true
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
			height: {
				default: "100%"
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
						remaining_qty: 0
					},
					filterType: {
						remaining_qty: '>'
					},
					sortBy: ['id'],
					sortDesc: [false]
				}
			}
		},
		data: function() {
			return {
				name: 'bomitem',
				itemKey: 'part_number',
				url: 'transaction/bomitemspb',
				loading: false,
				uploadDialog: false,
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
					"text": "Bom Receive Id",
					"value": "bom_receive_id",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
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
					"text": "Unique ID",
					"value": "bom_unique_id",
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
					"text": "Part Number",
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
					"text": "Description",
					"value": "description",
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
					"text": "Material",
					"value": "material",
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
					"text": "Mass",
					"value": "mass",
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
					"text": "Qty BOM",
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
					"text": "Received Qty",
					"value": "received_qty",
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
					"text": "Remaining Qty",
					"value": "remaining_qty",
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
				file: false,
				data: [],
				saveI: 0
			}
		},
		watch: {
			uploadDialog: function(val){
				if(!val)
					this.$refs.template.getItem()
			},
			file: function(val){
				if(val)
					this.data = val[0].data.slice(1)
			}
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
			add: function(){
				var self = this, spbitem = spbitem2
				//console.log(self.selected.item_id)
				//spbitem.headersObj.item_id.data = self.selected.item_id
				spbitem.headersObj.item_id.update = self.selected.item_id
				//spbitem.headersObj.qty.data = Number(self.selected.remaining_qty)
				spbitem.headersObj.qty.update = Number(self.selected.remaining_qty)
				spbitem.maxQty = Number(self.selected.remaining_qty)
				spbitem.headersObj.allocation.data = 'NI'
				spbitem.headersObj.allocation.update = 'NI'
				spbitem.$refs.template.openAdd()
			},
			/*save: function(){
				var self = this
				setTimeout(async () => {
					var tmp = self.data.filter(val=>{return val[0]!==true}).slice(0, 10)
					var str = []
					tmp.map(val=>{
						str.push([App.page().selected.id, val[2], val[5], val[6], (val[8]||'').trim().length == 0 ? '-' : val[8], (val[10]||'').trim().length == 0 ? '-' : val[10], val[11]].join(';;'))
					})
					str = str.join(';;;')
					var res = await axios.post(App.url+self.url, {
						batch: str
					})
					if(res.data.status==false){

					}
					else{
						var tmpVal = self.data.filter(val=>{return val[0]!==true})
						for(var i=0; i<10;i++){
							if(tmpVal[i])
								tmpVal[i][0] = true
						}
						if(self.data.filter(val=>{return val[0]!==true}).length > 0){
							self.data = App.updateObject(self.data)
							self.save()
						}else{
							self.data = App.updateObject(self.data)
						}
					}
				}, 100);
			},*/
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
                if (self.isInViewport) {
                    self.$refs.template.defaultItemsOptions.filter.bom_receive_id = self.data.bom_receive_id
                    self.$refs.template.getItems()
                }
			},
		},
		mounted: function() {

		}
	}

</script>
