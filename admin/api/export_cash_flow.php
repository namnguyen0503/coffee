<?php
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

$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end   = isset($_GET['end'])   ? $_GET['end']   : date('Y-m-d');
$diff_only = isset($_GET['diff_only']) && $_GET['diff_only'] == 'true';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- HEADER ---
$sheet->setCellValue('A1', "BÁO CÁO KIỂM SOÁT TIỀN MẶT");
$sheet->mergeCells('A1:J1'); 
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Từ: " . date('d/m/Y', strtotime($start)) . " - Đến: " . date('d/m/Y', strtotime($end)));
$sheet->mergeCells('A2:J2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$headers = [
    'A3'=>'Mã Ca', 'B3'=>'Nhân viên', 'C3'=>'Bắt đầu', 'D3'=>'Kết thúc',
    'E3'=>'Tiền Chốt Ca Trước', 'F3'=>'Tiền Vào Ca Này', 'G3'=>'Chênh Lệch',
    'H3'=>'Doanh Thu Ca', 'I3'=>'Tiền Chốt Ca', 'J3'=>'Ghi chú'
];

foreach ($headers as $k => $v) {
    $sheet->setCellValue($k, $v);
    $sheet->getStyle($k)->getFont()->setBold(true);
    $sheet->getStyle($k)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle($k)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
    $sheet->getStyle($k)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($k)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
}

// Chỉnh độ rộng cột
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(25); 
$sheet->getColumnDimension('C')->setWidth(20); 
$sheet->getColumnDimension('D')->setWidth(20); 
$sheet->getColumnDimension('E')->setWidth(22); 
$sheet->getColumnDimension('F')->setWidth(22); 
$sheet->getColumnDimension('G')->setWidth(18); 
$sheet->getColumnDimension('H')->setWidth(18); 
$sheet->getColumnDimension('I')->setWidth(18); 
$sheet->getColumnDimension('J')->setWidth(30);

// --- DỮ LIỆU ---
$sql = "SELECT curr.*, u.fullname,
        (SELECT prev.end_cash FROM work_sessions prev WHERE prev.id < curr.id ORDER BY prev.id DESC LIMIT 1) as prev_end_cash
        FROM work_sessions curr
        LEFT JOIN users u ON curr.user_id = u.id
        WHERE DATE(curr.start_time) BETWEEN '$start' AND '$end'
        ORDER BY curr.id DESC";

$query = mysqli_query($conn, $sql);
$rowNum = 4;

while ($row = mysqli_fetch_assoc($query)) {
    $current_start = floatval($row['start_cash']);
    $prev_end = ($row['prev_end_cash'] !== null) ? floatval($row['prev_end_cash']) : $current_start;
    $diff = $current_start - $prev_end;

    if ($diff_only && $diff == 0) continue; 

    $is_active = empty($row['end_time']) || $row['end_time'] == '0000-00-00 00:00:00';
    $endTimeTxt = $is_active ? 'Đang làm' : date('H:i d/m/Y', strtotime($row['end_time']));

    $sheet->setCellValue('A'.$rowNum, '#'.$row['id']);
    $sheet->setCellValue('B'.$rowNum, $row['fullname']);
    $sheet->setCellValue('C'.$rowNum, date('H:i d/m/Y', strtotime($row['start_time']))); 
    $sheet->setCellValue('D'.$rowNum, $endTimeTxt);
    
    $sheet->setCellValue('E'.$rowNum, $prev_end);
    $sheet->setCellValue('F'.$rowNum, $current_start);
    
    // --- SỬA Ở ĐÂY: Dùng abs() để lấy số dương ---
    $sheet->setCellValue('G'.$rowNum, abs($diff));
    // ---------------------------------------------
    
    $sheet->setCellValue('H'.$rowNum, floatval($row['total_sales'])); 
    $sheet->setCellValue('I'.$rowNum, floatval($row['end_cash']));
    $sheet->setCellValue('J'.$rowNum, $row['note']);

    // Format số
    $sheet->getStyle('E'.$rowNum.':I'.$rowNum)->getNumberFormat()->setFormatCode('#,##0');

    // Căn giữa các cột không phải số
    $sheet->getStyle('A'.$rowNum.':D'.$rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Tô màu nếu lệch (Vẫn dùng biến $diff gốc để kiểm tra có lệch hay không)
    if ($diff != 0) {
        $sheet->getStyle('F'.$rowNum.':G'.$rowNum)->getFont()->setColor(new Color('FFFF0000')); 
        $sheet->getStyle('G'.$rowNum)->getFont()->setBold(true);
    }
    
    // Kẻ viền cho từng dòng
    $sheet->getStyle('A'.$rowNum.':J'.$rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    $rowNum++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Kiem_soat_tien_ca_' . date('d-m-Y') . '.xlsx"'); 
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>