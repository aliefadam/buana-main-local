<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <!-- <div id="reader" width="600px" v-if="ready"></div> -->
        <div v-if="bottomNav == 0" style="flex: 1 100%; display: flex; flex-direction: column;">
            <input-scanner></input-scanner>
        </div>

        <div v-if="bottomNav == 1" style="flex: 1 100%; display: flex; flex-direction: column;">
           <local-data></local-data>
        </div>

        <div v-if="bottomNav == 2" style="flex: 1 100%; display: flex; flex-direction: column;">
			<div style="flex: 1; display: flex; flex-direction: column;">
				<v-tabs style="flex: 0" v-model="tab" mobile-breakpoint="0">
					<v-tab :key="0">Tracking Box</v-tab>
					<v-tab :key="1">Tracking Part</v-tab>
				</v-tabs>
				<v-tabs-items v-model="tab" style="flex: 1; display: flex;" class="tabs" touchless>
					<v-tab-item :key="0" style="height: 100%; display: flex; flex-direction: column;">
						<tracking :items-options="trackingBoxOptions" show-group></tracking>
					</v-tab-item>
					<v-tab-item :key="1" style="height: 100%; display: flex; flex-direction: column;">
						<tracking :items-options="trackingPartOptions" show-box show-group admin-mode></tracking>
					</v-tab-item>
				</v-tabs-items>
			</div>
        </div>
        <div v-if="bottomNav == 3" style="flex: 1 100%; display: flex; flex-direction: color">
          <report-data></report-data>
        </div>
        <div v-if="bottomNav == 4" style="flex: 1 100%; display: flex; flex-direction: color">
          <report-data no-input></report-data>
        </div>
        <div v-if="bottomNav == 5" style="flex: 1 100%; display: flex; flex-direction: color">
          <mpart v-if="allowMpart == true"></mpart>
        </div>
		<!-- <div style="flex: 1 60px;"> &nbsp;</div> -->
        <v-bottom-navigation grow v-model="bottomNav" color="primary" style="position: fixed; bottom: 0">
			<div style="position: fixed; bottom: 54px; left: 4px;" id="version">Ver {{version}}</div>
            <v-btn @click="inputScanner=true">
                <span>Input</span>

                <v-icon>mdi-barcode</v-icon>
            </v-btn>

            <v-btn @click="localData=true">
                <span>Local Data</span>

                <v-icon>mdi-server</v-icon>
            </v-btn>

            <v-btn @click="tracking=false">
                <span>Tracking</span>

                <v-icon>mdi-magnify</v-icon>
            </v-btn>

            <v-btn>
                <span>Report</span>

                <v-icon>mdi-microsoft-excel</v-icon>
            </v-btn>

            <v-btn>
                <span>Report 2</span>

                <v-icon>mdi-microsoft-excel</v-icon>
            </v-btn>

            <v-btn>
                <span>Master</span>

                <v-icon>mdi-package-variant</v-icon>
            </v-btn>
        </v-bottom-navigation>
        <v-overlay :absolute="true" :value="overlay">
            Updating local master part data...
        </v-overlay>
    </v-container>
</template>

<style>
	div.scanner > .container > .container{
		overflow: auto;
	}
	div.scanner > .container{
		padding-bottom: 56px !important;
	}
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
              allowMpart: false,
				tab: null,
				trackingBoxOptions: {
					filter: {
						process: ['packing', 'storage']
					}
				},
				trackingPartOptions: {
					filter: {
						process: "'packing_part', 'dismantle'"
					},
					filterType: {
						process: 'in'
					}
				},
                inputScanner: true,
                overlay: false,
                isTakePhoto: false,
                tracking: false,
                localData: false,
                sync: false,
                showForm: false,
                bottomNav: 0,
                ready: false,
                local: false,
                valid: false,
                scan: false,
                srcImg: false,
                cameraPhoto: false,
                info: '',
                error: '',
                headers: [{
                    "text": "Read Data",
                    "value": "barcode",
                    "type": "varchar",
                    "readonly": true,
                    "required": true,
                }, {
                    "text": "Longitude",
                    "value": "ln",
                    "type": "varchar",
                    "visible": false,
                    "readonly": true,
                    "required": true,
                }, {
                    "text": "Latitude",
                    "value": "lt",
                    "type": "varchar",
                    "readonly": true,
                    "required": true,
                    "visible": false,
                }, {
                    "text": "Process",
                    "value": "process",
                    "type": "list",
                    "data_value": ["Dismantling", "Packing", "Storage"],
                    "required": true,
                }]
            }
        },
        watch: {
          bottomNav: function(val){
            if(val==5){
              var c = prompt('Password')
              if('1723904'==hashCode(c)){
                this.allowMpart = true
              }
              else{
                alert('Wrong Password!')
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
            }
        },
        components: {
            'tracking': 'url:ui/tracking/item.vue',
            'local-data': 'url:ui/tracking/local.vue',
            'input-scanner': 'url:ui/tracking/input.vue',
            'report-data': 'url:ui/tracking/report.vue',
            'mpart': 'url:ui/tracking/mpart.vue'
        },
        methods: {
            getPart: async function(){
				var self = this
				self.overlay = true
				try {
					var res = await axios.get(App.url+'barcode/mpart', {
						params: {
							limit: -1
						}
					})
					if(res.data.data.length > 0){
						self.local.delete({})
						self.local.save(res.data.data)
					}
							
							self.overlay = false	
				} catch (err) {
							
					self.overlay = false	
				}
			}
        },
        mounted: function() {
            var self = this
            self.local = new LDB.Collection('material');
			self.getPart()
        }
    }
</script>