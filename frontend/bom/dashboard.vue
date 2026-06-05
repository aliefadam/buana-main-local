<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-tabs style="flex: 0" v-model="tab">
			<v-tab :key="0" v-show="check_user(['rusbi', 'administrator'])">Purchase Order (Approval 1)</v-tab>
			<v-tab :key="1" v-show="check_user(['rdarmagiri', 'administrator'])">Purchase Order (Approval 2)</v-tab>
			<!--<v-tab :key="2" v-show="check_user(['rdarmagiri', 'administrator'])">Payment (Approval 1)</v-tab>-->
			<!--<v-tab :key="3" v-show="check_user(['lengga', 'administrator'])">Payment (Approval 2)</v-tab>-->
			<v-tab :key="2" v-show="check_user(['rusbi', 'rdarmagiri', 'administrator'])">History Purchase Order</v-tab>
			<v-tab :key="3" v-show="check_user(['rusbi', 'administrator'])"> Approval Revisi</v-tab>
			<!--<v-tab :key="5" v-show="check_user(['lengga', 'rdarmagiri', 'administrator'])">History Payment</v-tab>-->
		</v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1;" class="tabs" touchless>
			<v-tab-item :value="0" :key="0" style="height: 100%;">
				<po v-if="dept1!=false || check_user(['rusbi', 'administrator'])" :items-options="poItemsOptions" :approval1="true" table-only btn-ask-draft></po>
			</v-tab-item>
			<v-tab-item :value="1" :key="1" style="height: 100%;">
				<po v-if="dept1!=false || check_user(['rdarmagiri', 'administrator'])" :items-options="poItemsOptions2" :approval1="false" table-only btn-approve-draft></po>
			</v-tab-item>
			<!--<v-tab-item :value="2" :key="2" style="height: 100%;" v-if="check_user(['rdarmagiri', 'administrator', 'payment_validator'])">-->
			<!--	<payment key="payment1" type="1" :items-options="paymentItemsOptions(1)" table-only></payment>-->
			<!--</v-tab-item>-->
			<!--<v-tab-item :value="3" :key="3" style="height: 100%;" v-if="check_user(['lengga', 'administrator', 'payment_approval'])">-->
			<!--	<payment key="payment2" type="2" :items-options="paymentItemsOptions(2)" table-only></payment>-->
			<!--</v-tab-item>-->
			<v-tab-item :value="2" :key="2" style="height: 100%;">
				<po v-if="dept1!=false || check_user(['rusbi', 'rdarmagiri', 'administrator'])" :items-options="poItemsOptions3()" table-only nointeraction></po>
			</v-tab-item>

            <v-tab-item :value="3" :key="3" style="height: 100%;">
				<po v-if="dept1!=false || check_user(['rusbi', 'administrator'])" :items-options="poItemsOptions4()" table-only :only-approve-revision="true" :nointeraction="true"></po>
			</v-tab-item>
			<!--<v-tab-item :value="5" :key="5" style="height: 100%;">-->
			<!--	<payment key="payment3" :items-options="paymentItemsOptions2()" table-only nointeraction></payment>-->
			<!--</v-tab-item>-->
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
						approved: '1, -2, 6',
						dept_id: null
					},
					filterType: {
						approved: 'in'
					},
					sortDesc: [false],
				},
				poItemsOptions2: {
					filter: {
						approved: '2, -3, 5',
						dept_id: null
					},
					filterType: {
						approved: 'in'
					},
					sortDesc: [false],
				// 	sortDesc: false
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
						approved: "-2,-1,3,5,6",
						approval_by: App.userData.data[0].userid,
						approval_2_by: App.userData.data[0].userid
					},
					filterType: {
						approved: 'in'
					},
					filterCondition: {
						approval_by: 'or',
						approval_2_by: 'or'
					},
				// 	sortDesc: [false],
				// 	prioritizeApproved2: true,
				// 	sortPriority: true          // Aktifkan sorting prioritas
				}
				if(check_user(['administrator'])){
				    delete ret.filter.approval_by
				    delete ret.filter.approval_2_by
				    delete ret.filterCondition.approval_by
				    delete ret.filterCondition.approval_2_by
				}
				return ret
			},
            poItemsOptions4: function(){
				var ret = {
					filter: {
						revision_approval: "1",
						// approval_by: App.userData.data[0].userid,
						// approval_2_by: App.userData.data[0].userid
					},
					filterType: {
					// 	approved: 'in'
					},
					filterCondition: {
					// 	approval_by: 'or',
					// 	approval_2_by: 'or'
					}
				}
				// if(check_user(['administrator'])){
				//     delete ret.filter.approval_by
				//     delete ret.filter.approval_2_by
				//     delete ret.filterCondition.approval_by
				//     delete ret.filterCondition.approval_2_by
				// }
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
			// else if(check_user(['rdarmagiri', 'administrator'])){
			//     self.tab = 1
			// }
			App.title = 'Dashboard'
        }
    }
</script>
