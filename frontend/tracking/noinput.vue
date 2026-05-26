<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :reload-if-visible="false" :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
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
            <!-- 
			<template v-slot:item.item_name="props">
			</template>
			 -->
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
			 <template v-slot:title-body>
				<div v-if="$refs.template">Total master without input: {{$refs.template.itemsTotal}}, Total master with input: {{tracked}}</div>
            </template>
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
            /* 'document-form': 'url:ui/ewis/test/document_form.vue' */
        },
        props: {
            value: undefined,
            data: {
                type: Object
            },
            p: {
                type: Object,
                default: {}
            },
            tableOnly: {
                type: Boolean,
                default: true
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
                type: Boolean,
                default: false
            },
            showExpand: {
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
                name: 'noinput',
                itemKey: 'barcode',
                url: 'barcode/noinput',
                loading: false,
                tracked: 0,
                headers: [{
                    text: 'Barcode',
                    value: 'barcode',
                    type: 'varchar',
					filter: true
                }, {
                    text: 'Part name',
                    value: 'part_name',
                    type: 'varchar',
					filter: true
                }, {
                    text: 'group name',
                    value: 'group_name',
                    type: 'varchar'
                }, {
                    text: 'area',
                    value: 'area',
                    type: 'varchar'
                }, {
                    text: 'process',
                    value: 'process',
                    type: 'varchar'
                }],
                //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
                selected: false,
                //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
                selectedAll: [],
                isInDom: false,
                isInViewport: false,
            }
        },
        watch: {

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
            }
        },
        methods: {
            onSelectedRow: function(val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                } else {
                    self.selected = val

                }
            },
            onSelectedRowAll: function(val) {
                var self = this
                self.selectedAll = val
            },
			getAlreadyTracked: async function(){
				//barcode/noinput/alreadytracked?process=dismantle&group_name=mech&area=a
				try {
					var self = this
					var res = await axios.get(App.url+'barcode/noinput/alreadytracked', {
						params: {
							area: self.p.area,
							group_name: self.p.group_name,
							process: self.p.process
						}
					})
					self.tracked = res.data.data[0].c
				} catch (err) {
								
				}
				
			},
            onVisible: function(isVisible, e) {
                var self = this
                self.isInViewport = !!isVisible
                self.isInDom = !!e.target.isConnected
                if (self.isInDom) {
					self.tracked = 0
					self.getAlreadyTracked()
                    self.$refs.template.defaultItemsOptions = {
                        "page": 1,
                        "itemsPerPage": 10,
                        "sortBy": ["barcode"],
                        "sortDesc": [true],
                        "groupBy": [],
                        "groupDesc": [],
                        "mustSort": false,
                        "multiSort": false,
                        "filter": {
							area: self.p.area,
							group_name: self.p.group_name,
							process: self.p.process
						},
                        "filterType": {
						}
                    }
                    self.$refs.template.getItems()
                }
            },
        },
        mounted: function() {

        }
    }
</script>