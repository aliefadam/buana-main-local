<html>
<head>
    <style type='text/css'>
        body {font-family: Verdana, Geneva, sans-serif}
		table{
			border: 0;
		}

    </style>
</head>
<body>
<h3>Request Approval NPB</h3>
    <table>
         <tr>
			<td>NPB No</td>
			<td>:<?= $npb_no; ?></td>
		</tr>
		<tr>
			<td>Notes</td>
			<td>:<?= $notes; ?></td>
		</tr>
		<tr>
			<td>Requestor</td>
			<td>:<?= $peminta_name; ?></td>
		</tr>
        <tr>
			<td>Kepala Bagian</td>
			<td>:<?= $kabag_name; ?></td>
		</tr>
        <tr>
			<td><br/>Link Approval NPB: </td>
            <td>https://internal.buanamultiteknik.com/#/warehouse/bom/warehouse/npb_approval/</td>
		</tr>
	</table>
</body>
</html>