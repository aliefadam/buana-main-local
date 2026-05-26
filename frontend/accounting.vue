<template>
	<v-layout class="login-layout">
		<v-container style="margin: 0; padding: 0;max-width: 100%;">
			<router-view style="flex: 1; padding: 0 !important" :class="'layout-'+$vuetify.breakpoint.name"
				:formatter="['_key', '_key']"></router-view>
		</v-container>
	</v-layout>
</template>

<style>
</style>

<script>
	module.exports = {
		name: '',
		data: function () {
			return {
				items: [

                    // {
                    //     name: 'Petty Cash',
                    //     children: [
                    //         {
                    //             name: 'Transaksi Petty Cash',
                    //             loadWithBase: '/finance/bom/finance/transaksi_pettycash',
                    //             disabled: !check_user(['administrator'])
                    //         },
                    //         // {
                    //         //     name: 'Laporan Petty Cash',
                    //         //     loadWithBase: '/finance/bom/finance/list_transfer_finance',
                    //         //     disabled: !check_user(['administrator'])
                    //         // },
                    //         {
                    //             name: 'Daftar POS Akun',
                    //             loadWithBase: '/finance/bom/finance/list_transfer_finance',
                    //             disabled: !check_user(['administrator'])
                    //         }
                    //     ]
                    // },
                    {
                        name: 'Transaction',
                        disabled: !check_user(['administrator'/*, 'dept_finance','finance'*/]),
                        children: [
                            {
                                name: 'Transaksi',
                                loadWithBase: '/accounting/finance/petty_cash/index',
                            },                            
                            // {
                            //     name: 'Transaction  ',
                            //     loadWithBase: '/finance/finance/transaction',
                            // },
                            
                            /* {
                                name: 'Laporan Petty Cash',
                                loadWithBase: '/finance/bom/finance/list_transfer_finance',
                                disabled: !check_user(['administrator'])
                            }, */
                            {
                                name: 'Daftar POS Akun',
                                loadWithBase: '/accounting/finance/posakun',
                            }
                        ]
                    }
				]
			}
		},
		methods: {

		},
		mounted: function () {
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