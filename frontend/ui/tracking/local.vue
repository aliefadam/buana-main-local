<template>
    <div style="flex: 1; display: flex; flex-direction: column;">
        <v-tabs style="flex: 0" v-model="tab" mobile-breakpoint="0">
            <v-tab :key="0">Dismantle Part</v-tab>
            <v-tab :key="1">Packing</v-tab>
            <v-tab :key="2">Storage</v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab" style="flex: 1; display: flex;" class="tabs" touchless>
            <v-tab-item :key="0" style="height: 100%; display: flex; flex-direction: column;">
                <v-row dense style="flex: 0;">
                    <v-col xs="12" md="3" lg="2" v-for="(item, index) in dismantle_items" :key="index">
                        <v-card outlined>
                            <v-list-item three-line>
                                <v-list-item-avatar tile size="80" color="grey">
                                    <v-img max-height="150" max-width="150" :src="item.img"></v-img>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                    <v-list-item-subtitle>
                                        Barcode: {{item.barcode}}<br />
                                        Latitude: {{item.lt}}<br />
                                        Longitude: {{item.ln}}
                                        <!-- <br />
										Process: {{item.process}} -->
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                                <v-icon style="position: absolute; top: 12px; right: 12px;" @click="remove('dismantle', item)">mdi-close</v-icon>
                            </v-list-item>
                        </v-card>
                    </v-col>
                </v-row>
				<v-btn small color="primary" style="position: fixed; bottom: 60px; right: 5px;" :loading="sync" @click="syncData" :disabled="sync">Sync All</v-btn>
            </v-tab-item>
            <v-tab-item :key="1" style="height: 100%;">
                <v-row dense style="flex: 0;">
                    <v-col xs="12" md="3" lg="2" v-for="(item, index) in packing_items" :key="index">
                        <v-card outlined @click="openDetail(item._id)">
                            <v-list-item three-line>
                                <v-list-item-avatar tile size="80" color="grey">
                                    <v-img max-height="150" max-width="150" :src="item.img"></v-img>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                    <v-list-item-subtitle>
                                        Barcode: {{item.barcode}}<br />
                                        Latitude: {{item.lt}}<br />
                                        Longitude: {{item.ln}}
                                        <!-- <br />
										Process: {{item.process}} -->
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                                <v-icon style="position: absolute; top: 12px; right: 12px;" @click="remove('packing', item)">mdi-close</v-icon>
                            </v-list-item>
                        </v-card>
                    </v-col>
                </v-row>
				<v-btn small color="primary" style="position: fixed; bottom: 60px; right: 5px;" :loading="sync" @click="syncData" :disabled="sync">Sync All</v-btn>
            </v-tab-item>
            <v-tab-item :key="2" style="height: 100%;">
                <v-row dense style="flex: 0;">
                    <v-col xs="12" md="3" lg="2" v-for="(item, index) in storage_items" :key="index">
                        <v-card outlined>
                            <v-list-item three-line>
                                <v-list-item-avatar tile size="80" color="grey">
                                    <v-img max-height="150" max-width="150" :src="item.img"></v-img>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                    <v-list-item-subtitle>
                                        Barcode: {{item.barcode}}<br />
                                        Latitude: {{item.lt}}<br />
                                        Longitude: {{item.ln}}
                                        <!-- <br />
										Process: {{item.process}} -->
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                                <v-icon style="position: absolute; top: 12px; right: 12px;" @click.stop="remove('storage', item)">mdi-close</v-icon>
                            </v-list-item>
                        </v-card>
                    </v-col>
                </v-row>
				<v-btn small color="primary" style="position: fixed; bottom: 60px; right: 5px;" :loading="sync" @click="syncData" :disabled="sync">Sync All</v-btn>
            </v-tab-item>
        </v-tabs-items>
		<v-action-dialog v-model="detail_dialog" :actions="false" fullscreen title="Packing Part">
			<v-row dense style="flex: 0;">
            	<v-col xs="12" md="3" lg="2" v-for="(item, index) in detail_items" :key="index">
                	<v-card outlined>
                    	<v-list-item three-line>
                        	<v-list-item-avatar tile size="80" color="grey">
                            	<v-img max-height="150" max-width="150" :src="item.img"></v-img>
                            </v-list-item-avatar>
                            <v-list-item-content>
                            	<v-list-item-subtitle>
                                        Barcode: {{item.barcode}}<br />
                                        Latitude: {{item.lt}}<br />
                                        Longitude: {{item.ln}}
                                        <!-- <br />
										Process: {{item.process}} -->
                            	</v-list-item-subtitle>
                            </v-list-item-content>
                            <v-icon style="position: absolute; top: 12px; right: 12px;" @click="remove('packing_part', item)">mdi-close</v-icon>
                    	</v-list-item>
                	</v-card>
            	</v-col>
        	</v-row>
		</v-action-dialog>
    </div>
</template>

<style>
</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {
                tab: null,
                sync: false,
                dismantle: new LDB.Collection('dismantle'),
                packing: new LDB.Collection('packing'),
                storage: new LDB.Collection('storage'),
                packing_part: new LDB.Collection('packing_part'),
                dismantle_items: [],
                packing_items: [],
                storage_items: [],
                packing_part_items: [],
				detail_dialog: false,
				detail_items: [],
				parent_id: false
            }
        },
        methods: {
			openDetail: function(parent_id){
				var self = this
				self.detail_dialog = true
				self.detail_items = []
				self.packing_part.find({parent_id: parent_id}, items=>{
					self.detail_items = items
					self.parent_id = parent_id
				})
			},
			updateData: function(){
				var self = this
				
				self.dismantle.find({}, items=>{
					self.dismantle_items = items
				})
				
				self.storage.find({}, items=>{
					self.storage_items = items
				})
				
				self.packing.find({}, items=>{
					self.packing_items = items
				})
				
				self.packing_part.find({}, items=>{
					self.packing_part_items = items
				})
			},
            remove: function(database, item) {
                var self = this
                var c = confirm(`Delete part ${item.barcode}?`)
                if (c) {
                    self[database].delete({
                        _id: item._id
                    })
                    if (database == 'packing') {
                        self.packing_part.delete({
                            parent_id: item._id
                        })
                    }
					self.updateData()
					if(database=='packing_part'){
						self.packing_part.find({parent_id: self.parent_id}, items=>{
							self.detail_items = items
						})
					}
                }
            },
            find: function(db, params) {
                return new Promise((resolve, reject) => {
                    db.find(params, function(result) {
                        resolve(result)
                    })
                });
            },
            syncData: async function() {
                var self = this,
                    ok = true
                self.sync = true
                while (self.dismantle.items.length > 0 && ok) {
                    var res = await axios.post(App.url + 'barcode/item', self.dismantle.items[0])
                    if (!res.data.status) {
                        App.errorMsg()
                        ok = false
                    } else {
                        self.dismantle.delete({
                            _id: self.dismantle.items[0]._id
                        })
                    }
                }
                while (self.packing.items.length > 0 && ok) {
                    var res = await axios.post(App.url + 'barcode/item', self.packing.items[0])
                    if (!res.data.status) {
                        App.errorMsg()
                        ok = false
                    } else {
                        var ok = true
                        var id = self.packing.items[0]._id
                        var parent_id = res.data.insertID
                        var parts = await self.find(self.packing_part, {
                            parent_id: id
                        })
                        for (i = 0; i < parts.length && ok; i++) {
                            var p = parts[i]
                            //p.parent_id = parent_id
                            self.packing_part.update({
                                parent_id: id
                            }, {
                                real_parent_id: parent_id
                            })
                            var pres = await axios.post(App.url + 'barcode/item', {
                                parent_id: parent_id,
                                barcode: p.barcode,
                                ln: p.ln,
                                lt: p.lt,
                                process: p.process,
                                img: p.img
                            })
                            if (!res.data.status) {
                                App.errorMsg()
                                ok = false
                            } else {
                                self.packing_part.delete({
                                    _id: p._id
                                })
                            }
                        }
                        self.packing.delete({
                            _id: self.packing.items[0]._id
                        })
                    }
                }
                while (self.storage.items.length > 0 && ok) {
                    var res = await axios.post(App.url + 'barcode/item', self.storage.items[0])
                    if (!res.data.status) {
                        App.errorMsg()
                        ok = false
                    } else {
                        self.storage.delete({
                            _id: self.storage.items[0]._id
                        })
                    }
                }
                self.sync = false
                App.successMsg()
				self.updateData()
            }
        },
        mounted: function() {
            var self = this
			self.updateData()
        }
    }
</script>