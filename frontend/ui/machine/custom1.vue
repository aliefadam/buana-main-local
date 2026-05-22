<template>
    <v-container v-resize="onResize" id="reg1" v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <div style="flex: 0; display: flex; justify-content: center;" class="tb">
            <v-checkbox label="Realtime" v-model="realtime" style="max-width: 100;" dense hide-details></v-checkbox>
            <v-checkbox label="Grouped" v-model="grouped" style="max-width: 100;" dense hide-details></v-checkbox>
			<v-btn small color="primary" outlined @click="prev+=24"><v-icon left small>mdi-chevron-left-circle</v-icon>Prev Day</v-btn>
			<v-btn small color="primary" outlined @click="prev+=factor"><v-icon left small>mdi-chevron-left-circle</v-icon>Prev 2 Hour</v-btn>
			<v-btn small color="primary" outlined @click="if(prev>factor) prev-=factor">Next 2 Hour<v-icon right small>mdi-chevron-right-circle</v-icon></v-btn>
			<v-btn small color="primary" outlined  @click="nextDay">Next Day<v-icon right small>mdi-chevron-right-circle</v-icon></v-btn>
			<div>Data between {{btwDate.replace(',', '-')}}</div>
			<v-spacer></v-spacer>
        </div>
        <div style="flex: 1; display: flex;">
            <!-- <div style="width: 35px; display: flex; flex-direction: column; justify-content: center; cursor: pointer;" @click="prev+=factor">
				<v-icon>mdi-chevron-left-circle</v-icon>
			</div> -->
			<div id="chart" key="1" style="width: 100%;height:100%; background-color: #fff; flex: 1"></div>
            <!-- <div style="width: 35px; display: flex; flex-direction: column; justify-content: center; cursor: pointer;" @click="if(prev>factor) prev-=factor">
				<v-icon>mdi-chevron-right-circle</v-icon>
			</div> -->
        </div>
    </v-container>
</template>

<style scoped>
	.tb > *{
		margin: 4px;
	}
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
				factor: 2,
                isInViewport: false,
                realtime: true,
                grouped: true,
                isInDom: false,
                chart: false,
				prev: 2,
                dataZoom: [{
                        type: 'inside',
                        start: 0,
                        end: 100,
                        xAxisIndex: [0, 1, 2]
                    },
                    {
                        start: 0,
                        end: 100,
                        xAxisIndex: [0, 1, 2]
                    }
                ],
                res0: {
					cat: [],
					temp: [],
					speed: [],
					trimmer: []
				},
                res1: {
					cat: [],
					temp: [],
					speed: [],
					trimmer: []
				},
                res2: {
					cat: [],
					temp: [],
					speed: [],
					trimmer: []
				},
                dzI: false,
				lastId: [0,0,0]
            }
        },
		computed: {
			btwDate: function(){
				var self = this
				return (self.prev == self.factor ? new Date().add('minute', -60*self.prev).formatDate('YYYY-MM-DD HH:mm:ss') : new Date().add('minute', -60*self.prev).formatDate('YYYY-MM-DD HH:00:00'))+','+(self.prev == self.factor ? new Date().add('minute', -60*self.prev+(60*self.factor)).formatDate('YYYY-MM-DD HH:mm:ss') : new Date().add('minute', -60*self.prev+(60*self.factor)).formatDate('YYYY-MM-DD HH:00:00'))
			}
		},
        methods: {
			nextDay: function(){
				var self = this
				if(self.prev>24+self.factor) 
					self.prev-=24
				else
					self.prev = Number(self.factor)
				if(self.prev==self.factor)
					self.realtime = true
			},
            onResize: function() {
                if (this.chart)
                    this.chart.resize({
                        silent: true
                    })
            },
            getData: async function(replace) {
                var self = this
				replace = true
                try {
                    var res0 = false,
                        res1 = false,
                        res2 = false,
                        proms = [],
						params = {
							filter: {
								created_date: (self.prev == self.factor ? new Date().add('minute', -60*self.prev).formatDate('YYYY-MM-DD HH:mm:ss') : new Date().add('minute', -60*self.prev).formatDate('YYYY-MM-DD HH:00:00'))+','+(self.prev == self.factor ? new Date().add('minute', -60*self.prev+(60*self.factor)).formatDate('YYYY-MM-DD HH:mm:ss') : new Date().add('minute', -60*self.prev+(60*self.factor)).formatDate('YYYY-MM-DD HH:00:00'))
							},
							filterType: {
								created_date: 'btw'
							},
							sortBy: ['created_date'],
							sortDesc: [true],
							limit: -1
						}

                    proms.push(new Promise(async (resolve, reject) => {
                        var res = await axios.get(App.url + 'machine/gbreg01', {
                            params: params
                        })
                        var cat = [],
                            temp = [],
                            speed = [],
                            trimmer = []
                        res.data.data.reverse().map(function(val) {
							if(val.id>self.lastId[0] || replace == true){
								cat.push(val.created_date)
								temp.push(val.garniture_temp)
								speed.push(val.machine_speed)
								trimmer.push(val.trimmer_position)
								self.lastId[0] = val.id
							}
                        })

                        res0 = {
                            cat,
                            temp,
                            speed,
                            trimmer
                        }
                        resolve(true)
                    }))

                    proms.push(new Promise(async (resolve, reject) => {
                        var res = await axios.get(App.url + 'machine/gbreg02', {
                            params: params
                        })
                        var cat = [],
                            temp = [],
                            speed = [],
                            trimmer = []
                        res.data.data.reverse().map(function(val) {
							if(val.id>self.lastId[1] || replace == true){
								cat.push(val.created_date)
								temp.push(val.garniture_temp)
								speed.push(val.machine_speed)
								trimmer.push(val.trimmer_position)
								self.lastId[1] = val.id
							}
                        })

                        res1 = {
                            cat,
                            temp,
                            speed,
                            trimmer
                        }
                        resolve(true)
                    }))

                    proms.push(new Promise(async (resolve, reject) => {
                        var res = await axios.get(App.url + 'machine/gbreg03', {
                            params: params
                        })
                        var cat = [],
                            temp = [],
                            speed = [],
                            trimmer = []
                        res.data.data.reverse().map(function(val) {
							if(val.id>self.lastId[2] || replace == true){
								cat.push(val.created_date)
								temp.push(val.garniture_temp)
								speed.push(val.machine_speed)
								trimmer.push(val.trimmer_position)
								self.lastId[2] = val.id
							}
                        })

                        res2 = {
                            cat,
                            temp,
                            speed,
                            trimmer
                        }
                        resolve(true)
                    }))

                    await Promise.all(proms)

					if(replace){
						self.res0.cat = res0.cat
						self.res0.temp = res0.temp
						self.res0.speed = res0.speed
						self.res0.trimmer = res0.trimmer

						self.res1.cat = res1.cat
						self.res1.temp = res1.temp
						self.res1.speed = res1.speed
						self.res1.trimmer = res1.trimmer

						self.res2.cat = res2.cat
						self.res2.temp = res2.temp
						self.res2.speed = res2.speed
						self.res2.trimmer = res2.trimmer
					}
					else{
						self.res0.cat = [].concat(self.res0.cat, res0.cat).slice(-500)
						self.res0.temp = [].concat(self.res0.temp, res0.temp).slice(-500)
						self.res0.speed = [].concat(self.res0.speed, res0.speed).slice(-500)
						self.res0.trimmer = [].concat(self.res0.trimmer, res0.trimmer).slice(-500)

						self.res1.cat = [].concat(self.res1.cat, res1.cat).slice(-500)
						self.res1.temp = [].concat(self.res1.temp, res1.temp).slice(-500)
						self.res1.speed = [].concat(self.res1.speed, res1.speed).slice(-500)
						self.res1.trimmer = [].concat(self.res1.trimmer, res1.trimmer).slice(-500)

						self.res2.cat = [].concat(self.res2.cat, res2.cat).slice(-500)
						self.res2.temp = [].concat(self.res2.temp, res2.temp).slice(-500)
						self.res2.speed = [].concat(self.res2.speed, res2.speed).slice(-500)
						self.res2.trimmer = [].concat(self.res2.trimmer, res2.trimmer).slice(-500)
					}

                    var series = [{
                        name: 'Reg01 Garniture Temp',
                        data: self.res0.temp,
                        yAxisIndex: 3,
                        type: 'line',
                        /* lineStyle: {
                            color: '#5470c6'
                        }, */
						symbol: 'none'
                    }, {
                        name: 'Reg01 Machine Speed',
                        data: self.res0.speed,
                        type: 'line',
                        /* lineStyle: {
                            color: '#91cc75'
                        }, */
						symbol: 'none'
                    }, {
                        name: 'Reg01 Trimmer Position',
                        data: self.res0.trimmer,
                        type: 'line',
                        /* lineStyle: {
                            color: '#fac858'
                        }, */
						symbol: 'none'
                    }, {
                        xAxisIndex: self.grouped ? 1 : 0,
                        yAxisIndex: self.grouped ? 4 : 3,
                        name: 'Reg02 Garniture Temp',
                        data: self.res1.temp,
                        type: 'line',
                        /* lineStyle: {
                            color: '#5470c6'
                        }, */
						symbol: 'none'
                    }, {
                        xAxisIndex: self.grouped ? 1 : 0,
                        yAxisIndex: self.grouped ? 1 : 0,
                        name: 'Reg02 Machine Speed',
                        data: self.res1.speed,
                        type: 'line',
                        /* lineStyle: {
                            color: '#91cc75'
                        }, */
						symbol: 'none'
                    }, {
                        xAxisIndex: self.grouped ? 1 : 0,
                        yAxisIndex: self.grouped ? 1 : 0,
                        name: 'Reg02 Trimmer Position',
                        data: self.res1.trimmer,
                        type: 'line',
                        /* lineStyle: {
                            color: '#fac858'
                        }, */
						symbol: 'none'
                    }, {
                        xAxisIndex: self.grouped ? 2 : 0,
                        yAxisIndex: self.grouped ? 5 : 3,
                        name: 'Reg03 Garniture Temp',
                        data: self.res2.temp,
                        type: 'line',
                        /* lineStyle: {
                            color: '#5470c6'
                        }, */
						symbol: 'none'
                    }, {
                        xAxisIndex: self.grouped ? 2 : 0,
                        yAxisIndex: self.grouped ? 2 : 0,
                        name: 'Reg03 Machine Speed',
                        data: self.res2.speed,
                        type: 'line',
                        lineStyle: {
                            color: '#91cc75'
                        },
						symbol: 'none'
                    }, {
                        xAxisIndex: self.grouped ? 2 : 0,
                        yAxisIndex: self.grouped ? 2 : 0,
                        name: 'Reg03 Trimmer Position',
                        data: self.res2.trimmer,
                        type: 'line',
                        /* lineStyle: {
                            color: '#fac858'
                        }, */
						symbol: 'none'
                    }]

					var color = self.grouped ? ['#5470c6', '#91cc75', '#fac858', '#5470c6', '#91cc75', '#fac858', '#5470c6', '#91cc75', '#fac858'] : ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc']

					var grid = self.grouped ?  [{
                                left: 100,
                                right: 50,
                                height: '20%'
                            },
                            {
                                left: 100,
                                right: 50,
                                top: '37.5%',
                                height: '20%'
                            },
                            {
                                left: 100,
                                right: 50,
                                top: '69%',
                                height: '20%'
                            }
                        ] : [{
							left: 100,
							right: 50
						}]
                    var option = {
                        dataZoom: [{
                                type: 'inside',
                                start: self.dataZoom[0].start,
                                end: self.dataZoom[0].end,
                                xAxisIndex: [0, 1, 2]
                            },
                            {
                                start: self.dataZoom[0].start,
                                end: self.dataZoom[0].end,
                                xAxisIndex: [0, 1, 2],
								zoomLock: true/* ,
								throttle: 1000 */
                            }
                        ],
                        grid: grid,
                        tooltip: {
                            trigger: 'axis',
                        },
                        xAxis: [{
                            type: 'category',
                            data: self.res0.cat
                        }, {
                            type: 'category',
                            gridIndex: self.grouped ? 1 : 0,
                            data: self.res1.cat,
							show: self.grouped ? true : false
                        }, {
                            type: 'category',
                            gridIndex: self.grouped ? 2 : 0,
                            data: self.res2.cat,
							show: self.grouped ? true : false
                        }],
                        legend: {
                            //type: 'scroll'
                        },
                        yAxis: [{
                            type: 'value',
                            max: 10000,
                            name: self.grouped ? "Reg 01" : "",
                            nameLocation: 'middle',
                            nameRotate: 90,
                            nameTextStyle: {
                                padding: [0, 0, 50, 0]
                            },
                        }, {
                            gridIndex: self.grouped ? 1 : 0,
                            type: 'value',
                            max: 10000,
                            name: self.grouped ? "Reg 02" : "",
                            nameLocation: 'middle',
                            nameRotate: 90,
                            nameTextStyle: {
                                padding: [0, 0, 50, 0]
                            }
                        }, {
                            gridIndex: self.grouped ? 2 : 0,
                            type: 'value',
                            max: 10000,
                            name: self.grouped ? "Reg 03" : "",
                            nameLocation: 'middle',
                            nameRotate: 90,
                            nameTextStyle: {
                                padding: [0, 0, 50, 0]
                            }
                        }, {
                            type: 'value',
                            max: 800,
                            position: 'right',
                            name: "Temp",
                        }, {
                            gridIndex: self.grouped ? 1 : 0,
                            type: 'value',
                            max: 800,
                            position: 'right',
                            name: "Temp",
                        }, {
                            gridIndex: self.grouped ? 2 : 0,
                            type: 'value',
                            max: 800,
                            position: 'right',
                            name: "Temp",
                        }],
                        color: color,//['#5470c6', '#91cc75', '#fac858', '#5470c6', '#91cc75', '#fac858', '#5470c6', '#91cc75', '#fac858'],
                        //'#5470c6', '#91cc75', '#fac858', '#ee6666'
                        //'#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc'
                        series: series
                    }

                    if (self.chart != false && !replace) {
                        try {
                            self.chart.setOption({
                                series: series,
								color: color,
								grid: grid,
                            }, {
                                silent: true
                            })
                            self.chart.dispatchAction({
								type: 'dataZoom',
								// optional; index of dataZoom component; useful for are multiple dataZoom components; 0 by default
								dataZoomIndex: 0,
								// percentage of starting position; 0 - 100
								start: self.dataZoom[0].start,
								// percentage of ending position; 0 - 100
								end: self.dataZoom[0].end
							})
                        } catch (err) {
							console.log(err)
                        }
                    } else {
						if(self.chart == false)
							self.chart = echarts.init(document.getElementById('chart'));
						self.chart.on('dataZoom', function(e) {
							if(self.dzI==false)
								clearTimeout(self.dzI)
							self.dzI = setTimeout(function() {
								try {
									if (e.batch) {
										e = e.batch[0]
									}
									self.dataZoom[0].start = e.start
									self.dataZoom[0].end = e.end
									self.dataZoom[1].start = e.start
									self.dataZoom[1].end = e.end
								} catch (err) {
									console.log(err)
								}
								self.dzI = false
							}, 250)
						})
                        self.chart.setOption(option, {
							notMerge: true
						})
                    }
                } catch (err) {
                    console.log(err)
                }
            },
            onVisible: function(isVisible, e) {
                var self = this
                self.isInViewport = !!isVisible
                self.isInDom = !!e.target.isConnected
            },
        },
		watch: {
			prev: async function(val){
				/* var isRealtime = false */
				/* if(this.realtime == true){
					isRealtime = true
					this.realtime = false
				} */
				if(val > 1)
					this.realtime = false
				else
					this.realtime = true
				await this.getData(true)
				/* if(isRealtime==true)
					this.realtime = true */
			},
			grouped: function(){
				this.getData(true)
			}
		},
        mounted: function() {
            var self = this
            loadMultipleLibrary('library/echarts.min.js').then(function(val) {
                self.getData()
                setNonPermanentInterval(function() {
                    if (self.realtime)
                        self.getData()
                }, 10000, 'reg1', document.getElementById('reg1'))
            }).catch(function(err) {

            })
        }
    }
</script>