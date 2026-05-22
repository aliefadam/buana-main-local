<?php
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
	function exportExcel($arr = array(), $fileName = "filename"){
		$styleHeader = [
            'font' => [
                'color' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
            'alignment' => [
                //'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FF879bd7',//BACBE5
                ]
            ],
        ];
        
        $styleArray = [
            'font' => [
                //'bold' => true,
            ],
            'alignment' => [
                //'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFDCE6F1',
                ]
            ],
        ];
        
        $styleArray2 = [
            'font' => [
                //'bold' => true,
            ],
            'alignment' => [
                //'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFFFF',
                ]
            ],
        ];
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $i = 0;
        $columns = "";
        $z = 1;
        $num = 0;
        foreach ($arr as $key => $value) {
            $value = json_decode(json_encode($value), true);
        	$row = $value;
        	if(isset($value["value"] )){
        		$row = $value["value"];
        	}
        	if($row == null){
        
        	}
        	else{
        		$row = json_decode(json_encode($row), true);
        		$tmpColumns = array_keys($row);
        
        		if(implode(",", $tmpColumns)!=$columns){//header
        			$i = 0;
        
        			$num = getNameFromNumber(count($tmpColumns)-1);
        			$spreadsheet->getActiveSheet()->fromArray($tmpColumns, NULL, 'A'.$z);
        			$spreadsheet->getActiveSheet()->getStyle('A'.$z.':'.$num.$z)->applyFromArray($styleHeader);
        			$z++;
        			$i++;
        		}
        		$spreadsheet->getActiveSheet()->fromArray(array_values($row), NULL, 'A'.$z);
        		if($i%2==0)
        			$spreadsheet->getActiveSheet()->getStyle('A'.$z.':'.$num.$z)->applyFromArray($styleArray);
        		else
        			$spreadsheet->getActiveSheet()->getStyle('A'.$z.':'.$num.$z)->applyFromArray($styleArray2);
        
        		$columns = implode(",", $tmpColumns);
        	}
        	$z++;
        	$i++;
        }
        foreach ($sheet->getColumnIterator() as $column) {
           $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
        $writer = new Xlsx($spreadsheet);
                
        //$writer->save('CreateExcelTable.xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
	}

	function getNameFromNumber($num) {
        $numeric = $num % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($num / 26);
        if ($num2 > 0) {
            return getNameFromNumber($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }