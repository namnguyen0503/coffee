/* =============================================================
   1. KHỞI TẠO BIẾN & DATA
   ============================================================= */
const order_id = document.getElementById('order-id');
const totalAmountElement = document.getElementById('total-amount'); 
const CART_STORAGE_KEY = 'pos_current_order';
let cartItems = [];

// Khởi tạo khi trang tải xong
document.addEventListener('DOMContentLoaded', () => {
    loadCartFromStorage();
    // Đợi 1 chút để DOM render xong rồi tính toán kho ban đầu
    setTimeout(updateProductAvailability, 100); 
});

/* =============================================================
   2. QUẢN LÝ STORAGE & GIỎ HÀNG
   ============================================================= */
function loadCartFromStorage() {
    const storedCart = localStorage.getItem(CART_STORAGE_KEY);
    if (storedCart) {
        try {
            cartItems = JSON.parse(storedCart);
        } catch (e) {
            cartItems = [];
        }
    } else {
        cartItems = [];
    }
    renderCart();
    updateTotalAmount();
}

function saveCartToStorage() {
    const cartJson = JSON.stringify(cartItems);
    localStorage.setItem(CART_STORAGE_KEY, cartJson);
    
    // Kích hoạt tính toán lại tồn kho trên Menu
    if (typeof updateProductAvailability === 'function') {
        updateProductAvailability(); 
    }
}

function updateTotalAmount() {
    let total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    if (totalAmountElement) {
        totalAmountElement.textContent = total.toLocaleString('vi-VN') + ' đ';
    }
}

/* =============================================================
   3. LOGIC TÍNH TOÁN KHO REAL-TIME (SỬA LỖI "--")
   ============================================================= */
function updateProductAvailability() {
    // 1. Reset kho tạm thời về trạng thái gốc từ Server
    let currentStock = JSON.parse(JSON.stringify(SERVER_INGREDIENTS)); 

    // 2. Trừ nguyên liệu đang bị "giam" trong giỏ hàng
    cartItems.forEach(item => {
        const recipe = SERVER_RECIPES[item.id];
        if (recipe) {
            recipe.forEach(ing => {
                if (currentStock[ing.id] !== undefined) {
                    currentStock[ing.id] -= (ing.qty * item.quantity);
                }
            });
        }
    });

    // 3. Cập nhật giao diện từng thẻ sản phẩm
    document.querySelectorAll('.product-item').forEach(card => {
        const productId = card.dataset.id;
        const recipe = SERVER_RECIPES[productId];
        const stockBadge = card.querySelector('.stock-remaining');
        const qtySpan = card.querySelector('.qty-val');

        if (!recipe || recipe.length === 0) {
            if(qtySpan) qtySpan.textContent = '∞';
            card.classList.remove('out-of-stock-material');
            return; 
        }

        let maxCanMake = Infinity;
        recipe.forEach(ing => {
            const available = currentStock[ing.id] || 0;
            const possible = Math.floor(available / ing.qty);
            if (possible < maxCanMake) maxCanMake = possible;
        });

        if (maxCanMake < 0) maxCanMake = 0;

        // Điền số vào giao diện
        if (qtySpan) qtySpan.textContent = maxCanMake;

        // Animation & Trạng thái hết hàng
        if (maxCanMake === 0) {
            card.classList.add('out-of-stock-material');
            if (stockBadge) stockBadge.innerHTML = 'Hết NL';
        } else {
            card.classList.remove('out-of-stock-material');
            if (stockBadge) stockBadge.innerHTML = `Còn: <span class="qty-val">${maxCanMake}</span>`;
            
            if (maxCanMake <= 5) stockBadge.classList.add('low-stock');
            else stockBadge.classList.remove('low-stock');
        }
    });
}

function calculateMaxPossibleExcludingCart(productId) {
    let tempStock = JSON.parse(JSON.stringify(SERVER_INGREDIENTS));
    cartItems.forEach(item => {
        if (item.id != productId) { // Không trừ chính nó
            const recipe = SERVER_RECIPES[item.id];
            if (recipe) {
                recipe.forEach(ing => {
                    if (tempStock[ing.id]) tempStock[ing.id] -= (ing.qty * item.quantity);
                });
            }
        }
    });

    const recipe = SERVER_RECIPES[productId];
    if (!recipe) return 999;
    let max = Infinity;
    recipe.forEach(ing => {
        const canMake = Math.floor((tempStock[ing.id] || 0) / ing.qty);
        if (canMake < max) max = canMake;
    });
    return max;
}

/* =============================================================
   4. TƯƠNG TÁC GIAO DIỆN (RENDER & EVENTS)
   ============================================================= */
function renderCart() {
    const cartList = document.getElementById('cart-list');
    if (!cartList) return;
    cartList.innerHTML = ''; 

    cartItems.forEach((item, index) => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center p-2';
        li.innerHTML = `
            <div>
                <span class="fw-bold">${item.name}</span> <br>
                <small class="text-muted"><span class="item-total-price">${(item.quantity * item.price).toLocaleString('vi-VN')}</span> đ</small>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-outline-secondary me-1 btn-minus" data-index="${index}">-</button>
                <input type="number" class="form-control form-control-sm text-center quantity-input fw-bold mx-1" 
                       value="${item.quantity}" data-index="${index}" style="width: 60px;">
                <button class="btn btn-sm btn-outline-secondary ms-1 btn-plus" data-index="${index}">+</button>
                <button class="btn btn-sm btn-danger ms-3 btn-remove" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
            </div>
        `;
        cartList.appendChild(li);
    });
}

// Click chọn món từ Menu
document.querySelector('#product-list-container')?.addEventListener('click', function(event) {
    const productCard = event.target.closest('.card.product-item');
    if (productCard && !productCard.classList.contains('out-of-stock-material')) {
        const id = parseInt(productCard.dataset.id);
        const price = parseInt(productCard.dataset.price);
        const name = productCard.querySelector('.card-title').textContent.trim();
        addItemToCart(id, name, price);
    }
});

function addItemToCart(id, name, price) {
    const maxPossible = calculateMaxPossibleExcludingCart(id);
    if (maxPossible <= 0) return; // Không cho thêm nếu hết kho

    const itemIndex = cartItems.findIndex(item => item.id === id); 
    if (itemIndex > -1) {
        if (cartItems[itemIndex].quantity < maxPossible) {
            cartItems[itemIndex].quantity += 1;
        }
    } else {
        cartItems.push({ id, name, price, quantity: 1 });
    }
    renderCart();
    updateTotalAmount();
    saveCartToStorage(); 
}

// Click các nút trong giỏ hàng (+, -, Xóa)
document.getElementById('cart-list')?.addEventListener('click', function(event) {
    const target = event.target.closest('button');
    if (!target) return;
    const index = parseInt(target.dataset.index);

    if (target.matches('.btn-plus')) {
        const max = calculateMaxPossibleExcludingCart(cartItems[index].id);
        if (cartItems[index].quantity < max) cartItems[index].quantity++;
    } else if (target.matches('.btn-minus')) {
        cartItems[index].quantity--;
        if (cartItems[index].quantity <= 0) cartItems.splice(index, 1);
    } else if (target.matches('.btn-remove')) {
        cartItems.splice(index, 1);
    }
    renderCart();
    updateTotalAmount();
    saveCartToStorage();
});

// Nhập số lượng trực tiếp (Real-time Input)
document.getElementById('cart-list')?.addEventListener('input', function(event) {
    if (event.target.classList.contains('quantity-input')) {
        const input = event.target;
        const index = parseInt(input.dataset.index);
        const item = cartItems[index];
        let val = input.value;
        if (val === '') return;

        let newQty = parseInt(val);
        let maxPossible = calculateMaxPossibleExcludingCart(item.id);
        
        if (newQty > maxPossible) {
            newQty = maxPossible;
            input.value = newQty;
            input.classList.add('input-error');
            setTimeout(() => input.classList.remove('input-error'), 500);
        }
        
        item.quantity = newQty;
        const row = input.closest('li');
        row.querySelector('.item-total-price').textContent = (item.quantity * item.price).toLocaleString('vi-VN');
        
        updateTotalAmount();
        updateProductAvailability();
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartItems));
    }
});

// 2. THÊM ĐOẠN NÀY: Xử lý khi người dùng lỡ tay xóa hết rồi click ra ngoài (Sự kiện blur)
document.getElementById('cart-list')?.addEventListener('focusout', function(event) {
    if (event.target.classList.contains('quantity-input')) {
        const input = event.target;
        const index = parseInt(input.dataset.index);
        const item = cartItems[index];

        // Nếu ô nhập bị trống hoặc không phải là số hợp lệ
        if (input.value.trim() === '' || parseInt(input.value) < 1 || isNaN(parseInt(input.value))) {
            console.log(`⚠️ Phát hiện ô nhập trống cho ${item.name}. Tự động đưa về 1.`);
            
            // Trả về 1
            item.quantity = 1;
            input.value = 1;

            // Cập nhật lại giao diện và tiền
            const row = input.closest('li');
            const itemTotalSpan = row.querySelector('.item-total-price');
            if (itemTotalSpan) {
                itemTotalSpan.textContent = (item.quantity * item.price).toLocaleString('vi-VN');
            }

            updateTotalAmount();
            updateProductAvailability();
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartItems));
        }
    }
});

/* =============================================================
   5. THANH TOÁN & BỘ LỌC (GIỮ NGUYÊN)
   ============================================================= */
// ... (Giữ nguyên phần handleCheckout, Filter và Search từ code cũ của bạn) ...
function handleCheckout() {
    // 1. Kiểm tra giỏ hàng
    if (cartItems.length === 0) {
        alert("Giỏ hàng rỗng! Vui lòng chọn món trước khi thanh toán.");
        return;
    }

    // 2. Xác nhận thanh toán
    if (confirm("Xác nhận thanh toán và IN HÓA ĐƠN?")) {
        // Tính tổng tiền
        const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        // Chuẩn hóa dữ liệu gửi server
        const itemsToSend = cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity
        }));

        const checkoutData = {
            total_amount: total,
            items: itemsToSend
        };

        // 3. Gửi Request
        fetch('../core/order_processor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(checkoutData)
        })
        .then(response => {
            if (!response.ok) throw new Error('Lỗi Server: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success === false) {
                alert(`LỖI: ${data.message}`);
                return;
            }

            // ... (Đoạn code fetch server giữ nguyên) ...

            // --- BẮT ĐẦU QUY TRÌNH IN HÓA ĐƠN ---
            
            // A. Điền dữ liệu vào mẫu in HTML (Giữ nguyên)
            document.getElementById('print-order-id').textContent = data.order_id;
            document.getElementById('print-date').textContent = new Date().toLocaleString('vi-VN');
            document.getElementById('print-total').textContent = total.toLocaleString('vi-VN') + ' đ';
            
            const printBody = document.getElementById('print-items-body');
            printBody.innerHTML = ''; 
            
            cartItems.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-start" style="width: 40%">${item.name}</td>
                    <td class="text-center" style="width: 15%">${item.quantity}</td>
                    <td class="text-end" style="width: 20%">${item.price.toLocaleString('vi-VN')}</td>
                    <td class="text-end fw-bold" style="width: 25%">${(item.price * item.quantity).toLocaleString('vi-VN')}</td>
                `;
                printBody.appendChild(tr);
            });

            // B. XỬ LÝ HIỂN THỊ TRƯỚC KHI IN (FIX LỖI TRẮNG)
            const invoiceDiv = document.getElementById('invoice-pos');
            
            // 1. Hiện hóa đơn lên (Gỡ class d-none)
            invoiceDiv.classList.remove('d-none');
            
            // 2. Gọi lệnh in
            setTimeout(() => {
                window.print();
                
                // 3. Sau khi bảng in tắt đi -> Ẩn hóa đơn lại
                invoiceDiv.classList.add('d-none');

                // 4. Reset quy trình bán hàng
                alert(`Thanh toán thành công! Đơn hàng #${data.order_id}`);
                cartItems = [];
                localStorage.removeItem(CART_STORAGE_KEY);
                renderCart();
                updateTotalAmount();
                updateProductAvailability();
                
                if (typeof order_id !== 'undefined') {
                    order_id.textContent = Number(data.order_id) + 1;
                }
            }, 500);

        })
        .catch(error => {
            console.error('LỖI AJAX:', error);
            alert('Đã xảy ra lỗi kết nối. Vui lòng kiểm tra lại.');
        });
    }
}
function handleCancel() {
    if (cartItems.length === 0) {
        alert("Giỏ hàng rỗng! Không có gì để hủy.");
        return;
    }   
    if (confirm("Bạn có chắc chắn muốn hủy đơn hàng hiện tại không?")) {
        cartItems = [];
        localStorage.removeItem(CART_STORAGE_KEY);
        renderCart();
        updateTotalAmount();
        console.log(' ĐƠN HÀNG ĐÃ BỊ HỦY BỞI NGƯỜI DÙNG.');
        alert("Đơn hàng đã được hủy.");
    }
}

document.getElementById('checkout-btn')?.addEventListener('click', handleCheckout);
document.getElementById('cancel-btn')?.addEventListener('click', handleCancel);


/* =============================================================
   1. CHỨC NĂNG LỌC DANH MỤC (FILTER) - Đã sửa selector
   ============================================================= */
document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', function() {
        
        // UI: Đổi màu nút active
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active'); // CSS mới dùng class 'active' chứ không phải btn-dark
            // Nếu dùng bootstrap btn thì toggle class btn-primary/btn-outline...
        });
        this.classList.add('active');

        const filterValue = this.getAttribute('data-filter'); 
        
        // Logic: Lấy tất cả thẻ sản phẩm
        const allProducts = document.querySelectorAll('.product-item'); 

        allProducts.forEach(productCard => {
            // SỬA QUAN TRỌNG: Tìm thẻ bao ngoài bằng class chung 'product-card-wrapper'
            // thay vì hardcode '.col-4' hay '.col-lg-2'
            const columnContainer = productCard.closest('.product-card-wrapper'); 
            
            if (!columnContainer) return; // Bỏ qua nếu không tìm thấy

            const productCategoryId = productCard.getAttribute('data-category-id');

            if (filterValue === 'all') {
                columnContainer.style.display = ''; // Reset về mặc định (hiện)
            } else {
                if (productCategoryId === filterValue) {
                    columnContainer.style.display = '';
                } else {
                    columnContainer.style.display = 'none';
                }
            }
        });
    });
});

/* =============================================================
   2. CHỨC NĂNG TÌM KIẾM (SEARCH) - Sửa thành sự kiện 'input'
   ============================================================= */
const searchInput = document.getElementById('search-input');

if (searchInput) {
    // Dùng sự kiện 'input' thay vì 'keyup' -> gõ đến đâu ăn đến đó (kể cả paste chuột)
    searchInput.addEventListener('input', function(event) {
        
        const searchText = event.target.value.toLowerCase().trim(); 
        const allProducts = document.querySelectorAll('.product-item');

        allProducts.forEach(productCard => {
            // SỬA QUAN TRỌNG: Tìm đúng thẻ bao ngoài mới
            const columnContainer = productCard.closest('.product-card-wrapper'); 
            
            if (!columnContainer) return;

            const productName = productCard.querySelector('.card-title').textContent.toLowerCase();

            // Logic tìm kiếm
            if (productName.includes(searchText)) {
                columnContainer.style.display = ''; // Hiện
            } else {
                columnContainer.style.display = 'none'; // Ẩn
            }
        });

        // Tự động reset nút Filter về "Tất cả" nếu đang tìm kiếm để tránh nhầm lẫn
        if (searchText.length > 0) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            const allBtn = document.querySelector('.filter-btn[data-filter="all"]');
            if(allBtn) allBtn.classList.add('active');
        }
    });
}


