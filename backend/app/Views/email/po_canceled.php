<html>
<head>
    <style type='text/css'>
        body {background-color: #CCD9F9;
             font-family: Verdana, Geneva, sans-serif}
		table{
			border: 0;
		}

    </style>
</head>
<body>

    <h3>Final Approved PO Cancel</h3>
    <table>
	<tr>
	    <td>No PO</td>
		<td>:<?= $po_no; ?></td>
	</tr>
	<tr>
			<td>Title</td>
			<td>:<?= $title; ?></td>
		</tr>
		<tr>
			<td>Approved By</td>
			<td>:<?= $user; ?></td>
		</tr>
		<tr>
			<td>Approved Date</td>
			<td>:<?= $date; ?></td>
		</tr>
		<tr>
			<td>Supplier</td>
			<td>:<?= $supplier; ?></td>
		</tr>
		 <tr>
			<td>No RFQ</td>
			<td>:<?= $rfq; ?></td>
		</tr>
		<tr>
			<td>No PR</td>
			<td>:<?= $pr_no; ?></td>
		</tr>
	</table>

</body>
</html>