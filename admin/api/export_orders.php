<?php
// 1. Thiết lập múi giờ Việt Nam
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

// 2. Nhận tham số ngày
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');

$start_sql = "$start_date 00:00:00";
$end_sql = "$end_date 23:59:59";

// 3. Tạo Spreadsheet mới
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// --- TIÊU ĐỀ ---
$sheet->setCellValue('A1', "DANH SÁCH ĐƠN HÀNG");
// Gộp ô từ A đến H (8 cột) cho cân đối với số lượng cột mới
$sheet->mergeCells('A1:H1'); 
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Từ ngày: " . date('d/m/Y', strtotime($start_date)) . " đến " . date('d/m/Y', strtotime($end_date)));
$sheet->mergeCells('A2:H2');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- HEADER CỘT (Cập nhật thêm cột mới) ---
$headers = [
    'A3' => 'Mã Đơn', 
    'B3' => 'Thời gian', 
    'C3' => 'Nhân viên', 
    'D3' => 'Tổng gốc',      // total_price
    'E3' => 'Giảm giá',      // Tính toán: total_price - final_amount
    'F3' => 'Thành tiền',    // final_amount
    'G3' => 'HTTT',          // payment_method
    'H3' => 'Trạng thái'
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
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15); // Cột HTTT
$sheet->getColumnDimension('H')->setWidth(18);

// --- LẤY DỮ LIỆU ---
// Cập nhật câu SQL theo đúng tên cột trong ảnh bạn gửi
$sql = "SELECT o.id, o.order_date, o.total_price, o.final_amount, o.payment_method, o.status, u.fullname 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.order_date BETWEEN '$start_sql' AND '$end_sql'
        ORDER BY o.id DESC";

$query = mysqli_query($conn, $sql);
$rowNum = 4;

while ($row = mysqli_fetch_assoc($query)) {
    // 1. Xử lý trạng thái
    $statusText = 'Chưa thanh toán';
    $statusColor = '000000'; 

    if ($row['status'] == 'paid') {
        $statusText = 'Đã thanh toán';
        $statusColor = '008000'; 
    } elseif ($row['status'] == 'canceled') {
        $statusText = 'Đã hủy';
        $statusColor = 'FF0000'; 
    }

    // 2. Tính tiền giảm giá (Tổng gốc - Thành tiền)
    $discount_amount = $row['total_price'] - $row['final_amount'];

    // 3. Xử lý phương thức thanh toán (HTTT)
    // Nếu trong DB lưu 'cash'/'transfer' thì đổi sang tiếng Việt cho đẹp
    $payment = $row['payment_method'];
    if($payment == 'cash') $payment = 'Tiền mặt';
    if($payment == 'transfer') $payment = 'Chuyển khoản';
    if($payment == 'card') $payment = 'Thẻ';

    // 4. Ghi dữ liệu vào Excel
    $sheet->setCellValue('A' . $rowNum, '#' . $row['id']);
    $sheet->setCellValue('B' . $rowNum, date('H:i d/m/Y', strtotime($row['order_date'])));
    $sheet->setCellValue('C' . $rowNum, $row['fullname']);
    $sheet->setCellValue('D' . $rowNum, $row['total_price']);  // Tổng gốc
    $sheet->setCellValue('E' . $rowNum, $discount_amount);     // Giảm giá
    $sheet->setCellValue('F' . $rowNum, $row['final_amount']); // Thành tiền
    $sheet->setCellValue('G' . $rowNum, $payment);             // HTTT
    $sheet->setCellValue('H' . $rowNum, $statusText);

    // Format số tiền (có dấu phẩy ngăn cách)
    $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
    
    // Canh giữa cho HTTT
    $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Tô màu trạng thái
    $sheet->getStyle('H' . $rowNum)->getFont()->setColor(new Color($statusColor));
    $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Kẻ viền bảng
    $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
    $rowNum++;
}

// --- XUẤT FILE ---
$filename = "Danh_sach_don_hang_" . date('d-m-Y_H-i') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>