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
    <h3>Final Approval NPB</h3>
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
			<td>Validated By</td>
			<td>:<?= $validator_name; ?></td>
		</tr>
        <tr>
			<td>Approved By</td>
			<td>:<?= $approver_name; ?></td>
		</tr>
        <tr>
			<td>Document : </td>
			<td>https://internal.buanamultiteknik.com/api/report/npb/index?npb_id=<?= $npb_id; ?></td>
		</tr>
	</table>
</body>
</html>