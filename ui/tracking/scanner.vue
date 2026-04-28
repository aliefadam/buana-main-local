<template>
    <div v-observe-visibility="onVisible" style="height: 100%; flex: 1; display: flex; flex-direction: column;">
        <qrcode-scanner @error-scan="onErrorScan" ref="scanner" v-if="ready" v-bind:qrbox="250" v-bind:fps="10" style="width: 500px; max-width: 100%;" @decodedCode="onSuccess" @decoded-code="onSuccess">
        </qrcode-scanner>
        <div style="padding: 8px;display: flex;width: 100%;">
            <v-spacer></v-spacer>
            <div style="width: 265px;">
                <div class="mb-2">
                    <v-autoform ref="scanForm" v-model="headers" :valid.sync="valid" hide-details="auto" :filled="true" v-if="!scan && isTakePhoto == false"></v-autoform>
                </div>
                <video id="videoId" autoplay="true" v-if="isTakePhoto"></video>
                <label class="v-label theme--light mt-2" v-if="!scan && isTakePhoto == false">{{labelPhoto}}</label>
                </label><br />
                <v-avatar class="profile" color="grey" size="150" tile v-if="!scan && isTakePhoto == false">
                    <v-img max-height="150" max-width="150" alt="imgId" id="imgId" :src="srcImg" v-if="srcImg!=false && isTakePhoto == false"></v-img>
                </v-avatar>
                <!-- <img style="width: 75%;" alt="imgId" id="imgId" :src="srcImg" v-if="srcImg!=false && isTakePhoto == false"> -->
            </div>
            <v-spacer></v-spacer>
        </div>
        <slot>
			<v-spacer></v-spacer>
		</slot>
        <div class="flex" style="flex: 0; padding: 4px; flex-wrap: wrap;
		justify-content: end;">
            <v-spacer></v-spacer>
            <slot name="prepend-menu"></slot>
            <template v-if="isTakePhoto == false">
                <v-btn small class="mr-2 mb-2" color="primary" v-if="ready && !scan" outlined @click="openScan">{{labelScan}}</v-btn>
                <v-btn small class="mr-2 mb-2" color="error" v-else outlined @click="stopScan">Close</v-btn>
            </template>
            <template v-if="scan == false">
                <v-btn small class="mr-2 mb-2" color="primary" outlined @click="startCamera" v-if="isTakePhoto == false">{{labelTakePhoto}}</v-btn>
                <template v-if="isTakePhoto">
                    <v-btn small class="mr-2 mb-2" color="success" outlined @click="takePhoto">
                        <v-icon>mdi-camera</v-icon>
                    </v-btn>
                    <v-btn small class="mr-2 mb-2" color="error" outlined @click="stopCamera">Close</v-btn>
                </template>
            </template>
            <v-btn small class="mr-2 mb-2" color="primary" :disabled="!valid && srcImg===false" outlined @click="save">{{labelSave}}</v-btn>
        </div>
        <v-overlay :absolute="true" :value="overlay">
            Getting location data...
        </v-overlay>
    </div>
</template>

<style scoped>
	.v-window-item--active{
		padding-bottom: 56px;
	}
</style>

<script>
    module.exports = {
        name: '',
        props: {
            disableLocation: {
                type: Boolean,
                default: false
            },
            checkPart: {
                type: Boolean,
                default: false
            },
            name: {
                type: String,
                default: ''
            },
            database: {
                type: String,
                default: 'barcode'
            },
            defaultLatLong: {
                type: Object,
                default: {
                    lon: '-',
                    lat: '-'
                }
            },
            defaultParams: {
                type: Object,
                default: {}
            }
        },
        data: function() {
            return {
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
                part: false,
                valid: false,
                scan: false,
                srcImg: false,
                cameraPhoto: false,
                labelPhoto: 'Photo',
                labelScan: 'Scan',
                labelSave: 'Save',
                labelTakePhoto: 'Take Photo',
                info: '',
                error: '',
                headers: [{
                        "text": "Barcode",
                        "value": "barcode",
                        "type": "varchar",
                        "readonly": false,
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
                      "text": "Get Location",
                      "value": "getLocation",
                      "type": "button",
                      "click": function(){
                        var self = App.page().scannerActive
                        self.setLocation()
                      }
                    }
                    /* , {
                                        "text": "Process",
                                        "value": "process",
                                        "type": "list",
                                        "data_value": ["Dismantling", "Packing", "Storage"],
                                        "required": true,
                                    } */
                ],
                isInDom: false,
                isInViewport: false,
            }
        },
        watch: {
            disableLocation: function(val) {
                var self = this
                if (val) {
                    self.lt.form = false
                    self.ln.form = false
                } else {
                    self.lt.form = true
                    self.ln.form = true
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
        methods: {
            onVisible: function(isVisible, e) {
                var self = this
                self.isInViewport = !!isVisible
                self.isInDom = !!e.target.isConnected
                if(self.isInViewport){
                  App.page().scannerActive = self
                }
                /* console.log(self.isInViewport, self.isInDom)
                if(!self.isInViewport){
                	self.stopCamera()
                	self.$emit('camera-stop')
                } */
            },
            startCamera: function() {
                var self = this
                self.isTakePhoto = true
                self.$nextTick(() => {
                    var FACING_MODES = JslibHtml5CameraPhoto.FACING_MODES
                    let videoElement = document.getElementById('videoId');
                    let imgElement = document.getElementById('imgId');
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
                        console.log('Camera stoped!');
                        self.isTakePhoto = false
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
            onErrorScan: function(err) {
                this.error = err
            },
			check: function(part){
				var self = this
				return new Promise((resolve, reject) => {
					self.part.find({barcode: part}, result=>{
						resolve(result)
					})
				});
			},
			checkAvailable: function(part){
				var self = this
				return new Promise((resolve, reject) => {
					self.local.find({barcode: part}, result=>{
						if(result.length>0)
							resolve(false)
						else
							resolve(true)
					})
				});
			},
            save: async function() {
                try {
                    var self = this,
                        item = {}
                    var formData = {}
                    self.headers.map(val => {
                        formData[val.value] = val.data;
                    });

					if(formData.barcode.trim()==''){
						throw 'Barcode cannot empty!'
					}
					
					if(self.checkPart){
						/*var check = await self.check(formData.barcode)
						if(check.length == 0){
							App.errorMsg('Tidak ada barcode yang sesuai!')
							return false
						}*/
					}
					var available = self.checkAvailable(formData.barcode)
					if(!available){
						App.errorMsg('Barcode telah ada!')
						return false
					}
                    var status = false
                    try {
                        formData.created_date = new Date('YYYY-MM-DD HH:mm:ss')
                        formData.img = self.srcImg
                        formData = Object.assign({}, self.defaultParams, formData);
                        self.local.save(formData, function(_item) {
                            item = _item
                            status = true
                        });
                    } catch (err) {
                        console.log(error)
                    }
                    if (!status) {
                        App.errorMsg()
                    } else {
                        self.headersObj.barcode.data = null
                        self.headersObj.ln.data = null
                        self.headersObj.lt.data = null
                        self.headers = App.updateObject(self.headers)
                        self.srcImg = false
                        App.successMsg()
                        self.$emit('after-save', item)
                    }
                } catch (err) {
                    console.log(err)
                }
            },
            syncData: async function() {
                var self = this,
                    ok = true
                self.sync = true
                while (self.local.items.length > 0 && ok) {
                    var res = await axios.post(App.url + 'barcode/item', self.local.items[0])
                    if (!res.data.status) {
                        App.errorMsg()
                        ok = false
                    } else {
                        self.local.delete(self.local.items[0])
                    }
                }
                self.sync = false
                App.successMsg()
            },
            onError: function(err) {
                var self = this
                //self.info = err
            },
            stopScan: function() {
                var self = this
                try {
                    self.$refs.scanner.stop()
                    self.scan = false
                } catch (err) {
                    //self.info = err
                }

                /* self.$nextTick(() => {
                    try {
                    } catch (err) {
						self.info = err
                    }
                }) */
            },
            openScan: function() {
                var self = this
                self.showForm = false
                self.headers.map(function(val) {
                    val.data = null
                })
                self.headers = App.updateObject(self.headers)
                self.scan = true
                self.$nextTick(() => {
                    self.$refs.scanner.start()
                })
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
            setLocation: async function(){
              var self = this
              try {
                self.overlay = true
                    var latlong = {
                        lon: '-',
                        lat: '-',
                    }
                    if (!self.disableLocation) {
                            try {
                                latlong = await self.getLocation()
                                if (latlong.lon == undefined) {
                                    self.error = latlong.toString()
                                    latlong = {
                                        lon: '-',
                                        lat: '-',
                                    }
                                }
                            } catch (err) {
                                self.error = err
                                var latlong = {
                                    lon: '-',
                                    lat: '-',
                                }
                            }
                        }
                        self.overlay = false
                        self.headersObj.ln.data = latlong.lon
                        self.headersObj.lt.data = latlong.lat
                        self.headers = App.updateObject(self.headers)
              } catch (err) {
                self.overlay = false
                self.onError(err)
              }
              
            },
            onSuccess: async function(data) {
                var self = this
                try {
                    var latlong = {
                        lon: '-',
                        lat: '-',
                    }
                    self.headersObj.barcode.data = data.decodedText
                    self.headers = App.updateObject(self.headers)
					self.$refs.scanner.stop()
                    self.scan = false
                    self.overlay = true
                    self.$nextTick(async () => {
                        if (!self.disableLocation) {
                            try {
                                latlong = await self.getLocation()
                                if (latlong.lon == undefined) {
                                    self.error = latlong.toString()
                                    latlong = {
                                        lon: '-',
                                        lat: '-',
                                    }
                                }
                            } catch (err) {
                                self.error = err
                                var latlong = {
                                    lon: '-',
                                    lat: '-',
                                }
                            }
                        }
                        self.overlay = false
                        self.headersObj.ln.data = latlong.lon
                        self.headersObj.lt.data = latlong.lat
                        self.headers = App.updateObject(self.headers)
                        self.$emit('success', self.headersObj)
                    })
                } catch (err) {
                    self.onError(err)
                }
                self.showForm = true
            }
        },
        mounted: function() {
            var self = this
            self.local = new LDB.Collection(self.database);
            self.part = new LDB.Collection('material');
			if(self.database == 'dismantle'){
				self.headersObj.barcode.text = 'Barcode Part'
				self.labelPhoto = 'Photo Part'
				self.labelScan = 'Scan Barcode Part'
				self.labelTakePhoto = 'Take Part Photo'
				self.labelSave = 'Save Part'
			}
			if(self.database == 'packing'){
				self.headersObj.barcode.text = 'Barcode Box'
				self.labelPhoto = 'Photo Box'
				self.labelScan = 'Scan Barcode Box'
				self.labelTakePhoto = 'Take Box Photo'
				self.labelSave = 'Save Box'
			}
			if(self.database == 'packing_part'){
				self.headersObj.barcode.text = 'Barcode Part'
				self.labelPhoto = 'Photo Part'
				self.labelScan = 'Scan Barcode Part'
				self.labelTakePhoto = 'Take Part Photo'
				self.labelSave = 'Save Part'
			}
			if(self.database == 'storage'){
				self.headersObj.barcode.text = 'Barcode Box'
				self.labelPhoto = 'Photo Box'
				self.labelScan = 'Scan Barcode Box'
				self.labelTakePhoto = 'Take Box Photo'
				self.labelSave = 'Save Box'
			}
			App.page()[self.name] = self
            loadMultipleLibrary('https://unpkg.com/html5-qrcode@2.3.4/html5-qrcode.min.js;./library/html5-camera.js').then(function(val) {
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

            })
        }
    }
</script>