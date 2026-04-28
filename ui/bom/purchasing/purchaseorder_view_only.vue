<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <po hide-approval :items-options="poItemsOptions" ref="po" hide-delete-button hide-add-button history table-only></po>
    </v-container>
</template>

<style>
	.tabs > .v-window__container{
		height: 100%;
	}
</style>

<script>
	module.exports = {
		name: 'Data Purchase Order',
        components: {
            'po': 'url:ui/bom/purchasing/purchaseorder.vue'
        },
		data: function () {
			return {
				tab: 0,
				poItemsOptions: {
					filter: {
						approved: "-2, 3"
					},
					filterType: {
						approved: 'btw'
					}
				},
			}
		},
		methods: {
			
		},
		mounted: function () {
			
		}
	}
</script>