<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-tabs style="flex: 0" v-model="tab">
			<v-tab :key="0" v-if="check_user(['sj_validasi','administrator'])">Approval Pengirim</v-tab>
            <v-tab :key="1" v-if="check_user(['sj_validasi', 'administrator'])">History</v-tab>
		</v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1;" class="tabs" touchless>
			<v-tab-item :key="0" style="height: 100%;">
				<sj ref="approval" revise v-if="check_user(['sj_validasi','administrator'])" key="0" :items-options="pengirimOptions" table-only ></sj>
			</v-tab-item>
            <v-tab-item :key="1" style="height: 100%;">
				<sj ref="" v-if="check_user(['sj_validasi', 'administrator'])" key="1" :items-options="historyOptions()" table-only nointeraction></sj>
			</v-tab-item>
		</v-tabs-items>
    </v-container>
</template>

<style scoped>
	.tabs > .v-window__container{
		height: 100%;
	}
</style>

<script>
    module.exports = {
        name: '',
        props: {
           
        },
        components: {
            'sj': 'url:ui/suratjalan/dashboard.vue'
        },
        data: function() {
            return {
                name: 'Dashboard',
				pengirimOptions: {
					filter:{
						approved:1,
						pengirim: App.userData.data[0].userid
					},
					filterType: {
					}
				},
				tab: null
            }
        },
        computed: {
        },
		watch: {
		},
        methods: {
            historyOptions: function(){
                var ret = {
					filter:{
						approved:"-1, 3",
                        pengirim: App.userData.data[0].userid
					},
					filterType: {
                        approved: "in"
					},
					filterCondition: {
					}
                }
                if(check_user(['administrator'])){
				    delete ret.filter.pengirim
				}
                return ret
            }
		},
        mounted: function() {
			var self = this
			App.title = 'Dashboard'
			if(check_user(['administrator'])){
				delete self.pengirimOptions.filter.pengirim
			}
        }
    }
</script>