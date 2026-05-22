<template>
	<v-container v-if="ready" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template hide-add-button :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
			<!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					
				</td>
			</template> -->
			<!-- 
			<template v-slot:expanded-item="props">
				<td :colspan="props.headers.length" style="height: auto;">
				</td>
			</template>
			 -->
			<!-- 
			<template v-slot:item.item_name="props">
			</template>
			 -->
			<!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->
			
            <template v-slot:prepend-menu>
				<v-btn small color="primary" outlined @click="dialogSubkoli=true">Scan Subkoli</v-btn>
            </template>
			
			<!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
			<!-- 
            <template v-slot:append-menu>
            </template>
			 -->
		</v-template>
        <v-action-dialog title="Scan Subkoli" fullscreen :actions="false" v-model="dialogSubkoli">
            <b>{{ subkoli || '' }}</b> <v-btn small color="primary" outlined @click="scan">Scan</v-btn>
            <subkoli-part v-if="subkoli" :data="target" :key="version"></subkoli-part>
			<div :style="'left: 50%; transform: translateX(-50%);position: absolute; align-self:center; width: 100%; height: 100%; background-color: #fff; display: flex y; width:100; flex-wrap: wrap;'+($refs.scanner ? ($refs.scanner.barcode.isScanning || subkoli == false ? ' z-index: 1;' : ' z-index: 0;') : ' z-index: 0;')" v-observe-visibility="onVisible">
				<div style="flex: 1;background-color: #fff;height: 100%; position: fixed; width: 100%; left: 0; top: 0;" @click="$refs.scanner.stop()"></div>
				<div style="display: flex;background-color: #fff; flex-direction: column;max-width: 100%;" @click="$refs.scanner.stop()">
					<qrcode-scanner @click.stop="function(){}" @error-scan="onErrorScan" ref="scanner" v-bind:qrbox="250" v-bind:fps="10" style="width: 500px; max-width: 100%; z-index: 10000;" @decodedCode="onSuccess" @decoded-code="onSuccess">
					</qrcode-scanner>
					<div style="flex: 1" @click="$refs.scanner.stop()"></div>
				</div>
				<div style="display: flex; align-items: baseline;">
				<v-text-field style="flex: 1;"
					label="Subcolly"
					v-model="subkoliInput"
				></v-text-field>&nbsp;<v-btn small color="primary" outlined @click="onSuccess({decodedText: subkoliInput})" dense hide-details>Load</v-btn></div>
				<div style="flex: 1;background-color: #fff;" @click="$refs.scanner.stop()"></div>
			</div>
        </v-action-dialog>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: '',
		creator: '',
		components: {
			'subkoli-part': 'url:ui/bom/transaction/subkoli_part_select.vue'
		},
		props: {
			value: undefined,
			data: {
				type: Object
			},
			tableOnly: {
				type: Boolean,
				default: false
			},
			tableFixedHeader: {
				type: Boolean,
				default: true
			},
			disableTable: {
				type: Boolean,
				default: false
			},
			activeColumn: {
				default: "flag"
			},
			showExpand: {
				type: Boolean,
				default: false
			},
			singleExpand: {
				type: Boolean,
				default: true
			},
			itemsOptions: {
				type: Object,
				default: {
					filter: {},
					filterType: {}
				}
			}
		},
		data: function() {
			return {
				name: 'spb',
                subkoli: false,
                barcode: false,
				itemKey: 'id',
				url: 'warehouse/spbitem',
				loading: false,
				ready: false,
				dialogSubkoli: false,
				subkoliInput: '',
				headers: [{
                    "text": "Name",
                    "value": "bom_spb_id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "disabled": false,
                    "readonly": true,
                    "visible": false,
                    "required": false,
                    "form": false,
                    "filter": false,
                    "groupable": false,
                    "url": App.url + 'warehouse/spb',
                    "searchby": ["id"],
                    "formatter": ["id", "spb_no"],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "0",
                    "limit": "10",
                }, {
                    "text": "Item No",
                    "value": "item_id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "data_value": [],
                    "disabled": true,
                    "visible": true,
                    "required": true,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "alias": "item_no",
                    "url": App.url + "bom/item",
                    "searchby": ['full'],
                    "formatter": ['id', 'full'],
                    "options": {
                        "filter": {
                        },
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "alias": "item_no"
                }, {
                    "text": "Item Name",
                    "value": "item_name",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Original Manufacture",
                    "value": "original_manufacture",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                }, {
                    "text": "Manufacture PN",
                    "value": "manufacture_pn",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                        
                }, {
                    "text": "Specification",
                    "value": "specification",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                }, {
                    "text": "Unit",
                    "value": "unit",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": false,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                }, {
                    "text": "Qty",
                    "value": "qty",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "float",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Allocation",
                    "value": "allocation",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": true,
                    "filter": true,
                    "groupable": false,
                    "data_value": ["NI", "NIS", "SC"]
                }, {
                    "text": "Notes",
                    "value": "notes",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "text",
                    "disabled": false,
                    "visible": false,
                    "required": true,
                    "form": false,
                    "filter": false,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Colly No",
                    "value": "koli_id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                },{
                    "text": "Subcolly No",
                    "value": "subkoli_id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Machine Name",
                    "value": "machine_name",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Machine No",
                    "value": "machine_no",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": true,
                    "required": true,
                    "form": false,
                    "filter": true,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "PO",
                    "value": "po_id",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "int",
                    "disabled": false,
                    "visible": false,
                    "required": true,
                    "form": false,
                    "filter": false,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
                    "text": "Po No",
                    "value": "po_no",
                    "align": "start",
                    "sortable": true,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
                    "disabled": false,
                    "visible": false,
                    "required": false,
                    "form": false,
                    "filter": false,
                    "groupable": false,
                    "url": "",
                    "searchby": [],
                    "formatter": [],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10"
                }, {
					"text": "Photo",
					"value": "photo",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"data_value": [],
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false,
                }],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
				isInDom: false,
				isInViewport: false,
                version: randomId(),
                target: {}
			}
		},
		watch: {
			ready(){
				var self = this
			},
			dialogSubkoli(val){
				var self = this
				if(val){
					this.$nextTick(()=>{
						setTimeout(() => {
							self.scan()
						}, 1000);
					})
				}
				else{
					self.$refs.template.getItems()
					self.$refs.scanner.stop()
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
            scan: function(){
                var self = this
				self.dialogSubkoli = true
				self.$refs.scanner.start()
				/* self.$nextTick(()=>{
					setTimeout(() => {
						self.$refs.scanner.start()
					}, 250);
				}) */
            },
			onSelectedRow: function(val) {
				var self = this
				if (val === undefined) {
					self.selected = false
				} else {
					self.selected = val

				}
			},
			onSelectedRowAll: function(val) {
				var self = this
				self.selectedAll = val
			},
			onVisible: function(isVisible, e) {
				var self = this
				self.isInViewport = !!isVisible
				self.isInDom = !!e.target.isConnected
			},
            onErrorScan(){

            },
            onSuccess: async function(data) {
                var self = this
                try {
                    self.version = randomId()
                    if(data.decodedText.match(/^bmt.subcolly.\d+/i)!=null){
                        self.target = {
                            subkoli_id: data.decodedText.split(/^bmt.subcolly./i)[1]
                        }

                        self.subkoli = data.decodedText

                        self.type = 'subcolly'
                
                       // self.headersObj[self.active].data = data.decodedText
                        //self.scan = false
                        self.headers = App.updateObject(self.headers)
                        self.$refs.scanner.stop()
                    }
                    else{
                        alert("QR Code is not valid!")
                    }
				}
				catch(e){
					App.errorMsg(e)
					//self.scan = false
					self.$refs.scanner.stop()
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
                                
                            }
                        }).catch(err => {
                            self.$emit('error-initialize', err);
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
