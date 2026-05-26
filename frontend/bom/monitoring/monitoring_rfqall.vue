<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
	    <v-template  v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly" :show-select="false" :items-options="itemsOptions">

            <template v-slot:item.grand_total="props">
                {{props.item.currency}} {{ Number(props.item.grand_total).format(2, 3) }}
            </template>
            <template v-slot:item.total_payment_pct="props">
                {{ Number(props.item.total_payment_pct) }}%
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
            },
			itemsOptions: {
				type: Object,
				default: {
					filter: {
					},
					filterType: {
					},
				}
			}
		},
        components: {

		},
		data: function() {
			return {
				name: 'Monitoring',
				itemKey: 'id',
				url: 'monitoring/monitoringrfqall',
				headers: [{
					"text": "Id",
					"value": "id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"type": "varchar",
					"disabled": false,
					"form": false,
					"visible": false,
					"filter": false,
					"groupable": false,
					"cellClass": ""
				},   
                {
					"text": "PR No",
					"value": "pr_no",
					"align": "start",
					"sortable": false,
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
					"text": "PR Subject",
					"value": "pr_subject",
					"align": "start",
					"sortable": false,
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
					"text": "PR Status",
					"value": "status_pr",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				},{
					"text": "PR Status",
					"value": "status",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false,
                    "data_value":[{
                        text:"New",
                        value:0
                    }, {
                        text:"Asking for Approval 1",
                        value:1
                    }, {
                        text: "Asking for Appproval 2",
                        value:2
                    }, {
                        text: "Final Approved",
                        value:3
                    }]
				},  {
					"text": "PO NO",
					"value": "po_no",
					"align": "start",
					"sortable": false,
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
					"value": "po_title",
					"align": "start",
					"sortable": false,
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
					"text": "PO Status",
					"value": "status_po",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false,
                    "data_value": [{ text:"New", value:0}, {text:"Asking for Approval 1", value:1}, {text: "Asking for Approval 2", value: 2}, {text: "Final Approved", value:3}]
				}, {
					"text": "Grand Total Price",
					"value": "grand_total",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "float",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "ETD",
					"value": "promised_delivery_date",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "ETA",
					"value": "eta_date",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false
				},{
					"text": "Payment PCT",
					"value": "total_payment_pct",
					"align": "start",
					"sortable": false,
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
				}, 
                {
					"text": "Status Payment",
					"value": "is_paid",
					"align": "start",
					"sortable": false,
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
				},  ]
			}
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
            statusPr: function(f){
				if(f == 0){
					return 'New'
				}
				if(f == 1){
					return 'Asking for Approval 1'
				}
				if(f == 2){
					return 'Asking for Approval 2'
				}
				if(f == 3){
					return 'Final Approved'
				}
			},
            statusPo: function(f){
				if(f == 0){
					return 'New'
				}
				if(f == 1){
					return 'Asking for Approval 1'
				}
				if(f == 2){
					return 'Asking for Approval 2'
				}
				if(f == 3){
					return 'Final Approved'
				}
			},
		},
		mounted: function() {

		}
	}

</script>
