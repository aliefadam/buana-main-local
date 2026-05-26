<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<!-- <v-tabs style="flex: 0" v-model="tab">
			<v-tab :key="0">History</v-tab>
		</v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1;" class="tabs">
			<v-tab-item :key="0" style="height: 100%;">
				<payment hide-add-invoice hide-approval :items-options="paymentItemsOptions" hide-delete-button hide-add-button show-complete-po history></payment>
			</v-tab-item>
		</v-tabs-items> -->
		<payment hide-add-invoice hide-approval :items-options="paymentItemsOptions" hide-delete-button hide-add-button show-complete-po show-payment-info history></payment>
    </v-container>
</template>

<style>
	.tabs > .v-window__container{
		height: 100%;
	}
</style>

<script>
	module.exports = {
		name: '',
        components: {
            'payment': 'url:ui/bom/payment/payment.vue',
        },
		data: function () {
			return {
				tab: 0,
				paymentItemsOptions: {
					filter: {
						approved: "-2,-1,4"
					},
					filterType: {
						approved: 'in'
					},
				}
			}
		},
		methods: {
			
		},
		mounted: function () {
			
		}
	}
</script>