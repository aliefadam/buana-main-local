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
    <h3>Revised Surat Jalan</h3>
    <table>
        <tr>
			<td>No</td>
			<td>:<?= $sj_no; ?></td>
		</tr>
		<tr>
			<td>Revised By</td>
			<td>:<?= $revised_by_name; ?></td>
		</tr>
		<tr>
			<td>Revised Date</td>
			<td>:<?= $revised_date; ?></td>
		</tr>
			<tr>
			<td>Revised Notes</td>
			<td>:<?= $revised_notes; ?></td>
		</tr>
	</table>
</body>
</html>