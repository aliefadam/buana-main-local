<template>
	<search-panel class="search-panel" category="planner" @search-completed="onSearchCompleted">
		<template v-slot:prepend-text="props">
			<template v-if="props.item.type=='task'">
				<b>{{props.item.data.category}} #{{(props.item.data.id||0).toString().padStart(4, 0)}}</b>
			</template>
		</template>
		<template v-slot:append-text="props">
			<!-- <template>{{props.item}}</template> -->
			<v-chip style="margin-top: 2px;" outlined small color="error" label>{{props.item.type}}</v-chip>
			<template v-if="props.item.type=='task'">
				<v-chip style="margin-top: 2px;" label small><b>% Done</b>&nbsp;{{props.item.data.percent_done}}</v-chip>
				<v-chip style="margin-top: 2px;" label small v-if="props.item.data['Assigned to__4']"><b>Assigned to</b>&nbsp;{{props.item.data["Assigned to__4"].split('=__=')[0]}}</v-chip>
				<template v-if="props.item.data.Priority__1">
					<v-chip style="border: 1px solid;" small flat class="immediate" v-if="props.item.data.Priority__1.split('=__=')[0] == 'Immediate'"><v-icon left>mdi-alert-decagram-outline</v-icon>{{props.item.data.Priority__1.split('=__=')[0]}}</v-chip>
					<v-chip style="border: 1px solid;" small flat class="urgent" v-else-if="props.item.data.Priority__1.split('=__=')[0] == 'Urgent'"><v-icon left>mdi-alert-circle-outline</v-icon>{{props.item.data.Priority__1.split('=__=')[0]}}</v-chip>
					<v-chip style="border: 1px solid;" small flat class="high" v-else-if="props.item.data.Priority__1.split('=__=')[0] == 'High'"><v-icon left>mdi-chevron-up-circle-outline</v-icon>{{props.item.data.Priority__1.split('=__=')[0]}}</v-chip>
					<v-chip style="border: 1px solid;" small flat class="normal" v-else-if="props.item.data.Priority__1.split('=__=')[0] == 'Normal'"><v-icon left>mdi-circle-outline</v-icon>{{props.item.data.Priority__1.split('=__=')[0]}}</v-chip>
					<v-chip style="border: 1px solid;" small flat class="low" v-else-if="props.item.data.Priority__1.split('=__=')[0] == 'Low'"><v-icon left>mdi-chevron-down-circle-outline</v-icon>{{props.item.data.Priority__1.split('=__=')[0]}}</v-chip>
				</template>
			</template>
			<!-- <v-chip style="margin-top: 2px;" outlined small color="error" label>{{props.item.search_type}}</v-chip>
			<v-chip style="margin-top: 2px;" v-if="props.item.done_ratio" label small><b>% Done</b>&nbsp;{{props.item.done_ratio}}</v-chip>
			<v-chip style="margin-top: 2px;" v-if="props.item.tracker" label small><b>Tracker</b>&nbsp;{{props.item.tracker}}</v-chip> -->
		</template>
	</search-panel>
</template>

<style scoped>
	.search-result-box > .v-card__text:last-child{
		margin-top: 8px;
	}
	.search-result-box{
		border-radius: 4px !important;
		border: 1px solid #d6d7d8 !important;
		background-color: #fff !important;
	}
	.search-result-box > a .redmine-search-title2{
		font-weight: bold; 
		background: #f9f9fa; 
		border-bottom: 1px solid #d6d7d8;
		margin-bottom: 8px;
	}
	.search-result-box > a .redmine-search-title{
		font-weight: bold; 
		background: #f9f9fa;
		border-top-left-radius: 4px;
		border-top-right-radius: 4px;
	}
	.search-result-box > a{
		margin-bottom: 8px;
	}
	.app-search{
		background-color: #fff !important;
	}
	.container > .v-card-title2{
		border-bottom: 1px solid #d6d7d8 !important;
	}
</style>

<script>
	module.exports = {
		name: '',
		components:{
			'search-panel': 'url:ui/base/search.vue'
		},
		data: function () {
			return {
				
			}
		},
		methods: {
			onSearchCompleted: function(data, q){
				var self = this
				/*setTimeout(()=>{
					new HR(".app-search", {
						highlight: [q]
					  }).hr();
				}, 250)*/
				/*
				var t = document.createElement('span')
				t.innerHTML = '<span data-hr="" style="background-color: rgb(255, 222, 112);">penawaran</span>''
				var n = temp2.childNodes[0]
				n.parentNode.insertBefore(t, n)
				n.parentNode.removeChild(n)
				*/
				setTimeout(()=>{
					self.highlight(q, '.search-panel .v-data-table__wrapper')
				}, 250)
			},
			highlight: function(q, parent){
				var self = this
				var r = new RegExp(`(${q})`, 'ig')
				
				if(typeof parent == 'string')
					parent = document.querySelector(parent)

				var c = parent.childNodes
				console.log(parent, parent.childNodes)
				for(var i = 0; i < c.length; i++){
					if(c[i].nodeType == 3){
						if(c[i].textContent.match(r)!=null){
							var t = document.createElement('span')
							t.innerHTML = c[i].textContent.replace(r, '<span data-hr="" style="background-color: rgb(255, 222, 112);">$1</span>')
							var n = c[i]
							n.parentNode.insertBefore(t, n)
							n.parentNode.removeChild(n)
						}
					}
					else{
						self.highlight(q, c[i])
					}
				}
			}
		},
		mounted: function () {
			console.log(this)
		}
	}
</script>