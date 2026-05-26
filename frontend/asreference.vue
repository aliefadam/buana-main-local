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
					{
						name: 'Dashboard',
				 		disabled: !check_user(['asreference_validasi','administrator']),
						loadWithBase: '/asreference/bom/asreference/dashboard'
					},
					{
					    name: 'Surat Permohonan',
					    children: [{
							name: 'Active',
							loadWithBase: '/asreference/bom/asreference/asreference',
                            disabled: !check_user(['administrator', 'asreference_admin', 'entry_inv_page'])
						}, {
						    name: 'History',
							loadWithBase: '/asreference/bom/asreference/',
                            disabled: !check_user(['administrator', 'asreference_admin', 'history_inv_page'])
						}],
                        disabled: !check_user(['administrator', 'asreference_admin','invoice_inv_page'])
					},
					{
					    name: 'Reimbursement',
					    children: [{
							name: 'Active',
							loadWithBase: '/asreference/bom/asreference/asreference',
                            disabled: !check_user(['administrator', 'asreference_admin', 'entry_listTra_page'])
						}, {
							name: 'History',
							loadWithBase: '/asreference/bom/asreference/history',
                            disabled: !check_user(['administrator', 'asreference_admin', 'history_lisTra_page'])
						}],
                        disabled: !check_user(['administrator', 'asreference_admin','listTransfer_lisTra_page'])
					}
					/*,
					 {
					    name: 'asreference',
					    children: [],
					    disabled: true
					} */
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