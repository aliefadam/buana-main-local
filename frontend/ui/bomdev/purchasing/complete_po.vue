<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template v-observe-visibility="onVisible" ref="template" @update:selected-row="onSelectedRow" v-model="value" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly">
			<template v-slot:menu-after-filter>
				<v-btn small color="primary" outlined @click="processStock" :disabled="!selected">Insert into Stock</v-btn>
			</template>
		</v-template>
	</v-container>
</template>

<style scoped>
</style>

<script>
	module.exports = {
		name: '',
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
				url: App.folderRoot+'payment/complete',
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
					"groupable": false
				}],
            	selected: false,
				isInDom: false,
				isInViewport: false,
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
				console.log(self.isInViewport, self.isInDom)
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
                } else {
                    self.selected = val
                }
            },
		},
		mounted: function() {

		}
	}

</script>
