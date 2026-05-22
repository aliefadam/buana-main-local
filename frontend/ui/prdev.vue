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
					// 	name: 'Dashboard Chart',
					// 	disabled: !check_user(['administrator']),
					// 	loadWithBase: '/pr/pr/chart'
					// },
                // {
				// 		name: 'Dashboard',
				// 		disabled: !check_user(['PR_validasi', 'administrator']),
				// 		loadWithBase: '/pr/pr/index'
				// 	},
					// {
					// 	name: 'Dashboard',
					// 	disabled: !check_user(['PR_validasi', 'administrator']),
					// 	loadWithBase: '/pr/pr/index'
					// },
					{
						name: 'Active',
						loadWithBase: '/prdev/bomdev/pr/active'
					},
					{
						name: 'History',
                        loadWithBase: '/prdev/bomdev/pr/history'
					},
                    {
						name: 'Monitoring',
                        loadWithBase: '/prdev/bomdev/pr/monitoring'
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