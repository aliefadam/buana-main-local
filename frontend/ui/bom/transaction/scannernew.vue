<template>
    <v-container style="flex: 1; display: flex; flex-direction: column; min-width: 100%;" v-if="ready">
        <div :style="'position: absolute; top: 0; left: 0; width: 100%; height: 100%;background-color: #fff; display: flex;'+($refs.scanner ? ($refs.scanner.barcode.isScanning ? ' z-index: 1;' : ' z-index: 0;') : ' z-index: 0;')">
            <div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
            <div style="display: flex;background-color: #fff; flex-direction: column;max-width: 100%;">
                <qrcode-scanner @click.stop="function(){}" @error-scan="onErrorScan" ref="scanner" v-bind:qrbox="250" v-bind:fps="10" style="width: 500px; max-width: 100%; z-index: 10000;" @decodedCode="onSuccess" @decoded-code="onSuccess">
                </qrcode-scanner>
                <div style="flex: 1" @click="$refs.scanner.stop()"></div>
            </div>
            <div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
        </div>
        <v-card style="display: flex; flex-direction: column; min-height: 100%; padding-bottom: 50px;">
            <v-card-text class="v-action-dialog-card" style="flex: 0 50px">
                <v-autoform ref="scanForm" v-model="headers" :valid.sync="valid" hide-details="auto" :filled="true"></v-autoform>
            </v-card-text>
            <v-divider></v-divider>
			<v-avatar class="profile" color="grey" size="150" tile v-if="srcImg!=false">
				<v-img max-height="150" max-width="150" alt="imgId" id="imgId" :src="srcImg"></v-img>
			</v-avatar>
            <div v-if="info!=''" v-html="info" style="flex: 1; padding: 16px;">
            </div>
            <div v-if="info!='' && !isBomReceive">
                <table class="">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Qty</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in items" :key="index">
                            <td>{{item.location}}</td>
                            <td>{{Math.abs(item.qty_actual)}}</td>
                            <td v-if="Number(item.qty_actual)<0">OUT</td>
                            <td v-else>IN</td>
                        </tr>
                    </tbody>
                </table>
            </div>
			<part-list v-if="isBomReceive" ref="partlist"></part-list>
            <v-card-actions style="position: fixed;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);">
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="function(){startScan()}">Scan</v-btn>
				<v-btn color="primary" outlined @click="startCamera">Photo</v-btn>
				<v-btn color="primary" outlined @click="srcImg=false">Clear</v-btn>
                <v-btn color="primary" v-if="!isBomReceive" @click="function(){receive()}" :disabled="(headers[0].data || '').trim()==''">Receive Item</v-btn>
                <v-btn color="primary" v-if="!isBomReceive" @click="function(){outgoing()}" :disabled="(headers[0].data || '').trim()==''">Outgoing Item</v-btn>
                <v-spacer></v-spacer>
            </v-card-actions>
        </v-card>
		<v-action-dialog fullscreen v-model="dialogPhoto" @save="takePhoto">
			<div style="display: flex; justify-content: center;">
				<video id="videoId" autoplay="true"></video>
			</div>
		</v-action-dialog>
    </v-container>
</template>

<style scoped>
    table {
        border-spacing: 1;
        border-collapse: collapse;
        background: white;
        border-radius: 6px;
        overflow: hidden;
        max-width: 800px;
        width: 100%;
        margin: 0 auto;
        position: relative;
    }

    table * {
        position: relative;
    }

    table td,
    table th {
        padding-left: 8px;
    }

    table thead tr {
        height: 60px;
        background: #FFED86;
        font-size: 16px;
    }

    table tbody tr {
        height: 48px;
        border-bottom: 1px solid #E3F1D5;
    }

    table tbody tr:last-child {
        border: 0;
    }

    table td,
    table th {
        text-align: left;
    }

    table td.l,
    table th.l {
        text-align: right;
    }

    table td.c,
    table th.c {
        text-align: center;
    }

    table td.r,
    table th.r {
        text-align: center;
    }

    
</style>

<script>
    module.exports = {
        name: '',
		components: {
			'part-list': 'url:ui/bom/transaction/scanpartlistnew.vue',
		},
        data: function() {
            return {
                ready: false,
                scan: true,
                valid: false,
                url: 'transaction/bomheader',
                active: 'item',
                headers: [{
                    "text": "Part Scan",
                    "value": "item",
                    "type": "varchar",
                    "readonly": false,
                    "required": true,
                    "focus": function() {
                        App.page().active = 'item'
                        App.page().startScan()
                    }
                }],
                info: '',
                items: [],
				isBomReceive: false,
				cameraPhoto: false,
				dialogPhoto: false,
				barcode: '',
				srcImg: false
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
		watch: {
			dialogPhoto: function(val){
				var self = this
				if(!val){
					self.stopCamera()
				}
			}
		},
        methods: {
			startCamera: function() {
                var self = this
				try {
					self.$refs.scanner.stop()
				} catch (err) {
								
				}
                self.dialogPhoto = true
                self.$nextTick(() => {
                    var FACING_MODES = JslibHtml5CameraPhoto.FACING_MODES
                    let videoElement = document.getElementById('videoId');
                    self.cameraPhoto = new JslibHtml5CameraPhoto.default(videoElement);
                    self.cameraPhoto.startCamera(FACING_MODES.ENVIRONMENT)
                        .then(() => {
                            console.log('Camera started !');
                        })
                        .catch((error) => {
                            console.error('Camera not started!', error);
                        });
                })
            },
            stopCamera: function() {
                var self = this
                if (self.cameraPhoto)
                    self.cameraPhoto.stopCamera()
                    .then(() => {
                        self.dialogPhoto = false
                    })
                    .catch((error) => {
                        console.log('No camera to stop!:', error);
                    });
            },
            takePhoto: function() {
                var self = this
                const config = {
                    sizeFactor: 0.5,
                    imageType: 'jpg',
                    imageCompression: .95,
                    isImageMirror: false
                };
                let dataUri = self.cameraPhoto.getDataUri(config);
                self.srcImg = dataUri;
                self.stopCamera()
            },
            getLocation: function() {
                return new Promise((resolve, reject) => {
                    var lat = false;
                    var lon = false;
                    if (navigator.geolocation) {
                        // get the current position
                        try {
                            navigator.geolocation.getCurrentPosition(
                                // if this was successful, get the latitude and longitude
                                function(position) {
                                    lat = position.coords.latitude;
                                    lon = position.coords.longitude;
                                    resolve({
                                        lat,
                                        lon
                                    })
                                },
                                // if there was an error
                                function(error) {
                                    reject(error)
                                }, {
                                    timeout: 6000,
                                    enableHighAccuracy: true,
                                    maximumAge: Infinity
                                });
                        } catch (err) {
                            reject(err.message)
                        }
                    } else {
                        reject("No location available!")
                    }
                });
            },
            async receive() {
                try {
                    var self = this
                    var c = prompt("Input QTY", 0)
                    var l = prompt("Input Location", '')
                    if (c === null) {} else {
                        var n = Number(c)
                        if (isNaN(n)) {
                            App.errorMessage('Please input valid number!')
                        } else {
                            var latlong = await self.getLocation()
                            var res = await axios.post(App.url + 'transaction/receivescan', {
                                latlong: latlong,
                                code: self.headers[0].data,
                                qty: Math.abs(n),
                                location: l

                            })
                            if (!res.data.status) throw res.data
                            self.headers[0].data = ''
                            App.successMsg()
                        }
                    }
                } catch (err) {
                    App.errorMsg()
                }
            },
            async outgoing() {
                try {
                    var self = this
                    var c = prompt("Input QTY", 0)
                    var l = prompt("Input Location", '')
                    if (c === null) {} else {
                        var n = Number(c)
                        if (isNaN(n)) {
                            App.errorMessage('Please input valid number!')
                        } else {
                            var latlong = await self.getLocation()
                            var res = await axios.post(App.url + 'transaction/receivescan', {
                                latlong: latlong,
                                code: self.headers[0].data,
                                qty: -Math.abs(n),
                                location: l

                            })
                            if (!res.data.status) throw res.data
                            self.headers[0].data = ''
                            App.successMsg()
                        }
                    }
                } catch (err) {
                    App.errorMsg()
                }
            },
            startScan: function() {
                var self = this
                self.$refs.scanner.start()
            },
            onErrorScan: function(e) {
                var self = this
                //self.$refs.scanner.stop()
                //console.log(e)
                //self.scan = false
            },
            onSuccess: async function(data) {
                var self = this
                try {
                    self.headersObj[self.active].data = data.decodedText
                    //self.scan = false
                    self.headers = App.updateObject(self.headers)
                    self.$refs.scanner.stop()
                    self.getInfo(data.decodedText)
                } catch (e) {
                    App.errorMsg(e)
                    //self.scan = false
                    self.$refs.scanner.stop()
                }
            },
            async getInfo(barcode) {
                var self = this
                try {
					self.barcode = barcode
					self.isBomReceive = barcode.match(/.0$/)!=null
                    var res = await axios.get(App.url + self.url + '/info2', {
                        params: {
                            barcode: barcode
                        }
                    })
                    var t = res.data.data[0]
                    self.items = res.data.data
					if(self.$refs.partlist){
						self.$refs.partlist.getData(self.barcode)
					}
					var qty = res.data.data.reduce((partialSum, a) => partialSum + Number(a.qty_actual), 0);
					if(self.isBomReceive)
						qty = '-'
                    self.info = `
					<table cellpadding="0" cellspacing="0" style="border: 0">
						<tr>
							<td>Name</td>
							<td>: ${t.machine_name||'-'}_${t.item_name}</td>
						</tr>
						<tr>
							<td>Notes</td>
							<td>: ${t.note||'-'}</td>
						</tr>
					</table>
					`
                } catch (error) {
					console.log(error)
                }
            }
        },
        mounted: function() {
            var self = this
            loadMultipleLibrary('https://unpkg.com/html5-qrcode;./library/html5-camera.js').then(function(val) {
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