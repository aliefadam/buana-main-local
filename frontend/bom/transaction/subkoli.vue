<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
            <template v-slot:menu-after-filter>
                <v-btn small color="warning" outlined @click="showScannerDialog=true" :disabled="!selected">
                    <v-icon small left>mdi-barcode</v-icon>Add Part
                </v-btn>
                <v-btn small color="primary" :disabled="selected==false" @click="photoDialog=true" outlined>Upload Photo</v-btn>
                <!-- <v-btn small color="primary"  @click="subkoliDialog=true" outlined>Add Subkoli from System</v-btn> -->
                <v-btn small color="primary" :disabled="selected==false" @click="itemDialog=true" outlined>Partlist</v-btn>
                <!-- <v-btn small color="primary" outlined @click="showBarcode=true" :disabled="!selected"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn> -->
            </template>
			<template v-slot:item.subkoli_no="props">
				BMT.SUBCOLLY.{{props.item.id}}
			</template>
        </v-template>
        <v-action-dialog @save="savePhoto" v-model="photoDialog" title="Upload Photo" fullscreen>
            <v-file-input v-model="photoFile" @change="onPhotoInput" truncate-length="15" filled></v-file-input>
            <img :src="photo" style="max-width: 100px; max-height: 100px; object-fit: contain;" @load="imageOnLoad" />
        </v-action-dialog>
        <v-action-dialog :actions="false" v-model="subkoliDialog" title=" Subkoli List" fullscreen>
            <subkolilist></subkolilist>
        </v-action-dialog>
        <v-action-dialog :actions="false" v-model="itemDialog" title="Part List" fullscreen>
            <subkolipart :data="dataid" :key="selected.id"></subkolipart>
        </v-action-dialog>
        <v-action-dialog :actions="false" v-model="showBarcodeDialog" title="Barcode" fullscreen>
            <v-btn small color="primary" outlined @click="print">Print</v-btn>
            <v-print ref="vprint" style="color: #000; ">
                <subkoli-barcode v-model="dataBarcode" :key="selected.id"></subkoli-barcode>
            </v-print>
        </v-action-dialog>
        <v-action-dialog v-model="showScannerDialog" title="Update Barcode" fullscreen :actions="false">
             <scanner-koli :data="dataid" @success="showScannerDialog=false"></scanner-koli>
		</v-action-dialog>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'subkoli',
        creator: '',
        components: {
            'subkolilist': 'url:ui/bom/transaction/subkolilist.vue',
            'subkolipart': 'url:ui/bom/transaction/subkoli_part.vue',
            'subkoli-barcode': 'url:ui/bom/transaction/barcode2.vue',
            "scanner-koli": "url:ui/bom/transaction/scannerkoli.vue",
            "v-print": "url:ui/base/print.vue",
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
                photo: '',
                photoFile: false,
                showScannerDialog: false,
                name: 'subkoli',
                itemKey: 'id',
                url: 'transaction/subkoli',
                showBarcode: false,
                showBarcodeDialog: false,
                dataBarcode: [],
                loading: false,
                go: false,
                itemDialog: false,
                photoDialog: false,
                subkoliDialog: false,
                headers: [{
                    "text": "Id",
                    "value": "id",
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
                    "text": "Koli Id",
                    "value": "koli_id",
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
                    "text": "subkoli Id",
                    "value": "subkoli_id",
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
                    "text": "Subkoli No",
                    "value": "subkoli_no",
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
                    "form": true,
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
                    "text": "Machine",
                    "value": "bom_receive_id",
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
                    "filter": false,
                    "groupable": false,
                    "url": App.url + "transaction/bom",
                    "searchby": ['info'],
                    "formatter": ['id', 'info'],
                    "options": {
                        "filter": {},
                        "filterType": {},
                        "filterCondition": {}
                    },
                    "paging": true,
                    "page": "1",
                    "limit": "10",
                    "alias": "name"
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
                    "filter": false,
                    "groupable": false,
                }, {
                    "text": "Created Date",
                    "value": "created_date",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "datetime",
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
                    "text": "Created By",
                    "value": "created_by",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "int",
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
                    "limit": "10",
                    "alias": "created_by_name"
                }, {
                    "text": "Modified Date",
                    "value": "modified_date",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "datetime",
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
                    "text": "Modified By",
                    "value": "modified_by",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "int",
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
                    "limit": "10",
                    "alias": "modified_by_name"
                }],
                //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
                selected: false,
                //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
                selectedAll: [],
                isInDom: false,
                isInViewport: false,
            }
        },
        watch: {
            async showBarcodeDialog(val) {
                if (val == false)
                    this.showBarcode = false
            },
            async showBarcode(val) {
                var self = this
                if (val) {
                    self.loading = true
                    try {
                        App.tmp.target = self
                        var res = await axios.get(App.url + '/transaction/subkolipart', {
                            params: {
                                subkoli_id: self.selected.id
                            }
                        })

                        self.go = true
                        var tmp = []

                        res.data.data.map(val => {
                            val.print = `${val.subkoli_id}.${val.id}.c`
                        })
                        self.dataBarcode = res.data.data
                        self.loading = false
                        self.showBarcodeDialog = true
                    } catch (err) {
                        self.loading = false
                        App.errorMsg()
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
            },
            dataid: function() {
                if (this.selected == false)
                    return {}
                return {
                    subkoli_id: this.selected.id,
                    //bom_receive_id: this.selected.bom_receive_id
                }
            }
        },
        methods: {
            print: function() {
                var w = window.open('', 'wnd');
                w.document.body.innerHTML = document.getElementsByClassName('section-to-print')[0].outerHTML
                w.print()
                w.setTimeout(() => {
                    w.close()
                }, 250)
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
                if (self.isInViewport) {
                    self.$refs.template.defaultItemsOptions.filter.koli_id = self.data.koli_id
                    self.$refs.template.getItems()
                }
            },
            onPhotoInput: function(val) {
                var self = this
                try {
                    if (val) {
                        const fileDataURL = val => new Promise((resolve, reject) => {
                            let fr = new FileReader();
                            fr.onload = () => resolve(fr.result);
                            fr.onerror = reject;
                            fr.readAsDataURL(val)
                        });

                        fileDataURL(val)
                            .then(data => (self.photo = data))
                            .catch(err => console.log(err));
                        console.log(val)
                    }
                } catch (err) {
                    console.log(err)
                }


            },
            savePhoto: async function() {
                var self = this
                try {
                    var res = await axios.post(App.url + 'transaction/subkolipart/updateSubkoliPhoto', {
                        pk_subkoli: self.selected.id,
                        photo2: self.photo
                    })
                } catch (error) {
                    console.log(error)
                }
                if (!res.data.status) throw res.data
                App.successMsg()
            },
            resize: function(image) {
                var canvas = document.createElement('canvas'),
                    max_size = 720, // TODO : pull max size from a site config
                    width = image.naturalWidth,
                    height = image.naturalHeight;
                if (width > height) {
                    if (width > max_size) {
                        height *= max_size / width;
                        width = max_size;
                    }
                } else {
                    if (height > max_size) {
                        width *= max_size / height;
                        height = max_size;
                    }
                }
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(image, 0, 0, width, height);
                self.photoFile = canvas.toDataURL('image/jpeg');
                //var resizedImage = dataURLToBlob(dataUrl);
            },
            imageOnLoad(e) {
                this.resize(e.target)
            },
        },
        mounted: function() {

        }
    }
</script>