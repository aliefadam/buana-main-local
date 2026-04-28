<template>
    <div style="flex: 1; display: flex; flex-direction: column;">
        <v-tabs style="flex: 0" v-model="tab" mobile-breakpoint="0">
            <v-tab :key="0">Dismantle Part</v-tab>
            <v-tab :key="1">Packing Process</v-tab>
            <v-tab :key="2">Storage</v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1; display: flex;" class="tabs" touchless>
            <v-tab-item :key="0" style="height: 100%; display: flex; flex-direction: column; padding-bottom: 28px;">
                <scanner check-part ref="scan1" name="scan1" key="scan1" database="dismantle" :default-params="{process: 'dismantle'}"></scanner>
            </v-tab-item>
            <v-tab-item :key="1" style="height: 100%; display: flex; flex-direction: column; padding-bottom: 28px;">
                <scanner ref="scan2" name="scan2" key="scan2" @success="onSuccess" database="packing" @after-save="onAfterSave" :default-params="{process: 'packing'}">
					<template v-slot>
						<div style="flex: 1; overflow: auto;">
							<v-row dense>
								<v-col xs="12" md="3" lg="2" v-for="(item, index) in packing_part_items" :key="index">
									<v-card outlined>
										<v-list-item three-line>
											<v-list-item-avatar tile size="80" color="grey">
												<v-img max-height="150" max-width="150" :src="item.img"></v-img>
											</v-list-item-avatar>
											<v-list-item-content>
												<v-list-item-subtitle>
													Barcode: {{item.barcode}}<br />
													Latitude: {{item.lt}}<br />
													Longitude: {{item.ln}}<!-- <br />
													Process: {{item.process}} -->
												</v-list-item-subtitle>
											</v-list-item-content>
											<v-icon style="position: absolute; top: 12px; right: 12px;" @click="remove(item)">mdi-close</v-icon>
										</v-list-item>
									</v-card>
								</v-col>
							</v-row>
						</div>
					</template>
					<template v-slot:prepend-menu>
						<v-btn small color="primary" class="mr-2" outlined @click="dialogAddPart=true">Add Part</v-btn>
					</template>
				</scanner>
				<v-action-dialog title="Add Part" v-model="dialogAddPart" :actions="false" fullscreen>
					<scanner check-part key="part" database="packing_part" @after-save="onAfterSavePart" :default-params="{parent_id: null, process: 'packing_part'}" :default-lat-long="defaultLatLong"></scanner>
				</v-action-dialog>
            </v-tab-item>
            <v-tab-item :key="2" style="height: 100%; display: flex; flex-direction: column; padding-bottom: 28px;">
                <scanner ref="scan3" name="scan2" key="scan3" database="storage" :default-params="{process: 'storage'}"></scanner>
            </v-tab-item>
        </v-tabs-items>
    </div>
</template>

<style>
    .container {
        padding: 0 !important;
    }

    .v-tabs-items>.v-window__container {
        flex: 1
    }

    .v-window__container {
        width: 100%;
    }
</style>

<script>
    module.exports = {
        name: '',
        components: {
            'scanner': 'url:ui/tracking/scanner.vue'
        },
        data: function() {
            return {
                tab: null,
				defaultLatLong: {
					ln: '-',
					lt: '-',
				},
				dialogAddPart: false,
				packing_part: false,
				packing_part_items: []
			}
        },
		watch:{
			tab: function(val){
				var self = this
				try {
					if(self.$refs.scan1){
						self.$refs.scan1.stopScan()
						self.$refs.scan1.stopCamera()
					}
					if(self.$refs.scan2){
						self.$refs.scan2.stopScan()
						self.$refs.scan2.stopCamera()
					}
					if(self.$refs.scan3){
						self.$refs.scan3.stopScan()
						self.$refs.scan3.stopCamera()
					}
				} catch (err) {
					console.log(err)
				}
			}
		},
        computed: {
        },
        methods: {
            onSuccess: function(headers){
				var self = this
				self.defaultLatLong = {
					lon: headers.ln.data, lat: headers.lt.data
				}
			},
			onAfterSave: function(data){
				var self = this
				//var db = new LDB.Collection('packing');
				//var db = new LDB.Collection('packing_part');
				//console.log(db)
				self.packing_part.update({parent_id: null}, {parent_id: data._id, real_parent_id: null})
				self.packing_part_items = []
			},
			onAfterSavePart: function(data){
				var self = this
				self.dialogAddPart = false
				self.packing_part.find({parent_id: null}, items=>{
					self.packing_part_items = items
				})
			},
			remove: function(item){
				var self = this
				var c = confirm(`Delete part ${item.barcode}?`)
				if(c){
					self.packing_part.delete({_id: item._id})
					self.packing_part.find({parent_id: null}, items=>{
						self.packing_part_items = items
					})
				}
			}
        },
        mounted: function() {
            var self = this
            self.packing_part = new LDB.Collection('packing_part');
			self.packing_part.find({parent_id: null}, items=>{
				self.packing_part_items = items
			})
        }
    }
</script>