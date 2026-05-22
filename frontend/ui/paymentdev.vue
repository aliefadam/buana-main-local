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
					// 	name: 'Dashboard',
				 	// 	disabled: !check_user('payment_validasi'),
					// 	loadWithBase: '/payment/bom/payment/dashboard'
					// },
					{
					    name: 'Invoice',
					    children: [{
							name: 'Entry',
							loadWithBase: '/paymentdev/bomdev/payment/invoice/active',
                           // disabled: !check_user(['administrator', 'payment_admin', 'entry_inv_page'])
						}, {
						    name: 'History',
							loadWithBase: '/paymentdev/bomdev/payment/invoice/history',
                            //: !check_user(['administrator', 'payment_admin', 'history_inv_page'])
						}],
                        //disabled: !check_user(['administrator', 'payment_admin','invoice_inv_page'])
					},
					{
					    name: 'Transfer List',
					    children: [{
							name: 'Entry',
							loadWithBase: '/paymentdev/bomdev/payment/transferlist/active',
                           // disabled: !check_user(['administrator', 'payment_admin', 'entry_listTra_page'])
						}, {
							name: 'History',
							loadWithBase: '/paymentdev/bomdev/payment/transferlist/history',
                            // disabled: !check_user(['administrator', 'payment_admin', 'history_lisTra_page'])
						}],
                        // disabled: !check_user(['administrator', 'payment_admin','listTransfer_lisTra_page'])
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