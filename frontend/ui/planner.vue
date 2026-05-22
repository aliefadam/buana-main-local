<template>
	<v-layout class="login-layout">
		<v-container style="margin: 0; padding: 0;max-width: 100%; background: #fff; min-height: 100%; height: fit-content;">
			<router-view v-if="ready" style="flex: 1; padding: 0 !important" :class="'layout-'+$vuetify.breakpoint.name" :formatter="['_key', '_key']"></router-view>
		</v-container>
	</v-layout>
</template>

<style>
    .planner-container {
        background: #F2F5F5 !important;
		font-family: -apple-system,BlinkMacSystemFont,"Segoe UI","Noto Sans",Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji";
    }

    .planner-container>* {
        margin: 8px !important;
    }

	.card-header{
		line-height: 28px;
		padding: 4px 16px;
		font-weight: bold; 
		background: #f9f9fa; 
		border-bottom: 1px solid #d6d7d8;
		display: flex;
		border-top-left-radius: 4px;
		border-top-right-radius: 4px;
		min-height: 37px;
		align-items: center;
	}
	.card-header > .v-btn{
		margin-right: -8px;
	}
	.card-planner > *:last-child{
		border-bottom-left-radius: 4px;
		border-bottom-right-radius: 4px;
	}
	.card-planner > *{
		padding-left: 16px;
		padding-right: 16px;
	}
	.markdown-body{
		background-color: inherit !important;
		/*font-size: 14px !important;*/
		font-size: .875rem !important;
		font-weight: 400 !important;
		line-height: 1.375rem !important;
		letter-spacing: .0071428571em !important;
		font-family: Roboto, sans-serif !important;
	}
	.v-application p{
		margin-bottom: 8px;
	}
	.layout-xs,
	.layout-sm {
		padding: 0 6px !important;
	}

	.card-blue{
		background-color: rgb(211, 227, 253) !important;
	
	}
	
	.card-blue-shade{
		background-color: rgb(168, 199, 250) !important;
		
	}
	
	.card-grey{
		background-color: rgb(227, 227, 227) !important;
	
	}
	
	.card-grey-shade{
		background-color: rgb(199, 199, 199) !important;
		
	}
	
	.card-aqua{
		background-color: rgb(194, 231, 255) !important;
	
	}
	
	.card-aqua-shade{
		background-color: rgb(127, 207, 255) !important;
		
	}
	
	.card-green{
		background-color: rgb(196, 238, 208) !important;
	
	}
	
	.card-green-shade{
		background-color: rgb(109, 213, 140) !important;
		
	}
	
	.card-yellow{
		border-radius: 4px;
		border: 1px solid #d6d7d8 !important;
		background-color: #fff !important;
	}
	
	.card-green2{
		background-color: #DCFFA8 !important;
	
	}

	.theme--light .v-overflow-menu{
		background-color: inherit;
	}

    .section-card * {
        font-size: 14px !important;
    }

    .section-card-12 * {
        font-size: 12px !important;
    }

    .section-card {
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 8px;
        font-size: 14px !important;
    }

	div.info *:not(.v-icon) {
		font-size: 12px !important;
		line-height: 1.25;
	}
	div.info .title{
		font-weight: bold;
		color: #031E32 !important;
	}
	div.info .title .v-icon *, div.info .title .v-icon{
		font-size: 20px !important;
	}
	div.info .subtitle{
		font-weight: bold;
		color: #96A9B0 !important;
		text-overflow: ellipsis;
		white-space: nowrap;
		overflow: hidden;
	}
	div.info .subtitle .mdi:before{
		font-size: 16px;
	}

	div.info{
		background-color: #fff !important;
		background: #fff !important;
	}

	div.info::after{
		display: none
	}

	.related-content > .info:not(:last-child){
		border-bottom: 1px solid #d6d7d8 !important;
	}

	.related-content > .info{
		padding-bottom: 8px;
	}
	.normal{
		background-color: rgba(2, 135, 255, 0.1) !important;
		color: rgb(2, 135, 255) !important;
		border: 1px solid;
		border-color: currentColor !important;
	}
	.normal .mdi:before{
		color: rgb(2, 135, 255) !important;
		font-size: 16px !important;
	}
	.low{
		background-color: rgba(22, 126, 32, 0.1) !important;
		color: rgb(22, 126, 32) !important;
		border: 1px solid;
		border-color: currentColor !important;
	}
	.low .mdi:before{
		/*color: rgb(145, 204, 117) !important;*/
		color: rgb(22, 126, 32) !important;
		font-size: 16px !important;
	}
	.high{
		background-color: rgba(227, 160, 8, 0.1) !important;
		color: rgb(227, 160, 8) !important;
		border: 1px solid;
		border-color: currentColor !important;
	}
	.high .mdi:before{
		/*color: rgb(250, 200, 88) !important;*/
		color: rgb(227, 160, 8) !important;
		font-size: 16px !important;
	}
	.urgent{
		/*background-color: rgba(251, 140, 0, 0.1) !important;
		color: rgb(251, 140, 0) !important;*/
		background-color: rgba(230, 85, 13, 0.1) !important;
    	color: rgb(230, 85, 13) !important;
		border: 1px solid;
		border-color: currentColor !important;
	}
	.urgent .mdi:before{
		/*color: rgb(252, 132, 82) !important;
		color: rgb(251, 140, 0) !important;*/
    	color: rgb(230, 85, 13) !important;
		font-size: 16px !important;
	}
	.immediate{
		background-color: rgba(249, 57, 31, 0.1) !important;
		color: rgb(249, 57, 31) !important;
		border: 1px solid;
		border-color: currentColor !important;
	}
	.immediate .mdi:before{
		color: rgb(249, 57, 31) !important;
		font-size: 16px !important;
	}
</style>

<script>
	module.exports = {
		data: function () {
			return {
				ready: false,
				items: [{
					name: 'My Page',
					loadWithBase: '/planner/planner/index'
				}, {
					name: 'Project',
					loadWithBase: '/planner/planner/project'
				}, {
					name: 'Task',
					loadWithBase: '/planner/planner/task'
				}, {
					name: 'Client',
					loadWithBase: '/planner/planner/client'
				}, {
					name: 'Master',
					disabled: !check_user('administrator'),
					children: [{
						name: 'Tracker',
						loadWithBase: '/planner/planner/category'
					}, {
						name: 'Field',
						loadWithBase: '/planner/planner/field'
					}, {
						name: 'Role & Permission',
						loadWithBase: '/planner/planner/role'
					}]
				}]
			};
		},
		methods: {
		},
		mounted: async function () {
			var lib = [
				/*'./library/nomarkdown/ckeditor.js',
				'./library/@ckeditor/ckeditor5-vue2/dist/ckeditor.js',*/
				'./library/markdown-it.min.js',
				'./library/markdown-it-plugins/collapsiblePlugin.js',
				//'./css/md/github.css',
				'./library/human-date.min.js',
				'./library/hr.js'
			]
			await loadMultipleLibrary(lib.join(';'))
			this.ready = true
			App.md = new markdownit({
				html: true,
				breaks: true,
				linkify: true,
			})
			App.openWindow = function createPopupWin(pageURL, pageTitle,
				popupWinWidth, popupWinHeight) {
				let left = (screen.width)/2-popupWinWidth/2;
				let top = (screen.height)/2-popupWinHeight/2;
				let myWindow = window.open(pageURL, pageTitle,
					'resizable=yes, width=' + popupWinWidth
					+ ', height=' + popupWinHeight + ', top='
					+ top + ', left=' + left);
			}
			App.items = []
			App.bottomItems = []
			App.bottomBtn = undefined
			App.drawer = false
			App.toolbar = true
			App.title = 'Planner'
			//App.loadWithBase('/index/base/apps')
			App.items = this.items.slice(0)
			App.searchControl = true
			App.searchCategory = ['Task', 'Project', 'Notes', 'Document']
			App.searchURL = {
				path: '/planner/search',
				url: '/planner/planner/search'
			}
			App.search = ''

			document.querySelector('.v-application--wrap .v-toolbar__title').onclick = function(){}
		}
	};
</script>