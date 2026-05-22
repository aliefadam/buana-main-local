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
				//     {
				// 	name: 'Home',
				// 	loadWithBase: '/rfq/rfq/index',
    //                 disabled: !(check_user(['administrator'])),
				// }, 
				// {
				// 	name: 'Dashboard',
				// 	loadWithBase: '/rfq/rfq/dashboard',
                //     disabled: !(check_user(['administrator'])),
				// }, 
                {
					name: 'Entry',
					loadWithBase: '/rfq/rfq/entry',
					// disabled: !(check_user(['RFQ']) && check_user(['rfq_local'])),
				}, {
					name: 'History',
					loadWithBase: '/rfq/rfq/history'
				}, {
					name: 'Monitoring',
					loadWithBase: '/rfq/rfq/monitoring',
				},
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