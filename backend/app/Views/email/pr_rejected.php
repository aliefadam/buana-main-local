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

    <h3>PR <?= $title; ?></h3>
    <table>
		<tr>
			<td>No PR</td>
			<td>:<?= $pr_no; ?></td>
		</tr>
		<tr>
			<td>Subject</td>
			<td>:<?= $pr_subject; ?></td>
		</tr>
		<tr>
			<td>Rejected By</td>
			<td>:<?= $rejected_name; ?></td>
		</tr>
        <tr>
			<td>Rejected Notes</td>
			<td>:<?= $rejected_notes; ?></td>
		</tr>
	</table>

</body>
</html>