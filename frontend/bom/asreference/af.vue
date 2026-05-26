<template>
	<v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
            <template v-slot:menu-after-filter>
                <v-btn color="primary" outlined small @click="openRefItems = true" :disabled="!selected">
                    AsReference Items
                </v-btn>
                <v-btn color="success" outlined small :disabled="AllowAskApproval" @click="askApproval">Ask Approval</v-btn>
            </template>
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>

			<!-- <template v-slot:expanded-item="props">
				<td ref="expanded" :colspan="props.headers.length" style="height: auto;" :key="props.item[itemKey]">
					
				</td>
			</template> -->
			<!-- 
			<template v-slot:expanded-item="props">
				<td :colspan="props.headers.length" style="height: auto;">
				</td>
			</template>
			 -->
			
		
			<template v-slot:item.status="props">
			    {{approvedStatus(props.item.approved)}}
			</template>
			 
			<!-- 
			<template v-slot:append-dialog-add>>
			</template>
			 -->
			<!-- 
            <template v-slot:prepend-menu>
            </template>
			 -->
			<!-- 
            <template v-slot:menu-after-edit>
            </template>
			 -->
			<!-- 
            <template v-slot:append-menu>
            </template>
			 -->
            
        <v-action-dialog :actions="false" v-model="openRefItems" title="Surat Permohonan Item" min-height="75%" fullscreen>
            <asreferenceitem-form :key="selected.id" :sel="processData" name="" :data="dataid"></asreferenceitem-form>
        </v-action-dialog>
        <v-action-dialog v-model="dialogNote" title="Approval Note" min-height="75%" @save="approve">
            <v-textarea label="Note" v-model="approval_note"></v-textarea>
        </v-action-dialog>
		</v-template>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: '',
		creator: '',
		components: {
			'asreferenceitem-form': 'url:ui/bom/asreference/asreferenceitem.vue',
		},
		props: {
			value: undefined,
			data: {
				type: Object
			},
			tableOnly: {
				type: Boolean,
				default: false
			},
			tableFixedHeader: {
				type: Boolean,
				default: true
			},
			disableTable: {
				type: Boolean,
				default: false
			},
			activeColumn: {
				default: "flag"
			},
			showExpand: {
				type: Boolean,
				default: false
			},
            hideApproval: {
                type: Boolean,
                default: false
            },			
			singleExpand: {
				type: Boolean,
				default: true
			},
			itemsOptions: {
				type: Object,
				default: {
					filter: {},
					filterType: {}
				}
			}
		},
		data: function() {
			return {
				name: 'asreference',
				itemKey: 'id',
				url: 'bom/asreference',
				loading: false,
				approval_note: '',
				dialogNote: false,
				headers: [{
					"text": "Id",
					"value": "id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Reference No",
					"value": "reference_no",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "int",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Title",
					"value": "title",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Status",
					"value": "status",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Note",
					"value": "note",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Reference Date",
					"value": "reference_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Created By",
					"value": "created_by_name",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Created Date",
					"value": "created_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"searchby": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}],
				//row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
				selected: false,
				//berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
				selectedAll: [],
                dataid: {},
				isInDom: false,
				isInViewport: false,
                processData: {},
                openRefItems: false,
			}
		},
		watch: {
            dialogItemGroup: function(val){
                if(!val)
                    this.$refs.template.getItems()
            },
		},
		computed: {
			headersObj: function() {
				var tmp = {},
					self = this
				Object.keys(self.headers).map(key => {
					var val = self.headers[key]
					tmp[val.value] = val
				})
				return tmp;
			},
            AllowAskApproval: function(){
                var self = this
                // Kembalikan true jika tidak valid untuk diproses
                if (self.selected.approved < '0' || !self.selected) {
                    return true;
                }
                if (self.selected.approved == '0' || self.seleted.approved == '-1'){
                    return false;
                    
                }
                console.log(self.selected.approved)
                return true
            },
		},
		methods: {
		    openApprove: function(){
		        var self = this
		        this.approval_note = ''
		        if (self.selected.approved == 1 || self.selected.approved == -2){
		            this.dialogNote = true
		        }else this.approve()
		    },
		    approve: async function(){
		        var self = this
                //cek status surat permohonan
                const status = self.selected.approved
		        let confMssg = 'are you sure want to approve this surat permohonan?'
		        if(status == -1 || status == -2)
		            confMssg = 'are you sure want to approve cancel this surat permohonan?'
		      
		        const userConfirmed = confirm(confMssg)
		        if(!userConfirmed) return
		        self.loading = true
		        let approved
		        if(status > 0)
		            approved = (status == 1) ? 1 : 2
		        else if (status < 0)
		            approved = (status == -2) ? -3 : -4
		            
		        const params = {
		            approved,
		            pk: self.selected.id
		        }
		        if(status == 1 || status == -2){
		            if (self.approval_note.trim() == '') {
		                App.errorMsg("Note cannot be empty!")
		                return false
		            }
		            params.approval_note = self.approval_note
		        }
		        try{
		            const r = await axios.put(App.url + 'bom/asreference', params)  
		            if(!r.data.status)
		                App.errorMsg(r.data)
		            else {
		                self.$refs.template.getItems()
		                App.successMsg()
		            } 
		        }
		        catch(err){
		            App.errorMsg('error occured')
		        }
		            
		 
		    },
		    askApproval: async function(val) {
		        var self = this
		        var c = confirm("Are you sure");
		        if (!c) return true;
		        switch (self.selected.approved){
		            case '0' :
    		            var r = await axios.put(App.url+'bom/asreference',{
    		                approved: 1,
    		                pk: self.selected.id,
    		                askapproval_note: self.askapproval_note
    		            })
    		            if(!r.data.status)
    		                App.errorMsg(r.data)
    		            else{
    		                self.$refs.template.getItems()
    		                App.successMsg()
    		            }
		            break;
		            case '1' :
		                App.errorMsg("telah ada perimntaan asking appproval 1!")
		            break;
		            case '3' :
		                App.errorMsg("Telah di approve!")
		            break;
		            case '-1' :
		                App.errorMsg("Telah di Reject!")
		            break;
		            default:
		                App.errorMsg("Permintaan tidak dikenali!")
		            break;
		        
		        }
		        self.dialogAskApprovalNote = false;
		    },
            approvedStatus(val) {
                switch (val) {
                    case '0': return 'New';
                    case '1': return 'Asking Approval 1';
                    case '2': return 'Asking Approval 2';
                    case '3': return 'Approved';
                    default: return val;
                }
            },

            onSelectedRow: function(val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                    self.processData = {}
                    self.dataid = {}
					self.disableDelete = false
					self.disableEdit = false
                } else {
                    self.selected = val
					var email = (val.email||"").split(';').map(val=>{
						return val.trim()
					}).filter(val=>{
						return val.trim()!=''
					})
					self.toSelected = [email[0]]
					self.ccSelected = email.slice(1)
					if(App.userData.data[0].email)
						self.ccSelected.push(App.userData.data[0].email)
                    self.processData = {
                        //purchase_order_id: val.id,
                        as_reference_id: val.id
                    }
					
					self.txtApproval = 'Ask Approval'
					if(val.approved == 0)
						self.txtApproval = 'Ask Approval'
                        self.disableEdit = false
					/* if(val.approved == 2)
						self.txtApproval = 'Ask Approval 2' */
					if(val.approved == 2 || val.approved == '1'){
						self.disableDelete = true
						self.disableEdit = true
					}
					if(val.approved < 0){
						self.disableEdit = true
					}
					if(val.approved > 2){
						self.disableEdit = false
					}
					else{
						// self.disableDelete = false
						// self.disableEdit = false
					}
                    self.dataid = {
                        as_reference_id: val.id
                    }
                    self.revData = {
						filter: {
							purchase_order_id: val.id,
							flag: 1
						}
                    }
                }
            },
                        openApprove: function() {
                var self = this
                this.approval_note = ''
                if (self.selected.approved == 1 || self.selected.approved == -2) {
                    this.dialogNote = true
                } else {
                    this.approve()
                }
            },
            approve: async function() {
                var self = this
                if(self.selected.approved == -2 || self.selected.approved == -3)
                    var c = confirm('Are you sure want to Approve Cancel this Surat Permohonan?')
                else
                    var c = confirm('Are you sure want to Approve this Surat Permohonan?')
                if (c) {
                    self.loading = true
                    if(self.selected.approved > 0)
                        var approved = self.selected.approved == 1 ? 2 : 3
                    else if(self.selected.approved < 0)
                        var approved = self.selected.approved == -2 ? -3 : -4
                    var params = {
                        approved: approved,
                        pk: self.selected.id
                    }
                    if (self.selected.approved == 1 || self.selected.approved == -2) {
                        if (self.approval_note.trim() == '') {
                            App.errorMsg("Note cannot be empty!")
                            return false
                        }
                        params.approval_note = self.approval_note
                    }
                    var r = await axios.put(App.url + 'bom/purchaseorder', params)
                    if (!r.data.status)
                        App.errorMsg(r.data)
                    else {
                        self.$refs.template.getItems()
                        App.successMsg()
                    }
                }
                self.loading = false
                self.dialogNote=false
            },
            askDraft: async function() {
                var self = this
				var c = confirm('Are you sure want to Ask for Draft PO?')
                if (c) {
                    self.loading = true
					var params = {
						approved: 5,
						pk: self.selected.id
					}
					params.ask_draft_note = self.ask_draft_note
					var r = await axios.put(App.url + 'bom/purchaseorder', params)
					if (!r.data.status)
						App.errorMsg(r.data)
					else {
						self.$refs.template.getItems()
						App.successMsg()
					}
				}
                self.loading = false
                self.dialogAskDraft=false
            },
            approveDraft: async function() {
                var self = this
				var c = confirm('Are you sure want to Approve for Draft PO?')
                if (c) {
                    self.loading = true
					var params = {
						approved: 6,
						pk: self.selected.id
					}
					var r = await axios.put(App.url + 'bom/purchaseorder', params)
					if (!r.data.status)
						App.errorMsg(r.data)
					else {
						self.$refs.template.getItems()
						App.successMsg()
					}
				}
                self.loading = false
            },
            reject: async function() {
                var self = this
				var c = confirm('Are you sure want to reject this PO?')
                if (c) {
                    self.loading = true
					var params = {
						approved: -1,
						pk: self.selected.id
					}
					if(self.selected.approved==2 || self.selected.approved==-3)
						params.reject_note2 = self.reject_note
					if(self.selected.approved==1 || self.selected.approved==-2)
						params.reject_note1 = self.reject_note
					var r = await axios.put(App.url + 'bom/purchaseorder', params)
					if (!r.data.status)
						App.errorMsg(r.data)
					else {
						self.$refs.template.getItems()
						App.successMsg()
					}
				}
                self.loading = false
                self.dialogReject=false
            },
            openRefItems: function() {
                var self = this
                self.openRefItems = true
            },
			onSelectedRowAll: function(val) {
				var self = this
				self.selectedAll = val
			},
			onVisible: function(isVisible, e) {
				var self = this
				self.isInViewport = !!isVisible
				self.isInDom = !!e.target.isConnected
			},
		},
		mounted: function() {

		}
	}

</script>
