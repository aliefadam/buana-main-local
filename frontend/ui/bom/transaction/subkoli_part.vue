<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
            <template v-slot:menu-after-filter>
                <!-- <v-btn small color="primary" :disabled="selected==false" @click="itemDialog=true" outlined>Partlist</v-btn> -->
                <!-- <v-btn small color="primary" outlined @click="showBarcode=true" :disabled="!selected">
                    <v-icon small left>mdi-barcode</v-icon>Print Barcode
                </v-btn> -->
                <!-- <v-btn small color="primary" outlined @click="showScannerDialog=true">
                    <v-icon small left>mdi-barcode</v-icon>Add From Barcode
                </v-btn> -->
                <v-btn small color="warning" outlined @click="showScannerDialog=true" :disabled="!selected">
                    <v-icon small left>mdi-barcode</v-icon>Update
                </v-btn>
            </template>
            <template v-slot:item.photo="props">
				<img :src="(props.item.photo||';')" style="width: 50px; height: 50px;" v-if="props.item.photo && props.item.photo != ''">
            </template>
        </v-template>
        <v-action-dialog :actions="false" v-model="showBarcodeDialog" title="Barcode" fullscreen>
            <v-btn small color="primary" outlined @click="print">Print</v-btn>
            <v-print ref="vprint" style="color: #000; ">
                <subkolipart-barcode v-model="dataBarcode" :key="selected.id"></subkolipart-barcode>
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
        name: 'subkolipart',
        creator: '',
        components: {
            'subkolipart-barcode': 'url:ui/bom/transaction/barcode.vue',
            "v-print": "url:ui/base/print.vue",
            "subkoli-scan": "url:ui/bom/transaction/subkoliscan.vue",
            "scanner-koli": "url:ui/bom/transaction/scannerkoli.vue",
            // "barcode-scanner": "url:ui/base/barcode-scanner.vue",
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
                name: 'subkolipart',
                itemKey: 'id',
                dataid: {},
                url: 'transaction/subkolipart',
                showScannerDialog: false,
                showBarcode: false,
                showBarcodeDialog: false,
                dataBarcode: [],
                loading: false,
                go: false,
                itemDialog: false,
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
                        text: "BOM",
                        value: "bom_header_id",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "list",
                        data_value: [],
                        disabled: false,
                        visible: false,
                        required: false,
                        form: true,
                        filter: true,
                        groupable: false,
                        url: App.url + "transaction/bomheader", //"bom/payment/complete",
                        searchby: ["description"],
                        formatter: ["id", "description"],
                        "input": function(data){
						var self= App.$get('subkolipart')
                        if(data.data){
                            self.headersObj.bom_receive_id.options.filter = {
                                bom_header_id: data.data
                            }
                        }
                        else 
                            delete self.headersObj.bom_receive_id.options.filter
                        self.headers = App.updateObject(self.headers)
					    } ,
                        options: {
                            filter: {
                            },
                            filterType: {
                            },
                            filterCondition: {},
                        },
                        paging: true,
                        page: "1",
                        limit: "10",
                    }, {
                        text: "BOM Receive No",
                        value: "bom_receive_id",
                        align: "start",
                        sortable: true,
                        filterable: false,
                        divider: false,
                        class: "",
                        width: "auto",
                        type: "list",
                        data_value: [],
                        disabled: false,
                        visible: false,
                        required: false,
                        form: true,
                        filter: true,
                        groupable: false,
                        url: App.url + "transaction/bom", //"bom/payment/complete",
                        searchby: ["name"],
                        formatter: ["id", "info"],
                        // "input": function(data){
						// var self= App.$get('subkolipart')
                        // if(data.data){
                        //     self.headersObj.item_id.options.filter = {
                        //         bom_receive_id: data.data
                        //     }
                        // }
                        // else 
                        //     delete self.headersObj.item_id.options.filter
                        // self.headers = App.updateObject(self.headers)
					    // } ,
                        options: {
                            filter: {
                                machine_no:'null'
                            },
                            filterType: {
                                machine_no:'!='
                            },
                            filterCondition: {},
                        },
                        paging: true,
                        page: "1",
                        limit: "10",
                    },  {
                        "text": "Part Number",
                        "value": "item_id",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "list",
                        "disabled": false,
                        "visible": true,
                        "required": false,
                        "form": true,
                        "filter": false,
                        "groupable": false,
                        "url": App.url + "transaction/bomitem",
                        "searchby": ['info'],
                        "formatter": ['item_id', 'info'],
                        "options": {
                            "filter": {
                            },
                            "filterType": {},
                            "filterCondition": {}
                        },
                        "paging": true,
                        "page": "1",
                        "limit": "10",
                        "alias":"item_name"
                    }, {
                        "text": "Description",
                        "value": "specification",
                        "align": "start",
                        "sortable": true,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "text",
                        "disabled": false,
                        "visible": true,
                        "required": true,
                        "form": false,
                        "filter": false,
                        "groupable": false
                    },{
                        "text": "Qty",
                        "value": "qty",
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
                        "groupable": false
                    }, {
                        "text": "Location",
                        "value": "location",
                        "align": "start",
                        "sortable": false,
                        "filterable": false,
                        "divider": false,
                        "class": "",
                        "width": "auto",
                        "type": "varchar",
                        "disabled": false,
                        "visible": true,
                        "required": false,
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
                    }
                ],
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
                        var res = await axios.get(App.url + '/transaction/bomheader/getitemsreceive', {
                            params: {
                                bom_receive_item_id: self.selected.id
                            }
                        })

                        self.go = true
                        var tmp = []

                        res.data.data.map(val => {
                            if (Number(val.qty) <= 60)
                                tmp.push(Array(Number(val.qty)).fill(val))
                            else
                                tmp.push([val])
                        })
                        console.log(tmp)
                        self.dataBarcode = tmp.flat() //res.data.data
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
                    self.dataid = {}
                } else {
                    self.selected = val
                    self.dataid = val
                    /* {
                        id: val.id
                    } */
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
                    if(this.$parent.$el.classList.contains('login-layout')){
                        self.$refs.template.defaultItemsOptions.filter.koli_id = null
                        self.$refs.template.defaultItemsOptions.filterType.koli_id = 'isnull'
                        self.$refs.template.getItems()
                    }
                    else{
                        self.$refs.template.defaultItemsOptions.filter.subkoli_id = self.data.subkoli_id
                        self.$refs.template.getItems()
                    }
                }
            },
        },
        mounted: function() {
        }
    }
</script>