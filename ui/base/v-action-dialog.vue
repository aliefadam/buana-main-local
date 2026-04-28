<template>
	<div>
		<v-teleport :to="mode+_uid">
			<v-card :min-height="computedMinHeight" :height="computedMinHeight == '100%' ? computedMinHeight : 'auto'" style="display: flex; flex-direction: column; max-height: 100%;">
				<v-card-title primary-title v-if="title">
					{{title}}<v-spacer></v-spacer><slot name="append-header"></slot>
					<v-btn color="error" outlined small @click="closeActionDialog" v-if="mode=='dialog'">
						<v-icon small left>mdi-chevron-left</v-icon>Back
					</v-btn>
				</v-card-title>
				<v-divider></v-divider>
				<v-card-text class="v-action-dialog-card">
					<!-- default slot -->
					<slot></slot>
				</v-card-text>
				<v-divider></v-divider>
				<v-card-actions v-if="actions">
					<v-spacer></v-spacer>
					<slot name="left-actions"></slot>
					<slot name="prepend-actions"></slot>
					<v-btn color="primary" outlined small @click="saveActionDialog" :disabled="disableSave" v-if="!hideSave">
						<v-progress-circular
							v-if="saveLoading"
							indeterminate
							size="14"
							width="2"
							color="primary"
							style="margin-right: 6px;"
						></v-progress-circular>
						<v-icon v-else small left>{{saveIcon}}</v-icon>{{labelSave}}
					</v-btn>
					<v-btn color="error" outlined small @click="closeActionDialog" v-if="mode=='dialog'">
						<v-icon small left>mdi-close</v-icon>{{labelClose}}
					</v-btn>
					<slot name="append-actions"></slot>
				</v-card-actions>
			</v-card>
		</v-teleport>
		<template v-if="mode=='expand'">
			<v-expand-transition v-if="!disableAnimation"
				<div v-if="value" class="v-action-dialog-expand">
					<v-teleport-location :name="'expand'+_uid">
					</v-teleport-location>
				</div>
			</v-expand-transition>
			<template v-else>
				<div v-if="value" class="v-action-dialog-expand">
					<v-teleport-location :name="'expand'+_uid">
					</v-teleport-location>
				</div>
			</template>
		</template>
		<v-dialog v-model="value" v-if="mode=='dialog'" :fullscreen="fullscreen" :scrollable="scrollable" :persistent="persistent" :overlay="overlay" :width="fullscreen ? '100%' : width" :max-width="fullscreen ? '100%' : maxWidth" transition="dialog-transition">
			<v-teleport-location :name="'dialog'+_uid">
			</v-teleport-location>
		</v-dialog>
	</div>
</template>

<style>
	.v-action-dialog-card{
		flex: 1; 
		overflow: auto; 
		padding: 12px !important;
		padding-top: 0px !important;
	}
</style>

<script>
	module.exports = {
		name: 'v-action-dialog',
		props:{
			/**
			 * dialog mode, `dialog` or `expand`
			*/
			mode: {
				default: 'dialog'
			},
			maxWidth: {
				default: '500px'
			},
			width: {
				default: '500px'
			},
			fullscreen: {
				type: Boolean,
				default: false
			},
			disableAnimation: {
				type: Boolean,
				default: false
			},
			value: {
				default: false
			},
			labelClose: {
				default: 'Close'
			},
			labelSave: {
				default: 'Save'
			},
			saveIcon: {
				default: 'mdi-content-save'
			},
			title: {
				default: 'Title'
			},
			actions: {
				default: true
			},
			saveLoading: {
				default: false
			},
			disableSave: {
				default: false
			},
			hideSave: {
				default: false
			},
			scrollable: {
				type: Boolean,
				default: true
			},
			persistent: {
				type: Boolean,
				default: true
			},
			overlay: {
				type: Boolean,
				default: true
			},
			minHeight: {
				default: 74
			}
		},
		computed: {
			computedMinHeight: function(){
				var self = this
				var height = self.minHeight
				if(height.toString().match(/\%/)!=null){
					height = window.innerHeight * (parseFloat(height)/100)
				}
				return self.fullscreen ? '100%' : height
			}
		},
		watch: {
			value: function(val){
				this.$emit('input', val)
			}
		},
		data: function () {
			return {

			}
		},
		methods: {
			saveActionDialog: function(){
				this.$emit('save')
			},
			closeActionDialog: function(){
				var self = this
				self.value = false
				self.$emit('close')
			}
		},
		mounted: function () {

		}
	}
</script>
