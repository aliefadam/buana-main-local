<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<!-- <v-tabs style="flex: 0" v-model="tab">
			<v-tab :key="0">History</v-tab> -->
			<!--<v-tab :key="1">Payment</v-tab>-->
		<!-- </v-tabs> -->
        <!-- <v-tabs-items v-model="tab" style="flex: 1;" class="tabs">
			<v-tab-item :key="0" style="height: 100%;"> -->
		<po name="History" hide-approval :items-options="poItemsOptions" ref="po" hide-delete-button hide-add-button history></po>
			<!-- </v-tab-item> -->
			<!--<v-tab-item :key="1" style="height: 100%;">-->
			<!--	<payment hide-add-invoice hide-approval :items-options="paymentItemsOptions" hide-delete-button hide-add-button show-complete-po history></payment>-->
			<!--</v-tab-item>-->
		<!-- </v-tabs-items> -->
    </v-container>
</template>

<style>
	.tabs > .v-window__container{
		height: 100%;
	}
</style>

<script>
	module.exports = {
        components: {
            // 'payment': 'url:ui/bom/purchasing/payment.vue',
            'po': 'url:ui/bom/purchasing/purchaseorder.vue'
        },
		data: function () {
			return {
				tab: 0,
				poItemsOptions: {
					filter: {
						approved: "-4,-1,3"
					},
					filterType: {
						approved: 'in'
					}
				},
				// paymentItemsOptions: {
				// 	filter: {
				// 		approved: "-2,-1,4"
				// 	},
				// 	filterType: {
				// 		approved: 'in'
				// 	},
				// 	sortBy: ["payment_date"],
				// 	sortDesc: ["desc"],
				// }
			}
		},
		methods: {
			
		},
		mounted: function () {
			
		}
	}
</script>