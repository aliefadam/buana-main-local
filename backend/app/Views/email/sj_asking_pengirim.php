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
    <h3>Asking Approval Surat Jalan</h3>
    <table>
        <tr>
			<td>No:</td>
			<td>:<?= $sj_no; ?></td>
		</tr>
		<tr>
			<td>From:</td>
			<td>:<?= $from; ?></td>
		</tr>
        <tr>
			<td>To</td>
			<td>:<?= $place; ?></td>
		</tr>
		<tr>
			<td>Pengirim</td>
			<td>:<?= $pengirim_name; ?></td>
		</tr>
		<tr>
			<td>Notes</td>
			<td>:<?= $sj_notes; ?></td>
		</tr>
        <tr>
			<td><br/>Link Approval Surat Jalan: </td>
            <td></td>
		</tr>
	</table>
</body>
</html>