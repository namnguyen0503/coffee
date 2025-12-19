SQL DATABASE STRUCTURE
1. categories (Danh mục sản phẩm)

id: INT (Primary Key, Auto Increment)
name: VARCHAR (Tên danh mục: Cà phê, Trà sữa, Đồ ăn vặt...)



2. products (Sản phẩm)

id: INT (Primary Key, Auto Increment)

name: VARCHAR (Tên món)

price: INT (Giá bán)

category_id: INT (Foreign Key trỏ đến categories.id)

image_url: VARCHAR (Đường dẫn ảnh sản phẩm)

status: TINYINT (1: Còn hàng, 0: Hết hàng - Dùng để ẩn/hiện trên POS)

is_active: BOOL (Trạng thái kích hoạt món) (0: Không bán, 1: còn bán)

3. orders (Hóa đơn tổng)




id: INT (Primary Key, Auto Increment)

order_date: DATETIME (Mặc định: CURRENT_TIMESTAMP)

total_price: INT (Tổng tiền hóa đơn)

status: VARCHAR (Trạng thái: paid, not_paid, cancelled)

user_id: INT (Foreign Key trỏ đến users.id - Người bán đơn này)

4. order_items (Chi tiết hóa đơn)




id: INT (Primary Key, Auto Increment)

order_id: INT (Foreign Key trỏ đến orders.id)

product_id: INT (Foreign Key trỏ đến products.id)

quantity: INT (Số lượng món khách mua)

5. users (Tài khoản hệ thống)




id: INT (Primary Key, Auto Increment)

fullname: VARCHAR (Tên hiển thị)

username: VARCHAR (Tên đăng nhập, Unique)

password: VARCHAR (Mật khẩu đã mã hóa hash)

role: ENUM ('admin', 'staff') (Phân quyền người dùng)

status: TINYINT (1: Hoạt động, 0: Bị khóa)

6. ingredients (Kho nguyên liệu)




id: INT (Primary Key, Auto Increment)

name: VARCHAR (Tên nguyên liệu: Hạt cafe, Sữa, Đường...)

unit: VARCHAR (Đơn vị tính: g, ml, lon...)

quantity: FLOAT (Số lượng tồn kho hiện tại)

min_quantity: FLOAT (Ngưỡng báo động khi sắp hết hàng)

7. recipes (Công thức món ăn)




id: INT (Primary Key, Auto Increment)

product_id: INT (Foreign Key trỏ đến products.id)

ingredient_id: INT (Foreign Key trỏ đến ingredients.id)

quantity_required: FLOAT (Lượng nguyên liệu tiêu hao cho 1 sản phẩm)

8. inventory_log (Nhật ký kho)




id: INT (Primary Key, Auto Increment)

ingredient_id: INT (Foreign Key trỏ đến ingredients.id)

type: ENUM ('import', 'export') (Loại giao dịch: Nhập/Xuất)

quantity: FLOAT (Số lượng thay đổi)

note: TEXT (Ghi chú lý do nhập/xuất)

created_at: TIMESTAMP (Thời gian thực hiện)

Mối quan hệ chính (Relationships):

products -> categories (N-1)

order_items -> orders & products (N-1)

recipes là bảng trung gian kết nối products và ingredients để trừ kho tự động.

orders liên kết với users để biết ai là người thực hiện thanh toán.