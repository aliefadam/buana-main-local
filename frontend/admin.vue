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
				items: [{
					name: 'Users',
					loadWithBase: '/admin/base/users'
				}, {
					name: 'User Group',
					loadWithBase: '/admin/base/user_group'
				},  {
					name: 'WA Info',
					loadWithBase: '/admin/base/nowa'
				},{
					name: 'Modules',
					loadWithBase: '/admin/base/modules'
				}, {
					name: 'Compare',
					loadWithBase: '/admin/admin/compare'
				}, {
					name: 'Helper',
					loadWithBase: '/admin/base/helper'
				}, {
					name: 'Document',
					children:[{
						name: 'Document',
						loadWithBase: '/admin/admin/document/document'
					}, {
						name: 'Fields',
						loadWithBase: '/admin/admin/document/fields'
					}, {
						name: 'List',
						loadWithBase: '/admin/admin/document/list'
					}, {
						name: 'List Values',
						loadWithBase: '/admin/admin/document/listvalues'
					}]
				}]
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