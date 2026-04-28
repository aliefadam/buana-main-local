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
    <h3>Rejected Surat Jalan</h3>
    <table>
        <tr>
			<td>No:</td>
			<td>:<?= $sj_no; ?></td>
		</tr>
		<tr>
			<td>Rejected By</td>
			<td>:<?= $rejected_by_name; ?></td>
		</tr>
		<tr>
			<td>Rejected Date</td>
			<td>:<?= $rejected_date; ?></td>
		</tr>
			<tr>
			<td>Rejected Notes</td>
			<td>:<?= $rejected_notes; ?></td>
		</tr>
	</table>
</body>
</html>