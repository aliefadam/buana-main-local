<template>
    <v-container v-observe-visibility="onVisible" style="flex: 1; display: flex; flex-direction: column; min-width: 100%;" v-if="ready">
        <div :style="'position: absolute; top: 0; left: 0; width: 100%; height: 100%;background-color: #fff; display: flex;'+($refs.scanner ? ($refs.scanner.barcode.isScanning ? ' z-index: 1;' : ' z-index: 0;') : ' z-index: 0;')">
            <div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
            <div style="display: flex;background-color: #fff; flex-direction: column;max-width: 100%;">
                <qrcode-scanner @click.stop="function(){}" @error-scan="onErrorScan" ref="scanner" v-bind:qrbox="250" v-bind:fps="10" style="width: 500px; max-width: 100%; z-index: 10000;" @decodedCode="onSuccess" @decoded-code="onSuccess">
                </qrcode-scanner>
                <div style="flex: 1" @click="$refs.scanner.stop()"></div>
            </div>
            <div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
        </div>
        <v-card style="display: flex; flex-direction: column; min-height: 100%; align-self:center">
            <v-card-title>Scanner Receive Page</v-card-title>
             <v-card-text class="v-action-dialog-card" style="align-items:center">
                <v-autoform ref="scanForm" v-model="headers" :valid.sync="valid" hide-details="auto" :filled="true"></v-autoform>
				<table style="margin-top: 5px;" v-if="items.length > 0">
					<thead>
						<tr>
							<th>Name</th>
							<th>Allocation</th>
							<th>Order Qty</th>
							<th>Complete Qty</th>
						</tr>
					</thead>
					<tr v-for="(item, index) in items" :key="index" @click="selected=index" :class="selected == index ? 'selected' : ''">
						<td>{{item.item_name}}</td>
						<td>{{item.allocation}}</td>
						<td>{{item.order_qty}}</td>
						<td>{{item.complete_qty}}</td>
					</tr>
				</table>
				<v-avatar class="profile" color="grey" size="150" tile v-if="srcImg!=false">
					<v-img max-height="150" max-width="150" alt="imgId" id="imgId" :src="srcImg"></v-img>
				</v-avatar>
            </v-card-text>
			<!-- /*position: fixed;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);*/ -->
            <v-card-actions style="">
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="function(){startScan()}">Scan</v-btn>
                <v-btn color="info" @click="startCamera('srcImg')">Part Photo</v-btn>
                <!-- <v-btn color="primary" v-if="!isBomReceive" @click="function(){receive()}" :disabled="(headers[0].data || '').trim()==''">Receive Item</v-btn> -->
                <!-- <v-btn color="primary" v-if="!isBomReceive" @click="function(){outgoing()}" :disabled="(headers[0].data || '').trim()==''">Outgoing Item</v-btn> -->
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

    .selected{
		background-color: #e5efff;
	}
</style>

<script>
    module.exports = {
        name: 'spbScanner',
		components: {
			'part-list': 'url:ui/bom/transaction/scanpartlist.vue',
		},
        data: function() {
            return {
                ready: false,
                showBomReceive: false,
                bomReceiveId: false,
                bomReceiveItems: [],
                item_id:'',
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
                        /* App.page().active = 'item'
                        App.page().startScan() */
                    },
					"input": function(){
						var self = App.$get('spbScanner')
					},
					blur: function(){
						console.log('asd')
					}
                }, {
                        "text": "QTY",
                        "value": "qty",
                        "type": "varchar",
                        "readonly": false,
                        "required": true
                    
                },{
                        "text": "Allocation",
                        "value": "allocation",
                        "type": "list",
                        "readonly": false,
                        "required": true,
                        "data_value": ["NI", "NIS", "SC"]
                    
                }],
                info: '',
                items: [],
				isBomReceive: false,
				barcode: '',
                srcImg: false,
                devices: [],
                cameraPhoto: false,
                dialogPhoto: false,
                photoTarget: false,
				selected: false
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
            },
        },
        watch:{
             showBomReceive: function(val) {
                if (val) {
                    this.getBomReceiveData()
                }
            },
        },
        methods: {
			startCamera: function(target) {
                var self = this
				self.photoTarget = target
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
                self[self.photoTarget] = dataUri;
                self.stopCamera()
            },
             onVisible: function(isVisible, e) {
                var self = this
                if (!isVisible) {
					
					this.selected = false
					this.srcImg = false
					this.headersObj.qty.data = null
					this.headersObj.item.data = null
					this.headersObj.allocation.data = null
					this.items = []
                    /* try {
                        if (self.data.id != undefined || self.data.id != null) {
                            self.showBomReceive = true
                        }
                    } catch (err) {
                        console.log(err)
                    } */
                }
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
             getBomReceiveData: async function() {
                var self = this
                try {
                    var barcode = self.headersObj.item.data.split('.')
                    var res = await axios.get(App.url + 'transaction/koli/bomreceive', {
                        params: {
                            bom_header_id: barcode[0],
                            item_id: barcode[1]
                        }
                    })
                    self.bomReceiveId = res.data.data[0].value
                    self.bomReceiveItems = res.data.data
                } catch (error) {

                }
            },
            onSuccess: async function(data) {
                var self = this
                try {
                    self.headersObj[self.active].data = data.decodedText
                    //self.scan = false
                    self.headers = App.updateObject(self.headers)
                    self.$refs.scanner.stop()
                    self.getInfo(data.decodedText)
                     if (item_id)
                        self.showBomReceive = true
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
							<td>Machine No</td>
							<td>: ${t.machine_no}</td>
						</tr>
						<tr>
							<td>No Part</td>
							<td>: ${t.item_name}</td>
						</tr>
						<tr>
							<td>Description</td>
							<td>: ${t.description}</td>
						</tr>
						<tr>
							<td>Application</td>
							<td>: ${t.application}</td>
						</tr>
						<tr>
							<td>Material</td>
							<td>: ${t.material}</td>
						</tr>
						<tr>
							<td>Mass</td>
							<td>: ${t.mass}</td>
						</tr>
						<tr>
							<td>QTY</td>
							<td>: ${qty}</td>
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
            loadMultipleLibrary('https://unpkg.com/html5-qrcode;./library/html5-camera.js?v1').then(function(val) {
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
                            self.devices = devices
                            if (devices && devices.length) {
                                var cam = devices.filter(val => {
                                    return val.label.match(/back/i) != null
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
                                //if(cameraId == undefined || cameraId == '')
                                    /*navigator.mediaDevices.enumerateDevices().then(videoInputDevices => {
                                        videoInputDevices.forEach(device =>
                                            self.cameraId = device
                                        );
                                    })*/
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