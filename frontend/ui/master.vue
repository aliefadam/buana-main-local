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
				// 	name: 'Dashboard',
				// 	loadWithBase: '/master/bom/master/dashboard',
                //     disabled: !check_user(['administrator', 'adm_admin', 'department_adm_page', 'supplier_adm_page', 'item_adm_page'])
				// }, 
                {
					name: 'Item',
					children: [{
							name: 'Entry',
							loadWithBase: '/master/bom/master/item'
						}], 
                    disabled: !check_user(['administrator', 'adm_admin', 'item_adm_page'])
				}, 
                {
					name: 'Supplier',
					children: [{
							name: 'Entry',
							loadWithBase: '/master/bom/master/supplier',
						}],
                    disabled: !check_user(['administrator', 'adm_admin','supplier_adm_page'])
				},                {
					name: 'Department',
					children: [{
							name: 'Entry',
							loadWithBase: '/master/bom/master/department',
						}],
                    disabled: !check_user(['administrator', 'adm_admin', 'department_adm_page'])
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