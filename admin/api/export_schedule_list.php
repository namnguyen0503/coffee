<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require '../vendor/autoload.php'; 
require '../../includes/db_connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$conn = connect_db();

$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-t');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$sheet->setCellValue('A1', "DANH SÁCH CA LÀM VIỆC CHI TIẾT");
$sheet->mergeCells('A1:D1'); 
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Từ: " . date('d/m/Y', strtotime($start)) . " Đến: " . date('d/m/Y', strtotime($end)));
$sheet->mergeCells('A2:D2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$headers = ['A3'=>'Ngày', 'B3'=>'Thứ', 'C3'=>'Ca làm việc', 'D3'=>'Nhân viên trực'];
foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $sheet->getStyle($cell)->getFont()->setBold(true);
    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
    $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(25);

// Data
$sql = "SELECT s.*, u.fullname 
        FROM work_schedules s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.shift_date BETWEEN '$start' AND '$end' 
        ORDER BY s.shift_date ASC, 
        CASE s.shift_type WHEN 'morning' THEN 1 WHEN 'afternoon' THEN 2 WHEN 'evening' THEN 3 END";

$query = mysqli_query($conn, $sql);
$rowNum = 4;
$days_vn = ['Sun'=>'Chủ nhật','Mon'=>'Thứ 2','Tue'=>'Thứ 3','Wed'=>'Thứ 4','Thu'=>'Thứ 5','Fri'=>'Thứ 6','Sat'=>'Thứ 7'];

while ($row = mysqli_fetch_assoc($query)) {
    $shiftName = '';
    if($row['shift_type']=='morning') $shiftName = 'Sáng';
    elseif($row['shift_type']=='afternoon') $shiftName = 'Chiều';
    else $shiftName = 'Tối';

    $sheet->setCellValue('A' . $rowNum, date('d/m/Y', strtotime($row['shift_date'])));
    $sheet->setCellValue('B' . $rowNum, $days_vn[date('D', strtotime($row['shift_date']))]);
    $sheet->setCellValue('C' . $rowNum, $shiftName);
    $sheet->setCellValue('D' . $rowNum, $row['fullname']);
    
    $sheet->getStyle('A'.$rowNum.':D'.$rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('A'.$rowNum.':C'.$rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $rowNum++;
}

$filename = "DS_Ca_Lam_" . date('dmY') . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>