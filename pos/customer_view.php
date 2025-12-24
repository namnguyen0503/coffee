<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xin chào quý khách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .welcome-screen { text-align: center; }
        .payment-screen { display: none; text-align: center; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .qr-img { max-width: 400px; width: 100%; border-radius: 10px; margin-bottom: 20px; }
        .total-amount { font-size: 3rem; color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>

    <div id="screen-welcome" class="welcome-screen">
        <img src="../admin/assets/dist/img/logo coffee.png" alt="Logo" style="width: 150px; margin-bottom: 20px;">
        <h1 class="display-4 fw-bold text-uppercase">Nguyễn Văn Coffee</h1>
        <p class="fs-3 text-muted">Xin chào quý khách!</p>
    </div>

    <div id="screen-payment" class="payment-screen">
        <h2 class="mb-4 text-primary"><i class="fa-solid fa-qrcode"></i> QUÉT MÃ ĐỂ THANH TOÁN</h2>
        <img id="customer-qr" src="" class="qr-img border">
        <div class="mt-3">
            <p class="mb-0 fs-4">Tổng thanh toán:</p>
            <div id="customer-total" class="total-amount">0 đ</div>
        </div>
        <p class="mt-3 text-muted fst-italic">Vui lòng kiểm tra kỹ số tiền trước khi xác nhận.</p>
    </div>

    <div id="screen-success" class="welcome-screen" style="display: none;">
        <i class="fa-solid fa-circle-check text-success" style="font-size: 100px;"></i>
        <h1 class="mt-4">Thanh toán thành công!</h1>
        <p class="fs-3">Cảm ơn và hẹn gặp lại.</p>
    </div>

    <script>
        // Kênh giao tiếp với tab POS chính
        const channel = new BroadcastChannel('pos_customer_display');

        const screenWelcome = document.getElementById('screen-welcome');
        const screenPayment = document.getElementById('screen-payment');
        const screenSuccess = document.getElementById('screen-success');

        const qrImg = document.getElementById('customer-qr');
        const totalText = document.getElementById('customer-total');

        channel.onmessage = (event) => {
            const data = event.data;

            if (data.type === 'SHOW_QR') {
                // 1. Hiển thị QR
                screenWelcome.style.display = 'none';
                screenSuccess.style.display = 'none';
                screenPayment.style.display = 'block';

                qrImg.src = data.url;
                totalText.textContent = parseInt(data.amount).toLocaleString('vi-VN') + ' đ';
            } 
            else if (data.type === 'SUCCESS') {
                // 2. Hiển thị cảm ơn
                screenPayment.style.display = 'none';
                screenWelcome.style.display = 'none';
                screenSuccess.style.display = 'block';

                // Tự quay về Welcome sau 5 giây
                setTimeout(() => {
                    screenSuccess.style.display = 'none';
                    screenWelcome.style.display = 'block';
                }, 5000);
            }
            else if (data.type === 'RESET') {
                // 3. Reset về màn hình chờ
                screenPayment.style.display = 'none';
                screenSuccess.style.display = 'none';
                screenWelcome.style.display = 'block';
            }
        };
    </script>
</body>
</html>