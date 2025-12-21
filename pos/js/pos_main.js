/* =============================================================
   1. KHỞI TẠO BIẾN & DATA
   ============================================================= */
const order_id = document.getElementById('order-id');
const totalAmountElement = document.getElementById('total-amount'); 
const CART_STORAGE_KEY = 'pos_current_order';
let cartItems = [];
let currentDiscountPercent = 0; // Biến lưu % giảm hiện tại
// Khởi tạo khi trang tải xong
document.addEventListener('DOMContentLoaded', () => {
    loadCartFromStorage();
    // Đợi 1 chút để DOM render xong rồi tính toán kho ban đầu
    setTimeout(updateProductAvailability, 100); 
    checkShiftStatus();
});
function checkShiftStatus() {
    fetch('../core/session_manager.php?action=check_status')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (!data.is_open) {
                // Nếu chưa vào ca -> Hiện modal bắt buộc
                const modal = new bootstrap.Modal(document.getElementById('modalStartShift'));
                modal.show();
            } else {
                console.log("Đang trong ca làm việc. Start time:", data.data.start_time);
            }
        }
    })
    .catch(err => console.error("Lỗi check shift:", err));
}

function startShift() {
    const cash = document.getElementById('start-cash-input').value;
    
    fetch('../core/session_manager.php?action=start_shift', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ start_cash: cash })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showCustomAlert(data.message);
            // Ẩn modal và reload để hệ thống chạy
            location.reload(); 
        } else {
            showCustomAlert(data.message);
        }
    });
}

function endShift() {
    if (!confirm("Bạn chắc chắn muốn chốt ca và đăng xuất?")) return;

    const cash = document.getElementById('end-cash-input').value;
    const note = document.getElementById('end-note-input').value;

    fetch('../core/session_manager.php?action=end_shift', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ end_cash: cash, note: note })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showCustomAlert(data.message); // Thông báo doanh thu
            window.location.href = '../login.php'; // Đá về trang login
        } else {
            showCustomAlert("Lỗi: " + data.message);
        }
    });
}
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
    // Tính tổng tiền gốc (Ép kiểu Number để chắc chắn không bị lỗi chuỗi)
    const totalOriginal = cartItems.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
    
    // Tính tiền giảm giá
    const discountAmount = totalOriginal * (currentDiscountPercent / 100);
    const totalFinal = totalOriginal - discountAmount;

    // Cập nhật UI
    // LƯU Ý: Phải dùng getElementById để chắc chắn trỏ đúng thẻ span hiển thị tiền
    const totalElement = document.getElementById('total-amount'); 
    
    if (totalElement) {
        if(currentDiscountPercent > 0) {
            // Nếu có giảm giá: Hiện giá gốc gạch ngang + Giá sau giảm
            totalElement.innerHTML = `
                <div class="d-flex flex-column align-items-end">
                    <small class="text-muted text-decoration-line-through" style="font-size: 0.8em;">
                        ${totalOriginal.toLocaleString('vi-VN')} đ
                    </small>
                    <span class="text-danger fw-bold">
                        ${totalFinal.toLocaleString('vi-VN')} đ
                    </span>
                </div>
            `;
        } else {
            // Không giảm giá: Hiện bình thường
            totalElement.textContent = totalFinal.toLocaleString('vi-VN') + ' đ';
        }
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
        // li.innerHTML = `
        //     <div>
        //         <span class="fw-bold">${item.name}</span> <br>
        //         <small class="text-muted"><span class="item-total-price">${(item.quantity * item.price).toLocaleString('vi-VN')}</span> đ</small>
        //     </div>
        //     <div class="d-flex align-items-center">
        //         <button class="btn btn-sm btn-outline-secondary me-1 btn-minus" data-index="${index}">-</button>
        //         <input type="number" class="form-control form-control-sm text-center quantity-input fw-bold mx-1" 
        //                value="${item.quantity}" data-index="${index}" style="width: 60px;">
        //         <button class="btn btn-sm btn-outline-secondary ms-1 btn-plus" data-index="${index}">+</button>
        //         <button class="btn btn-sm btn-danger ms-3 btn-remove" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
        //     </div>
        // `;
        li.innerHTML = `
    <div class="w-100">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <span class="fw-bold">${item.name}</span> <br>
                <small class="text-muted"><span class="item-total-price">${(item.quantity * item.price).toLocaleString('vi-VN')}</span> đ</small>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-outline-secondary me-1 btn-minus" data-index="${index}">-</button>
                <input type="number" class="form-control form-control-sm text-center quantity-input fw-bold mx-1" 
                       value="${item.quantity}" data-index="${index}" style="width: 40px;">
                <button class="btn btn-sm btn-outline-secondary ms-1 btn-plus" data-index="${index}">+</button>
                <button class="btn btn-sm btn-danger ms-2 btn-remove" data-index="${index}"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>
        <div class="mt-2">
            <input type="text" class="form-control form-control-sm note-input text-primary fst-italic" 
                   placeholder="Ghi chú (ít đá, mang về...)" 
                   data-index="${index}" 
                   value="${item.note || ''}"> 
        </div>
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
// function handleCheckout() {
//     // 1. Kiểm tra giỏ hàng
//     if (cartItems.length === 0) {
//         showCustomAlert("Giỏ hàng rỗng! Vui lòng chọn món trước khi thanh toán.");
//         return;
//     }

//     // 2. Xác nhận thanh toán
//     if (confirm("Xác nhận thanh toán và IN HÓA ĐƠN?")) {
//         // Tính tổng tiền
//         const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);

//         // Chuẩn hóa dữ liệu gửi server
//         const itemsToSend = cartItems.map(item => ({
//             product_id: item.id,
//             quantity: item.quantity
//         }));

//         const checkoutData = {
//             total_amount: total,
//             items: itemsToSend
//         };

//         // 3. Gửi Request
//         fetch('../core/order_processor.php', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/json' },
//             body: JSON.stringify(checkoutData)
//         })
//         .then(response => {
//             if (!response.ok) throw new Error('Lỗi Server: ' + response.status);
//             return response.json();
//         })
//         .then(data => {
//             if (data.success === false) {
//                 showCustomAlert(`LỖI: ${data.message}`);
//                 return;
//             }

//           // --- [ĐOẠN CODE MỚI BẮT ĐẦU TỪ ĐÂY] ---
//     // Cập nhật biến kho gốc SERVER_INGREDIENTS để nó "nhớ" là đã mất hàng
//     cartItems.forEach(item => {
//         const recipe = SERVER_RECIPES[item.id];
//         if (recipe) {
//             recipe.forEach(ing => {
//                 if (SERVER_INGREDIENTS[ing.id] !== undefined) {
//                     // Trừ vĩnh viễn vào biến gốc
//                     SERVER_INGREDIENTS[ing.id] -= (ing.qty * item.quantity);
//                 }
//             });
//         }
//     });
//     // --- [KẾT THÚC ĐOẠN CODE MỚI] ---

//     // --- BẮT ĐẦU QUY TRÌNH IN HÓA ĐƠN ---
//     // ... (Code in hóa đơn giữ nguyên)

//     setTimeout(() => {
//         window.print();
        
//         // 3. Sau khi bảng in tắt đi -> Ẩn hóa đơn lại
//         invoiceDiv.classList.add('d-none');

//         // 4. Reset quy trình bán hàng
//         showCustomAlert(`Thanh toán thành công! Đơn hàng #${data.order_id}`);
        
//         cartItems = []; // Lúc này reset giỏ hàng thì kho đã được trừ ở trên rồi
//         localStorage.removeItem(CART_STORAGE_KEY);
        
//         renderCart();
//         updateTotalAmount();
//         updateProductAvailability(); // Tính toán lại: (Kho mới đã trừ - 0) = Đúng thực tế
        
//         if (typeof order_id !== 'undefined') {
//             order_id.textContent = Number(data.order_id) + 1;
//         }
//     }, 500);
// })
//         .catch(error => {
//             console.error('LỖI AJAX:', error);
//             showCustomAlert('Đã xảy ra lỗi kết nối. Vui lòng kiểm tra lại.');
//         });
//     }
// }


// function handleCheckout() {
//     // 1. Kiểm tra giỏ hàng
//     if (cartItems.length === 0) {
//         showCustomAlert("Giỏ hàng rỗng! Vui lòng chọn món trước khi thanh toán.");
//         return;
//     }

//     // 2. Xác nhận thanh toán
//     if (confirm("Xác nhận thanh toán và IN HÓA ĐƠN?")) {
//         // Tính tổng tiền
//         const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);

//         // Chuẩn hóa dữ liệu gửi server
//         const itemsToSend = cartItems.map(item => ({
//     product_id: item.id,
//     quantity: item.quantity,
//     note: item.note || '' // Gửi note lên server
// }));

//         const checkoutData = {
//             total_amount: total,
//             items: itemsToSend
//         };

//         // 3. Gửi Request
//         fetch('../core/order_processor.php', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/json' },
//             body: JSON.stringify(checkoutData)
//         })
//         .then(response => {
//             if (!response.ok) throw new Error('Lỗi Server: ' + response.status);
//             return response.json();
//         })
//         .then(data => {
//             if (data.success === false) {
//                 showCustomAlert(`LỖI: ${data.message}`);
//                 return;
//             }

//             // === [FIX LỖI Ở ĐÂY] ===
//             // Khai báo biến invoiceDiv ngay đầu để chắc chắn nó tồn tại
//             const invoiceDiv = document.getElementById('invoice-pos');

//             // 1. CẬP NHẬT BIẾN KHO GỐC CLIENT (Để tránh hồi máu số lượng)
//             // Lưu ý: Nhớ đổi 'const' thành 'let' ở file menu.php thì dòng này mới chạy được
//             try {
//                 cartItems.forEach(item => {
//                     const recipe = SERVER_RECIPES[item.id];
//                     if (recipe) {
//                         recipe.forEach(ing => {
//                             if (SERVER_INGREDIENTS[ing.id] !== undefined) {
//                                 SERVER_INGREDIENTS[ing.id] -= (ing.qty * item.quantity);
//                             }
//                         });
//                     }
//                 });
//             } catch (e) {
//                 console.warn("Không thể cập nhật kho Client (Có thể do biến const):", e);
//             }

//             // 2. ĐIỀN DỮ LIỆU VÀO HÓA ĐƠN
//             document.getElementById('print-order-id').textContent = data.order_id;
//             document.getElementById('print-date').textContent = new Date().toLocaleString('vi-VN');
//             document.getElementById('print-total').textContent = total.toLocaleString('vi-VN') + ' đ';
            
//             const printBody = document.getElementById('print-items-body');
//             printBody.innerHTML = ''; 
            
//             cartItems.forEach(item => {
//                 const tr = document.createElement('tr');
//                 tr.innerHTML = `
//                     <td class="text-start" style="width: 40%">${item.name}</td>
//                     <td class="text-center" style="width: 15%">${item.quantity}</td>
//                     <td class="text-end" style="width: 20%">${item.price.toLocaleString('vi-VN')}</td>
//                     <td class="text-end fw-bold" style="width: 25%">${(item.price * item.quantity).toLocaleString('vi-VN')}</td>
//                 `;
//                 printBody.appendChild(tr);
//             });

//             // 3. DỌN DẸP GIỎ HÀNG & UPDATE UI (Làm ngay lập tức)
//             cartItems = [];
//             localStorage.removeItem(CART_STORAGE_KEY);
//             renderCart();
//             updateTotalAmount();
//             updateProductAvailability(); // Tính lại theo kho mới đã trừ
            
//             if (typeof order_id !== 'undefined') {
//                 order_id.textContent = Number(data.order_id) + 1;
//             }

//             // 4. HIỆN THÔNG BÁO & IN
//             showCustomAlert(`Thanh toán thành công! Đơn hàng #${data.order_id}`);

//             // Hiện khung in
//             if(invoiceDiv) {
//                 invoiceDiv.classList.remove('d-none');
                
//                 setTimeout(() => {
//                     window.print();
//                     // Ẩn lại sau khi bảng in hiện ra
//                     invoiceDiv.classList.add('d-none');
//                 }, 500);
//             } else {
//                 console.error("Lỗi: Không tìm thấy thẻ div hóa đơn (invoice-pos)");
//             }

//         })
//         .catch(error => {
//             console.error('LỖI AJAX:', error);
//             showCustomAlert('Đã xảy ra lỗi kết nối. Vui lòng kiểm tra console.');
//         });
//     }
// }
function handleCheckoutInternal() {
    // 1. Kiểm tra giỏ hàng
    if (cartItems.length === 0) {
        showCustomAlert("Giỏ hàng rỗng! Vui lòng chọn món trước khi thanh toán.");
        return;
    }

    // 2. Xác nhận thanh toán
        // Tính tổng tiền (Client side estimate)
        const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        // Lấy mã voucher hiện tại để gửi đi và hiển thị
        const voucherCodeInput = document.getElementById('voucher-code').value.trim().toUpperCase();

        const itemsToSend = cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            note: item.note || '' 
        }));

        const checkoutData = {
            total_amount: total, 
            items: itemsToSend,
            voucher_code: voucherCodeInput, // Gửi mã
            discount_percent: currentDiscountPercent // Gửi % (để admin verify)
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
                showCustomAlert(`LỖI: ${data.message}`);
                return;
            }

            // === PHẦN 1: CẬP NHẬT KHO CLIENT ===
            try {
                cartItems.forEach(item => {
                    const recipe = SERVER_RECIPES[item.id];
                    if (recipe) {
                        recipe.forEach(ing => {
                            if (SERVER_INGREDIENTS[ing.id] !== undefined) {
                                SERVER_INGREDIENTS[ing.id] -= (ing.qty * item.quantity);
                            }
                        });
                    }
                });
            } catch (e) { console.warn("Lỗi update kho client:", e); }

            // === PHẦN 2: CHUẨN BỊ IN HÓA ĐƠN ===
            const invoiceDiv = document.getElementById('invoice-pos');
            const stickerContainer = document.getElementById('sticker-container');
            const printDate = new Date();
            const timeString = `${printDate.getHours()}:${String(printDate.getMinutes()).padStart(2, '0')}`;
            const staffName = document.getElementById('print-staff')?.textContent || 'NV';

            // A. Điền thông tin chung
            document.getElementById('print-order-id').textContent = data.order_id;
            document.getElementById('print-date').textContent = printDate.toLocaleString('vi-VN');
            
            // B. Điền danh sách món
            const printBody = document.getElementById('print-items-body');
            printBody.innerHTML = ''; 
            
            cartItems.forEach(item => {
                const noteDisplay = item.note ? `<br><small class="fst-italic">(${item.note})</small>` : '';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-start" style="width: 40%">${item.name} ${noteDisplay}</td>
                    <td class="text-center" style="width: 15%">${item.quantity}</td>
                    <td class="text-end" style="width: 20%">${Number(item.price).toLocaleString('vi-VN')}</td>
                    <td class="text-end fw-bold" style="width: 25%">${(item.price * item.quantity).toLocaleString('vi-VN')}</td>
                `;
                printBody.appendChild(tr);
            });
            // C. ĐIỀN TỔNG TIỀN & VOUCHER (GIAO DIỆN MỚI GỌN GÀNG)
            const totalOriginal = Number(data.total_original); 
            const finalAmount = Number(data.final_amount);     
            const discountPercent = Number(data.discount_percent);
            const discountAmount = totalOriginal - finalAmount;

            // Xây dựng các dòng HTML
            let footerHtml = '';

            // Dòng 1: Tổng tiền hàng (Luôn hiện)
            footerHtml += `
                <div class="bill-row">
                    <span class="bill-label">Tổng tiền hàng:</span>
                    <span>${totalOriginal.toLocaleString('vi-VN')}</span>
                </div>
            `;

            // Dòng 2: Voucher/Giảm giá (Chỉ hiện nếu có giảm)
            if (discountPercent > 0) {
                const codeDisplay = voucherCodeInput ? `(${voucherCodeInput})` : '';
                footerHtml += `
                    <div class="bill-row fst-italic">
                        <span class="bill-label">Giảm giá ${codeDisplay} -${discountPercent}%:</span>
                        <span>-${discountAmount.toLocaleString('vi-VN')}</span>
                    </div>
                `;
            }

            // Dòng 3: Thành tiền (Chốt hạ)
            footerHtml += `
                <div class="bill-row final">
                    <span class="bill-label">THANH TOÁN:</span>
                    <span>${finalAmount.toLocaleString('vi-VN')} đ</span>
                </div>
            `;

            // Render vào thẻ div
            document.getElementById('print-total').innerHTML = footerHtml;
            

            // D. Tạo Tem Sticker (Giữ nguyên)
            if (stickerContainer) {
                stickerContainer.innerHTML = '';
                cartItems.forEach(item => {
                    for (let i = 1; i <= item.quantity; i++) {
                        const noteHtml = item.note ? `<div class="sticker-note">${item.note}</div>` : '';
                        const stickerHtml = `
                            <div class="sticker-item">
                                <div class="sticker-header">
                                    <span>#${data.order_id}</span> <span>${timeString}</span> <span>${i}/${item.quantity}</span>
                                </div>
                                <div class="sticker-product">${item.name}</div>
                                ${noteHtml}
                                <div class="sticker-footer">NV: ${staffName}</div>
                            </div>
                        `;
                        stickerContainer.insertAdjacentHTML('beforeend', stickerHtml);
                    }
                });
            }

            // === PHẦN 3: DỌN DẸP ===
            cartItems = [];
            localStorage.removeItem(CART_STORAGE_KEY);
            // Reset input voucher
            document.getElementById('voucher-code').value = '';
            document.getElementById('discount-display').textContent = '0%';
            currentDiscountPercent = 0;

            renderCart();
            updateTotalAmount();
            updateProductAvailability();
            
            if (typeof order_id !== 'undefined') order_id.textContent = Number(data.order_id) + 1;

            // === PHẦN 4: IN ẤN ===
            showCustomAlert(`Thanh toán thành công! Đơn hàng #${data.order_id}`);
            
            if(invoiceDiv) invoiceDiv.classList.remove('d-none');
            if(stickerContainer) stickerContainer.classList.remove('d-none');
            
            setTimeout(() => {
                performDualPrinting();
            }, 500);
        })
        .catch(error => {
            console.error('LỖI AJAX:', error);
            showCustomAlert('Đã xảy ra lỗi kết nối: ' + error.message);
        });
    
}
function handleCancel() {
    if (cartItems.length === 0) {
        showCustomAlert("Giỏ hàng rỗng! Không có gì để hủy.", "warning");
        return;
    }   
    
    // THAY confirm() BẰNG showCustomConfirm
    showCustomConfirm("Bạn có chắc chắn muốn hủy đơn hàng hiện tại không?", function() {
        // Đây là code sẽ chạy khi bấm "Đồng ý"
        cartItems = [];
        localStorage.removeItem(CART_STORAGE_KEY);
        renderCart();
        updateTotalAmount();
        console.log('ĐƠN HÀNG ĐÃ BỊ HỦY BỞI NGƯỜI DÙNG.');
        showCustomAlert("Đơn hàng đã được hủy.", "info");
    });
}

document.getElementById('checkout-btn')?.addEventListener('click', openPaymentModal);
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

// Lưu ghi chú khi gõ
document.getElementById('cart-list')?.addEventListener('input', function(event) {
    if (event.target.classList.contains('note-input')) {
        const index = parseInt(event.target.dataset.index);
        cartItems[index].note = event.target.value; // Lưu vào mảng
        saveCartToStorage(); // Lưu vào LocalStorage
    }
});



// Hàm xử lý in tách đôi: In Bill -> Đợi -> In Sticker
// function performDualPrinting() {
//     const body = document.body;
    
//     // --- LẦN 1: CHUẨN BỊ IN BILL ---
//     // 1. Reset class cũ (phòng hờ)
//     body.classList.remove('print-mode-sticker');
    
//     // 2. Thêm class in Bill
//     body.classList.add('print-mode-bill');
    
//     // 3. Gọi lệnh in
//     window.print();
    
//     // 4. Xóa class in Bill ngay sau khi dialog in tắt (hoặc lệnh gửi đi)
//     // Để trả lại trạng thái trắng cho lần in sau
//     body.classList.remove('print-mode-bill');

//     // --- LẦN 2: CHUẨN BỊ IN STICKER ---
//     const hasStickers = document.querySelectorAll('.sticker-item').length > 0;

//     if (hasStickers) {
//         // Đợi 500ms - 1s để máy in nuốt lệnh 1, tránh bị nghẽn lệnh
//         setTimeout(() => {
//             if (confirm("In TEM DÁN CỐC (Sticker) ngay bây giờ?")) {
//                 // 1. Thêm class in Sticker
//                 body.classList.add('print-mode-sticker');
                
//                 // 2. Gọi lệnh in
//                 window.print();
                
//                 // 3. Xóa class in Sticker
//                 body.classList.remove('print-mode-sticker');
//             }
            
//             // Xong xuôi thì ẩn hết đi
//             document.getElementById('invoice-pos').classList.add('d-none');
//             document.getElementById('sticker-container').classList.add('d-none');
            
//         }, 500);
//     } else {
//         // Nếu không có tem thì ẩn luôn invoice
//         document.getElementById('invoice-pos').classList.add('d-none');
//     }
// }
function performDualPrinting() {
    const body = document.body;
    
    // --- LẦN 1: IN BILL ---
    body.classList.remove('print-mode-sticker');
    body.classList.add('print-mode-bill');
    window.print();
    body.classList.remove('print-mode-bill');

    // --- LẦN 2: HỎI IN STICKER ---
    const hasStickers = document.querySelectorAll('.sticker-item').length > 0;

    if (hasStickers) {
        setTimeout(() => {
            // THAY confirm() BẰNG showCustomConfirm
            showCustomConfirm("In TEM DÁN CỐC (Sticker) ngay bây giờ?", function() {
                // Code chạy khi bấm Đồng ý
                body.classList.add('print-mode-sticker');
                window.print();
                body.classList.remove('print-mode-sticker');
                
                // Dọn dẹp sau khi in xong
                finishPrintingProcess();
            });

            // Nếu người dùng không bấm gì (treo modal) thì invoice vẫn hiện
            // Nhưng nếu họ bấm Hủy (Modal đóng) thì ta cũng nên ẩn invoice đi?
            // Tạm thời Modal Confirm của mình chỉ xử lý nút Yes. Nút No chỉ đóng Modal.
            // Để xử lý nút No (ẩn invoice), ta có thể thêm logic vào sự kiện đóng modal, nhưng không quá cần thiết.
            
        }, 1000); // Đợi 1s cho hộp thoại in bill tắt hẳn
    } else {
        finishPrintingProcess();
    }
}

// Hàm phụ để dọn dẹp UI
// function finishPrintingProcess() {
//     document.getElementById('invoice-pos').classList.add('d-none');
//     document.getElementById('sticker-container').classList.add('d-none');
// }
function finishPrintingProcess() {
    document.getElementById('invoice-pos').classList.add('d-none');
    document.getElementById('sticker-container').classList.add('d-none');
}
function checkVoucher() {
    const codeInput = document.getElementById('voucher-code');
    const code = codeInput.value.trim().toUpperCase();
    const discountDisplay = document.getElementById('discount-display');

    if (!code) {
        currentDiscountPercent = 0;
        discountDisplay.textContent = "0%";
        updateTotalAmount();
        return;
    }

    // 1. LOGIC ADMIN (Voucher vĩnh viễn)
    if (code === 'ADMINVIP') {
        let percent = ShowCustomPrompt("🔔 ADMIN DETECTED!\nNhập phần trăm muốn giảm giá (0-100):");
        
        if (percent !== null && percent.trim() !== "") {
            percent = parseFloat(percent);
            if (!isNaN(percent) && percent >= 0 && percent <= 100) {
                currentDiscountPercent = percent;
                showCustomAlert(`Đã áp dụng giảm giá ADMIN: ${percent}%`);
            } else {
                showCustomAlert("Số phần trăm không hợp lệ!");
                currentDiscountPercent = 0;
                codeInput.value = "";
            }
        } else {
            // Nếu bấm Cancel
            currentDiscountPercent = 0;
            codeInput.value = "";
        }
    } 
    // 2. LOGIC VOUCHER KHÁCH (Dùng 1 lần - Client chỉ hiển thị giả định, Server sẽ check lại)
    else if (code === 'WELCOME') {
        currentDiscountPercent = 10;
        showCustomAlert("Áp dụng mã WELCOME: Giảm 10%");
    }
    else if (code === 'FREESHIP') {
        currentDiscountPercent = 5;
        showCustomAlert("Áp dụng mã FREESHIP: Giảm 5%");
    }
    else {
        showCustomAlert("Mã giảm giá không hợp lệ!");
        currentDiscountPercent = 0;
        codeInput.value = "";
    }

    // Cập nhật giao diện
    discountDisplay.textContent = `-${currentDiscountPercent}%`;
    updateTotalAmount(); // Tính lại tổng tiền hiển thị
}

/* =============================================================
   LOGIC MODAL TÍNH TIỀN THỪA (NEW UX)
   ============================================================= */

// Biến lưu tổng tiền cuối cùng (sau khi trừ voucher)
let finalPaymentAmount = 0; 

function openPaymentModal() {
    // 1. Kiểm tra giỏ hàng trước
    if (cartItems.length === 0) {
        showCustomAlert("Giỏ hàng rỗng!");
        return;
    }

    // 2. Tính toán tổng tiền cần thanh toán
    const totalOriginal = cartItems.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
    const discountAmount = totalOriginal * (currentDiscountPercent / 100);
    finalPaymentAmount = totalOriginal - discountAmount;

    // 3. Reset giao diện Modal
    document.getElementById('pay-total-display').textContent = finalPaymentAmount.toLocaleString('vi-VN') + ' đ';
    document.getElementById('customer-pay-input').value = ''; // Reset ô nhập
    document.getElementById('change-due-display').textContent = '0 đ';
    document.getElementById('change-due-display').className = 'fw-bold fs-2 text-danger'; // Mặc định màu đỏ (chưa đủ tiền)

    // 4. Mở Modal
    const modal = new bootstrap.Modal(document.getElementById('modalPayment'));
    modal.show();

    // 5. Auto focus vào ô nhập tiền để nhân viên gõ luôn
    setTimeout(() => {
        document.getElementById('customer-pay-input').focus();
    }, 500);
}

// Sự kiện: Khi nhân viên nhập tiền khách đưa
document.getElementById('customer-pay-input')?.addEventListener('input', function(e) {
    calculateChange(Number(e.target.value));
});

// Sự kiện: Bấm các nút tiền nhanh (50k, 100k...)
document.querySelectorAll('.quick-pay').forEach(btn => {
    btn.addEventListener('click', function() {
        const val = Number(this.dataset.value);
        document.getElementById('customer-pay-input').value = val;
        calculateChange(val);
    });
});

// Sự kiện: Bấm nút "Đủ tiền" (Khách đưa vừa zin)
document.getElementById('btn-pay-exact')?.addEventListener('click', function() {
    document.getElementById('customer-pay-input').value = finalPaymentAmount;
    calculateChange(finalPaymentAmount);
});

// Hàm tính toán hiển thị
function calculateChange(customerGive) {
    const change = customerGive - finalPaymentAmount;
    const changeDisplay = document.getElementById('change-due-display');

    if (change >= 0) {
        changeDisplay.textContent = change.toLocaleString('vi-VN') + ' đ';
        changeDisplay.className = 'fw-bold fs-2 text-primary'; // Đủ tiền -> Màu xanh
        document.getElementById('btn-confirm-print').disabled = false;
    } else {
        changeDisplay.textContent = "Thiếu " + Math.abs(change).toLocaleString('vi-VN') + " đ";
        changeDisplay.className = 'fw-bold fs-3 text-danger'; // Thiếu tiền -> Màu đỏ
        // document.getElementById('btn-confirm-print').disabled = true; // Mở dòng này nếu muốn chặn không cho in khi thiếu tiền
    }
}

// Sự kiện: Bấm nút "IN HÓA ĐƠN" trong Modal
document.getElementById('btn-confirm-print')?.addEventListener('click', function() {
    // Ẩn modal trước
    const modalEl = document.getElementById('modalPayment');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

    // Gọi hàm thanh toán gốc (Backend + In ấn)
    handleCheckoutInternal(); 
});

/* =============================================================
   HELPER: HỆ THỐNG MODAL THAY THẾ ALERT/CONFIRM
   ============================================================= */

// 1. Hàm thay thế showCustomAlert()
function showCustomAlert(message, type = 'info') {
    const modalEl = document.getElementById('customAlertModal');
    const header = document.getElementById('alert-header');
    const title = document.getElementById('alert-title');
    const icon = document.getElementById('alert-icon');
    const msg = document.getElementById('alert-message');

    msg.textContent = message;

    // Cấu hình màu sắc icon/header
    header.className = 'modal-header text-white'; // Reset
    icon.className = 'fa-3x mb-3'; // Reset

    if (type === 'success') {
        header.classList.add('bg-success');
        title.textContent = 'Thành công';
        icon.classList.add('fa-solid', 'fa-circle-check', 'text-success');
    } else if (type === 'error' || type === 'danger') {
        header.classList.add('bg-danger');
        title.textContent = 'Lỗi';
        icon.classList.add('fa-solid', 'fa-circle-xmark', 'text-danger');
    } else if (type === 'warning') {
        header.classList.add('bg-warning');
        title.textContent = 'Cảnh báo';
        icon.classList.add('fa-solid', 'fa-triangle-exclamation', 'text-warning');
    } else {
        header.classList.add('bg-primary');
        title.textContent = 'Thông báo';
        icon.classList.add('fa-solid', 'fa-circle-info', 'text-primary');
    }

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// 2. Hàm thay thế confirm()
// Vì Modal không chặn dòng code (non-blocking) như confirm(), ta phải dùng Callback function
let confirmCallback = null; // Biến lưu hành động sẽ làm khi bấm Yes

function showCustomConfirm(message, callback) {
    const modalEl = document.getElementById('customConfirmModal');
    document.getElementById('confirm-message').textContent = message;
    
    // Lưu callback lại để dùng khi bấm nút "Đồng ý"
    confirmCallback = callback;

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// Gắn sự kiện cho nút "Đồng ý" (Chỉ làm 1 lần khi load trang)
document.getElementById('btn-confirm-yes')?.addEventListener('click', function() {
    if (confirmCallback) {
        confirmCallback(); // Chạy hành động đã lưu
    }
    // Ẩn modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('customConfirmModal'));
    modal.hide();
});


// 3. Hàm thay thế ShowCustomPrompt() (Dành riêng cho Voucher Admin)
let promptCallback = null;

function showCustomPrompt(message, callback) {
    const modalEl = document.getElementById('customPromptModal');
    document.getElementById('prompt-message').textContent = message;
    const input = document.getElementById('prompt-input');
    input.value = ''; // Reset

    promptCallback = callback;
    
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    setTimeout(() => input.focus(), 500); // Auto focus
}

document.getElementById('btn-prompt-submit')?.addEventListener('click', function() {
    const val = document.getElementById('prompt-input').value;
    if (promptCallback) {
        promptCallback(val);
    }
    const modal = bootstrap.Modal.getInstance(document.getElementById('customPromptModal'));
    modal.hide();
});