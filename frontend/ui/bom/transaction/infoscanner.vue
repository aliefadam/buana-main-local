<template>
    <v-container v-observe-visibility="onVisible" style="flex: 1; display: flex; flex-direction: column; min-width: 100%;" v-if="ready" id="scannerkoli">
        <div :style="'position: absolute; align-self:center; width: 100%; height: 100%; background-color: #fff; display: flex y; width:100; flex-wrap: wrap;'+($refs.scanner ? ($refs.scanner.barcode.isScanning ? ' z-index: 1;' : ' z-index: 0;') : ' z-index: 0;')">
            <div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
            <div style="display: flex;background-color: #fff; flex-direction: column;max-width: 100%;">
                <qrcode-scanner @click.stop="function(){}" @error-scan="onErrorScan" ref="scanner" v-bind:qrbox="250" v-bind:fps="10" style="width: 500px; max-width: 100%; z-index: 10000;" @decodedCode="onSuccess" @decoded-code="onSuccess">
                </qrcode-scanner>
                <div style="flex: 1" @click="$refs.scanner.stop()"></div>
            </div>
            <div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
        </div>
        <v-card>
            <v-card-text class="v-action-dialog-card" style="align-items:center">
                <template v-if="barcode">
					<b>Barcode: {{barcode}}</b>
					<table v-if="table" class="table tpl">
						<thead>
							<tr>
								<td v-for="(col, colIndex) in column" :key="colIndex+col">
									{{col}}
								</td>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(item, index) in table" :key="item[itemKey]">
								<td v-for="(col, colIndex) in column" :key="colIndex">
									{{item[colIndex]}}
								</td>
							</tr>
						</tbody>
					</table>
                </template>
                <template v-else>
                    Scan QR code to retrieve information
                </template>
            </v-card-text>
            <v-card-actions style="position: fixed; background-color: #fff; display: flex y; width:100%; flex-wrap: wrap; flex-gap: 5px; justify-content: center;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);">
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="function(){startScan()}">Start Scan</v-btn>
            </v-card-actions>
        </v-card>
    </v-container>
</template>

<style>
    @page {
        margin: 10mm 5mm;
    }

    * {
        font-size: 11px;
    }

    table.table {
        border: 0px;
        border-collapse: collapse;
        font-size: 11px;
        color: black;
        width: 100%;
    }

    table.table tr th {
        border-bottom: 1px solid #C5C5C5;
        margin-bottom: 4px;
        background-color: #F0F8FF;
    }

    table.table tr:last-child th {
        height: 4px;
    }

    table.table th>div {
        padding: 0 4px;
        border-radius: 3px;
        text-align: center;
        white-space: wrap;
    }

    table.table td>div {
        padding: 0 4px;
        border-radius: 3px;
        white-space: wrap;
    }

    table.table td {
        padding: 1.5px;
    }

    table.table td,
    table.table th {
        padding: 2px 4px;
        border: 1px solid black !important;
        min-width: 20px;
    }

    table.table td.table-title {
        font-weight: bold;
        text-align: center;
    }

    tr.page-break {
        page-break-before: always;
    }

    .tdvtop>tbody>tr>td {
        vertical-align: top;
    }

    .elements-per-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
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
        white-space: wrap;
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

    table.tpl th{
		text-transform: capitalize;
	}
</style>

<script>
    module.exports = {
        name: '',
        components: {},
        props: {
            data: {
                type: Object,
                default: {}
            }
        },
        data: function() {
            return {
                ready: false,
                column: [],
                scan: true,
                valid: false,
				itemKey: '',
                url: 'transaction/bomheader',
                info: '',
                items: [],
                isBomReceive: false,
                cameraPhoto: false,
                dialogPhoto: false,
                photoTarget: false,
                barcode: '',
                subkoli_id: '',
                table: false,
                srcImg: false,
                srcImg2: false,
                srcImg3: false,
                location: '0',
                selectedKoli: {},
                selectedSubkoli: {},
                itemInfo: {}
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
            showBomReceive: function(val) {
                if (val) {
                    this.getBomReceiveData()
                }
            },
            dialogPhoto: function(val) {
                var self = this
                if (!val) {
                    self.stopCamera()
                }
            },
            dialogSubkoliPart(val) {
                if (val == false)
                    this.showSubkoliPart = false
            },
            dialogSubkoli(val) {
                if (val == false)
                    this.showSubkoli = false
            },
            async showSubkoliPart(val) {
                var self = this
                if (val) {
                    try {
                        var res = await axios.get(App.url + '/transaction/koli/subkolipartlist', {
                            params: {
                                subkoli_id: self.headersObj.subkoli.data.split('.')[2]
                            }
                        })
                        var t = res.data.data[0]
                        self.items = res.data.data

                        self.subkolipartlist = 'Show'
                        self.dialogSubkoliPart = true

                    } catch (err) {
                        self.loading = false
                        App.errorMsg()
                    }
                }

            },
            async showSubkoli(val) {
                var self = this
                if (val) {
                    try {
                        var res = await axios.get(App.url + '/transaction/koli/subkolilist', {
                            params: {
                                koli_id: self.headersObj.koli.data.split('.')[2]
                            }
                        })
                        var t = res.data.data[0]
                        self.items = res.data.data

                        self.subkolilist = 'Show'
                        self.dialogSubkoli = true

                    } catch (err) {
                        self.loading = false
                        App.errorMsg()
                    }
                }
            },

        },
        methods: {
            async getItemInfo(item_id) {
                var self = this
                try {
                    var res = await axios.get(App.url + 'bom/item', {
                        params: {
                            filter: {
                                id: item_id
                            }
                        }
                    })
                    self.itemInfo = res.data.data[0]
                } catch (error) {

                }
            },
            onVisible: function(isVisible, e) {
                var self = this
                if (!!isVisible) {
                    try {
                        
                    } catch (err) {
                        console.log(err)
                    }
                }
            },
            clear: function() {
                this.srcImg = false;
                this.srcImg2 = false;
                this.srcImg3 = false;
                this.items = [];
                this.headersObj.koli.data = ''
                this.headersObj.subkoli.data = ''
                this.headersObj.item.data = ''
                this.headersObj.qty.data = 0
            },
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
                    self.barcode = data.decodedText.toLowerCase()
                    self.getInfo(self.barcode)
                    self.$refs.scanner.stop()
                } catch (e) {
                    App.errorMsg(e)
                    self.$refs.scanner.stop()
                }
            },

            async getInfo(barcode) {
                var self = this
                try {
                    self.barcode = barcode
					var t = barcode
					var url = ''
					if(barcode.match(/^bmt.stock./i)){
						t = barcode.split(/^bmt.stock./i)[1]
						url = 'transaction/stock'
                   		var res = await axios.get(App.url + url, {
							params: {
								filter: {
									item_id: t
								},
								limit: -1
							}
						})
						if(!res.data.status) throw res.data
						self.column = {
							allocation: 'Allocation',
							title: 'title',
							qty: 'qty',
							po_no: 'PO No'
						}
						//['allocation', 'title', 'qty', 'po_no']
						res.data.data.map(val=>{
							val.itemKey = [val.item_id, val.po_no].join('-')
						})
						self.table = res.data.data
						self.itemKey = 'itemKey'
					}
                } catch (error) {
                    console.log(error)
					App.errorMsg()
                }
            }
        },
        mounted: async function() {
            var self = this
            console.log(self)
            try {
                self.location = await self.getLocation()
            } catch (e) {
                self.location = {
                    lat: 0,
                    lon: 0
                }
            }
            setNonPermanentInterval(async () => {
                try {
                    self.location = await self.getLocation()
                } catch (e) {
                    self.location = {
                        lat: 0,
                        lon: 0
                    }
                }
            }, 60000, self.$el.id, self.$el)
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
                            App.page().devices = devices
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