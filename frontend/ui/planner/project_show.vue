<template>
	<div :style="`width: ${parseInt(mainWidth)+300}px; min-height: 100%; margin-left: auto; margin-right: auto; display: flex; background: #FFF; flex-wrap: wrap; align-content: baseline;`">
		<div class="flex flex-column planner-container" :style="`flex: 1 ${mainWidth};  overflow: auto; background: #FFF !important;`">
			<v-card elevation="0" class="card-yellow card-planner">
				<v-card-title style="font-weight: bold; padding-top: 8px; padding-bottom: 0; font-size: 10px; height: auto; background: rgba(251, 190, 1, 0.1); " v-if="parent != false && parent != null"><a :href="location.href.split('/').slice(0,5).concat(['planner', 'planner', 'project', parent.id]).join('/')">PROJ-{{(parent.id||0).toString().padStart(4, 0)}}-{{parent.name}}</a></v-card-title>
				<v-card-title class="card-header" :style="(data.parent_id == null ? 'padding-top: 8px;' : 'padding-top: 0;') "><div>B{{(data.created_date||'').substr(2,2)}}-{{(data.id||0).toString().padStart(4, 0)}}-{{data.name}}</div><v-spacer></v-spacer><v-btn @click="dialogProject=true" small text class="btn-bold" color="orange" v-if="allow(5)"><v-icon small left>mdi-pencil</v-icon> Edit</v-btn></v-card-title>
				<v-card-text style="padding-bottom: 8px;" class="markdown-body" v-html="App.md.render(data.description||'')">
				</v-card-text>
			</v-card>
			<!-- <div style="flex: 0" class="text-bold">
				Members
				<v-btn small text color="error" class="btn-bold" @click="openAddMember">
					<v-icon small left>mdi-plus</v-icon>Add
				</v-btn>
			</div>
			<div style="flex: 0 50px; display: flex; flex-wrap: wrap; gap: 16px; justify-content: left; margin-top: 0 !important;">
				<v-card elevation="0" class="card-blue section-card" v-for="(item, index) in members" :key="index">
					<div style="width: 100%; font-weight: bold; text-align: left; margin-bottom: 0;">
						{{index}}
					</div>
					<div class="memberlist">
						<v-chip close @click:close="deleteMember(item2.id, item2.name)" href v-for="(item2, index2) in item" :key="index2" outlined>{{item2.name}}</v-chip>
						
					</div>
				</v-card>
			</div> -->

			<page-task :hide-add-button="!allow(1)" :hide-edit-button="!allow(2)" :hide-delete-button="!allow(3)" :items-options="projectOptions" v-model="projectOptions" :table-only="false" style="margin-top: 0 !important;" table-height="fit-content">
			</page-task>
		</div>
		<div :style="'flex: 0 '+(['sm', 'xs'].includes($breakpoint) ? '100%; padding-left: 8px;' : '300px;')+' display: flex; flex-direction: column; overflow: hidden; padding-right: 8px;'" class="side-content">
			<v-card class="section-card section-card-12" elevation="0" outlined :style="'max-width: 100%; padding: 0 !important; box-sizing: border-box;'+(['sm', 'xs'].includes($breakpoint) ? 'margin-right: 0 !important;' : '')">
				<div class="flex" style="background: #f9f9fa; width: 100%; font-weight: bold; text-align: left; margin-bottom: 4px; align-items: center; padding: 4px 8px 4px 16px; border-bottom: 1px solid #d6d7d8">
					<span style="font-size: 14px !important;">Members</span> <v-spacer></v-spacer><v-btn small text color="error" class="btn-bold" @click="openAddMember" v-if="allow(9)">
						<v-icon small left>mdi-account-plus-outline</v-icon>Add
					</v-btn>
				</div>
				<div style="padding: 8px;">
					<div style="flex: 0 50px; display: flex; flex-wrap: wrap; gap: 4px; justify-content: left; margin-top: 0 !important;">
						<v-card elevation="0" class="section-card" v-for="(item, index) in members" :key="index" style="margin-bottom: 0; flex: 1 100%;">
							<div style="width: 100%; font-weight: bold; text-align: left; margin-bottom: 0;">
								{{index}}
							</div>
							<div class="memberlist">
								<template v-for="(item2, index2) in item" :key="index2">
									<v-chip v-if="index != 'Author'" class="member-chip" close @click:close="deleteMember(item2.id, item2.name)" href outlined>{{item2.name}}</v-chip>
									<v-chip v-else class="member-chip" href outlined>{{item2.name}}</v-chip>
								</template>
								<!-- <a href v-for="(item2, index2) in item" :key="index2">{{item2.name}}</a> -->
							</div>
						</v-card>
					</div>
					<div v-if="Object.keys(members).length == 0" class="info" style="height: 30px; align-items: center; display: flex; justify-content: center;">
						<div class="subtitle">No Members added!</div>
					</div>
				</div>
			</v-card>
		</div>

		<v-action-dialog v-model="dialogAddMember" title="Add Member" @save="addMember" :disable-save="!validAddMember" :save-loading="loading">
			<form-template :headers="formAddMember" :valid.sync="validAddMember" ref="formAddMember"></form-template>
		</v-action-dialog>

		<v-action-dialog v-model="dialogTask" :title="data.name" fullscreen :actions="false">
			<page-task :items-options="projectOptions" v-model="projectOptions" :table-only="false"></page-task>
		</v-action-dialog>

		<v-action-dialog v-model="dialogProject" title="Edit Project" @save="editProject" :disable-save="!valid" :save-loading="loading">
			<form-template :headers="headers" :valid.sync="valid" ref="headers"></form-template>
		</v-action-dialog>

		<v-overlay v-model="loading" style="z-index: 1000"><b>Loading...</b></v-overlay>
	</div>
</template>

<style scoped>

	div.info{
		background-color: #fff !important;
		background: #fff !important;
	}
	.member-chip{
		border: 0 !important;
    	height: auto;
		padding: 0 4px !important;
	}
	.member-chip .v-icon *, .member-chip .v-icon{
		color: #ff5252 !important;
	}
	.text-vertical-centered{
		display: flex;
    	align-items: center;
	}
	.low{
		background-color: #E5F3FE !important;
		color: #0287FF !important;
	}
	.low .mdi:before{
		color: #0287FF !important;
		font-size: 16px !important;
	}
	.normal{
		background-color: #EBF9EA !important;
		color: #2FC32F !important;
	}
	.normal .mdi:before{
		color: #2FC32F !important;
		font-size: 16px !important;
	}
	.important{
		background-color: #FFFBE5 !important;
		color: #FBBE01 !important;
	}
	.important .mdi:before{
		color: #FBBE01 !important;
		font-size: 16px !important;
	}
	.urgent{
		background-color: #FEE7E4 !important;
		color: #F9391F !important;
	}
	.urgent .mdi:before{
		color: #F9391F !important;
		font-size: 16px !important;
	}
	.side-content > *{
		width: 100%;
		margin-right: 8px;
		margin-top: 8px;
	}
    .btn-bold {
        font-weight: bold
    }

    .btn-bold *:before {
        font-weight: bold
    }

    .memberlist>a {
        margin-right: 8px;
    }

    .memberlist>a:last-child {
        margin-right: 0 !important;
    }

	.memberlist .v-chip {
		border: 0;
		line-height: 1;
	}

	.memberlist .v-chip .mdi {
		font-size: 18px !important;
		color: #ff5252 !important;
	}

    .planner-container {
        background: #F2F5F5 !important;
    }

    .planner-container>* {
        margin: 8px !important;
    }
</style>

<script>
    module.exports = {
        name: '',
		components: {
            'form-template': 'url:ui/admin/form-template.vue',
			'page-task': 'url:ui/planner/task.vue',
        },
        data: function() {
            return {
                projectId: -1,
                loading: false,
                parent: false,
                valid: false,
                dialogProject: false,
                dialogTask: false,
                dialogAddMember: false,
                validAddMember: false,
				headers: [{
					"text": "Project Name",
					"value": "name",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
                    "formWidth": "100%",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"paging": true,
					"page": 0,
					"limit": 10,
					"data_value": [],
					"url": "",
					"searchby": [],
					"formatter": [null, null],
					"pk": null,
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					}
				}, {
					"text": "Desciption",
					"value": "desciption",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
                    "formWidth": "100%",
					"type": "text",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"paging": true,
					"page": 0,
					"limit": 10,
					"data_value": [],
					"url": "",
					"searchby": [],
					"formatter": [null, null],
					"pk": null,
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					}
				}, {
					"text": "Parent Project",
					"value": "parent_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"formWidth": "100%",
					"type": "int",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
				}],
                formAddMember: [{
                    "text": "User",
                    "value": "username",
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
                            text: val.name,
                            value: val.username
                        }
                    },
                    "options": {
                        filter: {
							//groups: ';dept-seg;'
						},
                        filterType: {
							//groups: '%'
						},
                        filterCondition: {
                            email: 'OR',
                            username: 'OR',
                            //groups: 'and'
                        }
                    },
                    paging: true,
                    page: 0,
                    limit: 10
                }, {
                    "text": "Role",
                    "value": "role_id",
                    "align": "start",
                    "sortable": true,
                    "filter": true,
                    "divider": false,
                    "class": "",
                    "width": "auto",
                    "type": "list",
                    "data_value": [],
                    "url": App.url + "planner/role",
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
                    limit: 10
                }],
                data: {},
                members: {
                },
				projectOptions: {
					project_id: -1
				},
				access: {project: {}, task: {}}
            }
        },
		computed: {
			mainWidth: function(){
				var self = this
				if(self.$breakpoint == 'xl'){
					return '1264px'
				}
				if(self.$breakpoint == 'lg'){
					return '960px'
				}
				if(self.$breakpoint == 'md'){
					return '600px'
				}
				if(self.$breakpoint == 'sm'){
					return '100%'
				}
				else if(self.$breakpoint == 'xs'){
					return '100%'
				}
				else
					return 
			}
		},
		watch: {
			dialogAddMember: function(val){
				var self = this
				if(val){
					self.$nextTick(()=>{
						self.$refs.formAddMember.$refs.form.reset()
					})
				}
			}
		},
        methods: {
			nameFormat: function(name){
				var name = name.split(/\s+/)
				return name.map((val, i)=>{
					if(i!=name.length-1)
						return val.trim()[0]
					return val
				}).join('. ')
			},
			openAddMember: function(){
				var self = this
				self.dialogAddMember = true
			},
            editProject: async function() {
				var self = this
                return new Promise(async (resolve, reject) => {
                    try {
						self.loading = true
                        var res = await axios.put(App.url + 'planner/project', {
                            name: self.headers[0].data,
                            description: self.headers[1].data,
                            parent_id: self.headers[2].data == '' ? null : self.headers[2].data,
							pk: self.projectId
                        })
                        if (!res.data.status) throw res.data
                        App.successMsg()
						App.loadWithBase('/planner/planner/project/'+self.projectId)
                    } catch (err) {
                        App.errorMsg(err)
                    }
					await self.getData()
					self.loading = false
					self.dialogProject = false
                    resolve(true)
                });
            },
            addMember: function() {
				var self = this
                return new Promise(async (resolve, reject) => {
                    try {
						self.loading = true
                        var res = await axios.post(App.url + 'planner/project_member', {
                            username: self.formAddMember[0].data,
                            role_id: self.formAddMember[1].data,
							project_id: self.projectId
                        })
                        if (!res.data.status) throw res.data
                        App.successMsg()
                    } catch (err) {
                        App.errorMsg(err)
                    }
					self.loading = false
					self.getMember()
                    resolve(true)
                });
            },
            deleteMember: function(id, name) {
				var self = this
				var c = confirm("Are you sure to delete "+name+" as member")
				if(c)
					return new Promise(async (resolve, reject) => {
						try {
							self.loading = true
							var res = await axios.delete(App.url + 'planner/project_member', {
								params: {
									id: id
								}
							})
							if (!res.data.status) throw res.data
							App.successMsg()
						} catch (err) {
							App.errorMsg(err)
						}
						self.loading = false
						self.getMember()
						resolve(true)
					});
            },
            getProject: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/project', {
                            params: vTableParam({
                                filter: {
                                    id: self.projectId
                                },
								limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
                        if (res.data.data.length == 0) throw 'Project not found!'
                        self.data = res.data.data[0]
						self.headers[0].data = self.data.name
						self.headers[1].data = self.data.description
						self.headers[2].data = self.data.parent_id
						if(self.data.parent_id!=null)
							self.getParent()
						else
							parent = null
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getParent: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/project', {
                            params: vTableParam({
                                filter: {
                                    id: self.data.parent_id
                                },
								limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
                        if (res.data.data.length == 0) throw 'Parent Project not found!'
                        self.parent = res.data.data[0]
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
            getMember: function() {
                var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/project_member', {
                            params: vTableParam({
                                filter: {
                                    project_id: self.projectId
                                },
								limit: -1
                            })
                        })
                        if (!res.data.status) throw res.data
						var tmp = {}
						res.data.data.map(function(val){
							if(tmp[val.role_name]==undefined)
								tmp[val.role_name] = []
							tmp[val.role_name].push(val)
						})
                        self.members = tmp
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
            },
			getData: async function(){
				var self = this
				try {
					self.loading = true
					var self = this, proms = []
					proms.push(self.getAccess())
					proms.push(self.getProject())
					proms.push(self.getMember())
					await Promise.all(proms)
				} catch (err) {
								
				}
				self.loading = false
			},
			getAccess: async function(){
				var self = this
                return new Promise(async (resolve, reject) => {
                    try {
                        var res = await axios.get(App.url + 'planner/access/access', {
                            params: {project_id: self.projectId}
                        })
						self.access = res.data
                    } catch (err) {
                        App.errorMsg(err)
                    }
                    resolve(true)
                });
			},
			allow: function(check){
				var self = this, ok = false
				if(self.access.project.access){
					if(self.access.project.access.includes('$author$')){
						if([1, 5, 6, 7, 8, 9, 10].includes(Number(check))){
							ok = true
						}
					}
					else{
						if(self.access.project.access.includes(check.toString())){
							ok = true
						}
					}
				}

				return ok
			}
        },
        mounted: function() {
            var self = this
            self.projectId = location.hash.split('/')[4]
			self.projectOptions = {
				project_id: Number(self.projectId)
			}
			self.getData()
        }
    }
</script>