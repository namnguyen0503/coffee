<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Màn hình Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; height: 100vh; overflow: hidden; font-family: 'Segoe UI', sans-serif; }
        
        /* CỘT TRÁI: Trạng thái (Welcome/QR) */
        .left-panel { height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; border-right: 1px solid #ddd; background: white; transition: all 0.3s; }
        
        .welcome-screen img { width: 180px; margin-bottom: 20px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
        .welcome-title { font-weight: 800; color: #2c3e50; text-transform: uppercase; letter-spacing: 2px; }
        
        .payment-screen { display: none; text-align: center; width: 100%; }
        .qr-img { max-width: 350px; width: 100%; border-radius: 15px; border: 2px solid #eee; }
        
        .success-screen { display: none; text-align: center; }
        .success-icon { font-size: 8rem; color: #198754; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

        /* CỘT PHẢI: Giỏ hàng */
        .right-panel { height: 100vh; display: flex; flex-direction: column; background-color: #fff; }
        .cart-header { padding: 20px; background: #2c3e50; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .cart-body { flex: 1; overflow-y: auto; padding: 0; }
        .cart-item { border-bottom: 1px dashed #eee; padding: 15px 20px; display: flex; align-items: center; }
        .item-img { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; margin-right: 15px; }
        .item-info { flex: 1; }
        .item-name { font-weight: 600; font-size: 1.1rem; color: #333; margin-bottom: 2px; }
        .item-meta { font-size: 0.95rem; color: #666; }
        .item-total { font-weight: bold; color: #2c3e50; font-size: 1.1rem; }
        
        .cart-footer { padding: 20px; background: #f8f9fa; border-top: 2px solid #e9ecef; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 1.1rem; }
        .total-row { font-size: 1.8rem; font-weight: 800; color: #d63384; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 10px; }

        @keyframes popIn { 0% { transform: scale(0); } 100% { transform: scale(1); } }
    </style>
</head>
<body>

<div class="row g-0 h-100">
    <div class="col-md-7 left-panel">
        
        <div id="screen-welcome" class="welcome-screen text-center">
            <img src="../admin/assets/dist/img/logo coffee.png" alt="Logo">
            <h1 class="welcome-title display-5">Nguyễn Văn Coffee</h1>
            <p class="fs-4 text-muted mt-2">Xin chào quý khách!</p>
            <div class="mt-4">
                <span class="badge bg-light text-dark border p-2">Wifi: nguyenvan_coffee / Pass: 12345678</span>
            </div>
        </div>

        <div id="screen-payment" class="payment-screen">
            <h3 class="text-primary fw-bold mb-3"><i class="fa-solid fa-qrcode"></i> QUÉT MÃ THANH TOÁN</h3>
            <img id="customer-qr" src="" class="qr-img shadow-sm">
            <div class="mt-3">
                <div class="fs-5 text-muted">Số tiền cần thanh toán:</div>
                <div id="qr-total-display" class="display-4 fw-bold text-danger">0 đ</div>
            </div>
        </div>

        <div id="screen-success" class="success-screen">
            <div class="success-icon mb-3"><i class="fa-solid fa-circle-check"></i></div>
            <h2 class="fw-bold">Thanh toán thành công!</h2>
            <p class="fs-4 text-muted">Cảm ơn và hẹn gặp lại.</p>
        </div>
    </div>

    <div class="col-md-5 right-panel shadow-lg">
        <div class="cart-header">
            <i class="fa-solid fa-receipt me-2"></i> Thông tin đơn hàng
        </div>
        
        <div id="cart-container" class="cart-body">
            <div class="text-center text-muted mt-5 pt-5">
                <i class="fa-solid fa-basket-shopping fa-3x mb-3 opacity-25"></i>
                <p>Chưa có món nào được chọn</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="summary-row">
                <span class="text-muted">Tạm tính:</span>
                <span class="fw-bold" id="bill-subtotal">0 đ</span>
            </div>
            <div class="summary-row text-success">
                <span><i class="fa-solid fa-ticket me-1"></i> Giảm giá:</span>
                <span class="fw-bold" id="bill-discount">-0 đ</span>
            </div>
            <div class="summary-row total-row">
                <span>TỔNG CỘNG:</span>
                <span id="bill-total">0 đ</span>
            </div>
        </div>
    </div>
</div>

<script>
    const channel = new BroadcastChannel('pos_customer_display');

    // UI Elements
    const views = {
        welcome: document.getElementById('screen-welcome'),
        payment: document.getElementById('screen-payment'),
        success: document.getElementById('screen-success')
    };
    const qrImg = document.getElementById('customer-qr');
    const qrTotal = document.getElementById('qr-total-display');
    
    // Cart Elements
    const cartContainer = document.getElementById('cart-container');
    const elSubtotal = document.getElementById('bill-subtotal');
    const elDiscount = document.getElementById('bill-discount');
    const elTotal = document.getElementById('bill-total');

    function switchView(viewName) {
        Object.values(views).forEach(el => el.style.display = 'none');
        if (views[viewName]) views[viewName].style.display = 'block';
    }

    // Lắng nghe tín hiệu từ POS
    channel.onmessage = (event) => {
        const data = event.data;

        // 1. Cập nhật Giỏ hàng (Real-time)
        if (data.type === 'UPDATE_CART') {
            renderCart(data.items, data.subtotal, data.discount_amt, data.total);
            
            // Nếu đang ở màn hình QR mà giỏ hàng thay đổi -> Quay về Welcome để tránh QR sai tiền
            if (views.payment.style.display === 'block') {
                switchView('welcome');
            }
        }
        
        // 2. Hiện QR Code
        else if (data.type === 'SHOW_QR') {
            switchView('payment');
            qrImg.src = data.url;
            qrTotal.textContent = parseInt(data.amount).toLocaleString('vi-VN') + ' đ';
        } 
        
        // 3. Thanh toán thành công
        else if (data.type === 'SUCCESS') {
            switchView('success');
            setTimeout(() => {
                switchView('welcome');
                // Xóa giỏ hàng hiển thị
                renderCart([], 0, 0, 0); 
            }, 5000);
        }
        
        // 4. Reset
        else if (data.type === 'RESET') {
            switchView('welcome');
        }
    };

    function renderCart(items, subtotal, discountAmt, total) {
        // Cập nhật số tiền
        elSubtotal.textContent = subtotal.toLocaleString('vi-VN') + ' đ';
        elDiscount.textContent = '-' + discountAmt.toLocaleString('vi-VN') + ' đ';
        elTotal.textContent = total.toLocaleString('vi-VN') + ' đ';

        // Render danh sách món
        cartContainer.innerHTML = '';
        
        if (!items || items.length === 0) {
            cartContainer.innerHTML = `
                <div class="text-center text-muted mt-5 pt-5">
                    <i class="fa-solid fa-basket-shopping fa-3x mb-3 opacity-25"></i>
                    <p>Mời quý khách gọi món</p>
                </div>`;
            return;
        }

        items.forEach(item => {
    const itemTotal = item.price * item.quantity;

    const maxPossible = Number(item.max_possible ?? item.maxPossible);
    const isOver = !!item.is_over || (Number.isFinite(maxPossible) && Number(item.quantity) > maxPossible);

    const shortageLine = (isOver && Number.isFinite(maxPossible))
        ? `<div class="text-danger small mt-1">Không đủ nguyên liệu: tối đa ${maxPossible} (thiếu ${item.quantity - maxPossible})</div>`
        : '';

    const html = `
        <div class="cart-item">
            <img src="${item.img}" class="item-img" onerror="this.src='https://placehold.co/60'">
            <div class="item-info">
                <div class="item-name">${item.name}</div>
                <div class="item-meta">
                    ${item.quantity} x ${parseInt(item.price).toLocaleString('vi-VN')}
                </div>
                ${shortageLine}
            </div>
            <div class="item-total">
                ${itemTotal.toLocaleString('vi-VN')} đ
            </div>
        </div>
    `;
    cartContainer.insertAdjacentHTML('beforeend', html);
});

    }
</script>
</body>
</html>