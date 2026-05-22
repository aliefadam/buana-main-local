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
    <h2>DAILY REPORT SUMMARY</h2><br/>
    <h2>DATE: <?= $dateactivity; ?></h2><br/><br/>
    
    <h2>ACTIVITY AND TOTAL OF Part</h2><br/>

    <tr class="">
            <?php 
            foreach ($items as $key => $value) : ?>
            <tr>
			    <td><?php echo $value['type_report'] ?></td>
			    <td>: <?php if ($value['type_report']=='Pre-assembly') 
	{echo $value['subassy'];
} else {
	echo $value['value'];
}?> </td>
		    </tr>
            <?php endforeach;?>
    </tr>


    <br/><br/><span> https://internal.buanamultiteknik.com/api/report/summary/index?dateactivity=<?= $dateactivity; ?></span>
</body>
</html>