<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template v-observe-visibility="onVisible" ref="template" @update:selected-row="onSelectedRow" v-model="value" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly">
			<template v-slot:menu-after-filter>
				<v-btn small color="primary" outlined @click="dialogItem=true" :disabled="!selected">Items</v-btn>
				<v-btn small color="primary" outlined @click="openPrint" :disabled="!selected">Print SPB</v-btn>
			</template>
		</v-template>
		<v-action-dialog :actions="false" v-model="dialogItem" title="SPB Item" min-height="75%" fullscreen>
            <complete-po-item :key="selected.id" :sel="processData" name="" :data="dataid"></complete-po-item>
        </v-action-dialog>
		<v-action-dialog :actions="false" v-model="dialogPrint" title="SPB Print" min-height="75%" fullscreen>
            <v-print style="margin: auto; color: #000;">
				<nav class="tw-header print-header">
					<h1 class="px-2 text-xl text-center my-auto uppercase font-bold"><img src="../img/logo2.png"></h1>
					<div style="align-self: end;">Jl. Mayjend Yono Soewoyo No. 35 - Surabaya 60225 - Indonesia - Telp. (031) 99900899</div>
				</nav>
				<div style="font-size: 20px; font-weight: bold; padding-top: 14px;  padding-bottom: 14px; text-align: center;">
					SURAT PENERIMAAN BARANG
				</div>
				<table class="default-table">
					<tr>
						<td>PO</td>
						<td>: {{selected.po_no}}</td>
					</tr>
					<tr>
						<td>Date</td>
						<td>: {{print.date}}</td>
					</tr>
				</table>
			</v-print>
        </v-action-dialog>
	</v-container>
</template>

<style>
	.print-header{
		border-bottom: 3px double black !important;
		border-style: double;
		padding: 0 !important;
	}
</style>

<script>
	module.exports = {
		name: '',
		components: {
            'complete-po-item': 'url:ui/bom/purchasing/complete_po_item.vue',
            'v-print': 'url:ui/base/print.vue'
        },
		props: {
			value: undefined,
			data: {
				type: Object
			},
			tableOnly: {
				type: Boolean,
				default: true
			}
		},
		data: function() {
			return {
				name: 'item',
				itemKey: 'po_id',
				url: 'bom/payment/complete',
				headers: [{
					"text": "Id",
					"value": "po_id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"type": "auto",
					"disabled": false,
					"form": false,
					"visible": false,
					"filter": false,
					"groupable": false,
					"cellClass": ""
				}, {
					"text": "PO NO",
					"value": "po_no",
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
					"filter": true,
					"groupable": false
				}, {
					"text": "PO Title",
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
				}, {
					"text": "Invoice No",
					"value": "invoice_no",
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
					"groupable": false
				}, {
					"text": "Invoice %",
					"value": "payment_pct",
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
					"groupable": false
				}, {
					"text": "Invoice Total Price",
					"value": "total_price",
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
					"form": true,
					"filter": true,
					"groupable": false,
					"precision": 2,
				}],
            	selected: false,
				isInDom: false,
				isInViewport: false,
                processData: {},
                dataid: {},
				dialogItem: false,
				dialogPrint: false,
				print: {}
			}
		},
		watch: {
			isInViewport: function(val){
				var self = this
				if(val)
					self.$refs.template.getItems();
			}
		},
		methods: {
			onVisible: function(isVisible, e) {
				var self = this
				self.isInViewport = !!isVisible
				self.isInDom = !!e.target.isConnected
			},
			processStock: async function(){
				var self = this
				var res = await axios.get(App.url + 'bom/payment/transferstock', {
					params: {
						po_id: self.selected.po_id
					}
				})
				if(!res.data.status)
					App.errorMsg()
				else
					App.successMsg()
				self.$refs.template.getItems();
			},
            onSelectedRow: function (val) {
                var self = this
                if (val === undefined) {
                    self.selected = false
                    self.processData = {}
                    self.dataid = {}
                } else {
                    self.selected = val
					self.processData = {
                        purchase_order_id: val.po_id,
                    }
                    self.dataid = {
                        purchase_order_id: val.po_id,
                    }
                }
            },
			openPrint: async function(){
				try {
					var self = this
					var res = await axios.get(App.url+self.url+"/spb", {
						params: {
							id: self.selected.id
						}
					})
					self.dialogPrint=true
				} catch (err) {
					App.errorMsg()
				}
			}
		},
		mounted: function() {

		}
	}

</script>
