<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<history history  :items-options="historyOptions">
            
        </history>
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
            'history': 'url:ui/rfq/entry.vue',
        },
		data: function () {
			return {
                tab: 0,
                historyOptions: {
					filter: {
						status: "'Final Quotation', 'Cancel'"
					},
					filterType: {
						status: 'in'
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