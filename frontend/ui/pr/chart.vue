<template>
    <v-container @resize="onResize" id="dashboard-procurement" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%; transition: height .5s linear;">
        <v-chart @ready="onChartReady" :default-options="option" v-model="data" style="height: 100%; width: 100%;" ref="chart"></v-chart>
		<screen v-model="showAnimation"></screen>
    </v-container>
</template>

<style scoped>
</style>

<script>
    
    module.exports = {
        name: '',
        components: {
            'screen': 'url:ui/base/screen.vue',
        },
        data: function() {
            return {
				showAnimation: false,
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
        methods: {
			onResize: function(){
				if(this.chart){
					this.generateOption(this.rawData)
					this.data = App.updateObject(this.data)
					this.chart.resize()
				}
			},
			onChartReady(chart){
				chart.showLoading()
				this.chart = chart
			},
            generateOption(data){
                var self = this;
				var mini = ['sm', 'md', 'xs'].includes(App.breakpoint)
                self.option = {
                    title: {
                        text: 'Dashboard Procurement',
                        subtext: new Date().formatDate("DD.MM.YYYY HH:mm:ss"),
                        textStyle: {
                            fontFamily: 'Calibri',
                            fontSize: '2rem'
                        },
                        subtextStyle: {
                            fontFamily: 'Calibri',
                        	fontSize: '1.5rem',
                            padding: [0, 0, 30, 0],
                        },
                        textAlign: 'center',
                        left: '50%'
                    },
                    legend: {},
                    tooltip: {},
                    xAxis: [{
                            name: 'Difference from date '+new Date(data[1].log_date).formatDate("DD.MM.YYYY"),
                            nameLocation: 'center',
                            nameTextStyle: {
                                fontWeight: 'bolder',
                                padding: [mini ? 60 : 20, 0, 0, 100],
                                fontSize: '1.25rem',
                                fontFamily: 'Calibri'
                            },
                            type: 'category',
                            gridIndex: 0,
                            axisLabel: {
                                show: true,
                                interval: 0,
                                fontWeight: 'bolder',
                                fontSize: '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: 'left',
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
                                fontSize: '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								padding: [0, 0, 20, 0],
								align: 'left',
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
                                fontSize: '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: 'left',
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
                                fontSize: '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: 'left',
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
                                fontSize: '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: 'left',
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
                                fontSize: '1rem',
                                fontFamily: 'Calibri',
								rotate: mini ? -60 : 0,
								align: 'left',
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
                                fontSize: '1.25rem',
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
                                fontSize: '1.25rem',
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
                                fontSize: '1.25rem',
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
                        //min:0, max:

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
                        //show: false,
                        gridIndex: 4, minInterval: 1,
                        min:function(val){
							return Math.floor(val.min-((val.max+val.min)/100*10))
						}, max: function(val){
							return Math.ceil(val.max+((val.max+val.min)/100*10))
						}
                    }, {
                        //show: false,
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
						bottom: mini ? '50%' : '30%'
                    }, {
                        left: window.innerWidth / 5 * 1.9 + 40,
                        top: 100,
                        width: window.innerWidth / 5 * 1.5 - 40,
						bottom: mini ? '50%' : '30%'
                    }, {
                        left: window.innerWidth / 5 * 3.4 + 40,
                        top: 100,
                        width: window.innerWidth / 5 * 1.5 - 40,
						bottom: mini ? '50%' : '30%'
                    }, {
                        left: 40,
                        width: window.innerWidth / 5 * 1.9 - 40,
						top: mini ? '70%' : '70%',
						bottom: mini ? 80 : 40
                    }, {
                        left: window.innerWidth / 5 * 1.9 + 40,
                        width: window.innerWidth / 5 * 1.5 - 40,
						top: mini ? '70%' : '70%',
						bottom: mini ? 80 : 40
                    }, {
                        left: window.innerWidth / 5 * 3.4 + 40,
                        width: window.innerWidth / 5 * 1.5 - 40,
						top: mini ? '70%' : '70%',
						bottom: mini ? 80 : 40
                    }],
                    series: [
                        {
                            type: 'bar',
                            xAxisIndex: 0,
                            yAxisIndex: 0,
                            emphasis: {
                                focus: 'series'
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 30,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize: '1.25rem',
                                rich: {
                                    name: {}
                                }
                            }
                        },
                        {
                            type: 'bar',
                            xAxisIndex: 1,
                            yAxisIndex: 1,
                            emphasis: {
                                focus: 'series'
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 30,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize: '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        },
                        {
                            type: 'bar',
                            xAxisIndex: 2,
                            yAxisIndex: 2,
                            emphasis: {
                                focus: 'series'
                            },
                            label: {
                                show: true,
                                position: 'top',
                                distance: 30,
                                align: 'center',
                                verticalAlign: 'top',
                                rotate: 0,
                                formatter: '{c}',
                                fontSize: '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        },
                        {
                            type: 'bar',
                            xAxisIndex: 3,
                            yAxisIndex: 3,
                            emphasis: {
                                focus: 'series'
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
                                fontSize: '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        },
                        {
                            type: 'bar',
                            xAxisIndex: 4,
                            yAxisIndex: 4,
                            emphasis: {
                                focus: 'series'
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
                                fontSize: '1.25rem',
                                rich: {
                                    name: {}
                                }
                            },
                        },
                        {
                            type: 'bar',
                            xAxisIndex: 5,
                            yAxisIndex: 5,
                            emphasis: {
                                focus: 'series'
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
                                fontSize: '1.25rem',
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
					//console.log(self.option.xAxis[0])
					self.rawData = data.data.data
                    self.generateOption(data.data.data)
                    self.data = {
                        0: [{
                            name: 'Total',
                            value: parseInt(res.rfq_new) + parseInt(res.rfq_clarification) + parseInt(res.rfq_quotation) + parseInt(res.rfq_ready_for_pr),
                            itemStyle: {
                                color: '#2770BD'
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
                            name: 'PO New',
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
            self.getData()
            setNonPermanentInterval(function(){
                self.getData()
            }, 60000*15, 'dashboard-procurement', document.getElementById('dashboard-procurement'))
            setNonPermanentInterval(function(){
                self.showAnimation = true
            }, 60000*10, 'dashboard-procurement', document.getElementById('dashboard-procurement'))
        }
    }
</script>