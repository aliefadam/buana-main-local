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
						disabled: !check_user(['administrator']),
						loadWithBase: '/pr/pr/index'
					},
                    {
						name: 'Active',
						loadWithBase: '/pr/pr/pr',
                    	disabled: !check_user(['pr_admin', 'administrator']),
					},
					{
						name: 'History',
						loadWithBase: '/pr/pr/history',
                        disabled: !check_user(['pr_admin', 'administrator']),
					},
                    {
						name: 'Monitoring',
						loadWithBase: '/pr/pr/monitoring'
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