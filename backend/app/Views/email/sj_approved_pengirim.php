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
    <h3>Approved Surat Jalan</h3>
    <table>
        <tr>
			<td>No:</td>
			<td>:<?= $sj_no; ?></td>
		</tr>
		<tr>
			<td>Approved By</td>
			<td>:<?= $approved_by_name; ?></td>
		</tr>
		<tr>
			<td>Approved Date</td>
			<td>:<?= $approved_date; ?></td>
	</table>
</body>
</html>