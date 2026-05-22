<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader" show-expand single-expand>
			<template v-slot:menu-after-filter>
				<v-btn small color="info" outlined @click="laporan" :disabled="!selected">Report</v-btn>
				<v-btn small color="info" outlined @click="reportPeriodic=true;report=[]">Periodic Report</v-btn>
				<v-btn small color="info" outlined @click="laporanNew=true;report=[];target='getLaporanNew'">Report New</v-btn>
				<v-btn small color="info" outlined @click="laporanNew=true;report=[];target='getLaporanBalance'">Report Balance</v-btn>
				<v-btn small color="primary" outlined :disabled="!selected" @click="dialogPart=true">Detail</v-btn>
			</template>
			<!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					
				</td>
			</template> -->
			<template v-slot:item.saldo="props">
                {{ Number(props.item.saldo).format(2, 3) }}
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
			<!-- 
			 -->
			<template v-slot:item.total_debet="props">
                {{ Number(props.item.total_debet).format(2, 3) }}
            </template>
			<template v-slot:item.total_kredit="props">
                {{ Number(props.item.total_kredit).format(2, 3) }}
            </template>
			
			<template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					<rincian-transaksi table-only :data="{
						petty_cash_id: props.item[itemKey]
					}" :sel="props.item" :key="props.item[itemKey]" :table-fixed-header="false" :item-height-as-min-height="true" :table-fill="false"></rincian-transaksi>
				</td>
			</template>
		</v-template>
		<v-action-dialog :actions="false" v-model="dialogPart" :title="'Rincian Transaksi - '+(selected||{title:''}).title+' '+(selected||{header_date:''}).header_date" min-height="75%" fullscreen>
			<rincian-transaksi :key="selected.id" :data="dataid" :sel="processData" :table-only="tableOnly" ref="detail"></rincian-transaksi>
		</v-action-dialog>
		
		<v-action-dialog v-model="reportPeriodic" title="Periodic Report" fullscreen  label-save="Submit" @save="getReportPeriodic">
			<v-date-input v-model="date1" label="From"></v-date-input>
			<v-date-input v-model="date2" label="To"></v-date-input>
		</v-action-dialog>
		
		<v-action-dialog v-model="laporanNew" title="New Report" fullscreen  label-save="Submit" @save="openReport">
			<v-date-input v-model="date1" label="From"></v-date-input>
			<v-date-input v-model="date2" label="To"></v-date-input>
		</v-action-dialog>

		<v-action-dialog v-model="reportDialog" title="Report" fullscreen  label-save="print" @save="$refs.vprint.print()">
			<v-print ref="vprint" style="padding: 20pt !important; margin: 0; padding-top: 0;" v-if="report.length > 0">
				<table class="simple-table default-table default" style="margin-top: 10px;">
					<thead>
						<tr>
							<th>No</th>
							<th>Date</th>
							<th>Judul</th>
							<th>Keterangan</th>
							<th style="width: 15%">Attachment</th>
							<th style="width: 15%"></th>
							<th colspan="2">Jumlah</th>
						</tr>
					</thead>
					<tbody>
						<template v-for="(item, index) in report">
							<tr>
								<td>
									{{index+1}}
								</td>
								<td style="text-align: center;">
									{{item.header_date}}
								</td>
								<td>
									{{item.title}}
								</td>
								<td>
									{{item.description}}
								</td>
								<td>
									{{item.attachment}}
								</td>
								<td></td>
								<td style="width: 10%; text-align: right;">
									{{item.total_debet}}
								</td>
								<td style="width: 10%; text-align: right;">
									{{item.total_kredit}}
								</td>
							</tr>
							<tr>
								<th colspan="3"></th>
								<th>Rincian</th>
								<th>Bukti</th>
								<th>Pos Akun</th>
								<th>Debet</th>
								<th>Kredit</th>
							</tr>
							<tr v-for="(item2, index2) in item.detail" :key="index2">
								<td colspan="3"></td>
								<td>{{item2.rincian}}</td>
								<td>{{item2.bukti}}</td>
								<td>[{{item2.pos_akun_kode}}] {{item2.nama_pos}}</td>
								<td style=" text-align: right;">{{Number(item2.debet).format(2, 3)}}</td>
								<td style=" text-align: right;">{{Number(item2.kredit).format(2, 3)}}</td>
							</tr>
						</template>
					</tbody>
				</table>
			</v-print>
		</v-action-dialog>
		<v-action-dialog v-model="reportBalanceDialog" title="Report Balance" fullscreen  label-save="print" @save="$refs.vprint.print()">
			<v-print ref="vprint" style="padding: 20pt !important; margin: 0; padding-top: 0;" v-if="report.length > 0">
				<table class="simple-table default-table default" style="margin-top: 10px;" id="reportnew">
					<thead>
						<tr>
							<th>No</th>
							<th>Pos Akun</th>
							<th>Amount</th>
						</tr>
					</thead>
					<tbody>
						
						<template v-for="(item, index) in report">
							<tr v-if="item.nama_pos != 'Grand Total'">
								<td>
									{{index+1}}
								</td>
								<td>{{item.nama_pos}}</td>
								<td>{{Number(item.total).format(2,3)}}</td>
							</tr>
							<tr v-else>
								<th colspan="2">{{item.nama_pos}}</th>
								<th>{{Number(item.total).format(2,3)}}</th>
							</tr>
						</template>
					</tbody>
				</table>
			</v-print>
		</v-action-dialog>
		<v-action-dialog v-model="reportnewDialog" title="Report" fullscreen  label-save="print" @save="$refs.vprint.print()">
			<v-print ref="vprint" style="padding: 20pt !important; margin: 0; padding-top: 0;" v-if="report.length > 0">
				<table class="simple-table default-table default" style="margin-top: 10px;" id="reportbalance">
					<thead>
						<tr>
							<th>No</th>
							<th>Saldo</th>
							<th>Date Voucher</th>
							<th>No. Voucher</th>
							<th>Rekon Bank</th>
							<th>POS</th>
							<th style="width: 15%">Debet</th>
							<th style="width: 15%">Kredit</th>
							<th style="width: 15%">PPN</th>
							<th style="width: 15%">PPH23</th>
							<th style="width: 15%">Other</th>
							<th style="width: 15%">Net Amount</th>
							<th></th>
							<th>Description</th>
							<th>Beneficiary Name</th>
							<th>Bank Account</th>
							<th>NPWP</th>
							<th>PO Reference</th>
							<th>Invoice Date</th>
							<th>Due Date</th>
							<th>Invoice Ref</th>
							<th>NSFP - VAT IN</th>
							<th>Status</th>
							<th>Notes</th>
							<th>Input By</th>
						</tr>
					</thead>
					<tbody>
						<template v-for="(item, index) in report">
							<tr>
								<td>
									{{index+1}}
								</td>
								<td style="text-align: center;">
									{{Number(item.saldo).format(2,3)}}
								</td>
								<td>
									{{item.date_voucher}}
								</td>
								<td>
									{{item.no_voucher}}
								</td>
								<td>
									{{item.rekon_bank}}
								</td>
								<td>
									{{item.nama_pos}}
								</td>
								<td style="width: 10%; text-align: right;">
									{{Number(item.debet).format(2,3)}}
								</td>
								<td style="width: 10%; text-align: right;">
									{{Number(item.kredit).format(2,3)}}
								</td>
								<td style="width: 10%; text-align: right;">
									{{Number(item.ppn).format(2,3)}}
								</td>
								<td style="width: 10%; text-align: right;">
									{{Number(item.pph23).format(2,3)}}
								</td>
								<td style="width: 10%; text-align: right;">
									{{Number(item.other).format(2,3)}}
								</td>
								<td style="width: 10%; text-align: right;">
									{{item.net_amount}}
								</td>
								<td>
									{{item.title}}
								</td>
								<td>
									{{item.description}}
								</td>
								<td>
									{{item.beneficiary_name}}
								</td>
								<td>
									{{item.bank_account}}
								</td>
								<td>
									{{item.npwp}}
								</td>
								<td>
									{{item.po_reference}}
								</td>
								<td>
									{{item.invoice_date}}
								</td>
								<td>
									{{item.due_date}}
								</td>
								<td>
									{{item.invoice_ref}}
								</td>
								<td>
									{{item.nsfp}}
								</td>
								<td>
									{{item.status}}
								</td>
								<td>
									{{item.notes}}
								</td>
								<td>
									{{item.created_by_name}}
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</v-print>
            <template v-slot:prepend-actions>
				<v-btn small color="primary" outlined @click="tableToExcel(document.getElementById('reportbalance', 'Report Cash'))">Excel</v-btn>
            </template>
		</v-action-dialog>
	</v-container>
</template>

<style>
	.simple-table-td{
		vertical-align: top;
	}
	table.default{
		border: 1px solid #0f0f0f;
		font-size: 10px;
	}
	table.default th, table.default th:first-child, table.default th:last-child, table.default td, table.default td:first-child, table.default td:last-child{
		border: 1px solid #0f0f0f !important;
	}
</style>

<script>
	module.exports = {
		name: '',
		creator: '',
		components: {
			'rincian-transaksi': 'url:ui/finance/petty_cash/detail.vue',
            "v-print": "url:ui/base/print2.vue",
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
				name: 'petty cash',
				itemKey: 'id',
				target: '',
				url: 'finance/pettycash',
				date1: new Date().formatDate('YYYY-MM-DD'),
				date2: new Date().formatDate('YYYY-MM-DD'),
				loading: false,
				reportDialog: false,
				reportPeriodic: false,
				reportDialogPrint: false,
				reportnewDialog: false,
				reportBalanceDialog: false,
				laporanNew: false,
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
					"text": "Date",
					"value": "header_date",
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
					"text": "Title",
					"value": "title",
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
					"text": "Description",
					"value": "description",
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
				},{
					"text": "Saldo",
					"value": "saldo",
					"align": "float",
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
					"text": "Total Debet",
					"value": "total_debet",
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
					"text": "Total Kredit",
					"value": "total_kredit",
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
					text: '',
					value: 'data-table-expand'
				}],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
                dialogPart: false,
                dataid: {},
                processData: {},
				report: []
			}
		},
		watch: {
			dialogPart() {
				var self = this
				self.$refs.template.getItems()
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
			onSelectedRow: function(val) {
				var self = this
				if (val === undefined) {
					self.selected = false
                    self.processData = {}
					self.dataid = {}
				} else {
					self.selected = val
                    self.processData = {
                        petty_cash_id: val.id,
                        //rev: val.rev
                    }
                    self.dataid = {
                        petty_cash_id: val.id,
                    }
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
			async laporan(){
				var self = this
				var res = await axios.get(App.url+'finance/pettycash/report', {
					params: {
						id: self.selected.id
					}
				})
				self.report = res.data.data
				self.reportDialog = true
			},
			async getLaporanNew(){
				var self = this
				var res = await axios.get(App.url+'finance/pettycash/reportnew', {
					params: {
						date1: self.date1,
						date2: self.date2
					}
				})
				self.laporanNew = false
				self.report = res.data.data3
				self.reportnewDialog = true
			},
			async getLaporanBalance(){
				var self = this
				var res = await axios.get(App.url+'finance/pettycash/reportnew', {
					params: {
						date1: self.date1,
						date2: self.date2
					}
				})
				self.laporanNew = false
				var tmp = {}, total = 0
				res.data.data3.map((v, i)=>{
					if(i>0){
						if(tmp[v.nama_pos] == undefined)
							tmp[v.nama_pos] = {
								nama_pos: v.nama_pos,
								total: 0
							}
						tmp[v.nama_pos].total += v.net_amount
						total += v.net_amount
					}
				})
				self.report = Object.values(tmp)
				self.report.push({
					nama_pos: 'Grand Total',
					total: total
				})
				self.reportBalanceDialog = true
			},
			async getReportPeriodic(){
				
				var self = this
				var res = await axios.get(App.url+'finance/pettycash/reportperiodic', {
					params: {
						date1: self.date1,
						date2: self.date2
					}
				})
				self.reportPeriodic = false
				self.report = res.data.data
				self.reportDialog = true
			},
			openReport(){
				this[this.target]()
			}
		},
		mounted: function() {

		}
	}

</script>
