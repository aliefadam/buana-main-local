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
			<!-- 
			<template v-slot:item.item_name="props">
			</template>
			 -->
			<!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->
			
            <template v-slot:menu-after-filter>
				<!-- <v-btn small color="primary" outlined @click="newUpload=false;uploadDialog=true">Upload data</v-btn> -->
				<v-btn small color="primary" outlined @click="newUpload=true;uploadDialog=true">Upload data</v-btn>
				<!--<v-btn small color="primary" outlined @click="showBarcode=true" :disabled="!selected"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn>-->
				<!--<v-btn small color="primary" :disabled="!selected" outlined @click="">Receive</v-btn>-->
            </template>
			
			<!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
			<!-- 
            <template v-slot:append-menu>
            </template>
			 -->
			 <v-action-dialog title="Upload BOM Item" v-model="uploadDialog" fullscreen @save="save">
				<v-excel-reader v-model="file"></v-excel-reader><!-- <v-btn small color="primary" outlined @click="save">Save</v-btn> -->
				<table class="simple-table default-table" v-if="!newUpload">
					<thead>
						<tr>
							<th></th>
                            <th>Unique ID</th>
							<th>Item</th>
							<th>Part Number</th>
                            <th>QTY</th>
							<th>Unit QTY</th>
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
							<td>{{item[3]}}</td>
							<td>{{item[4]}}</td>
							<td>{{item[5]}}</td>
							<td>{{item[6]}}</td>
							<td>{{item[7]}}</td>
							<td>{{item[8]}}</td>
						</tr>
					</tbody>
				</table>
				<table class="simple-table default-table" v-else>
					<thead>
						<tr>
							<th></th>
                            <th>Unique ID</th>
							<th>Item</th>
							<th>Part Number</th>
							<th>QTY</th>
                            <th>Unit QTY</th>
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
							<td>{{item[3]}}</td>
							<td>{{item[4]}}</td>
							<td>{{item[5]}}</td>
							<td>{{item[6]}}</td>
                            <td>{{item[7]}}</td>
							<td>{{item[8]}}</td>
						</tr>
					</tbody>
				</table>
			 </v-action-dialog>
		</v-template>
		<v-action-dialog :actions="false" v-model="showBarcodeDialog" title="Bom Barcode" fullscreen>
			<v-btn small color="primary" outlined @click="print">Print</v-btn>
			<v-print ref="vprint" style="margin: auto; color: #000; padding: 20pt">
				<bom-barcode v-model="dataBarcode" :key="selected.id"></bom-barcode>
			</v-print>
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
			'bom-barcode': 'url:ui/bom/transaction/barcode.vue',
            "v-print": "url:ui/base/print.vue",
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
					filter: {},
					filterType: {},
					sortBy: ['id'],
					sortDesc: [false]
				}
			}
		},
		data: function() {
			return {
				name: 'bomitem',
				itemKey: 'id',
				url: 'transaction/bomitem',
				showBarcode: false,
				showBarcodeDialog: false,
				dataBarcode: [],
				loading: false,
				go: false,
				newUpload: false,
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
					"sortable": true,
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
					"text": "Unit QTY",
					"value": "unit_qty",
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
					"sortable": false,
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
				},  {
					"text": "Created Date",
					"value": "created_date",
					"align": "start",
					"sortable": false,
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
					"text": "Modified By",
					"value": "modified_by",
					"align": "start",
					"sortable": false,
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
				},{
					"text": "Modified Date",
					"value": "modified_date",
					"align": "start",
					"sortable": false,
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
			async showBarcodeDialog(val){
				if(val==false)
					this.showBarcode = false
			},
			async showBarcode(val){
				var self = this
				if(val){
					self.loading = true
					try {
						App.tmp.target = self
						var res = await axios.get(App.url+'/transaction/bomheader/getitemnew', {
							params: {
								bom_unique_id: self.selected.bom_unique_id,
								part_number: self.selected.part_number
							}
						})

						self.go = true
	
						self.dataBarcode = res.data.data
						self.loading = false
						self.showBarcodeDialog = true
					} catch (err) {
						self.loading = false
						App.errorMsg()
					}
				}
			},
			uploadDialog: function(val){
				if(!val)
					this.$refs.template.getItem()
			},
			file: function(val){
				if(val)
					this.data = val[0].data.filter(val=>val.length>2).slice(1)
				console.log(this.data)
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
			print: function(){
				var w = window.open('', 'wnd');
				w.document.body.innerHTML = document.getElementsByClassName('section-to-print')[0].outerHTML
				w.print()
				w.setTimeout(()=>{
					w.close()
				}, 250)
			},
			save: function(){
				var self = this
				setTimeout(async () => {
					var tmp = self.data.filter(val=>{return val[0]!==true}).slice(0, 10)
					var str = []
					if(self.newUpload==false){
						tmp.map(val=>{
							str.push([
								App.page().$refs.bom.selected.id, 
								(val[2]||'').toString().trim().length == 0 ? '-' : val[2], 
								(val[5]||'').trim().length == 0 ? '-' : val[5], 
								(val[6]||'').toString().trim().length == 0 ? '0' : val[6], 
								(val[8]||'').trim().length == 0 ? '-' : val[8], 
								(val[10]||'').trim().length == 0 ? '-' : val[10], 
								(val[11]||'').toString().trim().length == 0 ? '-' : val[11]
							].join(';;'))
						})
					}else{
						tmp.map(val=>{
							str.push([
								App.page().$refs.bom.selected.id, 
                                (val[1]||'').toString().trim().length == 0 ? '-' : val[1],
								(val[2]||'').toString().trim().length == 0 ? '-' : val[2], 
                                (val[3]||'').toString().trim().length == 0 ? '-' : val[3],
								(val[4]||'').toString().trim().length == 0 ? '-' : val[4], 
                                (val[5]||'').toString().trim().length == 0 ? '-' : val[5], 
								(val[6]||'').trim().length == 0 ? '-' : val[6],
                                (val[7]||'').toString().trim().length == 0 ? '-' : val[7],
                                (val[8]||'').toString().trim().length == 0 ? '-' : val[8],
								
                                
							].join(';;'))
						})
					}
					str = str.join(';;;')
					if(self.newUpload==false)
						var res = await axios.post(App.url+self.url, {
							batch: str
						})
					else
						var res = await axios.post(App.url+self.url, {
							batch2: str
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
                if (self.isInViewport) {
                    self.$refs.template.defaultItemsOptions.filter.bom_receive_id = self.data.bom_receive_id
                    self.$refs.template.getItems()
                }
			},
		},
		mounted: function() {
			console.log(this)
		}
	}

</script>
