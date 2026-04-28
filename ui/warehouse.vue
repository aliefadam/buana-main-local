<template>
    <v-layout class="login-layout">
        <v-container style="margin: 0; padding: 0;max-width: 100%;">
            <router-view style="flex: 1; padding: 0 !important" :class="'layout-'+$vuetify.breakpoint.name" :formatter="['_key', '_key']"></router-view>
        </v-container>
    </v-layout>
</template>

<style>
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
                items: [{
                        name: 'SPB Approval',
                        children: [{
                                name: 'Logistik',
                                loadWithBase: '/warehouse/bom/warehouse/approval',
                                disabled: !check_user(['administrator', 'kepala_logistik'])
                            }, {
                                name: 'Kepala Lembaga',
                                loadWithBase: '/warehouse/bom/warehouse/approval2',
                                disabled: !check_user(['administrator', 'spb_kabag'])
                            }, {
                                name: 'History',
                                loadWithBase: '/warehouse/bom/warehouse/spb_history',
                                disabled: !check_user(['administrator', 'spb_peminta', 'spb_kabag'])
                            },
                            /*{
                            	name: 'Direktur',
                                loadWithBase: '/warehouse/bom/warehouse/approval3',
                            	disabled: !check_user(['administrator', 'rdarmagiri'])
                            }*/
                        ],
                        disabled: !check_user(['administrator', 'kepala_logistik', 'spb_kabag'])
                    }, {
                        name: 'NPB Approval',
                        children: [{
                                name: 'Peminta',
                                loadWithBase: '/warehouse/bom/warehouse/npb_approval',
                                disabled: !check_user(['administrator', 'npb_peminta'])
                            }, {
                                name: 'Kepala Lembaga',
                                loadWithBase: '/warehouse/bom/warehouse/npb_approval2',
                                disabled: !check_user(['administrator', 'npb_kabag'])
                            }, {
                                name: 'History',
                                loadWithBase: '/warehouse/bom/warehouse/npb_history',
                                disabled: !check_user(['administrator', 'npb_peminta', 'npb_kabag'])
                            },
                            /*{
                            	name: 'Direktur',
                                loadWithBase: '/warehouse/bom/warehouse/npb_approval3',
                            	disabled: !check_user(['administrator', 'rdarmagiri'])
                            }*/
                        ],
                        disabled: !check_user(['administrator', 'npb_peminta', 'npb_kabag'])
                    }, {
                        name: 'Administration',
                        children: [{
                            name: 'Master Items',
                            loadWithBase: '/warehouse/bom/warehouse/item',
                        },{
                            name: 'Master Rack',
                            loadWithBase: '/warehouse/warehouse/rack'
                        }],
                        disabled: !check_user(['administrator', 'logistic_admin',])
                    },
                    {
                        name: 'Incoming',
                        children: [{
                                name: 'Outstanding PO',
                                loadWithBase: '/warehouse/bom/warehouse/monitoring_po'
                            }, 
                            // {
                            //     name: 'Active SPB',
                            //     loadWithBase: '/warehouse/bom/warehouse/spb'
                            // },
                            {
                                name: 'History SPB',
                                loadWithBase: '/warehouse/bom/warehouse/spb_history'
                            }, {
                                name: 'Insert to Rack',
                                loadWithBase: '/warehouse/warehouse/scanmasuk'
                             }, 
                            //  {
                            //     name: 'Receive BOM',
                            //     loadWithBase: '/warehouse/bom/transaction/scanner'
                            // }, 
                        // {
                        //     name: 'Scan Keluar',
                        //     loadWithBase: '/warehouse/warehouse/scankeluar'
                        // }
                            // {
                            //     name: 'Scanner',
                            //     loadWithBase: '/warehouse/bom/transaction/scannernew'
                            // },
                            // {
                            //     name: 'Received scan',
                            //     loadWithBase: '/warehouse/bom/transaction/receivescan'
                            // }
                        ],
                        disabled: !check_user(['administrator', 'logistic_admin', "history_spb"])
                    }, 
                    {
                        name: 'Warehouse',
                        children: [{
                            name: 'Stock',
                            loadWithBase: '/warehouse/bom/warehouse/stockgroup'
                        }, {
                            name: 'Info',
                            loadWithBase: '/warehouse/bom/warehouse/scaninfo',
                            disabled: !check_user(['administrator', 'logistic_admin'])
                        },  
                    ]
                    }, {
                        name: 'Outgoing',
                        children: [
                        //     {
                        //     name: 'Entry NPB',
                        //     loadWithBase: '/warehouse/bom/warehouse/npb',
                        //     disabled: !check_user(['administrator', 'logistic_npb'])
                        // },
                        {
                            name: 'History NPB',
                            loadWithBase: '/warehouse/bom/warehouse/npb_history',
                            disabled: !check_user(['administrator', 'logistic_npb'])
                        }, {
                            name: 'Monitoring NPB',
                            loadWithBase: '/warehouse/bom/warehouse/npb_monitoring',
                            disabled: !check_user(['administrator', 'logistic_npb_monitoring', 'logistic_admin', "logistic_npb"])
                        }],
                        disabled: !check_user(['administrator', 'logistic_npb_monitoring', 'logistic_admin', "logistic_npb"])
                    },
                    {
                        name: 'BOM',
                        children: [{
                                name: 'Generator',
                                loadWithBase: '/warehouse/bom/transaction/container'
                            },  {
                                name: 'SPB',
                                loadWithBase: '/warehouse/bom/spb'
                            },  {
                                name: 'Scanner',
                                loadWithBase: '/warehouse/bom/transaction/scannerkoli'
                            },  {
                                name: 'BOM Part List',
                                loadWithBase: '/warehouse/bom/transaction/bomheader'
                            },{
                                name: 'Colly List',
                                loadWithBase: '/warehouse/bom/transaction/koli'
                            }, {
                                name: 'Partlist without Koli',
                                loadWithBase: '/warehouse/bom/transaction/subkoli_part'
                            }, {
                                name:'PCT % BOM',
                                loadWithBase:'/warehouse/bom/transaction/bom_report_header',
                            }, {
                                name: 'Excel image',
                                loadWithBase: '/warehouse/bom/transaction/excelimage'
                            },
                            //     // {
                            //     //     name: 'Scanner Container',
                            //     //     loadWithBase: '/warehouse/bom/transaction/scannernew'
                            //     // }, {
                            //     //     name: 'Scanner Part List',
                            //     //     loadWithBase: '/warehouse/bom/transaction/scanner'
                            //     // }, {
                            //     //     name: 'Received scan',
                            //     //     loadWithBase: '/warehouse/bom/transaction/receivescan'
                            //     // }
                        ],
                         disabled: !check_user(['administrator', 'logistic_admin', "barcode_user", "bom"])

                    }
                    // {
                    //     name: 'Scan',
                    //     children: [{
                    //         name: 'Barang Masuk',
                    //         loadWithBase: '/warehouse/warehouse/scanmasuk'
                    //     }, {
                    //         name: 'Barang Keluar',
                    //         loadWithBase: '/warehouse/warehouse/scankeluar'
                    //     }, {
                    //         name: 'Info',
                    //         loadWithBase: '/warehouse/warehouse/scaninfo'
                    //     }],
                    //     disabled: !check_user(['administrator', 'logistic_admin'])
                    // }, {
                    //     name: 'Master',
                    //     children: [{
                    //         name: 'Item',
                    //         loadWithBase: '/warehouse/bom/master/item'
                    //     }, {
                    //         name: 'Rack',
                    //         loadWithBase: '/warehouse/warehouse/rack'
                    //     }],
                    //     disabled: !check_user(['administrator', 'logistic_admin'])
                    // }, 
                    /*{
						name: 'Report',
						children: [{
						    name: 'Stock Card',
						    loadWithBase: ''
						}],
						disabled: !check_user(['administrator', 'adm_logistik'])
					}, {
						name: 'SPB Approval',
						loadWithBase: '/warehouse/bom/warehouse/spbApproval/spbApproval',
						disabled: !check_user(['administrator'])
					}
				    {
					name: 'Transaction',
					children: [{
						name: 'Stock',
						loadWithBase: '/warehouse/bom/warehouse/stockgroup'
					}, {
						name: 'NPB',
						loadWithBase: '/warehouse/bom/warehouse/npb'
					    }]
					}*/
                ]
            }
        },
        methods: {

        },
        mounted: function() {
            App.items = []
            App.bottomItems = []
            App.bottomBtn = undefined
            App.drawer = false
            App.toolbar = true
            App.title = ''
            App.items = this.items.slice(0)
        }
    }
</script>