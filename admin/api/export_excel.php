<?php
// Load thư viện PhpSpreadsheet
require '../vendor/autoload.php'; 
require '../tinh-nang/db_connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$conn = connect_db();

// Lấy tham số ngày
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d', strtotime('-6 days'));
$start_sql = "$start_date 00:00:00";
$end_sql = "$end_date 23:59:59";

// Tạo Spreadsheet mới
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 1. Tiêu đề báo cáo
$sheet->setCellValue('A1', "BÁO CÁO DOANH THU");
$sheet->mergeCells('A1:D1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Từ ngày: $start_date đến $end_date");
$sheet->mergeCells('A2:D2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 2. Header cột
$headers = ['A3' => 'Mã Đơn', 'B3' => 'Thời gian', 'C3' => 'Nhân viên', 'D3' => 'Thành tiền'];
foreach ($headers as $cell => $val) {
    $sheet->setCellValue($cell, $val);
    $sheet->getStyle($cell)->getFont()->setBold(true);
    $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
}
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);

// 3. Lấy dữ liệu
$sql = "SELECT o.id, o.order_date, o.final_amount, u.fullname 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.status = 'paid' 
        AND o.order_date BETWEEN '$start_sql' AND '$end_sql' 
        ORDER BY o.order_date ASC";
$query = mysqli_query($conn, $sql);

$rowNum = 4;
$totalRevenue = 0;

while ($row = mysqli_fetch_assoc($query)) {
    $sheet->setCellValue('A' . $rowNum, '#' . $row['id']);
    $sheet->setCellValue('B' . $rowNum, date('H:i d/m/Y', strtotime($row['order_date'])));
    $sheet->setCellValue('C' . $rowNum, $row['fullname']);
    $sheet->setCellValue('D' . $rowNum, $row['final_amount']);
    $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    
    $totalRevenue += $row['final_amount'];
    $rowNum++;
}

// 4. Dòng tổng cộng
$sheet->setCellValue('A' . $rowNum, 'TỔNG CỘNG');
$sheet->mergeCells('A' . $rowNum . ':C' . $rowNum);
$sheet->setCellValue('D' . $rowNum, $totalRevenue);
$sheet->getStyle('A' . $rowNum . ':D' . $rowNum)->getFont()->setBold(true);
$sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');

// 5. Xuất file
$filename = "Bao_cao_doanh_thu_" . time() . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>