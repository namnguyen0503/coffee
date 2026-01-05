<?php
// 1. Thiết lập múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

require '../vendor/autoload.php'; 
require '../../includes/db_connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$conn = connect_db();

// 2. Tạo Spreadsheet mới
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- TIÊU ĐỀ ---
$sheet->setCellValue('A1', "DANH SÁCH MÃ GIẢM GIÁ (VOUCHER)");
$sheet->mergeCells('A1:E1'); 
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Ngày xuất: " . date('d/m/Y H:i'));
$sheet->mergeCells('A2:E2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- HEADER CỘT ---
$headers = [
    'A3' => 'ID', 
    'B3' => 'Mã Code', 
    'C3' => 'Giảm giá (%)', 
    'D3' => 'Mô tả',
    'E3' => 'Ngày tạo'
];

foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $style = $sheet->getStyle($cell);
    $style->getFont()->setBold(true);
    $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Chỉnh độ rộng cột
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(40); // Mô tả dài nên để rộng
$sheet->getColumnDimension('E')->setWidth(20);

// --- LẤY DỮ LIỆU ---
$sql = "SELECT * FROM vouchers ORDER BY id DESC";
$query = mysqli_query($conn, $sql);
$rowNum = 4;

while ($row = mysqli_fetch_assoc($query)) {
    $sheet->setCellValue('A' . $rowNum, $row['id']);
    $sheet->setCellValue('B' . $rowNum, $row['code']);
    $sheet->setCellValue('C' . $rowNum, $row['discount_percent'] . '%');
    $sheet->setCellValue('D' . $rowNum, $row['description']);
    
    // Format ngày tạo (Nếu có dữ liệu)
    $createDate = !empty($row['created_at']) ? date('H:i d/m/Y', strtotime($row['created_at'])) : '';
    $sheet->setCellValue('E' . $rowNum, $createDate);

    // Canh giữa các cột ID, Code, % và Ngày
    $sheet->getStyle('A' . $rowNum . ':C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Kẻ viền
    $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
    $rowNum++;
}

// --- XUẤT FILE ---
$filename = "Danh_sach_voucher_" . date('d-m-Y') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>