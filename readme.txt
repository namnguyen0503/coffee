Dưới đây là nội dung file **README.md** dành riêng cho cấu trúc Database của dự án Nguyễn Văn Coffee. Bản này được trình bày theo phong cách chuyên nghiệp, dễ đọc và thể hiện rõ các mối quan hệ logic.

---

# ☕ Database Structure - Nguyễn Văn Coffee

Tài liệu này mô tả chi tiết sơ đồ cơ sở dữ liệu MySQL cho hệ thống quản lý cửa hàng Cafe, bao gồm các phân hệ: Bán hàng (POS), Quản lý kho (Warehouse), và Quản trị (Admin).

## 📊 Sơ đồ Quan hệ Đơn vị (ER Diagram)

Dựa trên cấu trúc bảng, hệ thống vận hành theo các luồng chính:

* **Bán hàng:** `users` tạo `orders` -> `order_items` kết nối `products`.
* **Định lượng:** `products` liên kết với `ingredients` thông qua bảng trung gian `recipes`.
* **Kho vận:** `ingredients` được theo dõi biến động qua `inventory_log`.

---

## 📂 Danh sách các bảng

### 1. `categories` (Danh mục sản phẩm)

Phân loại các mặt hàng trong thực đơn.

* `id`: **INT** (PK, AI)
* `name`: **VARCHAR** - Tên danh mục (Cà phê, Trà sữa, Bánh ngọt...)

### 2. `products` (Sản phẩm)

Thông tin chi tiết về các món ăn/đồ uống.

* `id`: **INT** (PK, AI)
* `name`: **VARCHAR** - Tên món.
* `price`: **INT** - Giá bán niêm yết.
* `category_id`: **INT** (FK) - Liên kết với bảng `categories`.
* `image_url`: **VARCHAR** - Đường dẫn ảnh sản phẩm.
* `status`: **TINYINT** - Trạng thái kho (1: Còn hàng, 0: Hết hàng).
* `is_active`: **BOOL** - Trạng thái kinh doanh (1: Đang bán, 0: Ngừng bán).

### 3. `orders` (Hóa đơn tổng)

Lưu trữ thông tin giao dịch tổng quát.

* `id`: **INT** (PK, AI)
* `order_date`: **DATETIME** - Thời điểm tạo đơn (Mặc định: CURRENT_TIMESTAMP).
* `total_price`: **INT** - Tổng giá trị đơn hàng.
* `status`: **VARCHAR** - Trạng thái hóa đơn (`paid`, `not_paid`, `cancelled`).
* `user_id`: **INT** (FK) - Nhân viên/Quản lý thực hiện thanh toán.

### 4. `order_items` (Chi tiết hóa đơn)

Lưu các món cụ thể trong mỗi hóa đơn.

* `id`: **INT** (PK, AI)
* `order_id`: **INT** (FK) - Thuộc hóa đơn nào.
* `product_id`: **INT** (FK) - Món nào được mua.
* `quantity`: **INT** - Số lượng khách mua.

### 5. `users` (Tài khoản hệ thống)

Quản lý người dùng truy cập hệ thống.

* `id`: **INT** (PK, AI)
* `fullname`: **VARCHAR** - Tên đầy đủ.
* `username`: **VARCHAR** (Unique) - Tên đăng nhập.
* `password`: **VARCHAR** - Mật khẩu đã mã hóa (Hash).
* `role`: **ENUM** (`admin`, `staff`, `wh-staff`) - Phân quyền người dùng.
* `status`: **TINYINT** - Trạng thái tài khoản (1: Hoạt động, 0: Bị khóa).

### 6. `ingredients` (Kho nguyên liệu)

Quản lý vật tư đầu vào.

* `id`: **INT** (PK, AI)
* `name`: **VARCHAR** - Tên nguyên liệu (Hạt cafe, Sữa, Đường...).
* `unit`: **VARCHAR** - Đơn vị tính (g, ml, quả, túi...).
* `quantity`: **FLOAT** - Tồn kho thực tế.
* `min_quantity`: **FLOAT** - Ngưỡng báo động để nhập hàng thêm.

### 7. `recipes` (Công thức món ăn)

Cầu nối tính toán trừ kho tự động khi bán sản phẩm.

* `id`: **INT** (PK, AI)
* `product_id`: **INT** (FK) - Sản phẩm đầu ra.
* `ingredient_id`: **INT** (FK) - Nguyên liệu đầu vào.
* `quantity_required`: **FLOAT** - Định lượng tiêu hao cho **1 đơn vị** sản phẩm.

### 8. `inventory_log` (Nhật ký kho)

Lưu lịch sử mọi biến động nhập/xuất kho.

* `id`: **INT** (PK, AI)
* `ingredient_id`: **INT** (FK) - Nguyên liệu biến động.
* `type`: **ENUM** (`import`, `export`) - Loại giao dịch.
* `quantity`: **FLOAT** - Số lượng thay đổi.
* `cost`: **DECIMAL** - Chi phí nhập hàng (Dùng để tính giá vốn/lợi nhuận).
* `note`: **TEXT** - Lý do hoặc nguồn gốc hàng hóa.
* `user_id`: **INT** (FK) - Người thực hiện thao tác kho.
* `created_at`: **TIMESTAMP** - Thời gian thực hiện.

---

## 🔗 Các mối quan hệ chính

1. **Sản phẩm & Danh mục:** `products.category_id` → `categories.id` (Nhiều sản phẩm thuộc một danh mục).
2. **Đơn hàng & Chi tiết:** `order_items.order_id` → `orders.id` (Một đơn hàng có nhiều món).
3. **Bán hàng & Nhân viên:** `orders.user_id` → `users.id` (Biết ai là người bán đơn hàng đó).
4. **Công thức (Recipe):** Liên kết `products` và `ingredients`. Dùng để tính toán số lượng món "Còn" thực tế dựa trên nguyên liệu ít nhất trong kho.
5. **Lịch sử kho:** Kết nối `ingredients` và `users` để theo dõi trách nhiệm nhập/xuất hàng.

---

*Cập nhật lần cuối: 2025-12-20*