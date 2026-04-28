<template>
	<div class="markdown-body" v-html="App.md.render(data)">
	</div>
</template>

<style>
</style>

<script>
	module.exports = {
		name: '',
		data: function () {
			return {
				data: ''
			}
		},
		methods: {
			
		},
		mounted: async function () {
			var res = await axios.get('./library/markdown-it-plugins/readme.md', {
				params: {
				}
			})
			this.data = res.data
			
		}
	}
</script>