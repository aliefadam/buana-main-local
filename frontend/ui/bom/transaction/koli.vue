<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
            <template v-slot:menu-after-filter>
                <v-btn small color="primary" :disabled="selected==false" @click="photoDialog=true" outlined>Upload Photo</v-btn>
                <v-btn small color="primary" :disabled="selected==false" @click="itemDialog=true" outlined>Subkoli</v-btn>
                <v-btn small color="primary" :disabled="!data.container_id" @click="addKoliDialog=true" outlined>Add Koli</v-btn>
                <v-btn small color="error" :disabled="!selected" @click="deleteKoli" outlined>Delete</v-btn>
                <!-- <v-btn small color="primary" outlined @click="showBarcode=true" :disabled="!selected"><v-icon small left>mdi-barcode</v-icon>Print Barcode</v-btn> -->
            </template>
            <template v-slot:item.koli_no="props">
                BMT.COLLY.{{props.item.id}}
            </template>
        </v-template>
        <v-action-dialog :actions="false" v-model="itemDialog" title="SubKoli" fullscreen>
            <subkoli :data="dataid" :key="selected.id"></subkoli>
        </v-action-dialog>
        <!--<v-action-dialog @save="savePhoto" v-model="photoDialog" title="Upload Photo" fullscreen>-->
        <!--    <v-file-input v-model="photoFile" @change="onPhotoInput" truncate-length="15" filled></v-file-input>-->
        <!--    <img :src="photo" style="max-width: 100px; max-height: 100px; object-fit: contain;" @load="imageOnLoad" />-->
        <!--</v-action-dialog>-->
        <v-action-dialog @save="savePhoto" v-model="photoDialog" title="Upload Photo" fullscreen>
            <v-file-input v-model="photoFile" @change="onPhotoInput" truncate-length="15" filled></v-file-input>
            <img :src="photo" style="object-fit: contain;" ref="img" />
        </v-action-dialog>
        <!-- style="min-height: 90%;" class="add-koli"  -->
        <v-action-dialog @save="saveKoli" v-model="addKoliDialog" title="Add Koli to Container" fullscreen>
            <koli-add :target="data" ref="addKoli"></koli-add>
        </v-action-dialog>
        <v-action-dialog :actions="false" v-model="showBarcodeDialog" title="Barcode" fullscreen>
            <v-btn small color="primary" outlined @click="print">Print</v-btn>
            <v-print ref="vprint" style="color: #000; ">
                <subkoli-barcode v-model="dataBarcode" :key="selected.id"></subkoli-barcode>
            </v-print>
        </v-action-dialog>
    </v-container>
</template>

<style scoped>
</style>

<script>
    module.exports = {
        name: 'koli',
        creator: '',
        components: {
            'subkoli': 'url:ui/bom/transaction/subkoli.vue',
            'subkoli-barcode': 'url:ui/bom/transaction/barcode2.vue',
            "v-print": "url:ui/base/print.vue",
            "koli-add": "url:ui/bom/transaction/koliadd.vue"
        },
        props: {
            value: undefined,
            data: {
                type: Object,
                default: {

                }
            },
            tableOnly: {
                type: Boolean,
                default: true
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
                name: 'koli',
                itemKey: 'id',
                url: 'transaction/koli',
                showBarcode: false,
                showBarcodeDialog: false,
                addKoliDialog: false,
                dataBarcode: [],
                loading: false,
                go: false,
                itemDialog: false,
                photoDialog: false,
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
                    "text": "Container Id",
                    "value": "container_id",
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
                    "text": "Koli No",
                    "value": "koli_no",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "varchar",
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
                    "text": "Description",
                    "value": "description",
                    "align": "start",
                    "sortable": false,
                    "filterable": false,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "text",
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
            addKoliDialog(val) {
                var self = this
                if (val) {
                    self.$nextTick(() => {
                        setTimeout(() => {
                            self.$refs.addKoli.$parent.$el.parentNode.parentNode.style.minHeight = '90%'
                            self.$refs.addKoli.$parent.$el.style.minHeight = '100%'
                        }, 200)
                    })
                }
            },
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
                        var res = await axios.get(App.url + '/transaction/subkoli', {
                            params: {
                                koli_id: self.selected.id
                            }
                        })

                        self.go = true
                        var tmp = []

                        res.data.data.map(val => {
                            val.print = `${val.koli_id}.${val.id}.b`
                        })
                        self.dataBarcode = res.data.data
                        console.log(res.data.data)
                        self.loading = false
                        self.showBarcodeDialog = true
                    } catch (err) {
                        self.loading = false
                        App.errorMsg()
                        console.log(er)
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
                    koli_id: this.selected.id
                }
            }
        },
        methods: {
            resize: function(image) {
                var canvas = document.createElement('canvas'),
                    max_size = 480, // TODO : pull max size from a site config
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
                return canvas.toDataURL()
                //self.photoFile = canvas.toDataURL('image/jpeg');
                //var resizedImage = dataURLToBlob(dataUrl);
            },
            imageOnLoad(e) {
                this.resize(e.target)
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
                            .then(data => {
                                console.log(data)
                                self.photo = data
                            })
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
                    var res = await axios.post(App.url + 'transaction/subkolipart/updateKoliPhoto', {
                        pk_koli: self.selected.id,
                        photo: self.photo//self.resize(self.$refs.img)//self.photo
                    })
                } catch (error) {
                    console.log(error)
                }
                if (!res.data.status) throw res.data
                App.successMsg()
            },
            saveKoli: async function() {
                var self = this
                var sel = self.$refs.addKoli.selected

                try {
                    /* var res = await axios.put(App.url + 'transaction/subkolipart/update_container', {
                        koli_id: sel.id,
                        container_id: self.data.container_id
                    }) */
                    var res = await axios.get(App.url + 'transaction/subkolipart/update_container', {
                        params: {
                            koli_id: sel.id,
                            container_id: self.data.container_id
                        }
                    })
                    if (!res.data.status) throw res.data
                    App.successMsg()
                    self.$refs.addKoli.$refs.template.getItems()
                    self.$refs.template.getItems()
                } catch (err) {
                    App.errorMsg()
                }
            },
            deleteKoli: async function() {
                var self = this
                var sel = self.selected
                if (confirm("Are you sure to delete this data?")) {
                    try {
                        var res = await axios.put(App.url + 'transaction/subkolipart', {
                            pk: sel.subkoli_part_id,
                            container_id: null
                        })
                        console.log(res.data.status)
                        if (!res.data.status) throw res.data
                        App.successMsg()
                        self.$refs.addKoli.$refs.template.getItems()
                        self.$refs.template.getItems()
                    } catch (err) {
                        App.errorMsg()
                    }
                }
            },
            print: function() {
                var w = window.open('', 'wnd');
                w.document.body.innerHTML = document.getElementsByClassName('section-to-print')[0].outerHTML
                w.print()
                w.setTimeout(() => {
                    w.close()
                }, 250)
            },
            createKoli: async function() {
                var self = this
                var q = prompt('Input jumlah koli!')
                if (q) {
                    try {
                        if (isNaN(q)) {
                            alert('Isi dengan angka!')
                        } else {
                            var r = await axios.post(App.url + 'transaction/koli/auto', {
                                container_id: self.data.id,
                                qty: Number(q)
                            })
                        }
                    } catch (err) {
                        App.errorMsg()
                    }

                }
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
                    /*  self.$refs.template.defaultItemsOptions.filter.container_id = self.data.id
                     self.$refs.template.getItems() */
                }
            },
        },
        mounted: function() {

        }
    }
</script>