<template>
	<v-container class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		 <div style="display: flex;">
		     <!-- <v-select-paging @input="getData" clearable dense hide-details style="flex: 0 350px;" :formatter="['id', 'description']" :search-by="['description']" label="PR" pk="id" :options="options" :url="App.url+'bom/prsubledger'" v-model="pr_subledger"></v-select-paging> -->
		     <v-select-paging @input="getData" clearable dense hide-details style="flex: 0 350px;" :formatter="['pr_no', 'selectdesc']" :search-by="['selectdesc']" label="PR" pk="id" :options="options" :url="App.url+'bom/pr'" v-model="pr"></v-select-paging>
			<v-text-field hide-details dense v-model="desc" label="Subledger"></v-text-field>
			 <v-spacer></v-spacer>
			<v-btn small color="primary" outlined @click="toExcel">
				<v-icon>mdi-file-excel</v-icon>Excel
			</v-btn>
			<v-btn small color="primary" outlined @click="uploadDialog=true">
				<v-icon>mdi-file-excel</v-icon>Upload Project
			</v-btn>
		</div>
		<table v-if="items.length>0" class="default-table table-theme1 tablereport" style="border: 1 !important;">
			<thead>
				<tr>
					<th v-for="(item, index) in items[0]" :key="index" v-if="!['subledger_id'].includes(index)">{{index.replaceAll('_', ' ')}} </th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(item, index) in items" :key="index">
					<td v-for="(col, colIdx) in item" :key="colIdx" v-if="!['subledger_id'].includes(colIdx)">
						{{col}}
					</td>
				</tr>
			</tbody>
		</table>
		<v-action-dialog v-model="uploadDialog" @save="onUpload">
			<v-excel-reader v-model="files" hide-details dense style="flex: 0 300px;"></v-excel-reader>
		</v-action-dialog>
	</v-container>
</template>

<style scoped>
    .table-theme1 {
    	border: 1px solid black !important;
    	font-family: "lucida grande", Tahoma, verdana, arial, sans-serif;
    	font-size: 11px;
    	font-weight: bolder;
    }
    
    .table-theme1 thead tr th {
    	background-color: #DE847B !important;
    	border: 1px solid black !important;
    	color: #fff !important;
    	font-weight: bolder !important;
    }
    
    .table-theme1 thead tr:first-child th {
    	background-color: #963634 !important;
    	border: 1px solid black !important;
    	color: #fff !important;
    	font-weight: bolder !important;
    }
    
    .table-theme1.stripe tr:nth-child(even) td {
    	background-color: #EED6D3 /* #E6B8B7 */ !important;
    	border: 1px solid black !important;
    }
    
    .table-theme1.stripe tr:nth-child(odd) td {
    	background-color: #FFF !important;
    	border: 1px solid black !important;
    }
</style>

<script>
	module.exports = {
		name: '',
		creator: '',
		components: {
			/* 'document-form': 'url:ui/ewis/test/document_form.vue' */
		},
		props: {
			value: undefined,
			data: {
				type: Object
			}
		},
		data: function() {
			return {
				files: false,
				uploadDialog: false,
				uploadForm: [{
					type: 'excel'
				}],
				name: 'Monitoring PR PO',
				items: [],
				desc: null,
				pr: null,
				pr_subledger: null,
				options: {
					filter: {
						
					},
					filterType: {
					},
					filterCondition: {}
				}
			}
		},
		watch: {

		},
		computed: {
		},
		methods: {
            getData: async function() {
                var self = this
                try {
					var params = {
						filter: {
                            },
                            filterType: {
                            },
                            filterCondition: {},
                            limit: 50
					}
					if(self.pr_subledger){
						params.filter.subledger_id = self.pr_subledger
						params.filterType.subledger_id = '='
						params.limit = -1
					}
					if(self.pr){
						params.filter.pr_no = self.pr
						params.filterType.pr = '='
						params.limit = -1
					}
					if(self.desc){
						params.filter.subledger = self.desc
						params.filterType.subledger = '%'
					}
					
                    var res = await axios.get(App.url + 'pr/monitoring', {
                        params: params
                    })

                    self.items = res.data.data
                } catch (err) {
                    App.errorMsg(err)
                }
            },
		    toExcel: function() {
                var self = this
                var arr = [...document.querySelectorAll('.tablereport')]
				var tmp = [], i = 0
                self.loadingExcel = true
                tableToExcel(arr, 'Simulation', {
                    style: true
                }, function() {
                    self.loadingExcel = false
                })
            },
			onUpload: async function(){
				var self = this
				try {
					var files = self.files.filter(val=>val.state == 'visible')
					if(files.length > 0){
						var file = files[0]
						var header = [], item = [], del = []
						file.data.slice(2).map((val, i)=>{
							var v = val.slice(1)
							if(v[3]==null){
								header.push({"kode": v.slice(0,3).filter(x=>(x||'').trim()).join('/'), "item_no": v[4], "description": v[5]||''})
								del.push(`'${v.slice(0,3).filter(x=>(x||'').trim()).join('/')}'`)
							}
							else{
								item.push({"parent": v.slice(0,3).filter(x=>(x||'').trim()).join('/'), "item_no": v[4], "description": v[5]})
							}

						})

						var res = await axios.post(App.url+'pr/monitoring/upload', {
							header: JSON.stringify(header),
							item: JSON.stringify(item),
							del: JSON.stringify(del),
						})
						if(!res.data.status) throw res.data
						App.successMsg()
					}
				} catch (error) {
					App.errorMsg(error)
				}
			}
		},
		mounted: function() {
            this.getData()
		}
	}

</script>
