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
					name: 'Master Item',
					children: [{
							name: 'Entry',
							loadWithBase: '/administration/administration/item/entry'
						}], 
                    disabled: !check_user(['administrator', 'adm_admin', 'item_adm_page'])
				},  {
					name: 'Master Project',
					children: [{
							name: 'Entry',
							loadWithBase: '/administration/administration/project/entry',
						}, {
							name: 'Budget',
							loadWithBase: '/administration/administration/project/budget',
						}, {
							name: 'R&D',
							loadWithBase: '/administration/pr/rnd.vue',
						}],
                    disabled: !check_user(['administrator', 'project_adm_page'])
				},
                {
					name: 'Master Supplier',
					children: [{
							name: 'Entry',
							loadWithBase: '/administration/administration/supplier/entry',
						}],
                    disabled: !check_user(['administrator', 'adm_admin','supplier_adm_page'])
				},                {
					name: 'Master Department',
					children: [{
							name: 'Entry',
							loadWithBase: '/administration/administration/department/entry',
						}],
                    disabled: !check_user(['administrator', 'adm_admin', 'department_adm_page'])
				},                {
					name: 'Master Sub Budget',
					children: [{
							name: 'Entry',
							loadWithBase: '/administration/administration/budget/entry',
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