<?php

require 'vendor/autoload.php'; 
require 'db_connection.php';   

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

global $conn;
$conn= connect_db();
$sql = "SELECT * FROM products ORDER BY id DESC"; 
$result = mysqli_query($conn, $sql);


if (!$result) {
    die("Lỗi truy vấn SQL: " . mysqli_error($conn));
}


$inputFileName = 'template_baocao.xlsx';

try {
    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();

    
    $sheet->setCellValue('C2', date('d/m/Y H:i:s'));

  
    $rowIndex = 5;
    $stt = 1;

    
    while ($row = mysqli_fetch_assoc($result)) {
        
      
        $sheet->setCellValue('A' . $rowIndex, $stt);
        
        
        $sheet->setCellValue('B' . $rowIndex, $row['name']);
        
        
        $sheet->setCellValue('C' . $rowIndex, $row['category_id']); 
        
        
        $sheet->setCellValue('D' . $rowIndex, $row['price']);

       
        $rowIndex++;
        $stt++;
    }

    
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
   
    $sheet->getStyle('A5:D' . ($rowIndex - 1))->applyFromArray($styleArray);


   
    $sheet->setCellValue('C' . $rowIndex, 'TỔNG CỘNG:');
    $sheet->setCellValue('D' . $rowIndex, '=SUM(D5:D'.($rowIndex-1).')');
    $sheet->getStyle('C'.$rowIndex.':D'.$rowIndex)->getFont()->setBold(true);


   
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Danh_Sach_Mon_'.date('Y-m-d').'.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    echo 'Lỗi: ', $e->getMessage();
}
?>