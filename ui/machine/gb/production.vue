<template>
    <v-container id="prod" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%; background-color: #fff !important;">
        <v-row style="flex: 0; 
        font-size: 1em; font-weight: bold; background-color: #EDEDED;">
            <v-col cols="12" style="display: flex;">
                <div>Last Update:&nbsp;</div>
                <div> {{date.formatDate('DD MMM YYYY')}}<br />
                    {{date.formatDate('HH:mm:ss')}}
                </div>
                <div style="font-size: 1.5em; flex: 1; text-align: center;">GBB SECONDARY</div>
                <div style="color: rgba(0,0,0,0)">Last Update:&nbsp;</div>
                <div style="color: rgba(0,0,0,0)"> {{date.formatDate('DD MMM YYYY')}}<br />
                    {{date.formatDate('HH:mm:ss')}}
                </div>
            </v-col>
        </v-row>
        <v-row style="flex: 0">
            <v-col cols="12" md="6" v-for="(item, index) in dataByPos" :key="index" style="display: flex; justify-content: center;">
                <div style="margin: 1em; border-radius: 4px; display: inline-block; border: 3px solid rgba(0,0,0,0.9);" v-if="item!=null">
                    <table class="tpl" style="width: 35em; height: 1px;">
                        <tr>
                            <td style="border-top: 0; border-left: 0; width: 5em;">
                                <div style="height: 100%; display:inline-table">
									<div style="display: flex; flex-direction: column; height: 4.5em;">
										<div style="flex: 1; display: flex; align-items: center; justify-content: center;">{{item.line}}</div>
										<div style="font-size: 0.5em; flex: 0">Current speed: {{numberFormat(item.current_speed)}}</div>
									</div>
                                </div>
                            </td>
                            <td style="border-top: 0; border-right: 0; text-align: left; padding: 4px;" :class="cls(item)">
                                <div>Target: {{Math.floor(item.total_output)}} cig</div>
                                <div>Good Prod: {{Math.floor(item.good < 0 ? 0 : item.good)}} cig</div>
                                <div>Waste: {{Math.floor(item.waste)}} cig - {{numberFormat(Number(item.production == 0 ? 0: item.waste) / (Number(item.production == 0 ? item.waste: item.production)) * 100, 2)}}%</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-bottom: 0; border-left: 0;" :class="cls(item)">{{numberFormat(uptime(item), 2)}}%</td>
                            <td style="border-bottom: 0; border-right: 0;" :class="cls(item)">{{item.machine_type}}
                                <div style="font-size: 0.4em; ">Data datetime: {{item.created_date}} <span>{{isDc(item.quality)}}</span></div>
                            </td>
                        </tr>
                    </table>
                </div>
                <div v-else style="margin: 1em;">
                    <table class="tpl" style="width: 30em;">

                    </table>
                </div>
            </v-col>
        </v-row>
    </v-container>
</template>

<style scoped>
    .tpl td {
        border: 3px solid rgba(0, 0, 0, 0.9);
        text-align: center;
        font-weight: bold;
        font-size: 2em;
    }

    .tpl .bg-red {
        background-color: #EE6666;
    }

    .tpl .bg-green {
        background-color: #91CC75;
    }

    .tpl .bg-blue {
        background-color: #5470C6;
    }

	@-moz-document url-prefix() {
	.tpl {
			height: 100%;
		}
	}
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
                data: [],
                date: new Date(),
                position: [1, null, 3, 2]
            }
        },
        computed: {
            dataByPos: function() {
                var self = this
                var data = {},
                    tmp = {}
                self.data.map(function(val) {
                    tmp[val.machine_id] = val
                })
                self.position.map(function(val, i) {
                    data[i] = i == null ? null : tmp[val]
                })
                return data
            }
        },
        methods: {
			isDc: function(quality){
				return quality == 0 ? ', MC Disconnected' : ''
			},
            getData: async function() {
                var self = this
                try {
                    var res = await axios.get(App.url + 'machine/gb/data/uptime', {
                        params: {}
                    })
                    if (!res.data.status) throw res.data
                    self.date = new Date()
					res.data.data.map(function(val){
						var date = new Date(val.start_date+' '+val.start), created_date = new Date(val.created_date)
						if(created_date<date){
							val.production = 0
							val.waste = 0
							val.speed = 0
							val.good = 0
						}
						if(val.quality == 0){
							val.good = 0
							val.waste = 0
							val.production = 0
						}
					})
                    self.data = res.data.data
                } catch (err) {

                }
            },
            uptime: function(item) {
                var uptime = Number(item.production) / Number(item.total_output) * 100
                if (parseFloat(uptime) <= 0 || isNaN(uptime))
                    uptime = 0
                return uptime
            },
            cls: function(item) {
                var uptime = Number(item.production) / Number(item.total_output) * 100
                if (parseFloat(uptime) <= 0 || isNaN(uptime))
                    cls = ''
                else if (parseFloat(uptime) < 32.5)
                    cls = 'bg-red'
                else if (parseFloat(uptime) < 52)
                    cls = 'bg-red'
                else if (parseFloat(uptime) < 65)
                    cls = 'bg-red'
                else if (parseFloat(uptime) < 78)
                    cls = 'bg-green'
                else
                    cls = 'bg-blue'
                return cls
            }
        },
        mounted: function() {
            var self = this
            self.getData()
            setNonPermanentInterval(function() {
                self.getData()
            }, 15000, 'prod', document.getElementById('prod'))
        }
    }
</script>