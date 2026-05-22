<template>
	<div>
		<form-template :headers="formModel"></form-template>
		<v-row><v-spacer></v-spacer><v-btn small color="primary" outlined @click="save">Save</v-btn></v-row>

        <v-overlay v-model="loading" style="z-index: 1000"><b>Loading...</b></v-overlay>
	</div>
</template>

<style>
</style>

<script>
	module.exports = {
		name: 'role-access',
		components: {
            'form-template': 'url:ui/admin/form-template.vue',
        },
		props: {
			role: null
		},
		data: function () {
			return {
				loading: false,
				formModel: [{
                    "text": "Role",
                    "value": "role_id",
					"formWidth": "calc(50% - 8px)",
                    "align": "start",
                    "sortable": true,
                    "filter": true,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "data_value": [],
                    "url": App.url + "planner/role",
					default: null,
					data: null,
                    'searchby': ['name'],
                    'formatter': function(val) {
                        return {
                            text: val.name,
                            value: val.id
                        }
                    },
                    "options": {
                        filter: {},
                        filterType: {},
                        filterCondition: {}
                    },
                    paging: true,
                    page: 0,
                    limit: 10,
					input: function(item){
						var self = App.$get('role-access')
						self.loadData(item)
					}
					
                }/*,{
                    "text": "User",
                    "value": "username",
					"formWidth": "calc(50% - 8px)",
                    "align": "start",
                    "sortable": true,
                    "filter": true,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "data_value": [],
                    "url": App.url + "admin/users",
                    'searchby': ['username', 'email'],
                    'formatter': function(val) {
                        return {
                            text: val.username + ', ' + (val.email || '-'),
                            value: val.username
                        }
                    },
                    "options": {
                        filter: {},
                        filterType: {},
                        filterCondition: {
                            email: 'OR',
                            username: 'OR'
                        }
                    },
                    paging: true,
                    page: 0,
                    limit: 10
                }*/,{
					text: 'Add Task',
					value: 'add_task',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				},{
					text: 'Edit Task',
					value: 'edit_task',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				},{
					text: 'Delete Task',
					value: 'delete_task',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				},{
					text: 'Add Project',
					value: 'add_project',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				},{
					text: 'Edit Project',
					value: 'edit_project',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				},{
					text: 'Add Note',
					value: 'add_note',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				},{
					text: 'Add Reference',
					value: 'add_reference',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				},{
					text: 'Add Project Member',
					value: 'add_project_member',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				},{
					text: 'Add Task Member',
					value: 'add_task_member',
					type: 'switch',
					"formWidth": "calc(50% - 8px)",
					data_value: [0, 1]
				}]
			}
		},
        computed: {
            formObj: function() {
                var self = this,
                    obj = {}
                self.formModel.map(function(val) {
                    if (val.value)
                        obj[val.value] = val
                })
                return obj
            },
		},
		watch: {
			formModel: {
				immediate: true,
				deep: true,
				handler: function(){
					
				}
			},
			role: {
                immediate: true,
				handler: function(val){
					this.formModel[0].data = val
					this.loadData(this.formModel[0])
				}
			}
		},
		methods: {
			save: async function(){
				var self = this
				var params = {}
				self.formModel.map(function(val){
					if(val.data!=undefined){
						params[val.value] = val.data
					}
				})

				try {
					self.loading = true
					var res = await axios.post(App.url + 'planner/access', params)
					if (!res.data.status) throw res.data
					App.successMsg()
				} catch (err) {
					App.errorMsg(err)
				}
				self.loading = false
			},
			loadData: async function(item){
				var self = this
				self.loading = true
				var res = await axios.get(App.url + 'planner/access', {
					params: vTableParam({
						filter: {
							role_id: item.data
						},
						limit: -1
					})
				})

				self.formModel.map(val=>{
					if(val.value!='role_id')
						val.data = 0
				})

				res.data.data.map(val=>{
					self.formObj[val.name].data = 1
				})
				var tmp = App.updateObject(self.formModel)
				self.formModel = []
				self.$nextTick(()=>{
					self.formModel = tmp
				})
				self.loading = false
			}
		},
		mounted: function () {
			
		}
	}
</script>