<style>
    /* body{
			width: 21cm;
			height: 29.7cm;
			margin: 12.70mm 12.70mm 12.70mm 12.70mm; 
	}  */
    * {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
    }

    /* @media print {
		body{
				width: 21cm;
				height: 29.7cm;
				margin: 12.70mm 12.70mm 12.70mm 12.70mm; 
		} 
	} */

    .logo {
        background-image: url("https://internal.buanamultiteknik.com/img/logo.png");
        background-repeat: no-repeat;
        background-size: 65.93mm 23.42mm;
        background-color: red;
    }

    .bold {
        font-weight: 600;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid black;
    }

    .table td,
    .table th {
        border: 1px solid black;
        overflow-wrap: break-word;
        word-break: break-all;
        vertical-align: top;
        padding-left: 2px;
        padding-right: 2px;
    }

    .table th {
        background-color: #acb9ca;
    }

    .table.no-border {
        border: 0;
    }

    .table.no-border td,
    .table.no-border th {
        border: 0;
    }

    .table.td-no-vertical-border td {
        border-top: 0;
        border-bottom: 0;
    }

    .table td.border-all {
        border: 1px solid black;
    }

    .vcenter {
        vertical-align: middle !important;
    }

    .hcenter {
        text-align: center !important;
    }

    .right-align {
        text-align: right !important;
    }

    .left-align {
        text-align: left !important;
    }

    .grid>* {
        vertical-align: top !important;
        border-right: 1px solid black;
        margin: 0;
        padding: 2px;
        height: 100%;
    }
</style>
<table class="table no-border">
    <tr>
        <td colspan="4" class="hcenter vcenter bold" style="height: 23.42mm">
            <img src="{logo}" style="width: 65.93mm; height: 23.42mm; position: absolute; top: 9; left: 0;">
            Purchase Order</td>
    </tr>
    <tr>
        <td colspan="2" class="bold">To</td>
        <td style="width: 17.53mm"></td>
        <td style="width: 41.10mm"></td>
    </tr>
    <tr>
        <td colspan="2" class="bold">{supplier_name}</td>
        <td style="width: 17.53mm">PO</td>
        <td style="width: 41.10mm">: {po_no}</td>
    </tr>
    <tr>
        <td colspan="2" rowspan="2">{address}</td>
        <td style="width: 17.53mm">Date</td>
        <td style="width: 41.10mm">: {po_date}</td>
    </tr>
    <tr>
        <td style="width: 17.53mm">Subject</td>
        <td style="width: 41.10mm">: {title}</td>
    </tr>
    <tr>
        <td colspan="2">Attn : <span class="bold">{pic_name}</span></td>
        <td style="width: 17.53mm"></td>
        <td style="width: 41.10mm"></td>
    </tr>
    <tr>
        <td colspan="4">With reference to your submitted offer, herewith we send you our purchased order with following details:</td>
    </tr>
</table>
<div style="border: 1px solid black">
<table class="table td-no-vertical-border">
    <thead>
        <th style="width: 10.97mm">No</th>
        <th>Item Name</th>
        <th style="width: 14.27mm">Qty</th>
        <th style="width: 25.47mm">Unit</th>
        <th style="width: 34.93mm" colspan="2">Unit Price</th>
        <th style="width: 41.13mm" colspan="2">Total Price</th>
    </thead>
    <tbody>
        <tr>
            <td>&nbsp;</td>
            <td></td>
            <td></td>
            <td></td>
            <td style="width: 9.63mm; border-right: 0 !important;"></td>
            <td style="border-left: 0 !important;"></td>
            <td style="width: 9.63mm; border-right: 0 !important;"></td>
            <td style="border-left: 0 !important;"></td>
        </tr>
        {items}
        <tr>
            <td style="padding-left: 5px;">{no}</td>
            <td>Device Name: {item_name}
            </td>
            <td class="right-align">
                {order_qty}
            </td>
            <td>{unit}</td>
            <td style="width: 9.63mm; border-right: 0 !important;">{c}</td>
            <td class="right-align" style="width: 25.29mm; border-left: 0 !important;">{price_per_item}</td>
            <td style="width: 9.63mm; border-right: 0 !important;">{c}</td>
            <td class="right-align" style="width: 31.54mm; border-left: 0 !important;">{total_price_per_item}</td>
        </tr>
        <tr>
            <td></td>
            <td>
                Original Manufacture: {original_manufacture}
            </td>
            <td></td>
            <td></td>
            <td style="width: 9.63mm; border-right: 0 !important;"></td>
            <td style="border-left: 0 !important;"></td>
            <td style="width: 9.63mm; border-right: 0 !important;"></td>
            <td style="border-left: 0 !important;"></td>
        </tr>
        <tr>
            <td></td>
            <td>
                Manufacture PN/Type: {manufacture_pn}
            </td>
            <td></td>
            <td></td>
            <td style="width: 9.63mm; border-right: 0 !important;"></td>
            <td style="border-left: 0 !important;"></td>
            <td style="width: 9.63mm; border-right: 0 !important;"></td>
            <td style="border-left: 0 !important;"></td>
        </tr>
        <tr>
            <td></td>
            <td>
                Description: {specification}
            </td>
            <td></td>
            <td></td>
            <td style="width: 9.63mm; border-right: 0 !important;"></td>
            <td style="border-left: 0 !important;"></td>
            <td style="width: 9.63mm; border-right: 0 !important;"></td>
            <td style="border-left: 0 !important;"></td>
        </tr>
        {/items}
		<tr>
            <td colspan="8" class="border-all">&nbsp;</td>
        </tr>
		<tr>
			<td class="border-all hcenter bold" colspan="6">&nbsp;Total =</td>
			<td class="border-all bold" colspan="2">{c} {total}</td>
		</tr>
		<tr>
			<td class="border-all" colspan="8">Say</td>
		</tr>
		<tr>
			<td class="border-all bold vcenter" colspan="8">{terbilang} {currency}</td>
		</tr>
    </tbody>
</table>
</div>
<!-- <table class="table no-border">
    <tr>
        <td>

            <table class="table no-border">
                <tr>
                    <td class="border-all hcenter bold">&nbsp;Total =</td>
                    <td class="border-all bold" style="width:42.13mm">{c} {total}</td>
                </tr>
                <tr>
                    <td class="border-all" colspan="2">Say</td>
                </tr>
                <tr>
                    <td class="border-all bold vcenter" colspan="2">{terbilang} {currency}</td>
                </tr>
            </table>
        </td>
    </tr>
</table> -->
<br />
<span style="text-decoration: underline;">Notes:</span><br />
{keterangan}
<br />
<br />
<div style="page-break-inside: avoid;">
    <table class="table no-border" style="border: 0;">
        <tbody>
            <tr>
                <td style="width: 16.63mm"></td>
                <td style="width: 59.41mm" class="bold">{pt}<br />&nbsp;</td>
                <td></td>
            </tr>
            <tr>
                <td style="width: 16.63mm"></td>
                <td style="width: 59.41mm">
                    <img src="{barcode}" style="width: 30mm; height: 30mm" /><br />
                    <br />
                    <span style="text-decoration: underline" class="bold">{name}</span><br />
                    <span class="bold">{jabatan}</span>
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>