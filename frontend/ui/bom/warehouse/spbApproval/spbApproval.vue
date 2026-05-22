<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-tabs style="flex: 0" v-model="tab">
			<v-tab :key="0" v-show="check_user(['administrator'])">SPB Approval Peminta</v-tab>
			<v-tab :key="1" v-show="check_user(['administrator'])">SPB Approval Kepala Bagian</v-tab>
			<v-tab :key="2" v-show="check_user(['administrator'])">SPB History Approval</v-tab>
		</v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1;" class="tabs" touchless>
			<v-tab-item :value="0" :key="0" style="height: 100%;">
				<po v-if="dept1!=false || check_user(['administrator'])" :items-options="poItemsOptions" table-only></po>
			</v-tab-item>
			<v-tab-item :value="1" :key="1" style="height: 100%;">
				<po v-if="dept1!=false || check_user(['administrator'])" :items-options="poItemsOptions2" table-only></po>
			</v-tab-item>
			<v-tab-item :value="2" :key="2" style="height: 100%;">
				<po v-if="dept1!=false || check_user(['administrator'])" :items-options="poItemsOptions3()" table-only nointeraction></po>
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
            'po': 'url:ui/bom/dashboard/po.vue',
            // 'payment': 'url:ui/bom/dashboard/payment.vue',
        },
        data: function() {
            return {
                name: 'Dashboard',
                dept1: false,
                dept2: false,
				poItemsOptions: {
					filter: {
						approved: 1,
						dept_id: null
					}
				},
				poItemsOptions2: {
					filter: {
						approved: 2,
						dept_id: null
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
			paymentItemsOptions: function(type){
				return {
					filter: {
						approved: type == 1 ? 1 : 3
					}
				}
			},
			paymentItemsOptions2: function(){
				var ret = {
					filter: {
						approved:"-2,-1,4",
						approved1: App.userData.data[0].name,
						approved2: App.userData.data[0].name
					},
					filterType: {
						approved: 'in'
					},
					filterCondition: {
						approved1: 'or',
						approved2: 'or'
					}
				}
				if(check_user(['administrator'])){
				    delete ret.filter.approved1
				    delete ret.filter.approved2
				    delete ret.filterCondition.approved1
				    delete ret.filterCondition.approved2
				}
				return ret
			},
			poItemsOptions3: function(){
				var ret = {
					filter: {
						approved: "-2,-1,3",
						approval_by: App.userData.data[0].userid,
						approval_2_by: App.userData.data[0].userid
					},
					filterType: {
						approved: 'in'
					},
					filterCondition: {
						approval_by: 'or',
						approval_2_by: 'or'
					}
				}
				if(check_user(['administrator'])){
				    delete ret.filter.approval_by
				    delete ret.filter.approval_2_by
				    delete ret.filterCondition.approval_by
				    delete ret.filterCondition.approval_2_by
				}
				return ret
			},
			getDept: async function(){
				var self = this
				var response = await axios.get(App.url+'bom/department', {
					params: {
						filter: {
							approval_1: App.userData.data[0].userid
						}
					}
				})
				var data = response.data
				if(data.data.length>0){
					self.poItemsOptions.filter.dept_id = data.data[0].id
					if(check_user(['rusbi', 'rdarmagiri', 'administrator'])){
						delete self.poItemsOptions.filter.dept_id;
						delete self.poItemsOptions2.filter.dept_id;
					}
					self.dept1 = data.data[0].id
				}
				/* var response = await axios.get(App.url+'bom/department', {
					params: {
						filter: {
							approval_2: App.userData.data[0].userid
						}
					}
				})
				var data = response.data
				if(data.data.length>0){
					self.paymentItemsOptions.filter.dept_id = data.data[0].id
					self.dept2 = data.data[0].id
				} */
			}
		},
        mounted: function() {
			var self = this
			if(check_user(['rusbi', 'rdarmagiri', 'administrator'])){
				delete self.poItemsOptions.filter.dept_id;
				delete self.poItemsOptions2.filter.dept_id;
			}
			else
				self.getDept()
			if(check_user(['rusbi', 'administrator'])){
			    self.tab = 0
			}
			else if(check_user(['rdarmagiri', 'administrator'])){
			    self.tab = 1
			}
			else if(check_user(['rdarmagiri', 'administrator'])){
			    self.tab = 1
			}
			App.title = 'Dashboard'
        }
    }
</script>