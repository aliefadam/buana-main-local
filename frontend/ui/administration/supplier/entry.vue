<template>
	<v-container style="padding : 0 !important; height: 100%; display: flex; flex-direction: column; margin: 0; width: 100%; max-width: 100%;">
		<v-template v-model="value" ref="template" :item-key="itemKey" :url="url" :headers="headers" :name="name" :table-only="tableOnly">
            <template v-slot:title-body v-if="$refs.template">
                <b>Count Rows: </b>{{ $refs.template.itemsTotal }}
            </template>
            <template v-slot:item.info="props">
                {{props.item.name}}<br/>
                <b v-if="props.item.is_reimburse == 1">"Akun Reimburse"</b>
            </template>
			<template v-slot:item.pic_info="props">
				<span>PIC:</span> {{props.item.pic_name}}<br/>
				<span>PHONE:</span> {{props.item.phone}}<br/>
				<span>EMAIL:</span> {{props.item.email}}<br/>
				<span>ADDRESS:</span> {{props.item.address}}
			</template>
		    <template v-slot:item.bank_info="props">
				<span>BANK NAME:</span> {{props.item.bank}}<br/>
				<span>ACCOUNT NO/IBAND:</span> {{props.item.bank_account_no}}<br/>
				<span>ACCOUNT NAME:</span> {{props.item.bank_account_name}}<br/>
				<span>BIC SWIFT/CODE:</span> {{props.item.bic_swift_code}}<br/>
				<span>CURRENCY:</span> {{props.item.currency}}<br/>
				<span>ACCOUNT TYPE:</span>  {{bank_account_type(props.item.bank_account_type)}}<br/>
				<span>ACCOUNT RESIDENCE:</span> {{bank_account_residence(props.item.bank_account_residence)}}
			</template>
           	<template v-slot:item.created="props">
			    <b>Created</b><br/>
				<span>BY:</span> {{props.item.created_by_name}}<br/>
				<span>DATE:</span> {{props.item.created_date}}<br/><br/>
				<b>Modified</b><br/>
				<span>BY:</span> {{props.item.modified_by_name}}<br/>
				<span>DATE:</span> {{props.item.modified_date}}
			</template>
			<template v-slot:item.modified="props">
				<span>BY:</span> {{props.item.modified_by_name}}<br/>
				<span>DATE:</span> {{props.item.modified_date}}<br/>
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
				default: false
			}
		},
		data: function() {
			return {
				name: 'supplier',
				itemKey: 'id',
				url: 'bom/supplier',
				headers: [{
					"text": "Supplier Id",
					"value": "id",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": 100,
					"type": "auto",
					"disabled": false,
					"form": false,
					"visible": true,
					"filter": false,
					"groupable": false,
					"cellClass": "",
					"alias": "supplier_id"
				}, {
					"text": "Name",
					"value": "info",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Name",
					"value": "name",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false
				},  {
					"text": "PIC Info",
					"value": "pic_info",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": true,
					"required": true,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "PIC Name",
					"value": "pic_name",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Phone",
					"value": "phone",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Address",
					"value": "address",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Email",
					"value": "email",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Bank Info",
					"value": "bank_info",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"disabled": false,
					"visible": true,
					"required": false,
					"form": false,
					"filter": true,
					"groupable": false
				}, {
					"text": "Bank Name",
					"value": "bank",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Account No/IBAN",
					"value": "bank_account_no",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Currency",
					"value": "currency",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "Account Name",
					"value": "bank_account_name",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false
				}, {
					"text": "BIC/Swift Code",
					"value": "bic_swift_code",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
				}, {
					"text": "Account Type",
					"value": "bank_account_type",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"data_value": [{
						text: "Perorangan",
						value:1
					}, {
						text: "Perusahaan",
						value:2
					}, {
						text: "Pemerintah",
						value:3
					}],
				}, {
					"text": "Account Residence",
					"value": "bank_account_residence",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "list",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": true,
					"filter": true,
					"groupable": false,
					"data_value": [{
						text: "Residence / Penduduk",
						value:1
					}, {
						text: "Non Residence / Bukan Penduduk",
						value:2
					}],
				}, 
				// {
				// 	"text": "Transfer Type",
				// 	"value": "bank_account_transfer_type",
				// 	"align": "start",
				// 	"sortable": true,
				// 	"filterable": false,
				// 	"divider": false,
				// 	"class": "",
				// 	"width": "auto",
				// 	"type": "list",
				// 	"disabled": false,
				// 	"visible": false,
				// 	"required": false,
				// 	"form": true,
				// 	"filter": true,
				// 	"groupable": false,
				// 	"data_value": [{
				// 		text: "Transfer sesama BCA",
				// 		value:"BCA"
				// 	}, {
				// 		text: "Transfer Bank Lain dalam negeri dengan LLG",
				// 		value:"LLG"
				// 	}, {
				// 		text: "Transfer Bank Lain dalam negeri dengan RTGS",
				// 		value:"RTGS"
				// 	}]
				// }, {
				// 	"text": "Transaction Code",
				// 	"value": "bank_transaction_code",
				// 	"align": "start",
				// 	"sortable": true,
				// 	"filterable": false,
				// 	"divider": false,
				// 	"class": "",
				// 	"width": "auto",
				// 	"type": "list",
				// 	"disabled": false,
				// 	"visible": false,
				// 	"required": false,
				// 	"form": true,
				// 	"filter": true,
				// 	"groupable": false,
				// 	"data_value": [{
				// 		text: "Payroll / Gaji",
				// 		value:70
				// 	}, {
				// 		text: "Pembayaran dividen",
				// 		value:71
				// 	}, {
				// 		text: "Distribusi bantuan dana pemerintah",
				// 		value:72
				// 	}, {
				// 		text: "Pembayaran tagihan",
				// 		value:73
				// 	}, {
				// 		text: "Pembayaran lainnya",
				// 		value:78
				// 	}, {
				// 		text: "Pengembalian DKE pembayaran",
				// 		value:79
				// 	}, {
				// 		text: "Pembayaran cicilan",
				// 		value:80
				// 	}, {
				// 		text: "Pembayaran tagihan",
				// 		value:81
				// 	}, {
				// 		text: "Pembayaran pajak",
				// 		value:82
				// 	}, {
				// 		text: "Pembayaran lainnya",
				// 		value:88
				// 	}, {
				// 		text: "Pengembalian DKE pembayaran",
				// 		value:89
				// 	}],
				// }, 
                {
                    text: "isReimburse",
                    value: "is_reimburse",
                    type: "switch",
                    data_value: [0,1],
                    default: 0,
                    visible: false,
                    filter: true
                }, {
					"text": "Salutation",
					"value": "salutation",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "text",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Created",
					"value": "created",
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
				}, {
					"text": "Created By",
					"value": "created_by",
					"align": "start",
					"sortable": true,
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
                    "url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            group_name: "adm_admin",
                            sub_group_name: "adm_admin"
                        },
                        "filterType": {
                        },
                        "filterCondition": {
                            group_name:'or',
                            sub_group_name:'or'
                        }
                    },
                    paging: true,
                    page: "1",
                    limit: "10",
                    alias: "created_by_name",
				}, { 
					"text": "Created Date",
					"value": "created_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Created Date",
					"value": "crea_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"search": [],
					"formatter": [],
					"options": {
						"filter": {},
						"filterType": {},
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}, {
					"text": "Modified",
					"value": "modified",
					"align": "start",
					"sortable": false,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "varchar",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Modified By",
					"value": "modified_by",
					"align": "start",
					"sortable": true,
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
                    "url": App.url + "users/info",
                    "searchby": ["name"],
                    "formatter": ["id", "name"],
                    "options": {
                        "filter": {
                            group_name: "adm_admin",
                            sub_group_name: "adm_admin"
                        },
                        "filterType": {
                        },
                        "filterCondition": {
                            group_name:'or',
                            sub_group_name:'or'
                        }
                    },
                    paging: true,
                    page: "1",
                    limit: "10",
                    alias: "modified_by_name",
				}, {
					"text": "Modified Date",
					"value": "modified_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": false,
					"form": false,
					"filter": false,
					"groupable": false
				}, {
					"text": "Modified Date",
					"value": "mod_date",
					"align": "start",
					"sortable": true,
					"filterable": false,
					"divider": false,
					"class": "",
					"width": "auto",
					"type": "date",
					"disabled": false,
					"visible": false,
					"required": true,
					"form": false,
					"filter": true,
					"groupable": false,
					"url": "",
					"search": [],
					"formatter": [],
					"options": {
						"filter": {
                        },
						"filterType": {
                        },
						"filterCondition": {}
					},
					"paging": true,
					"page": "1",
					"limit": "10"
				}]
			}
		},
		methods: {
			bank_account_type: function(f){
				if(f==1){
					return 'Perorangan'
				}
				if(f==2){
					return 'Perusahaan'
				}
				if(f==3){
					return 'Pemerintah'
				}
			},
			bank_account_residence: function(f){
				if(f==1){
					return 'Residence / Penduduk'
				}
				if(f==2){
					return 'Non Residence / Bukan Penduduk'
				}
			}
		},
		mounted: function() {

		}
	}

</script>
