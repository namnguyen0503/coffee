☕ Database Structure - Nguyễn Văn Coffee
Tài liệu này mô tả chi tiết sơ đồ cơ sở dữ liệu MySQL cho hệ thống quản lý cửa hàng Cafe, bao gồm các phân hệ: Bán hàng (POS), Quản lý kho (Warehouse), Quản lý ca làm việc (Shift/Session) và Quản trị (Admin).

📊 Sơ đồ Quan hệ Đơn vị (ER Diagram)
Hệ thống vận hành theo các luồng dữ liệu chính:

Bán hàng: users mở work_sessions -> tạo orders -> order_items lấy thông tin từ products.

Định lượng: products cấu thành từ ingredients thông qua công thức recipes.

Kho vận: ingredients được theo dõi biến động qua inventory_log.

Tài chính: orders được gán vào work_sessions để chốt sổ cuối ngày.

📂 Danh sách các bảng
1. categories (Danh mục sản phẩm)
Phân loại các mặt hàng trong thực đơn.

id: INT (PK, AI)

name: VARCHAR - Tên danh mục (Cà phê, Trà sữa, Bánh ngọt...)

2. products (Sản phẩm)
Thông tin chi tiết về các món ăn/đồ uống.

id: INT (PK, AI)

name: VARCHAR - Tên món.

price: INT - Giá bán niêm yết.

category_id: INT (FK) - Liên kết với bảng categories.

image_url: VARCHAR - Đường dẫn ảnh sản phẩm.

status: TINYINT - Trạng thái kho (1: Còn hàng, 0: Hết hàng).

is_active: BOOL - Trạng thái kinh doanh (1: Đang bán, 0: Ngừng bán).

3. orders (Hóa đơn tổng)
Lưu trữ thông tin giao dịch tổng quát.

id: INT (PK, AI)

order_date: DATETIME - Thời điểm tạo đơn (Mặc định: CURRENT_TIMESTAMP).

total_price: INT - Tổng giá trị đơn hàng (Giá niêm yết chưa giảm).

status: VARCHAR - Trạng thái hóa đơn (paid, not_paid, cancelled).

user_id: INT (FK) - Nhân viên thực hiện thanh toán.

session_id: INT (FK) - [MỚI] Thuộc phiên làm việc/ca nào.

voucher_code: VARCHAR - [MỚI] Mã giảm giá áp dụng (nếu có).

discount_percent: DECIMAL - [MỚI] Phần trăm giảm giá (VD: 10.5%).

final_amount: DECIMAL - [MỚI] Tổng tiền thực thu (Sau khi trừ KM).

4. order_items (Chi tiết hóa đơn)
Lưu các món cụ thể trong mỗi hóa đơn.

id: INT (PK, AI)

order_id: INT (FK) - Thuộc hóa đơn nào.

product_id: INT (FK) - Món nào được mua.

quantity: INT - Số lượng khách mua.

5. users (Tài khoản hệ thống)
Quản lý người dùng truy cập hệ thống.

id: INT (PK, AI)

fullname: VARCHAR - Tên đầy đủ.

username: VARCHAR (Unique) - Tên đăng nhập.

password: VARCHAR - Mật khẩu đã mã hóa (Hash).

role: ENUM (admin, staff, wh-staff) - Phân quyền.

status: TINYINT - Trạng thái tài khoản (1: Hoạt động, 0: Bị khóa).

6. ingredients (Kho nguyên liệu)
Quản lý vật tư đầu vào.

id: INT (PK, AI)

name: VARCHAR - Tên nguyên liệu (Hạt cafe, Sữa, Đường...).

unit: VARCHAR - Đơn vị tính (g, ml, lon...).

quantity: FLOAT - Tồn kho thực tế.

min_quantity: FLOAT - Ngưỡng báo động nhập hàng.

7. recipes (Công thức món ăn)
Cầu nối trừ kho tự động khi bán.

id: INT (PK, AI)

product_id: INT (FK) - Sản phẩm đầu ra.

ingredient_id: INT (FK) - Nguyên liệu đầu vào.

quantity_required: FLOAT - Định lượng tiêu hao cho 1 đơn vị sản phẩm.

8. inventory_log (Nhật ký kho)
Lịch sử nhập/xuất kho.

id: INT (PK, AI)

ingredient_id: INT (FK) - Nguyên liệu biến động.

type: ENUM (import, export) - Loại giao dịch.

quantity: FLOAT - Số lượng thay đổi.

cost: DECIMAL - Chi phí nhập hàng (Giá vốn).

note: TEXT - Ghi chú/Nguồn gốc.

user_id: INT (FK) - Người thực hiện.

created_at: TIMESTAMP - Thời gian.

9. work_sessions (Phiên làm việc / Ca) - [MỚI]
Quản lý tiền mặt đầu ca và chốt doanh thu cuối ca.

id: INT (PK, AI)

user_id: INT (FK) - Nhân viên mở ca.

start_time: DATETIME - Giờ bắt đầu ca.

end_time: DATETIME - Giờ kết thúc ca (NULL nếu đang mở).

start_cash: DECIMAL - Tiền mặt có sẵn đầu ca.

end_cash: DECIMAL - Tiền mặt thực tế đếm được cuối ca.

total_sales: DECIMAL - Tổng doanh thu hệ thống ghi nhận trong ca.

note: TEXT - Ghi chú (ví dụ: Chênh lệch tiền do...).

status: ENUM (open, closed) - Trạng thái phiên.

🔗 Các mối quan hệ chính (Relationships)
Sản phẩm & Danh mục: products.category_id → categories.id (Nhiều sản phẩm thuộc một danh mục).

Đơn hàng & Chi tiết: order_items.order_id → orders.id (Một đơn hàng có nhiều món).

Bán hàng & Nhân viên: orders.user_id → users.id (Biết ai bán đơn).

Phiên làm việc & Đơn hàng: orders.session_id → work_sessions.id (Một ca có nhiều đơn hàng, giúp tổng hợp doanh thu theo ca chính xác).

Công thức (Recipe): Kết nối products và ingredients.

Kho vận: inventory_log kết nối ingredients và users.

Cập nhật lần cuối: 2025-12-21


