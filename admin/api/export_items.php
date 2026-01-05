<?php
// 1. Load thư viện và kết nối CSDL
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

// --- PHẦN 1: TIÊU ĐỀ BÁO CÁO ---
$sheet->setCellValue('A1', "DANH SÁCH MÓN ĂN & ĐỒ UỐNG");
// Gộp ô từ A1 đến E1 (5 cột)
$sheet->mergeCells('A1:E1'); 
// Định dạng: In đậm, Cỡ chữ 14, Canh giữa
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Ngày xuất báo cáo: " . date('d/m/Y H:i'));
$sheet->mergeCells('A2:E2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- PHẦN 2: HEADER CỘT (Dòng 3) ---
$headers = [
    'A3' => 'ID', 
    'B3' => 'Tên Món', 
    'C3' => 'Danh Mục', 
    'D3' => 'Giá Tiền',
    'E3' => 'Trạng Thái'
];

foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $style = $sheet->getStyle($cell);
    
    // In đậm
    $style->getFont()->setBold(true);
    // Kẻ khung viền mỏng
    $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    // Tô màu nền xám nhạt (giống file mẫu)
    $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
    // Canh giữa
    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Chỉnh độ rộng cột cho đẹp
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);

// --- PHẦN 3: ĐỔ DỮ LIỆU ---
$sql = "SELECT p.id, p.name, c.name as category_name, p.price, p.status 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.category_id ASC, p.id DESC";
$query = mysqli_query($conn, $sql);

$rowNum = 4; // Bắt đầu ghi dữ liệu từ dòng 4

while ($row = mysqli_fetch_assoc($query)) {
    // Xử lý trạng thái
    $statusText = ($row['status'] == 1) ? 'Đang bán' : 'Ngừng bán';
    
    // Ghi dữ liệu
    $sheet->setCellValue('A' . $rowNum, $row['id']);
    $sheet->setCellValue('B' . $rowNum, $row['name']);
    $sheet->setCellValue('C' . $rowNum, $row['category_name']);
    $sheet->setCellValue('D' . $rowNum, $row['price']);
    $sheet->setCellValue('E' . $rowNum, $statusText);
    
    // Định dạng dữ liệu từng dòng
    // ID canh giữa
    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Giá tiền: Format số có dấu phẩy (10,000)
    $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    
    // Trạng thái canh giữa
    $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Kẻ viền cho từng dòng dữ liệu (để bảng đẹp hơn)
    $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
    $rowNum++;
}

// --- PHẦN 4: XUẤT FILE ---
$filename = "Danh_sach_mon_" . date('d-m-Y') . ".xlsx";

// Header bắt buộc để trình duyệt hiểu là file Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>