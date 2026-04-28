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
				</td>p
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
			    <v-btn small color="primary" :disabled="selected==false" @click="itemDialog=true" outlined>Items</v-btn>
				<v-btn small color="primary" outlined @click="showBarcode=true" :disabled="!selected"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn>
				<v-btn small color="primary" outlined @click="showBarcodeNew=true" :disabled="!selected"><v-icon small left>mdi-barcode</v-icon>Print Barcode 2 Labels</v-btn>
			</template>
    <!--        <template v-slot:prepend-menu>-->
				<!--<v-btn small color="primary" :disabled="selected==false" @click="itemDialog=true" outlined>Add item</v-btn>-->
    <!--        </template>-->
			
			<!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
			<!-- 
            <template v-slot:append-menu>
            </template>
			 -->
		</v-template>
		<v-action-dialog :actions="false" v-model="itemDialog" title="Bom Partlist" fullscreen>
			<bom-item :data="dataid" :key="selected.id"></bom-item>
		</v-action-dialog>
		<v-action-dialog :actions="false" v-model="showBarcodeDialog" title="Bom Barcode" fullscreen>
			<v-btn small color="primary" outlined @click="print">Print</v-btn>
			<v-print ref="vprint" style="color: #000; ">
				<bom-barcode v-model="dataBarcode" :key="version"></bom-barcode>
			</v-print>
		</v-action-dialog>
			<v-action-dialog :actions="false" v-model="showBarcodeDialogNew" title="Bom Barcode" fullscreen>
			<v-btn small color="primary" outlined @click="print">Print</v-btn>
			<v-print-new ref="vprint" style="color: #000; ">
				<bom-barcode-new v-model="dataBarcodeNew" page-break :key="version"></bom-barcode-new>
			</v-print-new>
		</v-action-dialog>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: 'bom',
		creator: '',
		components: {
			'bom-item': 'url:ui/bom/transaction/bom_item.vue',
			'bom-barcode': 'url:ui/bom/transaction/barcode.vue',
			'bom-barcode-new': 'url:ui/bom/transaction/barcode_new.vue',
            "v-print": "url:ui/base/print.vue",
            "v-print-new": "url:ui/base/print.vue",
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
					filterType: {}
				}
			}
		},
		data: function() {
			return {
				name: 'bom',
				itemKey: 'id',
				url: 'transaction/bom',
				showBarcode: false,
				showBarcodeNew: false,
				showBarcodeDialog: false,
				showBarcodeDialogNew: false,
				dataBarcode: [],
				dataBarcodeNew: [],
				loading: false,
				go: false,
				itemDialog: false,
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
					"text": "bom_header_id",
					"value": "bom_header_id",
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
					"text": "Name",
					"value": "name",
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
					"text": "Machine No",
					"value": "machine_no",
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
					"text": "Po No",
					"value": "po_id",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					type: "list",
					data_value: [],
					disabled: false,
					visible: true,
					required: true,
					form: true,
					filter: true,
					groupable: false,
					url: App.url + "bom/purchaseorder",
					searchby: ["info"],
					formatter: ["id", "info"],
					options: {
						filter: {
						},
						filterType: {
						},
						filterCondition: {}
					},
					paging: true,
					page: "1",
					limit: "10",
					alias: "po_no",
				}, {
					"text": "Item",
					"value": "item_id",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"data_value": [],
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": App.url+"bom/item",
					"searchby": ['full'],
					"formatter": ['id', 'full'],
					"options": {
						"filter": {
							is_active: 1
						},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "item_no"
				}, {
					"text": "Qty",
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
				}, {
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
				}],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
				version: randomId(),
			}
		},
		watch: {
			async showBarcodeDialog(val){
				if(val==false)
					this.showBarcode = false
			},
			async showBarcodeDialogNew(val){
				if(val==false)
					this.showBarcodeNew = false
			},
			async showBarcode(val){
				var self = this
				if(val){
					self.version = randomId()
					self.loading = true
					try {
						App.tmp.target = self
						var res = await axios.get(App.url+'/transaction/bomheader/getitemsreceive', {
							params: {
								bom_receive_id: self.selected.id
							}
						})

						self.go = true
						var tmp = []

						res.data.data.map(val=>{
							if(Number(val.qty)<=60)
								tmp.push(Array(Number(val.qty)).fill(val))
							else
								tmp.push([val])
						})
						self.dataBarcode = tmp.flat()//res.data.data
						self.loading = false
						self.showBarcodeDialog = true
					} catch (err) {
						self.loading = false
						App.errorMsg()
					}
				}
			},
			async showBarcodeNew(val){
				var self = this
				if(val){
					self.version = randomId()
					self.loading = true
					try {
						App.tmp.target = self
						var res = await axios.get(App.url+'/transaction/bomheader/getitemsreceive', {
							params: {
								bom_receive_id: self.selected.id
							}
						})

						self.go = true
						var tmp = []

						res.data.data.map(val=>{
							if(Number(val.qty)<=60)
								tmp.push(Array(Number(val.qty)).fill(val))
							else
								tmp.push([val])
						})
						self.dataBarcodeNew = tmp.flat()//res.data.data
						console.log(self.dataBarcodeNew)
						self.loading = false
						self.showBarcodeDialogNew = true
					} catch (err) {
						self.loading = false
						App.errorMsg()
					}
				}
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
			},
			dataid: function(){
				if(this.selected == false)
					return {}
				return {
					bom_receive_id: this.selected.id
				}
			}
		},
		methods: {
			print: function(){
				var w = window.open('', 'wnd');
				w.document.body.innerHTML = document.getElementsByClassName('section-to-print')[0].outerHTML
				w.print()
				w.setTimeout(()=>{
					//w.close()
				}, 250)
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
                    self.$refs.template.defaultItemsOptions.filter.bom_header_id = self.data.bom_header_id
                    self.$refs.template.getItems()
                }
			},
		},
		mounted: function() {

		}
	}

</script>
