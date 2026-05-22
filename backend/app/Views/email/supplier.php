<html>
<head>
    <style type='text/css'>
        body {
             font-family: Verdana, Geneva, sans-serif}
		table{
			border: 0;
		}

    </style>
</head>
<body>

    <?= $salutation; ?><br/>
	<br/>
    Bersama ini telah kami lampirkan PO yang sudah disetujui dengan keterangan sebagai berikut:<br/><br/>
	&nbsp;&nbsp;&nbsp;&nbsp;1.&nbsp;&nbsp;PO no. <?= $po_no; ?> // <?= $rfq; ?> // <?= $pr_no; ?> // <?= $title; ?><br/><br/>
	Mohon dikirimkan invoice dan faktur pajak untuk selanjutnya bisa kami proses pembayarannya.<br/><br/><br/>
	Demikian kami sampaikan, terima kasih atas perhatian dan kerjasamanya.<br/><br/><br/>
	Hormat kami,<br/>
	<b><?= $sender; ?></b><br/>
	<b>PT. BUANA MULTI TEKNIK</b><br/>
	Spazio Tower Office Building<br/>
	8th Floor, Unit 808-809<br/>
	Jl. Mayjend Yono Soewoyo No. 35<br/>
	Pradahkalikendal, Kec. Dukuhpakis<br/>
	Surabaya 60226
</body>
</html>