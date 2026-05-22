<template>
	<v-container v-resize="onResize" id="finance-monitoring" class="no-padding-margin rotatable"
		style="padding : 0 !important; height: 100%; display: flex; flex-direction: row; margin: 0; width: 100%; max-width: 100%;">
		<div style="display: flex; flex: 1; flex-direction: column;">
			<div style=" flex: 1">
				<v-chart @ready="onChartReady" :default-options="summaryChart" v-model="summaryChartData"
					style="height: 100%; width: 100%;" ref="chart"></v-chart>
			</div>
			<div style=" flex: 1">
				<v-chart @ready="onChartReady2" :default-options="detailChart" v-model="detailChartData"
					style="height: 100%; width: 100%;" ref="chart"></v-chart>
			</div>
		</div>
		<div style="display: flex; flex: 1; flex-direction: column;">
			<div style=" flex: 1;">
				<v-card>
					<div class="v-data-table theme--light">
						<div class="v-data-table__wrapper">
							<table>
								<thead>
									<tr>
										<th class="text-left">
											No
										</th>
										<th class="text-left">
											Project Name
										</th>
										<th class="text-left">
											Sum of List Transfer
										</th>
										<th class="text-left">
											Sum of Rupiah
										</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item, index) in tableData" :key="index">
										<td>{{index+1}}</td>
										<td>{{item.name}}</td>
										<td>{{item.transfer_list}}</td>
										<td>{{item.sum_rupiah}}</td>
									</tr>
									<tr>
										<td>Grand Total</td>
										<td></td>
										<td>{{totalTransferList}}</td>
										<td>{{totalSum}}</td>
									</tr>
									<tr>
										<td>Summary</td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</v-card>
			</div>
			<div style=" flex: 1;">
				<v-card>
					<div class="v-data-table theme--light">
						<div class="v-data-table__wrapper">
							<table>
								<thead>
									<tr>
										<th class="text-left">
											No
										</th>
										<th class="text-left">
											Project Name
										</th>
										<th class="text-left">
											Sum of List Transfer
										</th>
										<th class="text-left">
											Sum of Rupiah
										</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item, index) in tableData2.filter(v=>v.project_name)" :key="index">
										<td>{{index+1}}</td>
										<td>{{item.project_name}}</td>
										<td>{{item.transfer_list || ''}}</td>
										<td>{{item.idr.format(2, 3, '.', ',')}}</td>
									</tr>
									<tr>
										<td>Grand Total</td>
										<td></td>
										<td>{{totalTransferList2.format(2, 3, '.', ',')}}</td>
										<td>{{totalSum2.format(2, 3, '.', ',')}}</td>
									</tr>
									<tr v-for="(item, index) in summary2" :key="index">
										<td>{{ index == 0 ? 'Summary' : '' }}</td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</v-card>
			</div>
		</div>
	</v-container>
</template>

<style></style>

<script>
module.exports = {
	name: '',
	data: function () {
		return {
			totalTransferList: 0,
			totalSum: 0,
			totalTransferList2: 0,
			totalSum2: 0,
			summary: [],
			summary2: [],
			tableData: [],
			tableData2: [],
			chart: false,
			chart2: false,
			detailChartData: {
			},
			summaryChartData: {
				0: []
			},
			summaryChart: {
				color: ['#5470C6', '#91CC75', '#EE6666', '#FAC858'],
				title: {
					text: 'Recon Nojorono',
					left: 'center'

				},
				legend: {
					top: 30
				},
				tooltip: {
					trigger: 'item'
				},
				xAxis: [],
				yAxis: [],
				series: [
					{
						name: 'Summary',
						type: 'pie',
						radius: ['0%', '55%'],
						avoidLabelOverlap: false,
						data: []
					}
				]
			},
			detailChart: {
				color: ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc'],
				title: {
					text: '',
					left: 'center'

				},
				legend: {
					top: 30
				},
				tooltip: {
					trigger: 'item'
				},
				xAxis: [{
					type: "category",
					data: []
				}],
				yAxis: [{
					type: "value",
					name: ''
				}],
				series: [
					{
						name: 'Anggaran Project',
						type: 'bar',
						stack: 'one',
						data: [],
						barMinHeight: 2
					},
					{
						name: 'Sudah Dibayar',
						type: 'bar',
						stack: 'one',
						data: [],
						barMinHeight: 2
					},
					{
						name: 'Invoice Belum Dibayar',
						type: 'bar',
						stack: 'one',
						data: [],
						barMinHeight: 2
					},
					{
						name: 'PO Akan Dibayar (Belum Ada Invoice)',
						type: 'bar',
						stack: 'one',
						data: [],
						barMinHeight: 2
					}
				]
			},
		}
	},
	methods: {
		onResize: function () {
			if (this.chart) {
				/* this.generateOption(this.rawData)
				this.data = App.updateObject(this.data) */
				this.chart.resize()
			}
			if (this.chart2) {
				/* this.generateOption(this.rawData)
				this.data = App.updateObject(this.data) */
				this.chart2.resize()
			}
		},
		onChartReady(chart) {
			var self = this
			chart.showLoading()
			this.chart = chart
			this.get()
		},
		onChartReady2(chart) {
			var self = this
			chart.showLoading()
			this.chart2 = chart
			this.get()
		},
		async get() {
			var self = this
			try {
				
				var res = await axios.get(App.url + 'bom/pobudget/getdatapaid', {
						params: {
							filter: {
								remaining_idr: 0
							},
							filterType:{
								remaining_idr: '!='
							},
							limit: -1
						}
				})

				var data = res.data
				var projects = {}

				var detailChartData = {
					0: [],
					1: [],
					2: [],
					3: [],
				}
				data.data.map(v=>{
					/*if(projects[v.project_name] == undefined){
						projects[v.project_name] = {
							anggaran: 0,
							sudah_dibayar: 0,
							belum_dibayar: 0,
							akan_dibayar: 0,
						}
					}*/

					projects[v.project_name] = v.project_name
					detailChartData[0].push(v.total_in_idr)
					detailChartData[1].push(v.paid_in_idr)
					detailChartData[2].push(v.invoice_total_price)
					detailChartData[3].push(v.remaining_idr)
				})
				self.detailChart.xAxis[0].data = Object.values(projects)
				self.detailChartData = detailChartData

				if (self.chart2)
					self.chart2.hideLoading()
				/* var data = {
					status: true,
					data: [{
						value: 35,
						name: 'anggaran',
						project: 'Project 1'
					}, {
						value: 35,
						name: 'sudah dibayar',
						project: 'Project 1'
					}, {
						value: 35,
						name: 'invoice belum dibayar',
						project: 'Project 1'
					}, {
						value: 35,
						name: 'po akan dibayar',
						project: 'Project 1'
					}, {
						value: 35,
						name: 'anggaran',
						project: 'Project 2'
					}, {
						value: 35,
						name: 'sudah dibayar',
						project: 'Project 2'
					}, {
						value: 35,
						name: 'invoice belum dibayar',
						project: 'Project 2'
					}, {
						value: 35,
						name: 'po akan dibayar',
						project: 'Project 2'
					}, {
						value: 35,
						name: 'anggaran',
						project: 'Project 3'
					}, {
						value: 35,
						name: 'sudah dibayar',
						project: 'Project 3'
					}, {
						value: 35,
						name: 'invoice belum dibayar',
						project: 'Project 3'
					}, {
						value: 35,
						name: 'po akan dibayar',
						project: 'Project 3'
					}, {
						value: 35,
						name: 'anggaran',
						project: 'Project 4'
					}, {
						value: 35,
						name: 'sudah dibayar',
						project: 'Project 4'
					}, {
						value: 35,
						name: 'invoice belum dibayar',
						project: 'Project 4'
					}, {
						value: 35,
						name: 'po akan dibayar',
						project: 'Project 4'
					}]
				} */
			} catch (error) {
				console.log(error)
			}
			try {

				/* var res2 = await axios.get(App.url + 'dummy2', {
						params: {
						}
				})

				var data2 = res2.data */

				var data2 = {
					status: true,
					data: [{ value: 1048580, name: 'Anggaran Project' },
					{ value: 135484, name: 'Sudah Dibayar' },
					{ value: 135484, name: 'Invoice Belum Dibayar' },
					{ value: 135484, name: 'PO Akan Dibayar (Belum Ada Invoice)' },]
				}
				
			} catch (error) {
				
			}
			try {

				/* var res3 = await axios.get(App.url + 'dummy2', {
						params: {
						}
				})

				var data3 = res3.data */

				var data3 = {
					status: true,
					data: [
						{ transfer_list: 1727092.51, name: 'RECON NOJORONO', sum_rupiah: 3972312773.00, type: 'BSL' },
						{ transfer_list: 977733.00, name: 'BMT TROWULAN RESEARCH AND DEVELOPMENT', sum_rupiah: 2248785900.00, type: 'OBH' },
					]
				}

				var totalTransferList = 0
				var totalSum = 0
				/* var summary = {}
				var summaryTotalList = 0
				var summaryTotalRp = 0 */
				data3.data.map(v=>{
					totalTransferList += v.transfer_list
					totalSum += v.sum_rupiah
					/* if(summary[v.type] == undefined)
						summary[v.type] = {
							transferList: 0,
							sumRupiah: 0,
						}
					summary[v.type].transferList += v.transfer_list
					summary[v.type].sumRupiah += v.sum_rupiah */
				})

				/*self.summary = summary*/

				self.totalTransferList = totalTransferList
				self.totalSum = totalSum
				self.tableData = data3.data
				
			} catch (error) {
				
			}
			
			try {

				var res4 = await axios.get(App.url + 'bom/pobudget/getdataproject', {
						params: {
						}
				})

				var data4 = res4.data

				var totalTransferList = 0
				var totalSum = 0
				/* var summary = {}
				var summaryTotalList = 0
				var summaryTotalRp = 0 */
				data4.data.map(v=>{
					v.idr = Number((v.idr||0).replace(/\,/g, ''))
					totalTransferList += v.transfer_list || 0
					totalSum += v.idr
					/* if(summary[v.type] == undefined)
						summary[v.type] = {
							transferList: 0,
							sumRupiah: 0,
						}
					summary[v.type].transferList += v.transfer_list
					summary[v.type].sumRupiah += v.sum_rupiah */
				})

				/*self.summary = summary*/

				self.totalTransferList2 = totalTransferList
				self.totalSum2 = totalSum
				self.tableData2 = data4.data
				
			} catch (error) {
				
			}

			/*var label = {}
			data.data.map(v=>{
				label[v.project] = v.project
				if(v.name == 'anggaran'){
					detailChartData[0].push(v.value)
				}
				if(v.name == 'sudah dibayar'){
					detailChartData[1].push(v.value)
				}
				if(v.name == 'invoice belum dibayar'){
					detailChartData[2].push(v.value)
				}
				if(v.name == 'po akan dibayar'){
					detailChartData[3].push(v.value)
				}
			})

			self.detailChart.xAxis[0].data = Object.values(label)
			self.detailChartData = detailChartData*/

			var summaryChartData = {
				0: []
			}
			data2.data.map(v=>{
				summaryChartData[0].push({
					value: v.value,
					name: v.name
				})
			})

			self.summaryChartData = summaryChartData

			if (self.chart)
				self.chart.hideLoading()
		}
	},
	mounted: function () {
		var self = this
		//self.get()
	}
}
</script>