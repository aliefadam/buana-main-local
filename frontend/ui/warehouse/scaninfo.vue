<template>
    <v-container style="height: 100%; flex: 1; display: flex; flex-direction: column;" v-if="ready">
		<div :style="'position: absolute; top: 0; left: 0; width: 100%; height: 100%;background-color: #fff; display: flex;'+($refs.scanner ? ($refs.scanner.barcode.isScanning ? ' z-index: 1;' : ' z-index: 0;') : ' z-index: 0;')">
			<div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
			<div style="display: flex;;background-color: #fff; flex-direction: column;">
				<qrcode-scanner @click.stop="function(){}" @error-scan="onErrorScan" ref="scanner" v-bind:qrbox="250" v-bind:fps="10" style="width: 500px; max-width: 100%; z-index: 10000;" @decodedCode="onSuccess" @decoded-code="onSuccess">
				</qrcode-scanner>
				<div style="flex: 1" @click="$refs.scanner.stop()"></div>
			</div>
			<div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
		</div>
		<v-card height="100%" style="display: flex; flex-direction: column; max-height: 100%;">
			<v-card-text class="v-action-dialog-card" style="flex: 1">
				<v-autoform ref="scanForm" v-model="headers" :valid.sync="valid" hide-details="auto" :filled="true"></v-autoform>
			</v-card-text>
			<v-divider></v-divider>
			<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn small color="primary" outlined @click="saveItem">Get Info</v-btn>
			</v-card-actions>
		</v-card>
        <v-action-dialog :actions="false" title="Warehoue Info" v-model="dialogInfo">
            <table class="default-table table-theme1 tablereport" style="border: 1 !important;">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Rack</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in items" :key="index">
                        <td>
                            {{item.item}}
                        </td>
                        <td>
                            {{item.rack}}
                        </td>
                        <td>
                            {{item.qty}}
                        </td>
                    </tr>
                </tbody>
            </table>
        </v-action-dialog>
    </v-container>
</template>

<style>
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
				ready: false,
				scan: true,
				valid: false,
				dialogInfo: false,
				active: false,
                items: [],
				headers: [{
					"text": "Part Scan",
					"value": "item",
					"type": "varchar",
					"readonly": false,
					"required": false,
					"focus": function(){
						App.page().active = 'item'
						App.page().startScan()
					}
				}, {
					"text": "Rack Scan",
					"value": "rack",
					"type": "varchar",
					"readonly": false,
					"required": false,
					"focus": function(){
						App.page().active = 'rack'
						App.page().startScan()
					}
				}, {
					"text": "Qty",
					"value": "qty",
					"type": "float",
					"readonly": false,
					"required": false,
                    "visible": false
				}]
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
			startScan: function(){
				var self = this
				self.$refs.scanner.start()
			},
			onErrorScan: function(e){
				var self = this
				//self.$refs.scanner.stop()
				//console.log(e)
				//self.scan = false
			},
            onSuccess: async function(data) {
                var self = this
                try {
                    var latlong = {
                        lon: '-',
                        lat: '-',
                    }
                    self.headersObj[self.active].data = data.decodedText
					//self.scan = false
					self.headers = App.updateObject(self.headers)
					self.$refs.scanner.stop()
				}
				catch(e){
					App.errorMsg(e)
					//self.scan = false
					self.$refs.scanner.stop()
				}
			},
			saveItem: async function() {
                var self = this;
                var params = {
                        filter: {
                            
                        },
                        filterType: {

                        },
                        filterCondition: {

                        }
                    }
                if(self.headersObj.item.data){
                    params.filter.item_id = self.headersObj.item.data
                }
                if(self.headersObj.rack.data){
                    params.filter.rack_id = self.headersObj.rack.data
                }
                var res = await axios.get(App.url + "warehouse/item", {
                    params: params
                });
                if (!res.data.status) {
                    App.errorMsg();
                } else {
                    self.items = res.data.data
                    App.successMsg();
                    self.dialogInfo = true
                }
            },
        },
        mounted: function() {
			var self = this
            loadMultipleLibrary('https://unpkg.com/html5-qrcode').then(function(val) {
                Vue.component('qrcode-scanner', {
                    props: {
                        qrbox: Number,
                        fps: Number
                    },
                    data: function() {
                        return {
                            barcode: false,
                            cameraId: false
                        }
                    },
                    template: `<div ref="reader"></div>`,
                    methods: {
                        start: function() {
                            var self = this
                            self.barcode.start(
                                    self.cameraId, {
                                        fps: 10, // Optional, frame per seconds for qr code scanning
                                        qrbox: {
                                            width: 250,
                                            height: 250
                                        } // Optional, if you want bounded box UI
                                    },
                                    (decodedText, decodedResult) => {
                                        self.$emit('decoded-code', {
                                            decodedText,
                                            decodedResult
                                        });
                                    },
                                    (errorMessage) => {
                                        self.$emit('error-scan', errorMessage);
                                    })
                                .catch((err) => {
                                    self.$emit('error-start', err);
                                });
                        },
                        stop: function() {
                            var self = this
                            self.barcode.stop()
                        }
                    },
                    mounted: function() {
                        var self = this
                        Html5Qrcode.getCameras().then(devices => {
                            /**
                             * devices would be an array of objects of type:
                             * { id: "id", label: "label" }
                             */
                            if (devices && devices.length) {
                                var cam = devices.filter(val => {
                                    return val.label.match('back') != null
                                });
                                if (cam.length == 0)
                                    cam = devices
                                var cameraId = cam[0].id
                                var id = randomId()
                                var el = self.$refs.reader
                                el.setAttribute('id', id)
                                const html5QrCode = new Html5Qrcode(id);
                                self.barcode = html5QrCode
                                self.cameraId = cameraId
                            }
                        }).catch(err => {
                            self.$emit('error-initialize', err);
                            // handle err
                        });

                    }
                });
                self.ready = true
            }).catch(function(err) {
				console.log(err, 'a')
            })
        }
    }
</script>