<template>
    <v-container v-resize="onResize" id="dashboard-procurement" class="no-padding-margin rotatable" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-chart @ready="onChartReady" :default-options="option" v-model="data" style="height: 100%; width: 100%; flex: 1" ref="chart"></v-chart>
		<v-screen v-model="showAnimation" ref="screen"></v-screen>
		<v-act-dialog fullscreen v-model="showData" :title="title" no-actions>
			<!-- <iframe :src="src" frameborder="0" style="border: 0px; width: 100%; height: 100%;"></iframe> -->
            <v-rfq-new v-if="chartSeries==0 && indexData==1"></v-rfq-new>
            <v-rfq-clarification v-if="chartSeries==0 && indexData==2"></v-rfq-clarification>
            <v-rfq-quotation v-if="chartSeries==0 && indexData==3"></v-rfq-quotation>
            <v-rfq-rfqreadyforpr v-if="chartSeries==0 && indexData==4"></v-rfq-rfqreadyforpr>
            <v-pr-prreadyforpr v-if="chartSeries==1 && indexData==1"></v-pr-prreadyforpr>
            <v-pr-prinprocess v-if="chartSeries==1 && indexData==2"></v-pr-prinprocess>
            <v-pr-prreadyforpo v-if="chartSeries==1 && indexData==3"></v-pr-prreadyforpo>
            <v-po-poreadyforpo v-if="chartSeries==2 && indexData==1"></v-po-poreadyforpo>
            <v-po-poinporcess v-if="chartSeries==2 && indexData==2"></v-po-poinporcess>
            <v-po-readyforapproval v-if="chartSeries==2 && indexData==3"></v-po-readyforapproval>
		</v-act-dialog>
		<!-- <login v-model="dialog_login" :login.sync="loginStatus"></login> -->
    </v-container>
</template>

<style>
.rotatable {
	-webkit-transition: all 0.5s ease-in-out;
	-moz-transition: all 0.5s ease-in-out;
	-o-transition: all 0.5s ease-in-out;
	transition: all 0.5s ease-in-out;
}

.rotated90 { 
	transform:rotate(360deg); 
	-webkit-transform:rotate(360deg); 
	-moz-transform:rotate(360deg); 
	-o-transform:rotate(360deg); 
}
</style>

<script> 
    module.exports = {
        name: '',
        components: {
			"login": "url:ui/users/login.vue",
            'v-screen': 'url:ui/base/screen.vue',
            'v-act-dialog': 'url:ui/base/v-action-dialog.vue',
            'v-rfq-new': 'url:ui/bom/monitoring/list_rfq_new.vue',
            'v-rfq-clarification': 'url:ui/bom/monitoring/list_rfq_clarification.vue',
            'v-rfq-quotation': 'url:ui/bom/monitoring/list_rfq_quotation.vue',
            'v-rfq-rfqreadyforpr': 'url:ui/bom/monitoring/list_rfq_readyforpr.vue',
            'v-pr-prreadyforpr': 'url:ui/bom/monitoring/list_pr_readyforpr.vue',
            'v-pr-prinprocess': 'url:ui/bom/monitoring/list_pr_inprocess.vue',
            'v-pr-prreadyforpo': 'url:ui/bom/monitoring/list_pr_readyforpo.vue',
            'v-po-poreadyforpo': 'url:ui/bom/monitoring/list_po_readyforpo.vue',
            'v-po-poinporcess': 'url:ui/bom/monitoring/list_po_inprocess.vue',
            'v-po-readyforapproval': 'url:ui/bom/monitoring/list_po_readyforapproval.vue',
        },
        data: function() {
            return {
				src: '',
				title: '',
				showAnimation: false,
				chartSeries: false,
                indexData: false,
				showData: false,
				dialog_login: true,
				loginStatus: false,
                data: {
                },
                w: window.innerWidth / 5,
                labelOption: {
                    show: true,
                    position: 'top',
                    distance: 15,
                    align: 'center',
                    verticalAlign: 'top',
                    rotate: 0,
                    formatter: '{c}',
                    fontSize: '0.5rem',
                    fontFamily: 'Calibri',
                    rich: {
                        name: {}
                    }
                },
                option: {
                },
				chart: false,
				rawData: []
            }
        },
		watch:{
			loginStatus: function(val){
				var self = this
				if(val){
					self.dialog_login = false
					self.getData()
					setNonPermanentInterval(function(){
						self.getData()
					}, 60000*15, 'dashboard-procurement', document.getElementById('dashboard-procurement'))
					
					setNonPermanentInterval(function(){
						var mins = parseInt(new Date().getMinutes())
						//if(mins/10 == 0){
							if(!['sm', 'md', 'xs'].includes(App.breakpoint))
								self.showAnimation = true
						//}
						//else{
						//	var elementToRotate = document.getElementById("dashboard-procurement"); 
						//	elementToRotate.classList.toggle("rotated90");
						//}
					}, 60000*5, 'dashboard-procurement', document.getElementById('dashboard-procurement'))
				}
			}
		},
        methods: {
			onResize: function(){
				if(this.chart){
					this.generateOption(this.rawData)
					this.data = App.updateObject(this.data)
					this.chart.resize()
				}
			},
			onChartReady(chart){
				var self = this
				chart.showLoading()
				this.chart = chart
                
				chart.on('click', function (params) {
					if(params.seriesIndex == 0 & params.dataIndex==1){
						self.title = "Issue Request"
						self.showData = true
						self.chartSeries = 0
                        self.indexData=1
                        console.log(self.chartSeries)
					}
                    if(params.seriesIndex == 0 & params.dataIndex==2){
                        self.title="Issue Request"
                        self.showData=true
                        self.chartSeries=0
                        self.indexData=2
                        console.log(self.chartSeries)
                    }
                    if(params.seriesIndex == 0 & params.dataIndex==3){
                        self.title="Issue Request"
                        self.showData=true
                        self.chartSeries=0
                        self.indexData=3
                        console.log(self.chartSeries)

                    }
                    if(params.seriesIndex==0 & params.dataIndex==4){
                        self.title="Issue Request"
                        self.showData=true
                        self.chartSeries=0
                        self.indexData=4
                        console.log(self.chartSeries)
                    }
                    if(params.seriesIndex==1 & params.dataIndex==1){
                        self.title="Purchase Requisition"
                        self.showData=true
                        self.chartSeries=1
                        self.indexData=1
                        console.log(self.chartSeries)
                    }
                    if(params.seriesIndex==1 & params.dataIndex==2){
                        self.title="Purchase Requisition"
                        self.showData=true
                        self.chartSeries=1
                        self.indexData=2
                        console.log(self.chartSeries)
                    }
                    if(params.seriesIndex==1 & params.dataIndex==3){
                        self.title="Purchase Requisition"
                        self.showData=true
                        self.chartSeries=1
                        self.indexData=3
                        console.log(self.chartSeries)
                    }
                    if(params.seriesIndex==2 & params.dataIndex==1){
                        self.title="Purchase Order"
                        self.showData=true
                        self.chartSeries=2
                        self.indexData=1
                        console.log(self.chartSeries)
                    }
                    if(params.seriesIndex==2 & params.dataIndex==2){
                        self.title="Purchase Order"
                        self.showData=true
                        self.chartSeries=2
                        self.indexData=2
                        console.log(self.chartSeries)
                    }
                    if(params.seriesIndex==2 & params.dataIndex==3){
                        self.title="Purchase Order"
                        self.showData=true
                        self.chartSeries=2
                        self.indexData=3
                        console.log(self.chartSeries)
                    }
				});
                
			},
            generateOption(data){
                var self = this;
				var mini = ['sm', 'md', 'xs'].includes(App.breakpoint)
                //var maxY = parseInt(res.rfq_new) + parseInt(res.rfq_clarification) + parseInt(res.rfq_quotation) + parseInt(res.rfq_ready_for_pr)
                //console.log(maxY)
                self.option = {
                    title: {
                        text: 'Dashboard Procurement',
                        subtext: new Date().formatDate("DD.MM.YYYY HH:mm:ss"),
                        textStyle: {
                            fontFamily: 'Calibri',
                            fontSize: mini ? '1.25rem' : '2rem'
                        },
                        subtextStyle: {
                            fontFamily: 'Calibri',
                        	fontSize: mini ? '1rem' : '1.5rem',
                            padding: [0, 0, 0, 0],
                        },
                        textAlign: 'center',
                        left: '50%'
                    },
                    legend: {},
                    xAxis: [{
                            name: 'Difference from date: '+new Date(data[1].log_date).formatDate("DD.MM.YYYY"),
                            nameLocation: 'center',
                            nameTextStyle: {
                                fontWeight: 'bolder',
                                padding: [mini ? 30 : 30, 0, 0, (window.innerWidth+(window.innerWidth/6))/2],
                                fontSize: mini ? '1rem' : '1.25rem',
                                fontFamily: 'Calibri'
                            },
                            type: 'category',
                            gridIndex: 0,
                            axisLabel: {
                                show: true,
                                interval: 0,
                                fontWeight: 'bolder',
                                fontSize: mini ? '0.8rem' : '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: mini ? 'left' : 'center',
                                formatter: function(value) {
                                    return ['Total', 'New', 'Clarification', 'Quotation', 'Ready for PR'][value]
                                }
                            },
                            axisTick: {
                                show: false
                            }
                        },
                        {   
                            type: 'category',
                            gridIndex: 1,
                            axisLabel: {
                                show: true,
                                interval: 0,
                                fontWeight: 'bolder',
                                fontSize: mini ? '0.8rem' : '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								padding: [0, 0, 20, 0],
								align: mini ? 'left' : 'center',
                                formatter: function(value) {
                                    return ['Total', 'Ready for PR', 'In Process', 'Ready for PO'][value]
                                }
                            },
                            axisTick: {
                                show: false
                            }
                        },
                        {
                            type: 'category',
                            gridIndex: 2,
                            axisLabel: {
                                show: true,
                                interval: 0,
                                fontWeight: 'bolder',
                                fontSize: mini ? '0.8rem' : '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: mini ? 'left' : 'center',
                                formatter: function(value) {
                                    return ['Total', 'Ready for PO', 'In Process', 'Ready for\nApproval'][value]
                                }
                            },
                            axisTick: {
                                show: false
                            }
                        }, 
                        {
                            type: 'category',
                            gridIndex: 3,
                            axisLabel: {
                                show: true,
                                interval: 0,
                                fontWeight: 'bolder',
                                fontSize: mini ? '0.8rem' : '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: mini ? 'left' : 'center',
                                formatter: function(value) {
                                    return ['Total', 'New', 'Clarification', 'Quotation', 'Ready for PR'][value]
                                }
                            },
                            axisTick: {
                                show: false
                            }
                        },
                        {
                            type: 'category',
                            gridIndex: 4,
                            axisLabel: {
                                show: true,
                                interval: 0,
                                fontWeight: 'bolder',
                                fontSize: mini ? '0.8rem' : '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: mini ? 'left' : 'center',
                                formatter: function(value) {
                                    return ['Total', 'Ready for PR', 'In Process', 'Ready for PO'][value]
                                }
                            },
                            axisTick: {
                                show: false
                            }
                        },
                        {
                            type: 'category',
                            gridIndex: 5,
                            axisLabel: {
                                show: true,
                                interval: 0,
                                fontWeight: 'bolder',
                                fontSize: mini ? '0.8rem' : '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: mini ? 'left' : 'center',
                                formatter: function(value) {
                                    return ['Total', 'Ready for PO', 'In Process', 'Ready for\nApproval'][value]
                                }
                            },
                            axisTick: {
                                show: false
                            }
                        },
						{
                            name: 'Issue Request',
                            nameLocation: 'center',
                            position: 'top',
                            gridIndex: 0,
                            type: 'category',
							axisLine: {
								show: false
							},
                            nameTextStyle: {
                                fontWeight: 'bolder',
                                padding: [15, 0, 0, 0],
                                fontSize: mini ? '1rem' : '1.25rem',
                                fontFamily: 'Calibri'
                            },
						},
						{
                            name: 'PR',
                            nameLocation: 'center',
                            position: 'top',
                            gridIndex: 1,
                            type: 'category',
							axisLine: {
								show: false
							},
                            nameTextStyle: {
                                fontWeight: 'bolder',
                                padding: [15, 0, 0, 0],
                                fontSize: mini ? '1rem' : '1.25rem',
                                fontFamily: 'Calibri'
                            },
						},
						{
                            name: 'PO',
                            nameLocation: 'center',
                            position: 'top',
                            gridIndex: 2,
                            type: 'category',
							axisLine: {
								show: false
							},
                            nameTextStyle: {
                                fontWeight: 'bolder',
                                padding: [30, 0, 0, 0],
                                fontSize: mini ? '1rem' : '1.25rem',
                                fontFamily: 'Calibri'
                            },
						}
                    ],
                    yAxis: [{
                        //show: false,
						name: '',
                        gridIndex: 0, minInterval: 1,
                        /* min:function(val){
							return Math.floor(val.min-((val.max+val.min)/100*10))
						},  */max: function(val){
							return Math.ceil(val.max+((val.max+val.min)/100*10))
						}
                    }, {
                        //show: false,
                        gridIndex: 1, minInterval: 1,
                        /* min:function(val){
							return Math.floor(val.min-((val.max+val.min)/100*10))
						},  */max: function(val){
							return Math.ceil(val.max+((val.max+val.min)/100*10))
						}
                    }, {
                        //show: false,
                        gridIndex: 2, minInterval: 1,
                        /* min:function(val){
							return Math.floor(val.min-((val.max+val.min)/100*10))
						},  */max: function(val){
							return Math.ceil(val.max+((val.max+val.min)/100*10))
						}
                    }, {
                        //show: false,
                        gridIndex: 3, minInterval: 1,
                        min:function(val){
							return Math.floor(val.min-((val.max+val.min)/100*10))
						}, max: function(val){
							return Math.ceil(val.max+((val.max+val.min)/100*10))
						}
                    }, {
                        gridIndex: 4, minInterval: 1,
                        min:function(val){
							return Math.floor(val.min-((val.max+val.min)/100*10))
						}, max: function(val){
							return Math.ceil(val.max+((val.max+val.min)/100*10))
						}
                    }, {
                        gridIndex: 5, minInterval: 1,
                        min:function(val){
							return Math.floor(val.min-((val.max+val.min)/100*10))
						}, max: function(val){
							return Math.ceil(val.max+((val.max+val.min)/100*10))
						}
                    }],
                    grid: [{
                        left: 40,
                        top: 100,
                        width: window.innerWidth / 5 * 1.9 - 40,
						bottom: mini ? '40%' : '40%'
                    }, {
                        left: window.innerWidth / 5 * 1.9 + 40,
                        top: 100,
                        width: window.innerWidth / 5 * 1.5 - 40,
						bottom: mini ? '40%' : '40%'
                    }, {
                        left: window.innerWidth / 5 * 3.4 + 40,
                        top: 100,
                        width: window.innerWidth / 5 * 1.5 - 40,
						bottom: mini ? '40%' : '40%'
                    }, {
                        left: 40,
                        width: window.innerWidth / 5 * 1.9 - 40,
						top: mini ? '80%' : '80%',
						bottom: mini ? 80 : 40
                    }, {
                        left: window.innerWidth / 5 * 1.9 + 40,
                        width: window.innerWidth / 5 * 1.5 - 40,
						top: mini ? '80%' : '80%',
						bottom: mini ? 80 : 40
                    }, {
                        left: window.innerWidth / 5 * 3.4 + 40,
                        width: window.innerWidth / 5 * 1.5 - 40,
						top: mini ? '80%' : '80%',
						bottom: mini ? 80 : 40
                    }],
                    series: [
                        {
                            type: 'bar',
                            selectedMode: 'single',
                            xAxisIndex: 0,
                            yAxisIndex: 0,
                            emphasis: {
                                focus: 'series',
                                barBorderColor: 'red',
                                barBorderWidth: 2,
                            },
                            tooltip: { 
                                show: true,
                                trigger: 'item',
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 30,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize: mini ? '1rem' : '1.25rem',
                                rich: {
                                    name: {}
                                }
                            }
                        },
                        {
                            type: 'bar',
                            selectedMode: 'single',
                            xAxisIndex: 1,
                            yAxisIndex: 1,
                            emphasis: {
                                focus: 'series',
                                barBorderColor: 'red',
                                barBorderWidth: 2,
                            },
                            tooltip: { 
                                show: true,
                                trigger: 'item',
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 30,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize:  mini ? '1rem' : '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        },
                        {
                            type: 'bar',
                            selectedMode: 'single',
                            xAxisIndex: 2,
                            yAxisIndex: 2,
                            emphasis: {
                                focus: 'series',
                                barBorderColor: 'red',
                                barBorderWidth: 2,
                            },
                            tooltip: { 
                                show: true,
                                trigger: 'item',
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 30,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize: mini ? '1rem' : '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        },
                        {
                            type: 'bar',
                            selectedMode: 'single',
                            xAxisIndex: 3,
                            yAxisIndex: 3,
                            emphasis: {
                                focus: 'series',
                                barBorderColor: 'red',
                                barBorderWidth: 2,
                            },
                            tooltip: { 
                                show: true,
                                trigger: 'item',
                            },
                            itemStyle: {
                                color: function(obj){
									return obj.value < 0 ? '#ee6666' : '#31B5FF'
								}
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 15,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize: mini ? '1rem' : '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        },
                        {
                            type: 'bar',
                            selectedMode: 'single',
                            xAxisIndex: 4,
                            yAxisIndex: 4,
                            tooltip: { 
                                show: true,
                                trigger: 'item',
                            },
                            emphasis: {
                                focus: 'series',
                                barBorderColor: 'red',
                                barBorderWidth: 2,
                            },
                            itemStyle: {
                                color: function(obj){
									return obj.value < 0 ? '#ee6666' : '#31B5FF'
								}
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 15,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize: mini ? '1rem' : '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        },
                        {
                            type: 'bar',
                            selectedMode: 'single',
                            xAxisIndex: 5,
                            yAxisIndex: 5,
                            tooltip: { 
                                show: true,
                                trigger: 'item',
                            },
                            emphasis: {
                                focus: 'series',
                                barBorderColor: 'red',
                                barBorderWidth: 2,
                            },
                            itemStyle: {
                                color: function(obj){
									return obj.value < 0 ? '#ee6666' : '#31B5FF'
								}
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 15,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize: mini ? '1rem' : '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        }
                    ]
                }
            },
            async getData(){
                try {
                    var self = this;
                    var data = await axios.get(App.url+'pr/monitoring/monitoring')
                    var res = data.data.data[0]
                    var res2 = data.data.data[1]
					self.rawData = data.data.data
                    self.generateOption(data.data.data)
                    self.data = {
                        0: [{
                            name: 'Total',
                            value: parseInt(res.rfq_new) + parseInt(res.rfq_clarification) + parseInt(res.rfq_quotation) + parseInt(res.rfq_ready_for_pr),
                            itemStyle: {
                                color: '#58E276'
                            }
                        }, {
                            name: 'New',
                            value: res.rfq_new,
                            itemStyle: {
                                color: '#B6F3DA'
                            }
                        }, {
                            name: 'Clarification',
                            value: res.rfq_clarification,
                            itemStyle: {
                                color: '#FFAD61'
                            }
                        }, {
                            name: 'Quotation',
                            value: res.rfq_quotation,
                            itemStyle: {
                                color: '#31B5FF'
                            }
                        }, {
                            name: 'Ready for PR',
                            value: res.rfq_ready_for_pr,
                            itemStyle: {
                                color: '#18723E'
                            }
                        }],
                        1: [{
                            name: 'Total',
                            value: parseInt(res.pr_ready_for_pr) + parseInt(res.pr_in_process) + parseInt(res.pr_ready_for_po),
                            itemStyle: {
                                color: '#2770BD'
                            }
                        }, {
                            name: 'Ready for PR',
                            value: res.pr_ready_for_pr,
                            itemStyle: {
                                color: '#FFAD61'
                            }
                        }, {
                            name: 'In Process',
                            value: res.pr_in_process,
                            itemStyle: {
                                color: '#31B5FF'
                            }
                        }, {
                            name: 'Ready for PO',
                            value: res.pr_ready_for_po,
                            itemStyle: {
                                color: '#18723E'
                            }
                        }],
                        2: [{
                            name: 'Total',
                            value: parseInt(res.po_ready_for_po) + parseInt(res.po_in_process) + parseInt(res.po_ready_for_approval),
                            itemStyle: {
                                color: '#8D6AC7'
                            }
                        }, {
                            name: 'Ready for PO',
                            value: res.po_ready_for_po,
                            itemStyle: {
                                color: '#FFAD61'
                            }
                        }, {
                            name: 'In Process',
                            value: res.po_in_process,
                            itemStyle: {
                                color: '#31B5FF'
                            }
                        }, {
                            name: 'Ready for Approval',
                            value: res.po_ready_for_approval,
                            itemStyle: {
                                color: '#18723E'
                            }
                        }],
						3: [{
                            name: 'Total',
                            value: (parseInt(res.rfq_new) + parseInt(res.rfq_clarification) + parseInt(res.rfq_quotation) + parseInt(res.rfq_ready_for_pr))-(parseInt(res2.rfq_new) + parseInt(res2.rfq_clarification) + parseInt(res2.rfq_quotation) + parseInt(res2.rfq_ready_for_pr)),
                        }, {
                            name: 'New',
                            value: res.rfq_new-res2.rfq_new,
                        }, {
                            name: 'Clarification',
                            value: res.rfq_clarification-res2.rfq_clarification,
                        }, {
                            name: 'Quotation',
                            value: res.rfq_quotation-res2.rfq_quotation,
                        }, {
                            name: 'Ready for PR',
                            value: res.rfq_ready_for_pr-res2.rfq_ready_for_pr,
                        }],
                        4: [{
                            name: 'Total',
                            value: (parseInt(res.pr_ready_for_pr) + parseInt(res.pr_in_process) + parseInt(res.pr_ready_for_po))-(parseInt(res2.pr_ready_for_pr) + parseInt(res2.pr_in_process) + parseInt(res2.pr_ready_for_po)),
                        }, {
                            name: 'Ready for PR',
                            value: res.pr_ready_for_pr-res2.pr_ready_for_pr,
                        }, {
                            name: 'In Process',
                            value: res.pr_in_process-res2.pr_in_process,
                        }, {
                            name: 'Ready for PO',
                            value: res.pr_ready_for_po-res2.pr_ready_for_po,
                        }],
                        5: [{
                            name: 'Total',
                            value: (parseInt(res.po_ready_for_po) + parseInt(res.po_in_process) + parseInt(res.po_ready_for_approval))-(parseInt(res2.po_ready_for_po) + parseInt(res2.po_in_process) + parseInt(res2.po_ready_for_approval)),
                        }, {
                            name: 'PO New',
                            value: res.po_ready_for_po-res2.po_ready_for_po,
                        }, {
                            name: 'In Process',
                            value: res.po_in_process-res2.po_in_process,
                        }, {
                            name: 'Ready for Approval',
                            value: res.po_ready_for_approval-res2.po_ready_for_approval,
                        }]
                    }
                } catch (error) {
					console.log(error)
                    App.errorMsg()
                }
				self.chart.hideLoading()
            }
        },
        mounted: function() {
            var self = this
			self.dialog_login = false
			self.getData()
			setNonPermanentInterval(function(){
				self.getData()
			}, 60000*15, 'dashboard-procurement', document.getElementById('dashboard-procurement'))
			
			setNonPermanentInterval(function(){
				var mins = parseInt(new Date().getMinutes())
				//if(mins/10 == 0){
					if(!['sm', 'md', 'xs'].includes(App.breakpoint))
						self.showAnimation = true
				//}
				//else{
				//	var elementToRotate = document.getElementById("dashboard-procurement"); 
				//	elementToRotate.classList.toggle("rotated90");
				//}
			}, 60000*5, 'dashboard-procurement', document.getElementById('dashboard-procurement'))
        }
    }
</script>