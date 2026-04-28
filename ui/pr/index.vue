<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-tabs style="flex: 0" v-model="tab">
			<v-tab :key="0" v-show="check_user(['pr_peminta','administrator'])">Requestor Page</v-tab>
			<v-tab :key="1" v-show="check_user(['buana','administrator'])">Approver Page</v-tab>
            <v-tab :key="2"v-show="check_user(['pr_peminta', 'pr_penyetuju','administrator'])">History Page</v-tab>
		</v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1;" class="tabs" touchless>
			<v-tab-item :value="0" :key="0" style="height: 100%;">
				<pr revise v-if="check_user(['pr_peminta','administrator'])" :items-options="pemintaOptions" table-only></pr>
			</v-tab-item>
			<v-tab-item :value="1" :key="1" style="height: 100%;">
				<pr  revise v-if="check_user(['pr_penyetuju','administrator'])" :items-options="penyetujuOptions" table-only></pr>
			</v-tab-item>
            <v-tab-item :value="2" :key="2" style="height: 100%;">
				<pr v-if="check_user(['pr_peminta',  'pr_penyetuju','administrator'])"  :items-options="historyOptions()" table-only nointeraction history></pr>
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
            'pr': 'url:ui/pr/dashboard.vue'
        },
        data: function() {
            return {
                name: 'Dashboard',
                dept1: false,
                dept2: false,
				pemintaOptions: {
					filter:{
						status: "1, -2",
						pr_peminta: App.userData.data[0].userid
					},
					filterType: {
                         status: "in"
					}
				},
				penyetujuOptions: {
					filter:{
						status:"2, -3",
						pr_menyetujui: App.userData.data[0].userid
					},
					filterType: {
                         status: "in"
					},
					
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
						status:"-1, 2, 3, -4",
                        pr_peminta: App.userData.data[0].userid,
						pr_menyetujui: App.userData.data[0].userid
					},
					filterType: {
                        status: "in"
					},
					filterCondition: {
                        pr_peminta: "or",
                        pr_menyetujui: "or",
					}
                }
                if(check_user(['administrator'])){
				    delete ret.filter.pr_peminta
				    delete ret.filter.pr_menyetujui
				    delete ret.filterCondition.pr_peminta
				    delete ret.filterCondition.pr_menyetujui
				}
                return ret
            }
		},
        mounted: function() {
			var self = this
			App.title = 'Dashboard'
			if(check_user(['administrator'])){
				delete self.pemintaOptions.filter.pr_peminta
				delete self.pemintaOptions.filter.pr_menyetujui
				delete self.penyetujuOptions.filter.pr_peminta
				delete self.penyetujuOptions.filter.pr_menyetujui
			}
            if(check_user(['johanes', 'rdarmagiri', 'administrator'])){
				self.tab = 0
			}
            else if(check_user(['buana', 'administrator'])){
				self.tab = 1
			}
            
        }
    }
</script>