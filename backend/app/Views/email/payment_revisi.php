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

    <h3><?= $msg; ?></h3>
    <table>
		<tr>
			<td>No Payment:</td>
			<td><?= $payment_no; ?></td>
		</tr>
		<tr>
			<td>Notes:</td>
			<td><?= $notes; ?></td>
		</tr>
	</table>

</body>
</html>