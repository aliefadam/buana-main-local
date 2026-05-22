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
        <v-card style="display: flex; flex-direction: column; padding-bottom: 0px; align-self:center">
            <v-card-title>Scanner Page</v-card-title>
            <v-card-text class="v-action-dialog-card" style="align-items:center">
                <v-autoform ref="scanForm" v-model="headers" :valid.sync="valid" hide-details="auto" :filled="true"></v-autoform>
            </v-card-text>
            <v-select v-if="showBomReceive" single-line filled auto-select-first hide-details dense v-model="bomReceiveId" :items="bomReceiveItems" label="Bom Receive" style=" max-width: 300px !important;"></v-select>
            <v-divider></v-divider>
			<div style="font-weight: bold;">Item Name: {{itemInfo.item_name}}</div>
			<!-- <div style="font-weight: bold;">devices: {{devices}}</div> -->
            <div style="padding: 6px" v-if="location.lat==0">Location: unknown</div>
            <div style="padding: 6px" v-else>Location: {{ location.lat }}, {{ location.lon }}</div>
            <v-avatar class="profile" color="grey" size="150" tile v-if="srcImg!=false">
                <v-img max-height="150" max-width="150" alt="imgId" id="imgId" :src="srcImg"></v-img>
            </v-avatar>
            <v-avatar class="profile" color="grey" size="150" tile v-if="srcImg2!=false">
                <v-img max-height="150" max-width="150" alt="imgId2" id="imgId2" :src="srcImg2"></v-img>
            </v-avatar>
            <v-avatar class="profile" color="grey" size="150" tile v-if="srcImg3!=false">
                <v-img max-height="150" max-width="150" alt="imgId3" id="imgId3" :src="srcImg3"></v-img>
            </v-avatar>
            <v-btn color="primary" outlined @click="showSubkoli=true" v-if="selectedKoli.koli_id">Info Colly</v-btn>
            <v-btn color="primary" outlined @click="showSubkoliPart=true" v-if="selectedSubkoli.subkoli_id">Info Subcolly</v-btn>

            <v-card-actions style="position: fixed; background-color: #fff; display: flex y; width:100%; flex-wrap: wrap; flex-gap: 5px; justify-content: center;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);">
                <v-spacer></v-spacer>
                <v-btn color="primary" @click="function(){startScan()}">Start Scan</v-btn>
                <v-btn color="info" @click="startCamera('srcImg3')">Part Photo</v-btn>
                <v-btn color="info" @click="startCamera('srcImg')">Subkoli Photo</v-btn>
                <v-btn color="info" @click="startCamera('srcImg2')">Koli Photo</v-btn>
                <v-btn color="success" @click="addKoli">Save Data</v-btn>
                <v-btn color="primary" outlined v-if="data.id" @click="App.$get('subkolipart').showScannerDialog = false">Close</v-btn>
                <v-btn color="red" @click="clear">Clear</v-btn>
            </v-card-actions>
        </v-card>

        <v-action-dialog fullscreen v-model="dialogPhoto" @save="takePhoto">
            <div style="display: flex; justify-content: center;">
                <video id="videoId" autoplay="true"></video>
            </div>
        </v-action-dialog>

        <v-action-dialog fullscreen v-model="dialogSubkoli" :actions="false" title="Barcode Colly Info">
            <div v-if="subkolilist!=''">
                <div style="font-size: 15px; color:black; font-weight: bold; padding-top: 14px;  padding-bottom: 14px; ">
                    <br />Colly No: BMT.COLLY.{{selectedKoli.koli_id.toString().padStart(4, '0')}}<br /></div>
                <table class="table tdvtop" style="width:100%;border:1px solid #000 !important;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 13px;"> No </th>
                            <th rowspan="2">Subcolly No</th>
                            <th rowspan="2"> Machine </th>
                            <th rowspan="2"> Machine No </th>
                        <th rowspan="2"> Photo </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in items" :key="index">
                            <td style="text-align: center;">{{ item.no }}</td>
                            <td>BMT.SUBCOLLY.{{ item.id.toString().padStart(4, '0') }}</td>
                            <td>{{ item.description }}</td>
                            <td>{{ item.machine_no }}</td>
                            <td v-if="item.photo!=''"><img :src='item.photo' style="width: 100px; height: 100px;" :key="item.no" /></td><td v-else></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </v-action-dialog>

        <v-action-dialog fullscreen v-model="dialogSubkoliPart" :actions="false" title="Barcode Subcolly Info">
            <div v-if="subkolipartlist!=''">
                <div style="font-size: 15px; color:black; font-weight: bold; padding-top: 14px;  padding-bottom: 14px;">
                    <br />Subcolly No: BMT.SUBCOLLY.{{selectedSubkoli.subkoli_id.toString().padStart(4, '0')}}<br />
                    <!--<img :src='item.photo' style="width: 150px; height: 150px;" :key="item.no" />-->
                    </div>
                <table class="table tdvtop" style="width:100%;border:1px solid #000 !important;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 13px;"> No </th>
                            <th rowspan="2">Part No</th>
                            <th rowspan="2"> Description </th>
                            <th rowspan="2"> QTY </th>
                            <th rowspan="2"> Location </th>
                            <th rowspan="2"> Photo </th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in items" :key="index">
                            <td style="text-align: center;">{{ item.no }}</td>
                            <td>{{ item.item_name }}</td>
                            <td v-html="item.specification.replace(/\n/g, '<br>')"></td>
                            <td>{{ item.qty }}</td>
                            <td><a :href="'https://www.google.com.sa/maps/search/'+item.location" target="_blank">open location</a></td>
                            <td v-if="item.photo3!=''"><img :src='item.photo3' style="width: 100px; height: 100px;" :key="item.no" /></td><td v-else></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </v-action-dialog>
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
        white-space: nowrap;
    }

    table.table td>div {
        padding: 0 4px;
        border-radius: 3px;
        white-space: nowrap;
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
</style>

<script>
    module.exports = {
        name: '',
        components: {
            'part-list': 'url:ui/bom/transaction/scanpartlistnew.vue',
            'subkoli-part': 'url:ui/bom/transaction/subkoli_part.vue',
            'subkoli': 'url:ui/bom/transaction/subkoli.vue',
        },
        props: {
            data: {
                type: Object,
                default: {}
            }
        },
        data: function() {
            return {
                ready: false,
                showSubkoli: false,
                showBomReceive: false,
                dialogSubkoli: false,
                showSubkoliPart: false,
                dialogSubkoliPart: false,
                bomReceiveId: false,
                bomReceiveItems: [],
                devices: [],
                scan: true,
                valid: false,
                url: 'transaction/bomheader',
                activeitem: 'item',
                activekoli: 'koli',
                activesubkoli: 'subkoli',
                headers: [{
                        "text": "Part Scan",
                        "value": "item",
                        "type": "varchar",
                        "readonly": false,
                        "required": false,
                        "focus": function() {
                            /* App.page().activeitem = 'item'
                            App.page().startScan() */
                        }
                    },
                    {
                        "text": "Subcolly Scan",
                        "value": "subkoli",
                        "type": "varchar",
                        "readonly": false,
                        "required": false,
                        "focus": function() {
                            /* App.page().activesubkoli = 'subkoli'
                            App.page().startScan() */
                        }
                    },
                    {
                        "text": "Colly Scan",
                        "value": "koli",
                        "type": "varchar",
                        "readonly": false,
                        "required": false,
                        "focus": function() {
                            /* App.page().activekoli = 'koli'
                            App.page().startScan() */
                        }
                    },
                    {
                        "text": "QTY Part",
                        "value": "qty",
                        "type": "varchar",
                        "readonly": false,
                        "required": false
                    }
                ],
                info: '',
                subkolilist: '',
                subkolipartlist: '',
                items: [],
                isBomReceive: false,
                cameraPhoto: false,
                dialogPhoto: false,
                photoTarget: false,
                barcode: '',
                subkoli_id: '',
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
			async getItemInfo(item_id){
				var self = this
				try {
					var res = await axios.get(App.url+'bom/item', {
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
                        if (self.data.id != undefined || self.data.id != null) {
                            if (self.data.koli_id) {
                                self.headersObj.koli.data = 'BMT.COLLY.' + self.data.koli_id.toString().padStart(4, '0')
                            }
                            if (self.data.subkoli_id) {
                                self.headersObj.subkoli.data = 'BMT.SUBCOLLY.' + self.data.subkoli_id.toString().padStart(4, '0')
                            }
                            self.headersObj.item.data = self.data.bom_header_id + '.' + self.data.item_id + '.1'
                            self.showBomReceive = true

                            self.selectedKoli = {
                                koli_id: self.data.koli_id,
                            }
                            self.selectedSubkoli = {
                                subkoli_id: self.data.subkoli_id,
                            }
                        }
                        else{
                            if (self.data.subkoli_id) {
                                self.headersObj.subkoli.data = 'BMT.SUBCOLLY.' + self.data.subkoli_id.toString().padStart(4, '0')
                            }
                        }
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
            addKoli: async function() {
                var self = this
                try {
                    if (isNaN(Number(self.headersObj.qty.data))) alert("Masukkan angka pada QTY!")
                    else {
                        var loc = '0';
                        try {
                            loc = self.location.lat + ',' + self.location.lon
                        } catch (error) {}

                        var koli_id = null;
                        try {
                            koli_id = self.headersObj.koli.data.split('.')[2]
                        } catch (error) {
                            koli_id = null;
                        }
                        var subkoli_id = null;
                        try {
                            subkoli_id = self.headersObj.subkoli.data.split('.')[2]
                        } catch (error) {
                            subkoli_id = null;
                        }
                        var item_id = null;
                        try {
                            item_id = self.headersObj.item.data.split('.')[1]
							self.getItemInfo(item_id)
                        } catch (error) {
                            item_id = null;
                        }

                        if (item_id == null) // (1 == 0)
                        {
                            var res = await axios.post(App.url + 'transaction/koli/addscan', {
                                    koli_id: koli_id  || '',
                                    item_id:null,
                                    bom_header_id:null,
                                    qty:0,
                                    subkoli_id: subkoli_id,
                                    photo: self.srcImg || '',
                                    photo2: self.srcImg2 || '',
                                    photo3: self.srcImg3 || '',
                                    location: loc,
                                    bom_receive_id: self.bomReceiveId
                                })
                                self.$emit('success')
                                  if (res.data.status == false) throw res.data
                            App.successMsg()
                            
                        }
                             
                        else {

                            if (self.data.id != undefined || self.data.id != null) {
                                var res = await axios.post(App.url + 'transaction/koli/updatescan', {
                                    id: self.data.id,
                                    qty: self.headersObj.qty.data,
                                    koli_id: koli_id  || '',
                                    subkoli_id: subkoli_id,
                                    item_id: item_id, //self.headersObj.item.data.split('.')[1],
                                    bom_header_id: self.headersObj.item.data.split('.')[0],
                                    photo: self.srcImg || '',
                                    photo2: self.srcImg2 || '',
                                    photo3: self.srcImg3 || '',
                                    location: loc,
                                    bom_receive_id: self.bomReceiveId
                                })
                                self.$emit('success')
                            } else {
                                var res = await axios.post(App.url + 'transaction/koli/addscan', {
                                    qty: self.headersObj.qty.data,
                                    koli_id: koli_id || '',
                                    subkoli_id: subkoli_id,
                                    item_id: self.headersObj.item.data.split('.')[1],
                                    bom_header_id: self.headersObj.item.data.split('.')[0],
                                    photo: self.srcImg || '',
                                    photo2: self.srcImg2 || '',
                                    photo3: self.srcImg3 || '',
                                    location: loc,
                                    bom_receive_id: self.bomReceiveId
                                })
                            }
                            if (res.data.status == false) throw res.data
                            App.successMsg()
                        }
                    }
                } catch (error) {
                    console.log(error)
                    App.errorMsg()
                }
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
                    if (data.decodedText.toLowerCase().match('bmt.subcolly.') != null || data.decodedText.toLowerCase().match('bmt.subkoli.') != null)
                        self.headersObj['subkoli'].data = data.decodedText
                    else if (data.decodedText.toLowerCase().match('bmt.colly.') != null || data.decodedText.toLowerCase().match('bmt.koli.') != null)
                        self.headersObj['koli'].data = data.decodedText
                    else
                        self.headersObj['item'].data = data.decodedText

                    self.selectedKoli = {
                        koli_id: self.headersObj.koli.data == undefined || self.headersObj.koli.data == '' ? null : Number(self.headersObj.koli.data.split('.')[2]),
                    }
                    self.selectedSubkoli = {
                        subkoli_id: self.headersObj.subkoli.data == undefined || self.headersObj.subkoli.data == '' ? null : Number(self.headersObj.subkoli.data.split('.')[2]),
                    }

                    var item_id = null;
                    try {
                        item_id = self.headersObj.item.data.split('.')[1]
                    } catch (error) {
                        item_id = null;
                    }
                    if (item_id)
                        self.showBomReceive = true
                    /* self.headersObj[self.activeitem].data = data.decodedText
                    self.headersObj[self.activekoli].data = data.decodedText
                    self.headersObj[self.activesubkoli].data = data.decodedText */
                    //self.scan = false
                    self.headers = App.updateObject(self.headers)
                    self.$refs.scanner.stop()
                    //  self.showSubkoli(data.decodedText)
                    //self.getInfo(data.decodedText)
                } catch (e) {
                    App.errorMsg(e)
                    //self.scan = false
                    self.$refs.scanner.stop()
                }
            },

            //         async getInfo(barcode) {
            //             var self = this
            //             try {
            // 	self.barcode = barcode
            // 	self.isBomReceive = barcode.match(/.0$/)!=null
            //                 var res = await axios.get(App.url + self.url + '/info2', {
            //                     params: {
            //                         barcode: barcode
            //                     }
            //                 })
            //                 var t = res.data.data[0]
            //                 self.items = res.data.data
            // 	if(self.$refs.partlist){
            // 		self.$refs.partlist.getData(self.barcode)
            // 	}
            // 	var qty = res.data.data.reduce((partialSum, a) => partialSum + Number(a.qty_actual), 0);
            // 	if(self.isBomReceive)
            // 		qty = '-'
            //                 self.info = `
            // 	<table cellpadding="0" cellspacing="0" style="border: 0">
            // 		<tr>
            // 			<td>Name</td>
            // 			<td>: ${t.machine_name||'-'}_${t.item_name}</td>
            // 		</tr>
            // 		<tr>
            // 			<td>Notes</td>
            // 			<td>: ${t.note||'-'}</td>
            // 		</tr>
            // 	</table>
            // 	`
            //             } catch (error) {
            // 	console.log(error)
            //             }
            //         }
        },
        mounted: async function() {
            var self = this
            console.log(self)
            try{
                self.location = await self.getLocation()
            }
            catch(e){
                self.location = {lat:0,lon:0}
            }
            setNonPermanentInterval(async () => {try{
                self.location = await self.getLocation()
            }
            catch(e){
                self.location = {lat:0,lon:0}
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