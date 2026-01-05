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
use PhpOffice\PhpSpreadsheet\Style\Color;

$conn = connect_db();

// 2. Tạo Spreadsheet mới
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- TIÊU ĐỀ ---
$sheet->setCellValue('A1', "DANH SÁCH NHÂN VIÊN");
$sheet->mergeCells('A1:F1'); 
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Ngày xuất: " . date('d/m/Y H:i'));
$sheet->mergeCells('A2:F2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- HEADER CỘT ---
$headers = [
    'A3' => 'ID', 
    'B3' => 'Họ và Tên', 
    'C3' => 'Tên đăng nhập', 
    'D3' => 'Chức vụ',
    'E3' => 'Trạng thái',
    'F3' => 'Ngày tạo'
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
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(25);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(18);
$sheet->getColumnDimension('F')->setWidth(20);

// --- LẤY DỮ LIỆU ---
// Chỉ lấy các trường cần thiết, bỏ qua password và status_work
$sql = "SELECT id, fullname, username, role, status, created_at FROM users ORDER BY id ASC";
$query = mysqli_query($conn, $sql);
$rowNum = 4;

while ($row = mysqli_fetch_assoc($query)) {
    // 1. Xử lý chức vụ (Việt hóa)
    $roleText = $row['role'];
    if ($row['role'] == 'admin') $roleText = 'Quản trị viên';
    elseif ($row['role'] == 'staff') $roleText = 'Nhân viên';
    elseif ($row['role'] == 'wh-staff') $roleText = 'Thủ kho';

    // 2. Xử lý trạng thái (Theo yêu cầu của bạn)
    $statusText = 'Đã khóa';
    $statusColor = 'FF0000'; // Đỏ

    if ($row['status'] == 1) {
        $statusText = 'Đang làm việc';
        $statusColor = '008000'; // Xanh lá
    }

    // 3. Format ngày tạo
    $createDate = !empty($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '';

    // 4. Ghi vào Excel
    $sheet->setCellValue('A' . $rowNum, $row['id']);
    $sheet->setCellValue('B' . $rowNum, $row['fullname']);
    $sheet->setCellValue('C' . $rowNum, $row['username']);
    $sheet->setCellValue('D' . $rowNum, $roleText);
    $sheet->setCellValue('E' . $rowNum, $statusText);
    $sheet->setCellValue('F' . $rowNum, $createDate);

    // Canh giữa
    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D' . $rowNum . ':F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Tô màu trạng thái
    $sheet->getStyle('E' . $rowNum)->getFont()->setColor(new Color($statusColor));
    $sheet->getStyle('E' . $rowNum)->getFont()->setBold(true);

    // Kẻ viền
    $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
    $rowNum++;
}

// --- XUẤT FILE ---
$filename = "Danh_sach_nhan_vien_" . date('d-m-Y') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>