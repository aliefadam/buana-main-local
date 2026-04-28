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
    <h3>Rejected SPB <?= $spb_no; ?></h3>
    <table>
		<tr>
			<td>Notes</td>
			<td>:<?= $notes; ?></td>
		</tr>
		<tr>
			<td>Rejected By</td>
			<td>:<?= $rejected_by; ?></td>
		</tr>
	</table>
</body>
</html>