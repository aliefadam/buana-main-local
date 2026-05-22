<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-tabs style="flex: 0" v-model="tab">
			<v-tab :key="0">Purchase Order</v-tab>
			<v-tab :key="1" v-if="check_user(['rdarmagiri', 'administrator'])">Payment (Approval 1)</v-tab>
			<v-tab :key="2" v-if="check_user(['lengga', 'administrator'])">Payment (Approval 2)</v-tab>
		</v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1;" class="tabs">
			<v-tab-item :key="0" style="height: 100%;">
				<po v-if="dept1!=false || check_user('administrator')" :items-options="poItemsOptions" table-only></po>
			</v-tab-item>
			<v-tab-item :key="1" style="height: 100%;" v-if="check_user(['rdarmagiri', 'administrator'])">
				<payment key="payment1" type="1" :items-options="paymentItemsOptions(1)" table-only></payment>
			</v-tab-item>
			<v-tab-item :key="2" style="height: 100%;" v-if="check_user(['lengga', 'administrator'])">
				<payment key="payment2" type="2" :items-options="paymentItemsOptions(2)" table-only></payment>
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
            'po': App.ui+'dashboard/po.vue',
            'payment': App.ui+'dashboard/payment.vue',
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
			getDept: async function(){
				var self = this
				var response = await axios.get(App.model+'department', {
					params: {
						filter: {
							approval_1: App.userData.data[0].userid
						}
					}
				})
				var data = response.data
				if(data.data.length>0){
					self.poItemsOptions.filter.dept_id = data.data[0].id
					if(check_user('administrator'))
						delete self.poItemsOptions.filter.dept_id;
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
			if(check_user('administrator'))
				delete self.poItemsOptions.filter.dept_id;
			else
				self.getDept()
			App.title = 'Dashboard'
        }
    }
</script>