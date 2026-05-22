<template>
	<invoice history table-only :items-options="itemsOptions"></invoice>
</template>

<style>
</style>

<script>
	module.exports = {
		name: '',
        components: {
            'invoice': 'url:ui/bom/payment/invoice.vue',
        },
		props: {
			itemsOptions: {
				type: Object,
				default: {
					filter: {
						payment_id: null
					},
					filterType: {
						payment_id: 'notnull'
					}
				}
			}
		},
		data: function () {
			return {
				
			}
		},
		methods: {
			
		},
		mounted: function () {
			
		}
	}
</script>