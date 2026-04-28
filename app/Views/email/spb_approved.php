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
    <h3>Final Approval <?= $spb_no; ?></h3>
    <table>
		<tr>
			<td>Notes</td>
			<td>:<?= $notes; ?></td>
		</tr>
		<tr>
			<td>Validated By</td>
			<td>:<?= $validator_name; ?></td>
		</tr>
        <tr>
			<td>Approved By</td>
			<td>:<?= $approver_name; ?></td>
		</tr>
	</table>
</body>
</html>