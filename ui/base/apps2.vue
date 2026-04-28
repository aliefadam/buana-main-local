<template>
	<v-container style="padding : 1em !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%; padding: 1em;">
		<v-row flex-col transition="slide-y-transition" style="padding: 0.25em;">
			<template v-for="(item, index) in items" :key="index">
				<v-col md="4" lg="3" xl="2"><v-card flat :to="item.url" class="appbox" :style="`background: ${item.background}; color: ${item.color}`">{{item.name}}</v-card></v-col>
			</template>
		</v-row>
	</v-container>
</template>

<style scoped>
	.appbox{
		border-radius: 0.5em;
		border: 1px solid rgba(0,0,0, 0.1);
		height: 100px;
		display: flex;
		justify-content: center;
		align-items: center;
		font-weight: 800;
		margin: 0.25em;
		margin-left: 0;
		margin-top: 0;
		cursor: pointer;
	}
</style>

<script>
	module.exports = {
		name: '',
		props: {
		},
		data: function() {
			return {
				name: 'users',
				items: []
			}
		},
		methods: {
			getData: async function(){
				var self = this
				var res = await axios.get(App.url+'admin/modules/available', {
					params: {
					}
				})
				self.items = res.data.data
			}
		},
		mounted: function() {
			this.getData()
		}
	}

</script>
