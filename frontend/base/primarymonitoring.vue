<template>
    <v-container v-observe-visibility="onVisible" class="no-padding-margin" style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
        <v-template :table-loading.sync="loading" :show-expand="showExpand" :single-expand="singleExpand" :data="data" :items-options="itemsOptions" @update:selected-row="onSelectedRow" @update:selected-row-all="onSelectedRowAll" delete-mode="delete" :active-column="activeColumn" v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :table-fixed-header="tableFixedHeader">
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
			<template v-slot:item.batch="props">
				<div style="white-space: pre;">{{props.item.batch}}</div>
			</template>
			<template v-slot:item.p1="props">
				<div style="white-space: pre;">{{props.item.p1}}</div>
			</template>
			<template v-slot:item.p2="props">
				<div style="white-space: pre;">{{props.item.p2}}</div>
			</template>
			<template v-slot:item.p3="props">
				<div style="white-space: pre;">{{props.item.p3}}</div>
			</template>
			<template v-slot:item.p4="props">
				<div style="white-space: pre;">{{props.item.p4}}</div>
			</template>
			<template v-slot:item.p5="props">
				<div style="white-space: pre;">{{props.item.p5}}</div>
			</template>
			<template v-slot:item.v="props">
				<div style="white-space: pre;">{{props.item.v}}</div>
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
                default: "flag"
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
					filter: {
					},
					filterType: {
					}
				}
            }
        },
        data: function() {
            return {
                name: 'Process Monitoring ',
                itemKey: 'batch',
                url: '',
                loading: false,
                headers: [{
					text: "Batch",
					value: "batch",
					type: "text"
				}, {
					text: "Process 1",
					value: "p1",
					type: "text"
				}, {
					text: "Process 2",
					value: "p2",
					type: "text"
				}, {
					text: "Process 3",
					value: "p3",
					type: "text"
				}, {
					text: "Process 4",
					value: "p4",
					type: "text"
				}, {
					text: "Process 5",
					value: "p5",
					type: "text"
				}, {
					text: "Validated",
					value: "v",
					type: "text"
				}],
                //row yg terselect, jika multiple selection maka hanya berisi row yg terselect terakhir
                selected: false,
                //berisi semua row yg terselect, jika tidak multiple selection maka hanya terisi 1 data yg terselect
                selectedAll: [],
                isInDom: false,
                isInViewport: false,
				items: [{
					batch: `Batch No: 1401
Line: A`,
					p1: `Brand Name
time: 2023-10-13 11:00
in: 100 Kg
out: 100 Kg
`,
					p2: `Brand Name
time: 2023-10-13 11:05
in: 100 Kg
out: 100 Kg`,
					p3: `Brand Name
time: 2023-10-13 11:20
in: 100 Kg
out: 100 Kg`,
					p4: `Brand Name
time: 2023-10-13 11:40
in: 100 Kg
out: 100 Kg`,
					p5: `Brand Name
time: 2023-10-13 12:00
in: 100 Kg
out: 100 Kg`,
					v: `2023-10-13 12:05
by PIC A`
				}, {
					batch: `Batch No: 1402
Line: A`,
					p1: `
`,
					p2: `Brand Name
time: 2023-10-13 11:05
in: 100 Kg
out: 100 Kg`,
					p3: `Brand Name
time: 2023-10-13 11:20
in: 100 Kg
out: 100 Kg`,
					p4: `Brand Name
time: 2023-10-13 11:40
in: 100 Kg
out: 100 Kg`,
					p5: `Brand Name
time: 2023-10-13 12:00
in: 100 Kg
out: 100 Kg`,
					v: `2023-10-13 12:05
by PIC A`
				}, {
					batch: `Batch No: 1403
Line: A`,
					p1: `Brand Name
time: 2023-10-13 11:00
in: 100 Kg
out: 100 Kg
`,
					p2: ``,
					p3: ``,
					p4: `Brand Name
time: 2023-10-13 11:40
in: 100 Kg
out: 100 Kg`,
					p5: `Brand Name
time: 2023-10-13 12:00
in: 100 Kg
out: 100 Kg`,
					v: `2023-10-13 12:05
by PIC A`
				}]
            }
        },
        watch: {
            
        },
		computed: {
            headersObj: function(){
                var tmp = {}, self = this
                Object.keys(self.headers).map(key=>{
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
            onVisible: function(isVisible, e) {
                var self = this
                self.isInViewport = !!isVisible
                self.isInDom = !!e.target.isConnected
            },
        },
        mounted: function() {
			var self = this
			self.$nextTick(()=>{
				self.$refs.template.itemsTotal = self.items.length
				self.$refs.template.items = self.items
			})
        }
    }
</script>