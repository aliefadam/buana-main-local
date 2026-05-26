<template>
    <v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-row style="flex: 0">
            <v-col>
                <v-switch label="levensthein" hide-details filled dense v-model="levensthein" style="margin: auto; width: 100%; flex: 0; width: 140px"></v-switch>
            </v-col>
			<v-col>
				<v-text-field
					label="Similarity"
					v-model="similarity"
				></v-text-field>
			</v-col>
        </v-row>
        <v-row style="flex: 0">
            <v-col cols="6">
                File 1
            </v-col>
            <v-col cols="6">
                File 2
            </v-col>
        </v-row>
        <v-row style="flex: 0">
            <v-col cols="6">
                <v-excel-reader v-model="files1" hide-details dense style="flex: 0 300px;"></v-excel-reader>
            </v-col>
            <v-col cols="6">
                <v-excel-reader v-model="files2" hide-details dense style="flex: 0 300px;"></v-excel-reader>
            </v-col>
        </v-row>
        <v-row style="flex: 0">
            <v-col cols="12">
                <div style="color: red">{{error1}}</div>
            </v-col>
            <!-- <v-col cols="6">
                <div style="color: red">{{error2}}</div>
            </v-col> -->
        </v-row>
        <v-row style="flex: 0">
            <v-col cols="6">
                <v-row>
                    <v-col>
                        <v-select auto-select-first hide-details dense filled v-model="sheet1select" label="Sheet" :items="sheet1"></v-select><br/>
                        <v-select auto-select-first hide-details dense filled v-model="sheet1filter" label="Filter" :items="availableFilter"></v-select>
                    </v-col>
                    <v-col>
                        <v-select auto-select-first hide-details dense filled v-model="column1select" label="Column" :items="column1"></v-select>
                    </v-col>
                    <v-col>
                        <v-textarea auto-select-first hide-details dense filled
							label="Formatter"
							v-model="formatter1"
							textarea
						></v-textarea>
                    </v-col>
					<!-- <v-col>
						<v-btn small color="primary" outlined>Add</v-btn>
					</v-col> -->
                </v-row>
            </v-col>
			
			<v-col cols="6">
                <v-row>
                    <v-col>
                        <v-select auto-select-first hide-details dense filled v-model="sheet2select" label="Sheet" :items="sheet2"></v-select><br/>
                        <v-select auto-select-first hide-details dense filled v-model="sheet2filter" label="Filter" :items="availableFilter"></v-select>
                    </v-col>
                    <v-col>
                        <v-select auto-select-first hide-details dense filled v-model="column2select" label="Column" :items="column2"></v-select>
                    </v-col>
                    <v-col>
                        <v-textarea auto-select-first hide-details dense filled
							label="Formatter"
							v-model="formatter2"
							textarea
						></v-textarea>
                    </v-col>
					<!-- <v-col>
						<v-btn small color="primary" outlined>Add</v-btn>
					</v-col> -->
                </v-row>
            </v-col>
        </v-row>
		<b>Summary</b>
        <v-row style="flex: 0">
            <v-col cols="6">
                <v-row>
                    <v-col>
                        <v-select auto-select-first hide-details dense filled v-model="sum1select" label="Column" :items="column1"></v-select>
                    </v-col>
					<v-col>
						<v-btn small color="primary" outlined @click="sum1.push(sum1select)">Sum Column</v-btn>
					</v-col>
					<v-col v-if="sum1.length>0">
						<v-chip close v-for="(item, index) in sum1" :key="index" @click.close="sum1.splice(index,1)">{{column1.filter(val=>val.value==sum1)[0].text}}</v-chip>
					</v-col>
                </v-row>
            </v-col>
			
			<v-col cols="6">
                <v-row>
                    <v-col>
                        <v-select auto-select-first hide-details dense filled v-model="sum2select" label="Column" :items="column2"></v-select>
                    </v-col>
					<v-col>
						<v-btn small color="primary" outlined @click="sum2.push(sum2select)">Sum Column</v-btn>
					</v-col>
					<v-col v-if="sum2.length>0">
						<v-chip close v-for="(item, index) in sum2" :key="index" @click.close="sum2.splice(index,1)">{{column2.filter(val=>val.value==sum2)[0].text}}</v-chip>
					</v-col>
                </v-row>
            </v-col>
        </v-row>
        <v-row style="flex: 0">
            <v-col cols="6" v-if="column1select!=null">
				<v-row v-for="(item, index) in 6" :key="index">
					<v-col>{{files1[sheet1select].data[item+1][column1select]}}</v-col>
				</v-row>
			</v-col>
            <v-col cols="6" v-if="column2select!=null">
				<v-row v-for="(item, index) in 6" :key="index">
					<v-col>{{files2[sheet2select].data[item+1][column2select]}}</v-col>
				</v-row>
			</v-col>
		</v-row>
		<b>Filter Formatter</b>
        <v-row style="flex: 0">
            <v-col cols="6">
				<v-text-field
					label="Formatter Name"
					v-model="filterFormatterName"
				></v-text-field>
				<v-textarea auto-select-first hide-details dense filled
					label="Filter Formatter"
					v-model="filterFormatterText"
					textarea
				></v-textarea>
				<v-btn small color="primary" outlined @click="filterFormatter[filterFormatterName] = filterFormatterText; saveFilterFormatter();">Add</v-btn>
			</v-col>
            <v-col cols="6">
				<template v-for="(item, index) in filterFormatter" :key="index">
					<v-chip close @click:close="removeFilterFormatter(index)" @click="filterFormatterName=index; filterFormatterText = item">{{index}} <v-switch :ref="'filter-'+index" @click.stop="function(){toggleSelect('filter-'+index, index)}" style="margin: 0;" hide-details dense></v-switch></v-chip>
				</template>
			</v-col>
		</v-row>
		<b>Selected Formatter</b>
		<v-row>
			<v-col>
				<template v-for="(item, index) in selectedFilter">
					<v-chip>{{item}}</v-chip>
				</template>
			</v-col>
		</v-row>
		<b>Regex</b>
		<v-row>
			<v-col>
				<v-text-field
					label="Regex"
					v-model="reg"
					style="max-width: 140px;"
				></v-text-field>
				<v-switch label="Filter Output Using Regex" hide-details filled dense v-model="filterRegex" style="margin: auto; width: 100%; flex: 0; width: 140px"></v-switch>
			</v-col>
		</v-row>
		<b>Export</b>
        <v-row style="flex: 0">
			<v-col class="inlineboxchild">
				<v-btn small color="primary" outlined @click="createExcel('notListedInFile2')">List if not in file 2</v-btn>
				<v-btn small color="primary" outlined @click="createExcel('file2listedInBoth')">File 2 listed in both files</v-btn>
				<v-btn small color="primary" outlined @click="createExcel('file1listedInBoth')">File 1 listed in both files</v-btn>
				<v-btn small color="primary" outlined @click="createExcel('notListedInFile1')">List if not in file 1</v-btn>
				<v-btn small color="primary" outlined @click="createExcel('regex1')">Find regex in File 1</v-btn>
				<v-btn small color="primary" outlined @click="createExcel('regex2')">Find regex in File 2</v-btn>
				<v-btn small color="primary" outlined @click="createExcel('notregex1')">Find regex not in File 1</v-btn>
				<v-btn small color="primary" outlined @click="createExcel('notregex2')">Find regex not in File 2</v-btn>
			</v-col>
		</v-row>
		<table class="default-table" ref="tbl" v-if="table.length > 0">
			<thead>
				<tr>
					<td v-for="(item, index) in files2[sheet2select].data[0]" :key="index">
						{{item}}
					</td>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(item, index) in table" :key="index">
					<td v-for="(col, colidx) in item" :key="colidx">
						{{col}}
					</td>
				</tr>
			</tbody>
		</table>
		<v-spacer></v-spacer>
		<v-overlay v-model="overlay">Loading...</v-overlay>
    </v-container>
</template>

<style scoped>
	.inlineboxchild>*{
		display: inline-block;
	}
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
                reg: '',
                error1: '',
                error2: '',
                overlay: false,
                levensthein: false,
                files1: null,
                files2: null,
				sheet1: [],
				sheet1select: null,
				column1: [],
				column1select: null,
				data1: [],
				formatter1: '',
				sum1select: null,
				sum1: [],
				//
				sheet2: [],
				sheet2select: null,
				column2: [],
				column2select: null,
				data2: [],
				formatter2: '',
				sum2select: null,
				sum2: [],
				similarity: 1,
				table: [],
				filterRegex: false,
				filterFormatter: {},
				filterFormatterName: '',
				filterFormatterText: '',
				selectedFilter: [],
				availableFilter : [],
				sheet1filter: null,
				sheet2filter: null,
            }
        },
		watch: {
			/* filterFormatter: function(){
				var text = JSON.stringify(this.filterFormatter)
				localStorage.setItem('filterFormatter', text)
			}, */
			files1: function(){
				var self = this
				self.sheet1 = self.files1.map((val, i)=>{return {text: val.name, value: i}})
				self.sheet1select = null
			},
			sheet1select: function(){
				var self = this
				if(self.sheet1select == null){
					self.column1 = []
					self.column1select = null
				}
				else{
					self.column1 = self.files1[self.sheet1select].data[0].slice(1).map((val, i)=>{return {text: val, value: i+1}})
					self.column1select = null
				}
			},
			column1select: function(){
				var self = this
				self.formatter1 = ''
			},
			files2: function(){
				var self = this
				self.sheet2 = self.files2.map((val, i)=>{return {text: val.name, value: i}})
				self.sheet2select = null
			},
			sheet2select: function(){
				var self = this
				if(self.sheet2select == null){
					self.column2 = []
					self.column2select = null
				}
				else{
					self.column2 = self.files2[self.sheet2select].data[0].slice(1).map((val, i)=>{return {text: val, value: i+1}})
					self.column2select = null
				}
			},
			column2select: function(){
				var self = this
				self.formatter2 = ''
			}
		},
        methods: {
			saveFilterFormatter: function(){
				var self = this
				localStorage.setItem("filterFormatter", JSON.stringify(self.filterFormatter))
				self.filterFormatter = App.updateObject(self.filterFormatter)
				self.availableFilter = Object.keys(self.filterFormatter)
			},
			toggleSelect: function(ref, index){
				//if(this.selectedFilter.includes(index)){
					var add = this.$refs[ref][0].lazyValue
				if(!add){
					this.selectedFilter.splice(this.selectedFilter.indexOf(index), 1)
				}
				else
					this.selectedFilter.push(index)
			},
			removeFilterFormatter(index){
				var self = this
				delete self.filterFormatter[index]
				localStorage.setItem(self.filterFormatter, JSON.stringify(self.filterFormatter))
			},
			createExcel: async function(type){
				var self = this
				self.error1 = ''
				self.error12 = ''
				try {
					self.overlay = true
					if(self.column1select!=null){
						var col = self.column1select
						var format = Function("val", "row", self.formatter1)
						var file1 = self.files1[self.sheet1select].data
						if(self.sheet1filter!=null){
							var filterFormat = Function("values", "file1", "file2", self.filterFormatter[self.sheet1filter])
							file1 = filterFormat(self.files1[self.sheet1select].data, self.files1[self.sheet1select].data, self.files2[self.sheet2select].data)
						}
						var seed = file1.map(function(val){
							var t = val[col] == null ? '' : val[col]
							if(self.formatter1.trim()!="")
								return format(t, val)
							else
								return t
						})
					}
					console.log('a')
					var tmp = []
					if(self.column2select!=null){
						var col2 = self.column2select
						var format2 = Function("val", "row", self.formatter2)
						var file2 = self.files2[self.sheet2select].data
						if(self.sheet2filter!=null){
							var filterFormat = Function("values", "file1", "file2", self.filterFormatter[self.sheet2filter])
							file2 = filterFormat(self.files2[self.sheet2select].data, self.files1[self.sheet1select].data, self.files2[self.sheet2select].data)
						}
					console.log('b', file2)
						var seed2 = file2.map(function(val){
							var t = val[col2] == null ? '' : val[col2]
							if(self.formatter2.trim()!="")
								return format2(t, val)
							else
								return t
						})
					}

					console.log(file1, file2)

					if(self.reg.trim() == "")
						var reg = new RegExp("(.*)", "i")
					else
						var reg = new RegExp(self.reg, "i")

					if(type == 'file2listedInBoth'){
						//console.log(seed)
						file2.slice(1).map(function(val){
							//if listed in both
							var t = val[col2] == null ? '' : val[col2]
							if(self.formatter2.trim()!="")
								t = format2(t, val)
							
							if(type=='file2listedInBoth'){
								//console.log(t, seed.includes(t))
								if(seed.includes(t)){
									val.map(function(v){
										v = {
											value: v,
											fill: {
												type: "pattern",
												pattern: "solid",
												fgColor: {
													argb: 'FF91cc75'
												}
											} 
										}
									})
									tmp.push(val)
								}
							}
						})
					}else if(type == 'file1listedInBoth'){
						//console.log(seed)
						file1.slice(1).map(function(val){
							//if listed in both
							var t = val[col] == null ? '' : val[col]
							if(self.formatter1.trim()!="")
								t = format(t, val)
							
							if(type=='file1listedInBoth'){
								//console.log(t, seed.includes(t))
								if(seed2.includes(t)){
									val.map(function(v){
										v = {
											value: v,
											fill: {
												type: "pattern",
												pattern: "solid",
												fgColor: {
													argb: 'FF91cc75'
												}
											} 
										}
									})
									tmp.push(val)
								}
							}
						})
					}else if(type=='notListedInFile1'){
						file2.slice(1).map(function(val, i){
							//if listed in both
							var t = val[col2] == null ? '' : val[col2]
							if(self.formatter2.trim()!="")
								t = format2(t, val)
							if(type=='notListedInFile1')
								if(!seed.includes(t)){
									tmp.push(val)
								}
						})
					}else if(type=='notListedInFile2'){
						file1.slice(1).map(function(val, i){
							//if listed in both
							var t = val[col] == null ? '' : val[col]
							if(self.formatter1.trim()!="")
								t = format(t, val)
							if(type=='notListedInFile2')
								if(!seed2.includes(t)){
									tmp.push(val)
								}
						})
					}
					else if(type=='regex1'){
						file1.slice(1).map(function(val){
							//if listed in both
							if(val!=null)
								if(val[col]!=null){
									var t = val[col] == null ? '' : val[col]
									if(self.formatter1.trim()!="")
										t = format(t, val)
									if(t.match(reg)!=null)
										tmp.push(val)
								}
						})
					}
					else if(type=='regex2'){
						file2.slice(1).map(function(val){
							//if listed in both
							if(val!=null)
								if(val[col2]!=null){
									var t = val[col2] == null ? '' : val[col2]
									if(self.formatter2.trim()!="")
										t = format2(t, val)
									if(t.match(reg)!=null)
										tmp.push(val)
								}
						})
					}
					else if(type=='notregex1'){
						file1.slice(1).map(function(val){
							//if listed in both
							if(val!=null)
								if(val[col]!=null){
									var t = val[col] == null ? '' : val[col]
									if(self.formatter1.trim()!="")
										t = format(t, val)
									if(t.match(reg)==null)
										tmp.push(val)
								}
						})
					}
					else if(type=='notregex2'){
						file2.slice(1).map(function(val){
							//if listed in both
							if(val!=null)
								if(val[col2]!=null){
									var t = val[col2] == null ? '' : val[col2]
									if(self.formatter2.trim()!="")
										t = format2(t, val)
									if(t.match(reg)==null)
										tmp.push(val)
								}
						})
					}

					if(self.filterRegex){
						var tmpCol = -1;
						var formatter = ''
						var fnFormat = function(val){return val}
						switch (type) {
							case 'file2listedInBoth':
								tmpCol = col2
								formatter = self.formatter2
								fnFormat = format2
								break;
							case 'file1listedInBoth':
								tmpCol = col
								formatter = self.formatter1
								fnFormat = format
								break;
							case 'notregex1':
								tmpCol = col
								formatter = self.formatter1
								fnFormat = format
								break;
							case 'notregex2':
								tmpCol = col2
								formatter = self.formatter2
								fnFormat = format2
								break;
							case 'regex1':
								tmpCol = col
								formatter = self.formatter1
								fnFormat = format
								break;
							case 'regex2':
								tmpCol = col2
								formatter = self.formatter2
								fnFormat = format2
								break;
						
							default:
								break;
						}
						var tmp2 = tmp.filter(val=>{
							var t = val[tmpCol] == null ? '' : val[tmpCol]
							if(formatter.trim()!="")
								t = fnFormat(t, val)
							return t.match(reg)!=null
						})
						tmp = tmp2
					}
					if(self.sum1.length > 0 && ['file1listedInBoth', 'notregex1', 'regex1'].includes(type)){
						//Number.isNaN(Number('asd'))

						var tmp2 = {}
						tmp.map(function(val){
							if(tmp2[val[col]]==undefined){
								tmp2[val[col]] = val
							}
							else{
								self.sum1.map(function(s){
									var n = Number(val[s])
									var x = Number(tmp2[val[col]][s])
									if(Number.isNaN(x))
										tmp2[val[col]][s] = n
									else
										tmp2[val[col]][s] = n + x
								})
							}
						})
						tmp = Object.values(tmp2)
					}
					self.selectedFilter.map(function(f){
						var format = Function("values", "file1", "file2", self.filterFormatter[f])
						var tmp2 = format(tmp, self.files1[self.sheet1select].data, self.files2[self.sheet2select].data)
						tmp = tmp2
					})
					if(self.sum2.length > 0 && ['file2listedInBoth', 'notregex2', 'regex2'].includes(type)){
						//Number.isNaN(Number('asd'))

						var tmp2 = {}
						tmp.map(function(val){
							if(tmp2[val[col2]]==undefined){
								tmp2[val[col2]] = val
							}
							else{
								self.sum1.map(function(s){
									var n = Number(val[s])
									var x = Number(tmp2[val[col2]][s])
									if(Number.isNaN(x))
										tmp2[val[col2]][s] = n
									else
										tmp2[val[col2]][s] = n + x
								})
							}
						})
						tmp = Object.values(tmp2)
					}
					await self.toExcel(tmp)
					self.overlay = false
				} catch (err) {
					self.error1 = err
					self.overlay = false
					App.errorMsg()
				}
			},
			toExcel: function(arr){
				return new Promise((resolve, reject) => {
					
				var self = this
				var wb = new ExcelJS.Workbook()
				var ws = wb.addWorksheet(("Data"), {
					pageSetup:{paperSize: 9, fitToPage: true, fitToWidth: 1, fitToHeight: 18}
				});
				ws.pageSetup.margins = {
					left: 0.5, right: 0.5,
					top: 0.3, bottom: 0.3,
					header: 0, footer: 0
				};

				arr.map(function(rowVal, rowIdx){
					rowVal.map(function(colVal, colIdx){
						var row = ws.getRow(rowIdx+1)
						var cell = row.getCell(colIdx)
						if(typeof colVal == 'object' && colVal != null && colVal != undefined){
							Object.keys(colVal).forEach(function (key) {
								var val = obj[key]
								cell[key] = val
							});
						}
						else{
							cell.value = colVal
						}
					})
				})
				wb.xlsx.writeBuffer({
						base64: true
					})
					.then(function (xls64) {
						// build anchor tag and attach file (works in chrome)
						var data = new Blob([xls64], {
							type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
						});
						var a = document.createElement("a");
						

						var url = URL.createObjectURL(data);
						a.href = url;
						a.download = "export.xlsx";
						document.body.appendChild(a);
						a.click();
						setTimeout(function () {
							document.body.removeChild(a);
							window.URL.revokeObjectURL(url);
						},
						0);
						resolve(true)
					})
					.catch(function (error) {
						console.log(error.message);
						reject(false)
					});
				});
			}
        },
        mounted: function() {
			var self = this
			self.filterFormatter = JSON.parse(localStorage.getItem('filterFormatter') || "{}")
			self.availableFilter = Object.keys(self.filterFormatter)
        }
    }
</script>