<template>
	<v-layout :class="'login-layout '+classId">
		<v-container style="margin: 0; padding: 0;max-width: 100%;">
			<router-view style="flex: 1; padding: 1em" :class="'layout-'+$vuetify.breakpoint.name"></router-view>
		</v-container>
	</v-layout>
</template>

<style>
	.layout-xs,
	.layout-sm {
		padding: 0em !important;
	}
</style>

<script>
	module.exports = {
		name: '',
		data: function () {
			return {
				classId: location.hash.match(/sa\/(.*)\/__/i)[1].replace(/\//g, '-')
			}
		},
		methods: {

		},
		mounted: function () {
			App.items = []
			App.bottomItems = []
			App.individual = true
			/*if (location.hash.match(/callbutton\/phase3/) == null)
				App.load('/login')*/
		}
	}
</script>