/* =============================================================
   CẤU HÌNH NGÂN HÀNG (VIETQR)
   ============================================================= */
const BANK_CONFIG = {
    BANK_ID: 'TCB',       // Mã ngân hàng (MB, VCB, ACB, TPB...)
    ACCOUNT_NO: '258905038888', // Số tài khoản của bạn
    TEMPLATE: 'compact', // Giao diện QR: 'compact', 'qr_only', 'print'
    ACCOUNT_NAME: 'NGUYEN DANH NHAT NAM' // Tên chủ tài khoản (để hiển thị cho chắc)
};
// Kênh giao tiếp màn hình khách
const customerChannel = new BroadcastChannel('pos_customer_display');
const modalPaymentEl = document.getElementById('modalPayment');
modalPaymentEl.addEventListener('hidden.bs.modal', function () {
    customerChannel.postMessage({ type: 'RESET' });
});
// [ADD] chống spam thông báo (theo key) trong khoảng thời gian ngắn
const __customerNoticeThrottle = new Map();

/**
 * Gửi thông báo sang customer_view.php qua BroadcastChannel
 * @param {string} message
 * @param {'info'|'warning'|'error'|'success'} level
 * @param {object} meta
 * @param {string} throttleKey - key để chống spam (vd: productId + action)
 * @param {number} throttleMs
 */
function postCustomerNotice(message, level = 'warning', meta = {}, throttleKey = '', throttleMs = 1200) {
  try {
    if (typeof customerChannel === 'undefined') return;

    // Chống spam: nếu cùng key trong throttleMs thì bỏ qua
    if (throttleKey) {
      const now = Date.now();
      const last = __customerNoticeThrottle.get(throttleKey) || 0;
      if (now - last < throttleMs) return;
      __customerNoticeThrottle.set(throttleKey, now);
    }

    customerChannel.postMessage({
      type: 'NOTICE',
      level,
      message,
      meta,
      at: Date.now()
    });
  } catch (e) {
    // Không làm crash POS nếu popup khách chưa mở / trình duyệt không hỗ trợ
    console.warn('postCustomerNotice failed:', e);
  }
}

function openCustomerScreen() {
    // Mở popup window
    window.open('customer_view.php', 'CustomerScreen', 'width=800,height=600');
}
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
// Đánh dấu item vượt tồn để dùng cho: viền đỏ + gửi sang customer_view
function refreshCartValidation() {
    cartItems.forEach(it => {
        const max = calculateMaxPossibleExcludingCart(it.id);
        it.max_possible = max;
        it.is_over = Number(it.quantity) > max;
    });
}

function getOverItems() {
    refreshCartValidation();
    return cartItems.filter(it => it.is_over);
}

function updateTotalAmount() {
    refreshCartValidation();
    // 1. Tính tổng tiền gốc (Sum Price * Qty)
    let totalOriginal = 0;
    cartItems.forEach(item => {
        totalOriginal += Number(item.price) * Number(item.quantity);
    });

    // 2. Tính tiền giảm giá
    // (currentDiscountPercent là biến toàn cục, mặc định là 0)
    let discountAmount = totalOriginal * (currentDiscountPercent / 100);

    // 3. Tính tổng tiền cuối cùng (ĐÂY LÀ BIẾN BẠN BỊ THIẾU)
    let finalTotal = totalOriginal - discountAmount;

    // 4. Cập nhật giao diện POS (Nhân viên)
    const totalEl = document.getElementById('total-amount');
    const discountEl = document.getElementById('discount-display');
    
    if (totalEl) {
        totalEl.textContent = finalTotal.toLocaleString('vi-VN') + ' đ';
    }
    
    if (discountEl) {
        discountEl.textContent = `-${currentDiscountPercent}%`;
    }

    // 5. Cập nhật biến toàn cục dùng cho thanh toán (nếu có dùng ở openPaymentModal)
    // finalPaymentAmount = finalTotal; 

    // 6. Gửi dữ liệu sang Màn hình khách (Fix lỗi ReferenceError ở đây)
    if (typeof customerChannel !== 'undefined') {
        customerChannel.postMessage({
            type: 'UPDATE_CART',
            items: cartItems,      // Danh sách món (có ảnh)
            subtotal: totalOriginal, // Tổng gốc
            discount_amt: discountAmount, // Tiền giảm
            total: finalTotal      // Tổng cuối (Biến này giờ đã được định nghĩa ở bước 3)
        });
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
                <input type="number"
       class="form-control form-control-sm text-center quantity-input fw-bold mx-1 ${item.is_over ? 'border border-2 border-danger' : ''}"
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
    const itemIndex = cartItems.findIndex(item => item.id === id);

    // Chỉ chặn khi THÊM MỚI mà maxPossible <= 0 (món hết hoàn toàn)
    // (Nếu đã có trong giỏ thì vẫn cho tăng vượt theo yêu cầu)
    const maxPossible = calculateMaxPossibleExcludingCart(id);
    if (itemIndex === -1 && maxPossible <= 0) {
        showCustomAlert("Món này tạm hết hàng hoặc không đủ nguyên liệu!", "warning");
        return;
    }

    if (itemIndex > -1) {
        cartItems[itemIndex].quantity += 1; // cho vượt
    } else {
        // giữ nguyên đoạn lấy ảnh của bạn
        let imgUrl = 'https://placehold.co/60';
        const productEl = document.querySelector(`.product-item[data-id="${id}"]`);
        if (productEl) {
            const imgTag = productEl.querySelector('img');
            if (imgTag) imgUrl = imgTag.src;
        }

        cartItems.push({
            id: id,
            name: name,
            price: price,
            quantity: 1,
            img: imgUrl
        });
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
    cartItems[index].quantity++;
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
        if (isNaN(newQty)) return;
        if (newQty < 1) newQty = 1;

        item.quantity = newQty;

        // Recompute max & flag
        const maxPossible = calculateMaxPossibleExcludingCart(item.id);
        item.max_possible = maxPossible;
        item.is_over = newQty > maxPossible;

        // Viền đỏ nếu vượt (không clamp)
        if (item.is_over) {
            input.classList.add('border', 'border-2', 'border-danger');
            input.classList.add('input-error'); // rung/nháy nếu bạn đã có CSS
            setTimeout(() => input.classList.remove('input-error'), 500);
        } else {
            input.classList.remove('border', 'border-2', 'border-danger');
        }

        // Update total giá của dòng
        const row = input.closest('li');
        row.querySelector('.item-total-price').textContent =
            (item.quantity * item.price).toLocaleString('vi-VN');

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

function handleCheckoutInternal() {
    // 1. Kiểm tra giỏ hàng
    if (cartItems.length === 0) {
        showCustomAlert("Giỏ hàng rỗng! Vui lòng chọn món trước khi thanh toán.");
        return;
    }
    const overItems = getOverItems();
    if (overItems.length > 0) {
        const msg = overItems
            .map(i => `${i.name}: yêu cầu ${i.quantity}, tối đa ${i.max_possible}`)
            .join(' | ');
        showCustomAlert("Không thể thanh toán: không đủ nguyên liệu. " + msg, "warning");
        return;
    }

    // 2. Xác nhận thanh toán
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    const voucherEl = document.getElementById('voucher-code');
    const voucherCodeInput = voucherEl ? voucherEl.value.trim().toUpperCase() : '';

    const itemsToSend = cartItems.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        note: item.note || '' 
    }));

    const checkoutData = {
        total_amount: total, 
        items: itemsToSend,
        voucher_code: voucherCodeInput,
        discount_percent: currentDiscountPercent,
        payment_method: currentPaymentMethod 
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
        else if (data.success === true) {
            showCustomAlert("Thanh toán thành công!", "success");

            // --- [THÊM MỚI: Báo màn hình khách cảm ơn] ---
            if (typeof customerChannel !== 'undefined') {
                customerChannel.postMessage({ type: 'SUCCESS' });
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
            } catch (e) { 
                console.warn("Lỗi update kho client:", e); 
            }

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
            if (printBody) {
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
            }

            // C. ĐIỀN TỔNG TIỀN & VOUCHER
            const totalOriginal = Number(data.total_original || 0); 
            const finalAmount = Number(data.final_amount || 0);     
            const discountPercent = Number(data.discount_percent || 0);
            const discountAmount = totalOriginal - finalAmount;

            let footerHtml = `
                <div class="bill-row">
                    <span class="bill-label">Tổng tiền hàng:</span>
                    <span>${totalOriginal.toLocaleString('vi-VN')}</span>
                </div>
            `;

            if (discountPercent > 0) {
                const codeDisplay = voucherCodeInput ? `(${voucherCodeInput})` : '';
                footerHtml += `
                    <div class="bill-row fst-italic">
                        <span class="bill-label">Giảm giá ${codeDisplay} -${discountPercent}%:</span>
                        <span>-${discountAmount.toLocaleString('vi-VN')}</span>
                    </div>
                `;
            }

            footerHtml += `
                <div class="bill-row final">
                    <span class="bill-label">THANH TOÁN:</span>
                    <span>${finalAmount.toLocaleString('vi-VN')} đ</span>
                </div>
            `;

            const printTotalEl = document.getElementById('print-total');
            if (printTotalEl) printTotalEl.innerHTML = footerHtml;
            
            // D. Tạo Tem Sticker
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
            
            if (voucherEl) voucherEl.value = '';
            const discDisp = document.getElementById('discount-display');
            if (discDisp) discDisp.textContent = '0%';
            currentDiscountPercent = 0;

            renderCart();
            updateTotalAmount();
            updateProductAvailability();
            
            const nextOrderIdEl = document.getElementById('order_id');
            if (nextOrderIdEl) nextOrderIdEl.textContent = Number(data.order_id) + 1;

            // === PHẦN 4: IN ẤN ===
            if(invoiceDiv) invoiceDiv.classList.remove('d-none');
            if(stickerContainer) stickerContainer.classList.remove('d-none');
            
            setTimeout(() => {
                if (typeof performDualPrinting === 'function') {
                    performDualPrinting();
                }
            }, 500);
        }
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

    showCustomConfirm("Bạn có chắc chắn muốn hủy đơn hàng hiện tại không?", function () {
        cartItems = [];
        localStorage.removeItem(CART_STORAGE_KEY);

        renderCart();
        updateTotalAmount();        // gửi UPDATE_CART (giỏ trống) sang customer view
        updateProductAvailability(); // ✅ FIX: tính lại tồn kho / badge menu ngay lập tức

        // (tuỳ chọn nhưng nên có) đưa customer view về màn hình welcome sạch sẽ
        if (typeof customerChannel !== 'undefined') {
            customerChannel.postMessage({ type: 'RESET' });
        }

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
    const discountDisplay = document.getElementById('discount-display');
    
    // Safety check
    if (!codeInput) return;

    const code = (codeInput.value || "").trim().toUpperCase();

    // Reset nếu rỗng
    if (!code) {
        applyDiscount(0);
        return;
    }

    // 1. ƯU TIÊN: LOGIC ADMIN (Vẫn giữ nguyên tính năng nhập tay linh hoạt)
    // Dù trong DB có mã ADMINVIP 100%, nhưng ở POS ta muốn nhập tay tùy ý
    if (code === 'ADMINVIP') {
        showCustomPrompt("🔔 ADMIN DETECTED!\nNhập phần trăm muốn giảm giá (0-100):", function(percent) {
            if (percent !== null && percent.trim() !== "") {
                let p = parseFloat(percent);
                if (!isNaN(p) && p >= 0 && p <= 100) {
                    applyDiscount(p);
                    showCustomAlert(`Đã áp dụng quyền ADMIN: Giảm ${p}%`, 'success');
                } else {
                    showCustomAlert("Số phần trăm không hợp lệ!", 'error');
                    applyDiscount(0);
                    codeInput.value = "";
                }
            } else {
                applyDiscount(0);
                codeInput.value = "";
            }
        });
        return; // Kết thúc, không gọi API nữa
    }

    // 2. LOGIC THƯỜNG: CHECK DB (AJAX)
    // Gọi file PHP vừa tạo ở Bước 1
    fetch('check_voucher.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: code })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Tìm thấy voucher trong DB
            applyDiscount(data.percent);
            showCustomAlert(`Áp dụng mã ${data.code}: Giảm ${data.percent}%`, 'success');
        } else {
            // Không tìm thấy
            showCustomAlert(data.message, 'warning');
            applyDiscount(0);
            codeInput.value = ""; // Xóa mã sai đi
        }
    })
    .catch(err => {
        console.error(err);
        showCustomAlert("Lỗi kết nối Server!", 'error');
    });

    // Helper function để cập nhật UI gọn hơn
    function applyDiscount(percent) {
        currentDiscountPercent = percent;
        if (discountDisplay) {
            discountDisplay.textContent = `-${currentDiscountPercent}%`;
        }
        updateTotalAmount();
    }
}
/* =============================================================
   LOGIC MODAL TÍNH TIỀN THỪA (NEW UX)
   ============================================================= */

// Biến lưu tổng tiền cuối cùng (sau khi trừ voucher)


function openPaymentModal() {
    // 1. Kiểm tra giỏ hàng trước
    if (cartItems.length === 0) {
        showCustomAlert("Giỏ hàng rỗng!", "warning");
        return;
    }
    const overItems = getOverItems();
    if (overItems.length > 0) {
        const msg = overItems
            .map(i => `${i.name}: yêu cầu ${i.quantity}, tối đa ${i.max_possible}`)
            .join(' | ');
        showCustomAlert("Không đủ nguyên liệu để lên đơn. " + msg, "warning");
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
    const msgEl = document.getElementById('prompt-message');
    const inputEl = document.getElementById('prompt-input');

    if (!modalEl) {
        alert("Lỗi: Không tìm thấy HTML của Modal Prompt!");
        return;
    }

    msgEl.textContent = message;
    inputEl.value = ''; // Reset ô nhập
    promptCallback = callback; // Lưu hàm callback lại để dùng sau
    
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Auto focus và lắng nghe phím Enter
    setTimeout(() => {
        inputEl.focus();
        // Xóa sự kiện cũ để tránh bị double submit nếu mở nhiều lần
        inputEl.onkeydown = null; 
        inputEl.onkeydown = function(e) {
            if (e.key === 'Enter') {
                document.getElementById('btn-prompt-submit').click();
            }
        };
    }, 500);
}

// Xử lý khi bấm nút "Xác nhận"
document.getElementById('btn-prompt-submit')?.addEventListener('click', function() {
    const val = document.getElementById('prompt-input').value;
    
    // Ẩn modal
    const modalEl = document.getElementById('customPromptModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

    // Gọi lại hàm xử lý (checkVoucher logic) với giá trị vừa nhập
    if (promptCallback) {
        promptCallback(val);
        promptCallback = null; // Reset để tránh lỗi
    }
});

// Hàm chuyển đổi giao diện Tiền mặt / Chuyển khoản
function togglePaymentMethod(method) {
    const cashSection = document.getElementById('cash-payment-section');
    const transferSection = document.getElementById('transfer-payment-section');
    const confirmBtn = document.getElementById('btn-confirm-print');

    if (method === 'transfer') {
        cashSection.style.display = 'none';
        transferSection.style.display = 'block';
        
        // Khi chọn CK, mặc định là khách đã trả đủ
        document.getElementById('transfer-amount-hint').textContent = finalPaymentAmount.toLocaleString('vi-VN') + " đ";
        
        // Cho phép in luôn (không cần tính tiền thừa)
        confirmBtn.disabled = false; 
    } else {
        cashSection.style.display = 'block';
        transferSection.style.display = 'none';
        
        // Reset lại tính toán tiền mặt
        document.getElementById('customer-pay-input').value = '';
        document.getElementById('change-due-display').textContent = '0 đ';
        confirmBtn.disabled = true; // Phải nhập tiền mới cho in
    }
}

/* =============================================================
   LOGIC THANH TOÁN (PAYMENT)
   ============================================================= */
let currentPaymentMethod = 'cash'; // Mặc định là tiền mặt
let finalPaymentAmount = 0; // Biến toàn cục lưu số tiền cần trả

// 1. Hàm mở Modal Thanh toán
function openPaymentModal() {
    if (cartItems.length === 0) {
        showCustomAlert("Giỏ hàng rỗng!", "warning");
        return;
    }

    // Tính tiền
    const totalOriginal = cartItems.reduce((sum, item) => sum + (Number(item.price) * Number(item.quantity)), 0);
    const discountAmount = totalOriginal * (currentDiscountPercent / 100);
    finalPaymentAmount = totalOriginal - discountAmount;

    // Reset UI Modal
    document.getElementById('pay-total-display').textContent = finalPaymentAmount.toLocaleString('vi-VN') + ' đ';
    document.getElementById('customer-pay-input').value = '';
    document.getElementById('change-due-display').textContent = '0 đ';
    
    // Reset về Tab Tiền mặt mặc định
    const cashTabBtn = document.querySelector('#method-cash');
    const cashTabInstance = new bootstrap.Tab(cashTabBtn);
    cashTabInstance.show();
    setPaymentMethod('cash');

    const modal = new bootstrap.Modal(document.getElementById('modalPayment'));
    modal.show();

    // Auto focus ô nhập tiền sau 0.5s
    setTimeout(() => {
        document.getElementById('customer-pay-input').focus();
    }, 500);
}

// 2. Hàm chuyển đổi phương thức (Cash <-> Transfer)
function setPaymentMethod(method) {
    currentPaymentMethod = method;
    const btnConfirm = document.getElementById('btn-confirm-payment');

    if (method === 'transfer') {
        // Nếu chọn chuyển khoản -> Tạo QR ngay
        generateVietQR(finalPaymentAmount);
        btnConfirm.disabled = false; // Chuyển khoản thì cho bấm luôn (nhân viên tự check)
    } else {
        // Nếu chọn tiền mặt -> Reset validation
        calculateChange(0); // Tính lại tiền thừa
        customerChannel.postMessage({ type: 'RESET' }); // Ẩn QR bên khách đi cho đỡ nhầm
    }
}

// 3. Hàm tạo ảnh VietQR
function generateVietQR(amount) {
    const imgEl = document.getElementById('vietqr-image');
    const loadEl = document.getElementById('qr-loading');
    const infoText = document.getElementById('bank-info-text');

    // Ẩn ảnh, hiện loading
    imgEl.style.display = 'none';
    loadEl.classList.remove('d-none');

    // Nội dung chuyển khoản: "Ban 1" (Ví dụ, nếu có số bàn thì thêm vào, ở đây demo dùng mã đơn giả định)
    // Thực tế nên lấy Order ID nếu có, hoặc Time
    const memo = "POS " + new Date().getHours() + "h" + new Date().getMinutes(); 

    // Tạo link API VietQR (QuickLink)
    // Format: https://img.vietqr.io/image/<BANK_ID>-<ACCOUNT_NO>-<TEMPLATE>.png?amount=<AMOUNT>&addInfo=<CONTENT>&accountName=<NAME>
    const qrUrl = `https://img.vietqr.io/image/${BANK_CONFIG.BANK_ID}-${BANK_CONFIG.ACCOUNT_NO}-${BANK_CONFIG.TEMPLATE}.png?amount=${amount}&addInfo=${encodeURIComponent(memo)}&accountName=${encodeURIComponent(BANK_CONFIG.ACCOUNT_NAME)}`;

    // Set src cho ảnh
    imgEl.src = qrUrl;
    infoText.textContent = `${BANK_CONFIG.BANK_ID} - ${BANK_CONFIG.ACCOUNT_NO}`;

    // Khi ảnh tải xong
    imgEl.onload = function() {
        loadEl.classList.add('d-none');
        imgEl.style.display = 'inline-block';
    };
    customerChannel.postMessage({
        type: 'SHOW_QR',
        url: qrUrl,
        amount: amount
    });
}

// 4. Logic tính tiền thừa (Như cũ)
document.getElementById('customer-pay-input')?.addEventListener('input', (e) => calculateChange(Number(e.target.value)));

document.querySelectorAll('.quick-pay').forEach(btn => {
    btn.addEventListener('click', function() {
        const val = Number(this.dataset.value);
        document.getElementById('customer-pay-input').value = val;
        calculateChange(val);
    });
});

document.getElementById('btn-pay-exact')?.addEventListener('click', function() {
    const exact = finalPaymentAmount;
    document.getElementById('customer-pay-input').value = exact;
    calculateChange(exact);
});

function calculateChange(customerGive) {
    const change = customerGive - finalPaymentAmount;
    const changeDisplay = document.getElementById('change-due-display');
    const btnConfirm = document.getElementById('btn-confirm-payment');

    if (change >= 0) {
        changeDisplay.textContent = change.toLocaleString('vi-VN') + ' đ';
        changeDisplay.className = 'fw-bold fs-1 text-success';
        btnConfirm.disabled = false;
    } else {
        changeDisplay.textContent = "Thiếu " + Math.abs(change).toLocaleString('vi-VN') + " đ";
        changeDisplay.className = 'fw-bold fs-3 text-danger';
        btnConfirm.disabled = true; // Thiếu tiền không cho in
    }
}

// 5. Nút Xác Nhận Thanh Toán
document.getElementById('btn-confirm-payment')?.addEventListener('click', function() {
    // Tắt modal
    const modalEl = document.getElementById('modalPayment');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

    // Gọi hàm xử lý đơn hàng (Gửi payment_method xuống)
    handleCheckoutInternal(); 
});