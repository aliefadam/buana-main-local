<template>
    <div>
        <div v-if="is_request_payment" style="text-align: center; font-weight: bold; margin-bottom: 20px;">PAYMENT<br />REQUEST</div>
        <div v-else style="text-align: center; font-weight: bold; margin-bottom: 20px;">TRANSFER LIST<br />SUMMARY</div>
        <!-- <div>DATE: {{date}}<br /></div>
        <div style="text-align: right; font-weight: bold;">{{payno}}<br /></div> -->
        <table style="width: 100%;">
            <tr>
                <th></th>
                <th></th>
            </tr>
            <tr>
                <td>DATE: {{date}}</td>
                <td style="text-align: right; font-weight: bold;">{{ payno }}</td>
            </tr>
        </table>
        <table class="table tdvtop" style="border:1px solid #000 !important; width: 100%">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 13px; padding: 2px;">No</th>
                    <th rowspan="2">PO NO</th>
                    <th rowspan="2" style="width: 50px; white-space: nowrap;">Ref <br />Invoice No</th>
                    <th rowspan="2" style="width: 50px; white-space: nowrap;">Payment<br />Code</th>
                    <th rowspan="2" style="width: 60px; white-space: nowrap;">ETD</th>
                    <th rowspan="2" style="width: 60px; white-space: nowrap;">Supplier</th>
                    <th v-if="currency" colspan="6" style="width: 180px;">Payment</th>
                    <th v-else colspan="4" style="width: 150px;">Payment</th>
                </tr>
                <tr>
                    <th>PCT</th>
                    <th v-if="currency" colspan="4">Total</th>
                    <th v-else colspan="2">Total</th>
                    <th>Notes</th>
                </tr>
                <tr>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; width: 15px;"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0;"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0;"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0;"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0;"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0;"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0;"></th>
                    <th v-if="currency" style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0; "></th>
                    <th v-if="currency" style="background: #fff; height: 2px; padding: 0; border-right: 0; width: 70px"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0;"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0; border-left: 0; width: 70px; word-wrap: break-word; overflow: hidden;"></th>
                    <th style="background: #fff; height: 2px; padding: 0; border-right: 0;"></th>
                </tr>
            </thead>
            <tbody v-for="item in petty">
                <tr>
                    <td v-if="item.has_credit_note" style="text-align: center; white-space: nowrap;" rowspan="4">{{item.no_urut}}</td>
                    <td v-else style="text-align: center; white-space: nowrap;" rowspan="3">{{item.no_urut}}</td>
                    <td v-if="item.isuraian" style="text-align: center; white-space: nowrap;">As Reference</td>
                    <td v-else style="white-space: nowrap;">{{item.po_no1}}<br />{{item.po_no2}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.ref_invoice_no}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.kode_pembayaran}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.etd}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.name}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.payment_pct}}%</td>
                    <td v-if="currency" style="width: 1px; border-right: 0 !important;  white-space: nowrap;">{{item.c}}</td>
                    <td v-if="currency" style="text-align: right; border-left: 0 !important; white-space: nowrap;">{{item.grand_total_price_original}}</td>
                    <td style="width: 1px; border-right: 0 !important;  white-space: nowrap;">Rp.</td>
                    <td style="text-align: right; border-left: 0 !important; white-space: nowrap;">{{item.grand_total_price}}</td>
                    <td style="text-align: left">{{item.notes}}</td>
                </tr>
                <tr >
                    <td v-if="currency" colspan="11"><b>Bank Info.</b> Bank Name: {{ item.bank }}, Account No:{{ item.bank_account_no }}, Receiver Name: {{ item.bank_account_name }}</td>
                    <td v-else colspan="9"><b>Bank Info.</b> Bank Name:  {{ item.bank }}, Account No:{{ item.bank_account_no }}, Receiver Name: {{ item.bank_account_name }}</td>
                </tr>
                <tr v-if="item.has_credit_note">
                    <td v-if="currency" colspan="11"><b>{{ item.credit_note_display }}</b></td>
                    <td v-else colspan="9"><b>{{ item.credit_note_display }}</b></td>
                </tr>
                <tr v-if="item.isuraian">
                    <td v-if="currency" colspan="11">{{item.uraian}}</td>
                    <td v-else colspan="9">{{ item.uraian }}</td>
                </tr>
                <tr v-else>
                    <td v-if="currency" colspan="11">
                        Parts: {{item.parts}}</td>
                    <td v-else colspan="9">
                        Parts: {{item.parts}}</td>
                </tr>
            </tbody>
            <tbody>
                <tr v-if="petty_cash_currency">
                    <td style="text-align: center; font-weight: bold;" colspan="7">Total Pemakaian Petty Cash</td>
                    <td style="border-right: 0 !important; font-weight: bold;">{{currencyAsing}}</td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;">{{pettyCashCurrencyAsing}}</td>
                    <td style="border-right: 0 !important; font-weight: bold;">Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;">{{total_petty_cash}}</td>
                </tr>
                <tr v-if="petty_cash">
                    <td style="text-align: center; font-weight: bold;" colspan="7">Total Pemakaian Petty Cash</td>
                    <td style="border-right: 0 !important; font-weight: bold;">Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;">{{total_petty_cash}}</td>
                </tr>
            </tbody>
            <tbody v-for="item in notPetty">
                <tr>
                    <td v-if="item.has_credit_note" style="text-align: center; white-space: nowrap;" rowspan="4">{{item.no_urut}}</td>
                    <td v-else style="text-align: center; white-space: nowrap;" rowspan="3">{{item.no_urut}}</td>
                    <td v-if="item.isuraian" style="text-align: center; white-space: nowrap;">As Reference</td>
                    <td v-else style="white-space: nowrap;">{{item.po_no1}}<br />{{item.po_no2}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.ref_invoice_no}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.kode_pembayaran}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.etd}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.name}}</td>
                    <td style="text-align: center; word-break: break-all;">{{item.payment_pct}}%</td>
                    <td v-if="currency" style="width: 1px; border-right: 0 !important;  white-space: nowrap;">{{item.c}}</td>
                    <td v-if="currency" style="text-align: right; border-left: 0 !important; white-space: nowrap;">{{item.grand_total_price_original}}</td>
                    <td style="width: 1px; border-right: 0 !important;  white-space: nowrap;">Rp. </td>
                    <td style="text-align: right; border-left: 0 !important; white-space: nowrap;">{{item.grand_total_price}}</td>
                    <td style="text-align: left">{{item.notes}}</td>
                </tr>
                <tr>
                    <td v-if="currency" colspan="11"><b>Bank Info.</b> Bank Name:  {{ item.bank }}, Account No: {{ item.bank_account_no }}, Receiver Name: {{ item.bank_account_name }}</td>
                    <td v-else colspan="10"><b>Bank Info.</b> Bank Name:  {{ item.bank }}, Account No: {{ item.bank_account_no }}, Receiver Name: {{ item.bank_account_name }}</td>
                </tr>
                <tr v-if="item.has_credit_note">
                    <td v-if="currency" colspan="11"><b>{{ item.credit_note_display }}</b></td>
                    <td v-else colspan="10"><b>{{ item.credit_note_display }}</b></td>
                </tr>
                <tr v-if="item.isuraian">
                    <td v-if="currency" colspan="11">{{item.uraian}}</td>
                    <td v-else colspan="9">{{ item.uraian }}</td>
                </tr>
                <tr v-else>
                    <td v-if="currency" colspan="11">
                        Parts: {{item.parts}}</td>
                    <td v-else colspan="9">
                        Parts: {{item.parts}}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr v-if="petty_cash_currency">
                    <td style="text-align: center; font-weight: bold;" colspan="7">Total</td>
                    <td style="border-right: 0 !important; font-weight: bold;">{{currencyAsing}}</td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;">{{nonPettyCashCurrencyAsing}}</td>
                    <td style="border-right: 0 !important;">Rp.</td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;" colspan="2">{{totalNonPettyCash}}</td>
                </tr>
                <tr v-if="petty_cash">
                    <td style="text-align: center; font-weight: bold;" colspan="6">Total</td>
                    <td style="border-right: 0 !important; font-weight: bold;">Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;" colspan="2">{{totalNonPettyCash}} </td>
                </tr>
                <tr v-if="currency">
                    <td colspan="12"></td>
                </tr>
                <tr v-else>
                    <td colspan="10"></td>
                </tr>
                <tr v-if="has_credit_note_currency">
                    <td style="text-align: center; font-weight: bold;" colspan="7">Credit Note</td>
                    <td style="border-right: 0 !important; font-weight: bold;">{{currencyAsing}}</td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;">{{credit_note_asing}}</td>
                    <td style="border-right: 0 !important; font-weight: bold;">Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;" colspan="2">{{credit_note}}</td>
                </tr>
                <tr v-if="has_credit_note_idr">
                    <td style="text-align: center; border-right: 0 !important; font-weight: bold;" colspan="7">Credit Note</td>
                    <td style="border-right: 0 !important; font-weight: bold;" >Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;" colspan="2">{{credit_note}}</td>
                </tr>
                <tr v-if="currency">
                    <td style="text-align: center; font-weight: bold;" colspan="7">Total</td>
                    <td style="border-right: 0 !important; font-weight: bold;">{{currencyAsing}}</td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;">{{totalCurrencyAsing}}</td>
                    <td style="border-right: 0 !important; font-weight: bold;">Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;">{{total}}</td>
                    <td></td>
                </tr>
                <tr v-else>
                    <td style="text-align: center; border-right: 0 !important; font-weight: bold;" colspan="7">Total</td>
                    <td style="border-right: 0 !important; font-weight: bold;" >Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;" colspan="2">{{total}}</td>
                </tr>
                <tr v-if="currency">
                    <td style="text-align: center; font-weight: bold;" colspan="7">Grand Total</td>
                    <td style="border-right: 0 !important; font-weight: bold;">{{currencyAsing}}</td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;">{{grand_total_asing}}</td>
                    <td style="border-right: 0 !important; font-weight: bold;">Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;" colspan="2">{{ grand_total }}</td>
                </tr>
                <tr v-if="no_currency">
                    <td style="text-align: center; border-right: 0 !important; font-weight: bold;" colspan="7">Grand Total</td>
                    <td style="border-right: 0 !important; font-weight: bold;" >Rp. </td>
                    <td style="border-left: 0 !important; text-align: right; font-weight: bold;" colspan="2">{{ grand_total }}</td>
                </tr>

            </tfoot>
        </table>
        <table style="border: 0; border-collapse: collapse;
		font-size: 12px;
		width: 100%;">
            <tr>
                <td style="page-break-inside: avoid;">
                    <table style="border: 0; border-collapse: collapse;
				font-size: 12px;
				width: 100%;">
                        <tr>
                            <td style="text-align: center">Mengetahui</td>
                            <td></td>
                            <td style="text-align: center">Menyetujui</td>
                        </tr>
                        <tr>
                            <td style="height: 100px; text-align: center; ">
                                <img :src="mengetahui" style="height: 100px;" v-if="validated" />
                            </td>
                            <td>
                            </td>

                            <td style="height: 100px; text-align: center; ">
                                <img :src="menyetujui" style="height: 100px;" v-if="approved" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: bold; text-decoration: underline;">{{ approved1 }}</td>
                            <td></td>
                            <td style="text-align: center; font-weight: bold; text-decoration: underline;">{{ approved2 }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</template>

<style>

    * {
        font-size: 11px;
    }

    table.table {
        border: 0px;
        border-collapse: collapse;
        font-size: 11px;
        width: 100%;
    }

    table.table tr th {
        border-bottom: 1px solid #C5C5C5;
        margin-bottom: 4px;
        background-color: #DDEBF6;
    }

    table.table tr:last-child th {
        height: 4px;
    }

    table.table th>div {
        padding: 0 4px;
        border-radius: 3px;
        text-align: center;
        white-space: nowrap;
    }

    table.table td>div {
        padding: 0 4px;
        border-radius: 3px;
        white-space: nowrap;
    }

    table.table td {
        padding: 1.5px;
    }

    table.table td,
    table.table th {
        padding: 2px 4px;
        border: 1px solid black !important;
        min-width: 20px;
    }

    table.table td.table-title {
        font-weight: bold;
        text-align: center;
    }

    tr.page-break {
        page-break-before: always;
    }

    .tdvtop>tbody>tr>td {
        vertical-align: top;
    }

	.pdf-l{
		display: block; 
		width: 100%; 
		object-fit: cover;
		margin: auto;
		page-break-after: always;
	}

	.pdf-p{
		display: block; 
		max-width: 100%;
		/* min-height: 100%;  */
		object-fit: cover;
		margin: auto;
		page-break-after: always;
	}

	@media print {

		.pdf-l{
			display: block; 
			max-height: 100%; 
			width: 100%; 
			object-fit: cover;
			page-break-after: always;
		}

		.pdf-p{
			display: block; 
			height: 100%; 
			max-width: 100%; 
			object-fit: cover;
			page-break-after: always;
		}
	}

</style>

<script>
    module.exports = {
        name: '',
        data: function() {
            return {

            }
        },
        methods: {

        },
        mounted: function() {

        }
    }
</script>
