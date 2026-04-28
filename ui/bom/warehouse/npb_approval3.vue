<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :disable-delete-button="checkSelected('approved')>=0" :disable-edit-button="checkSelected('approved')>=0" :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
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
            </template>
			
			<!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
		   <template v-slot:item.approved="props">
			{{status[props.item.approved]}}
		   </template>
			
            <template v-slot:append-menu>
				<v-btn small color="primary" outlined @click="openPrint" :disabled="!selected">Print NPB</v-btn>
                <v-btn color="success" outlined small @click="approve" :disabled="disableApproval">
                    Approve
                </v-btn>
                <v-btn color="error" outlined small @click="reject" :disabled="disableApproval">
                    Reject
                </v-btn>
				<v-btn small color="primary" outlined @click="openItem" :disabled="!selected">Items</v-btn>
            </template>

			<v-action-dialog fullscreen :actions="false" title="NPB Item" v-model="dialogItem">
				<npb-item :data="{npb_id: selected.id}" :table-only="[4].includes(checkSelected('flag'))"></npb-item>
			</v-action-dialog>
			<v-action-dialog label-save="print" @save="$refs.vprint.print()" v-model="dialogPrint" title="NPB Print" min-height="75%" fullscreen>
				<v-print ref="vprint" style="margin: auto; color: #000; max-width: 100%; padding: 20pt">
					<nav class="tw-header print-header">
						<h1 class="px-2 text-xl text-center my-auto uppercase font-bold"><img src="../img/logo2.png"></h1>
						<div style="align-self: end; font-size: 10px;">Jl. Mayjend Yono Soewoyo No. 35 - Surabaya 60225 - Indonesia - Telp. (031) 99900899</div>
					</nav>
					<div style="font-size: 20px; font-weight: bold; padding-top: 14px;  padding-bottom: 14px; text-align: center;">
						SURAT SERAH TERIMA BARANG<br/>
						<span style="font-size: 12px; font-weight: normal;">No.: {{selected.npbno}}</span>
					</div>
					<br/><br/><br/>
					<table class="default-table with-border print-table table-align-top" style=" max-width: 100%;">
						<thead>
							<tr>
								<th>No.</th>
								<th>Part No.</th>
								<th>Nama Barang</th>
								<th>Jumlah</th>
								<th>Satuan</th>
								<th>Keterangan</th>
							</tr>
						</thead>
						<tbody>
							<template v-for="(item, index) in print.existing">
								<tr v-for="(v, i) in item.count" :key="index+'-'+i" :class="i !=0 ? 'no-top-border':''">
									<td style="text-align: center;" :rowspan="item.count" v-if="i==0">{{item.no}}</td>
									<td :rowspan="item.count" v-if="i==0">
										{{item.item_no}}
									</td>
									<td :rowspan="item.count" v-if="i==0">
                                        {{item.item_name}} <br><span>Manufacture PN: {{ item.manufacture_pn }}</span>
									</td>
									<td :style="'text-align: right; width: 50pt;'+(item.count != v ? '':'')">{{numberFormat(item.qty[i])}}</td>
									<td :style="'text-align: center; width: 50pt;'+(item.count != v ? '':'')">{{item.unit[i]}}</td>
									<td :style="'text-align: left; width: 120pt;'+(item.count != v ? '':'')">{{item.notes[i]}}</td>
								</tr>
							</template>
						</tbody>
					</table>
					<br/>
					<br/>
					<table class="default-table no-border">
						<tr>
							<td style="width: 33%;"></td>
							<td style="width: 33%;"></td>
							<td style="width: 33%;">Surabaya, {{new Date(selected.created_date).formatDate('D MMMM YYYY')}}</td>
						</tr>
						<tr>
							<td style="width: 33%;">
								<br/>
								Kepala Logistik<br/><br/><br/><br/>
								( Arie Purjanto )
							</td>
							<td style="width: 33%;">
								<br/>
								Peminta<br/><br/><br/><br/>
								( {{selected.peminta_name}} )
							</td>
							<td style="width: 33%;">
								Mengetahui,<br/>
								Director<br/><br/><br/><br/>
								( Rinaldi Darmagiri )
							</td>
						</tr>
					</table>
				</v-print>
			</v-action-dialog>
		</v-template>
	</v-container>
</template>

<style>
	.table-align-top td{
		vertical-align: top;
	}
	.print-table td{
		text-wrap: wrap;
	}
	.print-header{
		border-bottom: 3px double black !important;
		border-style: double;
		padding: 0 !important;
	}
	.no-border, .no-border td, .no-border th{
		border: 0px !important;
	}
	.with-border, .with-border td, .with-border th{
		border: 1px solid black !important;
	}
	.no-top-border td{
		border-top: 0 !important;
	}
	.td-no-border-bottom{
		border-bottom: 0 !important;
	}
</style>

<script>
	module.exports = {
		name: '',
		creator: '',
		components: {
			'npb-item': 'url:ui/bom/warehouse/npb_item.vue',
            'v-print': 'url:ui/base/print.vue'
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
					filter: {
						approved: 3,
						flag: 1
					},
					filterType: {}
				}
			}
		},
		data: function() {
			return {
				status: {
					'-1': 'Rejected',
					0: 'New',
					1: 'Asking Approval',
					4: 'Approved'
				},
				name: 'npb',
				itemKey: 'id',
				url: 'transaction/npb',
				loading: false,
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
					"text": "Npb No",
					"value": "npb_no",
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
					"text": "Peminta",
					"value": "peminta_id",
					"align": "start",
					"sortable": true,
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
					"url": App.url + "users",
					"searchby": ["name"],
					"formatter": ["id", "name"],
					"options": {
						"filter": {
							id: [58, 9]
						},
						"filterType": {
							id: 'in'
						},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10",
					"alias": "peminta_name"
				}, {
					"text": "Flag",
					"value": "flag",
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
					"text": "Status",
					"value": "approved",
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
					"text": "Approved By",
					"value": "approved_by",
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
					"alias": "approved_by_name"
				}, {
					"text": "Rejected By",
					"value": "rejected_by",
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
					"alias": "rejected_by_name"
				}],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
				dialogItem: false,
				dialogPrint: false,
				print: {},
				dialogPrint: false,
				print: {}
			}
		},
		watch: {
			dialogItem: function(val){
				if(!val){
					this.$refs.template.getItems()
				}
			}
		},
		computed: {
			disableApproval: function(){
				var self = this
				if(self.selected == false)
					return true
				if(self.selected.approved==3)
					return false
				return true
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
		methods: {
			checkSelected: function(key, value){
				var self = this
				if(self.selected==false)
					return false
				if(value==undefined)
					return self.selected[key]
				return self.selected[key] == value
			},
			openItem: function(){
				var self = this
				self.dialogItem = true
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
			approve: async function(){
				var self = this
				var c = confirm("Are you sure?")
				if(!c)
					return true
				if(self.selected.approved==3){
					var r = await axios.put(App.url+self.url, {
						approved: 4,
						pk: self.selected.id
					})
					if(!r.data.status)
						App.errorMsg(r.data)
					else{
						self.$refs.template.getItems()
						App.successMsg()
					}
				}
			},
			reject: async function(){
				var self = this
				var c = confirm("Are you sure?")
				if(!c)
					return true
				if(self.selected.approved==1){
					var r = await axios.put(App.url+self.url, {
						approved: -1,
						pk: self.selected.id
					})
					if(!r.data.status)
						App.errorMsg(r.data)
					else{
						self.$refs.template.getItems()
						App.successMsg()
					}
				}
			},
			completeNPB: async function(){
				var self = this
				var c = confirm("Are you sure?")
				if(c){
					var res = await axios.put(App.url+'transaction/npb', {
						pk: self.selected.id,
						approved: 1
					})

					if(!res.data.status)
						App.errorMsg()
					else{
						App.successMsg()
						self.$refs.template.getItems()
					}
				}
			},
			openPrint: async function(){
				try {
					var self = this
					var res = await axios.get(App.url+"transaction/npbitem", {
						params: {
							filter : {
								npb_id: self.selected.id
							},
							sortBy: ['item_id']
						}
					})
					self.print.data = res.data.data
					self.print.existing = {}
					var no = 1
					self.print.data.map(val=>{
						if(self.print.existing[val.item_id] == undefined){
							self.print.existing[val.item_id] = val
							self.print.existing[val.item_id].no = no
							self.print.existing[val.item_id].count = 0
							self.print.existing[val.item_id].allocation = [val.allocation]
							self.print.existing[val.item_id].notes = [val.notes]
							self.print.existing[val.item_id].qty = [val.qty]
							self.print.existing[val.item_id].unit = [val.unit]
							no++
						}
						else{
							self.print.existing[val.item_id].allocation.push(val.allocation)
							self.print.existing[val.item_id].notes.push(val.notes)
							self.print.existing[val.item_id].qty.push(val.qty)
							self.print.existing[val.item_id].unit.push(val.unit)
						}
						self.print.existing[val.item_id].count++
					})
					self.dialogPrint=true
				} catch (err) {
					App.errorMsg()
				}
			}
		},
		mounted: function() {
			var self = this
			if(check_user(['administrator']))
				delete self.itemsOptions.filter.peminta_id
		}
	}

</script>
