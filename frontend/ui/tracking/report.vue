<template>
    <div>
        <div style="padding-top: 10px;">
            <v-select class="m-2" :items="groups" hide-details dense label="Group" v-model="group" clearable style="min-width: 150px; max-width: 300px; display: inline-block;"></v-select>
            <v-select class="m-2" :items="areas" hide-details dense label="Area" v-model="areagroup" clearable style="min-width: 150px; max-width: 300px; display: inline-block;"></v-select>
            <v-checkbox label="Picture" v-model="picture" hide-details dense style="max-width: 100px; display: inline-block;"></v-checkbox>
			<v-btn class="m-2" small color="primary" outlined @click="getData">View Report</v-btn>
            <v-btn class="m-2" small color="primary" outlined @click="excel">Export Excel</v-btn>
            <v-spacer></v-spacer>
        </div>
        <v-row dense v-if="ready">
            <v-col cols="12" v-if="!noInput">
                <v-card outlined @click="detailPart=true" style="height: 100%;">
                    <!-- <v-list-item three-line>
                        <v-list-item-content>
                            <div class="text-overline mb-2">
                                Part not registered {{partnotregistered.length}}
                            </div>
                            <div class="text--primary">
                                <b>Dismantle: </b> <template v-if="info['dismantle_-']">{{numberFormat(info['dismantle_-'].c/info['dismantle_-'].part_count*100) || 0}}%</template><template v-else>0%</template><br />
                                <b>Packing: </b> <template v-if="info['storage_part_-']">{{numberFormat(info['storage_part_-'].c/info['storage_part_-'].part_count*100) || 0}}%</template><template v-else>0%</template><br />
                                <b>Storage: </b> <template v-if="info['packing_part_-']">{{numberFormat(info['packing_part_-'].c/info['packing_part_-'].part_count*100) || 0}}%</template><template v-else>0%</template>
                            </div>
                        </v-list-item-content>
                    </v-list-item> -->
                    <div class="text-h4 text--primary" style="display: flex; align-items: center; justify-content: center; height: 100%">
                        <template v-if="partnotregistered==false">Loading...</template>
                        <template v-else>Part not registered {{partnotregistered.length}}</template>
                    </div>
                </v-card>
            </v-col>
            <v-col xs="12" md="4" v-if="!noInput">
                <v-card outlined>
                    <v-list-item three-line>
                        <v-list-item-content>
                            <div class="text-overline mb-2">
                                Group: Mechanic
                            </div>
                            <div class="text--primary" style="display: flex">
                                <div style="flex: 1">
									<template v-if="!noInput">
                                    	<b>Dismantle (Data Input): </b><br /> <template v-if="info['dismantle_mech']">100%</template><template v-else>0%</template><br />
									</template>
									<b>Dismantle (By Real Area): </b><br /> <template v-if="info['dismantle_mech']">100%</template><template v-else>0%</template>
                                    <fieldset v-if="area['mech']['dismantle']">
                                        <legend>Area</legend>
                                        <template v-if="area['mech']['dismantle']" v-for="(item, index) in area['mech']['dismantle']">
                                            <div v-if="index!='DUCTING'" @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                                <div style="flex: 1">
									<template v-if="!noInput">
                                    	<b>Packing (Data Input): </b><br /> <template v-if="info['packing_part_mech']">100%</template><template v-else>0%</template><br />
									</template>
									<b>Packing (By Real Area): </b><br /> <template v-if="info['packing_part_mech']">100%</template><template v-else>0%</template>
									<fieldset v-if="area['mech']['packing_part']">
                                        <legend>Area</legend>
                                        <template v-if="area['mech']['packing_part']" v-for="(item, index) in area['mech']['packing_part']">
                                            <div v-if="index!='DUCTING'" @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                                <div style="flex: 1">
									<template v-if="!noInput">
                                    	<b>Storage (Data Input): </b><br /> <template v-if="info['storage_part_mech']">100%</template><template v-else>0%</template><br />
									</template>
									<b>Storage (By Real Area): </b><br /> <template v-if="info['storage_part_mech']">100%</template><template v-else>0%</template>
									<fieldset v-if="area['mech']['storage_part']">
                                        <legend>Area</legend>
                                        <template v-if="area['mech']['storage_part']" v-for="(item, index) in area['mech']['storage_part']">
                                            <div v-if="index!='DUCTING'" @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                            </div>
                        </v-list-item-content>
                    </v-list-item>
                </v-card>
            </v-col>
            <v-col xs="12" md="4" v-if="!noInput">
                <v-card outlined>
                    <v-list-item three-line>
                        <v-list-item-content>
                            <div class="text-overline mb-2">
                                Group: Electronic
                            </div>
                            <div class="text--primary" style="display: flex">
                                <div style="flex: 1">
									<template v-if="!noInput">
                                    	<b>Dismantle (Data Input): </b><br /> <template v-if="info['dismantle_elect']">{{numberFormat(info['dismantle_elect'].c/info['dismantle_elect'].part_count*100) || 0}}%</template><template v-else>0%</template><br />
                                    </template>
									<b>Dismantle (By Real Area): </b><br /> <template v-if="info['dismantle_elect']">{{numberFormat(info['dismantle_elect'].real_area) || 0}}%</template><template v-else>0%</template>
                                    <fieldset v-if="area['elect']['dismantle']">
                                        <legend>Area</legend>
                                        <template v-if="area['elect']['dismantle']" v-for="(item, index) in area['elect']['dismantle']">
                                            <div @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                                <div style="flex: 1">
									<template v-if="!noInput">
                                    	<b>Packing (Data Input): </b><br /> <template v-if="info['packing_part_elect']">{{numberFormat(info['packing_part_elect'].c/info['packing_part_elect'].part_count*100) || 0}}%</template><template v-else>0%</template><br />
									</template>
									<b>Packing (By Real Area): </b><br /> <template v-if="info['packing_part_elect']">{{numberFormat(info['packing_part_elect'].real_area) || 0}}%</template><template v-else>0%</template>
									<fieldset v-if="area['elect']['packing_part']">
                                        <legend>Area</legend>
                                        <template v-if="area['elect']['packing_part']" v-for="(item, index) in area['elect']['packing_part']">
                                            <div @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                                <div style="flex: 1">
									<template v-if="!noInput">
                                    	<b>Storage (Data Input): </b><br /> <template v-if="info['storage_part_elect']">{{numberFormat(info['storage_part_elect'].c/info['storage_part_elect'].part_count*100) || 0}}%</template><template v-else>0%</template><br />
									</template>
									<b>Storage (By Real Area): </b><br /> <template v-if="info['storage_part_elect']">{{numberFormat(info['storage_part_elect'].real_area) || 0}}%</template><template v-else>0%</template>
									<fieldset v-if="area['elect']['storage_part']">
                                        <legend>Area</legend>
                                        <template v-if="area['elect']['storage_part']" v-for="(item, index) in area['elect']['storage_part']">
                                            <div @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                                <!-- <template v-for="(item, index) in area['elect']"><br />
                                <b>{{index}}: </b>{{item}}
								</template> -->
                            </div>
                        </v-list-item-content>
                    </v-list-item>
                </v-card>
            </v-col>
            <v-col xs="12" md="4" v-if="!noInput">
                <v-card outlined>
                    <v-list-item three-line>
                        <v-list-item-content>
                            <div class="text-overline mb-2">
                                Group: Electrical
                            </div>
                            <div class="text--primary" style="display: flex">
                                <div style="flex: 1">
									<template v-if="!noInput">
                                    	<b>Dismantle (Data Input): </b><br /> <template v-if="info['dismantle_electrical']">{{numberFormat(info['dismantle_electrical'].c/info['dismantle_electrical'].part_count*100) || 0}}%</template><template v-else>0%</template><br />
                                    </template>
									<b>Dismantle (By Real Area): </b><br /> <template v-if="info['dismantle_electrical']">{{numberFormat(info['dismantle_electrical'].real_area) || 0}}%</template><template v-else>0%</template>
                                    <fieldset v-if="area['electrical']['dismantle']">
                                        <legend>Area</legend>
                                        <template v-if="area['electrical']['dismantle']" v-for="(item, index) in area['electrical']['dismantle']">
                                            <div @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                                <div style="flex: 1">
									<template v-if="!noInput">
                                   		<b>Packing (Data Input): </b><br /> <template v-if="info['packing_part_electrical']">{{numberFormat(info['packing_part_electrical'].c/info['packing_part_electrical'].part_count*100) || 0}}%</template><template v-else>0%</template><br />
									</template>
									<b>Packing (By Real Area): </b><br /> <template v-if="info['packing_part_electrical']">{{numberFormat(info['packing_part_electrical'].real_area) || 0}}%</template><template v-else>0%</template>
									<fieldset v-if="area['electrical']['packing_part']">
                                        <legend>Area</legend>
                                        <template v-if="area['electrical']['packing_part']" v-for="(item, index) in area['electrical']['packing_part']">
                                            <div @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                                <div style="flex: 1">
									<template v-if="!noInput">
                                    	<b>Storage (Data Input): </b><br /> <template v-if="info['storage_part_electrical']">{{numberFormat(info['storage_part_electrical'].c/info['storage_part_electrical'].part_count*100) || 0}}%</template><template v-else>0%</template><br />
									</template>
									<b>Storage (By Real Area): </b><br /> <template v-if="info['storage_part_electrical']">{{numberFormat(info['storage_part_electrical'].real_area) || 0}}%</template><template v-else>0%</template>
									<fieldset v-if="area['electrical']['storage_part']">
                                        <legend>Area</legend>
                                        <template v-if="area['electrical']['storage_part']" v-for="(item, index) in area['electrical']['storage_part']">
                                            <div @click="openNoInput(index, item)"><b>{{index}}: </b><template v-if="!noInput">{{numberFormat(item.pct) || 0}}% </template>({{numberFormat(item.d) || 0}}%)</div>
                                        </template>
                                    </fieldset>
                                </div>
                                <!-- <template v-for="(item, index) in area['electrical']"><br />
                                <b>{{index}}: </b>{{item}}
								</template> -->
                            </div>
                        </v-list-item-content>
                    </v-list-item>
                </v-card>
            </v-col>
        </v-row>
        <table class="tpl maintable" v-if="ready">
            <thead>
				<tr>
					<th colspan="2" style="text-align: left;background-color: #fff !important;"><v-text-field
						label="Search"
						v-model="search" hide-details dense
						></v-text-field>
					</th>
					<th colspan="15" style="text-align: left;background-color: #fff !important;">
						<v-btn small color="primary" outlined @click="onSearchData">search</v-btn>
					</th>
				</tr>
                <tr>
                    <th rowspan="2">No</th>
                    <th rowspan="2">Part Number</th>
                    <th rowspan="2" style="width: 300px;">Part Name</th>
                    <th rowspan="2">Group</th>
                    <th rowspan="2">Area</th>
                    <th rowspan="2" style="width: 300px;">Type</th>
                    <th rowspan="2">Qty</th>
                    <th rowspan="2">UOM</th>
                    <th rowspan="2">Application</th>
                    <th colspan="3">Date</th>
                    <th rowspan="2">Box no</th>
                    <th rowspan="2">Storage Location</th>
                    <th colspan="3" v-if="picture">Picture</th>
                </tr>
                <tr>
                    <th>Dismantling</th>
                    <th>Packing</th>
                    <th>Storage</th>
                    <th v-if="picture">Part Dismantling</th>
                    <!-- <th v-if="picture">Part Packing</th> -->
                    <th v-if="picture">Box Packing</th>
                    <th v-if="picture">Box Storage</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in searchData" :key="index">
                    <td>{{index+1}}</td>
                    <td>{{item.barcode}}</td>
                    <td style="width: 300px;"><div style="white-space: pre-wrap;">{{item.part_name}}</div></td>
                    <td>{{item.group_name}}</td>
                    <td>{{item.area}}</td>
                    <td style="width: 300px;"><div style="white-space: pre-wrap;">{{item.type}}</div></td>
                    <td>{{Number(item.qty).format(2,3,',','.')}}</td>
                    <td>{{item.uom}}</td>
                    <td>{{item.application}}</td>
                    <td>{{item.dismantling_date}}</td>
                    <td>{{item.packing_date}}</td>
                    <td>{{item.storage_date}}</td>
                    <td>{{item.box_no}}</td>
                    <td :href="'https://www.google.com/maps/search/?api=1&query='+item.lt+','+item.ln">
                        <a v-if="item.ln!=null" :href="'https://www.google.com/maps/search/?api=1&query='+item.lt+','+item.ln">Latitude: {{item.lt}}<br />
                            Longitude: {{item.ln}}
                        </a>
                    </td>
					<template v-if="picture">
						<td :value-img="item.pic_dismantling" img="true" style="text-align: center; vertical-align: middle;">
							<v-avatar class="profile" color="grey" size="50" tile @click="openImage(item.id_dismantling)">
								<img max-height="50" max-width="50" alt="imgId" :src="'https://main.buanamultiteknik.com/api/barcode/item/image?id='+item.id_dismantling"></img>
							</v-avatar>
						</td>
						<!-- <td :value-img="item.pic_packing" img="true">
							<v-avatar class="profile" color="grey" size="150" tile @click="openImage(item)">
								<v-img max-height="150" max-width="150" alt="imgId" :src="'https://main.buanamultiteknik.com/api/barcode/item/image?id='+item.id_packing"></v-img>
							</v-avatar>
						</td> -->
						<td :value-img="item.pic_box" img="true" style="text-align: center; vertical-align: middle;">
							<v-avatar class="profile" color="grey" size="50" tile @click="openImage(item.id_box)">
								<v-img max-height="50" max-width="50" alt="imgId" :src="'https://main.buanamultiteknik.com/api/barcode/item/image?id='+item.id_box"></v-img>
							</v-avatar>
						</td>
						<td :value-img="item.pic_storage" img="true" style="text-align: center; vertical-align: middle;">
							<v-avatar class="profile" color="grey" size="50" tile @click="openImage(item.id_storage)">
								<v-img max-height="50" max-width="50" alt="imgId" :src="'https://main.buanamultiteknik.com/api/barcode/item/image?id='+item.id_storage"></v-img>
							</v-avatar>
						</td>
					</template>
                </tr>
            </tbody>
        </table>
        <v-action-dialog :actions="false" v-model="detailPart" title="Part not Registered">
        	<v-btn class="m-2" small color="primary" outlined @click="excelUnregisteredHideImage=!excelUnregisteredHideImage">Toggle Image</v-btn>
        	<v-btn class="m-2" small color="primary" outlined @click="excelUnregistered">Export Excel</v-btn>
            <table class="tpl unregistered">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <!-- <th>Part Name</th> -->
                        <th v-if="excelUnregisteredHideImage">Image</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in partnotregistered" :key="index">
                        <td>{{item.barcode}}</td>
                        <!-- <td>{{item.part_name}}</td> -->
                        <td img="true" style="text-align: center; vertical-align: middle;" v-if="excelUnregisteredHideImage">
                            <v-avatar class="profile" color="grey" size="50" tile>
                                <img max-height="50" max-width="50" alt="imgId" :src="'https://main.buanamultiteknik.com/api/barcode/item/imagebarcode?barcode='+item.barcode"></img>
                            </v-avatar>
                        </td>
                    </tr>
                </tbody>
            </table>
        </v-action-dialog>
		<v-action-dialog v-model="dialogNoInput" fullscreen :actions="false" :title="noinputprops.process+' without input'">
			<noinput ref="noinput" :p="noinputprops"></noinput>
		</v-action-dialog>
        <v-overlay :absolute="true" class="idxoverlay" :value="overlay" style="z-index: 1000;">
            Loading data...
        </v-overlay>
    </div>
</template>

<style>
    fieldset {
        border: 1px solid black;
        padding: 4px 8px;
    }

    legend {
        background-color: #eeeeee;
        /* border: 1px solid black; */
        padding: 4px 8px;
    }

    table.tpl {
        table-layout: fixed;
		
        margin-top: 1em;
        background: #fff;
    }

    table.tpl th {
        white-space: nowrap;
    }

    table.tpl th,
    table.tpl td {
        border: 1px solid #333;
        padding: 4px;
        white-space: nowrap;
        vertical-align: top;
    }

    table.tpl th,
    table.tpl tr.second td {
        background: #FFFF00;
    }

    /* table.tpl td:first-child,
    table.tpl tr.first td {
        font-weight: bold;
    } */

    table.tpl tr:first-child th,
    table.tpl tr.first td {
        background: #FFC000;
    }
</style>

<script>
    module.exports = {
        name: 'report',
		props: {
			noInput: {
				type: Boolean,
				default: false
			}
		},
        components: {
            'noinput': 'url:ui/tracking/noinput.vue'
        },
        data: function() {
            return {
                data: [],
                area: {},
                excelUnregisteredHideImage: true,
                partnotregistered: false,
                info: {},
                groups: [{text:"Mechanic", value:"mech"}, {text:"Electronic", value:"elect"}, {text:"Electrical", value:"electrical"}],
                group: null,
				areas: ["A", "B", "C", "D", "E", "F"],
                areagroup: null,
                detailPart: false,
                dialogNoInput: false,
                picture: false,
                ready: false,
                overlay: false,
                search: '',
                searchNow: '',
				noinputprops: {},
				searchData: []
            }
        },
        methods: {
			onSearchData: function(){
				var self = this
				var tmp = self.data.filter(function(val){
					return val.barcode.toLowerCase().match(self.search.toLowerCase())!=null || (val.box_no || '').toLowerCase().match(self.search.toLowerCase())!=null
				})

				self.searchData = tmp
			},
			openNoInput: function(val, val2){
				var self = this
				self.noinputprops = val2
				self.dialogNoInput = true
				/*self.$refs.noinput */
			},
			openImage: function(item){
				window.open('https://main.buanamultiteknik.com/api/barcode/item/imagefull?id='+item)
			},
            getData: async function() {
                var self = this,
                    params = {}
                self.overlay = true
                try {
					self.data = []
					self.search = ''
					self.searchData = []
                    if (self.group !== null) {
                        params = {
                            group: self.group
                        }
                    }
                    if (self.areagroup !== null) {
                        params.area = self.areagroup
                    }

					params.i = randomId()

                    var response = await fetch('https://main.buanamultiteknik.com/api/barcode/item/report?' + new URLSearchParams(params));
                    /* await axios.get(App.url + 'barcode/item/report', {
                                            params: params
                                        }) */

                    if (response.ok) { // if HTTP-status is 200-299
                        // get the response body (the method explained below)
                        var res = await response.json();
                    } else {
                        alert("HTTP-Error: " + response.status);
                    }
/* 
                    var resInfo = await axios.get(App.url + 'barcode/item/infopercent')

                    var tmp = {},
                        area = {}
                    resInfo.data.data.map(function(val) {
                        tmp[val.process + '_' + (val.group_name || '-')] = val
                    })

                    self.info = tmp */

                    /* var partnotregistered = await axios.get(App.url + 'barcode/item/partnotregistered')

                    self.partnotregistered = partnotregistered.data.data */
					//console.log(res)
                    if (!res.status)
                        App.errorMsg()
                    else{
                        self.data = res.data
						var tmp = self.data.filter(function(val){
							return val.barcode.match(self.search)!=null
						})

						self.searchData = tmp
					}
                    self.overlay = false
                } catch (err) {
					console.log(err)
					App.errorMsg()
                    self.overlay = false
                }
            },
            excelUnregistered: async function(){
            	try {
					this.overlay = true
	                await tableToExcel(document.querySelector('.unregistered'), 'Part Unregistered', {
	                    style: true
	                })
					this.overlay = false
				} catch (err) {
					console.log(err)
					this.overlay = false
				}
            },
            excel: async function() {
				try {
					this.overlay = true
	                await tableToExcel(document.querySelector('.maintable'), 'Report Tracking', {
	                    style: true
	                })
					this.overlay = false
				} catch (err) {
					console.log(err)
					this.overlay = false
				}
            }
        },
        mounted: async function() {
            var self = this
			try {
				self.overlay = true
	            var resInfo = await axios.get(App.url + 'barcode/item/infopercent')
	
	            var tmp = {}, tmp2 = [], processData = {}
	            resInfo.data.data.map(function(val) {
	                tmp[val.process + '_' + (val.group_name || '-')] = val
					if(processData[val.process] == undefined)
						processData[val.process] = 0
					processData[val.process]+=Number(val.part_count)
	            })
	            resInfo.data.data.map(function(val) {
					val.totalItem = processData[val.process]
				})
	
	            var resArea = await axios.get(App.url + 'barcode/item/infoarea')
	
	            var area = {},
	                total = {},
	                tmp2 = [];
	            ['mech', 'elect', 'electrical'].map(function(group) {
	                area[group] = {};
	                ['dismantle', 'packing_part', 'storage'].map(function(process) {
	                    area[group][process] = {
	                        'A': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'B': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'C': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'D': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'E': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'F': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        }
	                    }
	                })
	            })
	            resArea.data.data.map(function(val) {
					/*if(val.group_name=='mech' && val.process == "storage_part" && val.area == "A")
						val.part_count = 300*/
	                var areaTxt = (val.area || '-').replace(/area/ig, '').trim().toUpperCase()
	                if (area[val.group_name] == undefined)
	                    area[val.group_name] = {}
	                if (area[val.group_name][val.process] == undefined)
	                    area[val.group_name][val.process] = {
	                        'A': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'B': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'C': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'D': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'E': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        },
	                        'F': {
	                            c: 0,
	                            pct: 0,
	                            total: 0,
								totalInfo: {
									part_count: 0
								}
	                        }
	                    }
	                if (total[val.group_name + '_' + val.process] == undefined)
	                    total[val.group_name + '_' + val.process] = 0
	                total[val.group_name + '_' + val.process] += Number(val.c)
					
	                tmp2.push(val)
	                area[val.group_name][val.process][areaTxt] = val
	            })
	
	            tmp2.map(function(val) {
					val.d = (val.c/val.part_count)*100
					if(val.group_name=='mech')
						val.d = 100
					if(val.d > 100) 
						val.d = 100
					val.pct = 16.66*val.d/100
					if(tmp[val.process + '_' + (val.group_name || '-')].g == undefined){
						tmp[val.process + '_' + (val.group_name || '-')].g = 0
						tmp[val.process + '_' + (val.group_name || '-')].h = 0
					}
					
					tmp[val.process + '_' + (val.group_name || '-')].g += Number(val.part_count)
					tmp[val.process + '_' + (val.group_name || '-')].h += Number(val.c)
					if(tmp[val.process + '_' + (val.group_name || '-')].real_area==undefined)
						tmp[val.process + '_' + (val.group_name || '-')].real_area = 0
					tmp[val.process + '_' + (val.group_name || '-')].real_area+=Number(val.pct||0)
					//val.c = 16.66*(val.part_count/val.c*100)/100
	                val.total = total[val.group_name + '_' + val.process]
					val.totalInfo = tmp[val.process + '_' + (val.group_name || '-')]
					if(tmp[val.process + '_' + (val.group_name || '-')].totalArea == undefined){
						tmp[val.process + '_' + (val.group_name || '-')].totalArea = total[val.group_name + '_' + val.process]
						tmp[val.process + '_' + (val.group_name || '-')].cArea = 0
					}
					tmp[val.process + '_' + (val.group_name || '-')].cArea += Number(val.c)
	            })
	
	            self.info = tmp
	            self.area = area
	            self.ready = true
	
	            var partnotregistered = await axios.get(App.url + 'barcode/item/partnotregistered')
	
	            self.partnotregistered = partnotregistered.data.data
				self.overlay = false
			} catch (err) {
				self.overlay = false
			}
        }
    }
</script>