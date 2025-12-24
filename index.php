<?php
session_start();

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])|| !in_array($_SESSION['role'], ['admin'])) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin
$fullname = $_SESSION['fullname'];
$role = $_SESSION['role']; 

// Xử lý hiển thị tên chức vụ cho đẹp
$role_label = 'Nhân viên';
if ($role === 'admin') $role_label = 'Quản lý cấp cao';
if ($role === 'wh-staff') $role_label = 'Thủ kho';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Nguyễn Văn Coffee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .dashboard-container { margin-top: 50px; }
        .welcome-banner { background: #6f4e37; color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        
        .feature-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            height: 100%;
            text-decoration: none; 
            color: inherit;
            display: block; /* Đảm bảo thẻ a full block */
        }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .card-icon { font-size: 3rem; margin-bottom: 15px; }
        
        /* Màu sắc cho từng role */
        .bg-pos { background: linear-gradient(135deg, #11998e, #38ef7d); color: white; }
        .bg-admin { background: linear-gradient(135deg, #eb3349, #f45c43); color: white; }
        /* [MỚI] Màu cho kho (Xanh dương đậm) */
        .bg-warehouse { background: linear-gradient(135deg, #2c3e50, #4ca1af); color: white; }
    </style>
</head>
<body>

<div class="container dashboard-container">
    <div class="welcome-banner d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0">Xin chào, <?= htmlspecialchars($fullname) ?>!</h2>
            <small>Chức vụ: <strong><?= $role_label ?></strong></small>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="modal fade" id="modalEndShiftRoot" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-door-closed"></i> KẾT THÚC CA & ĐĂNG XUẤT</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-dark">
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i> Hệ thống phát hiện bạn đang trong ca làm việc. Vui lòng chốt tiền để đăng xuất.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tổng tiền mặt thực tế trong két:</label>
                    <input type="number" id="root-end-cash-input" class="form-control form-control-lg" placeholder="Nhập số tiền...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Ghi chú (nếu lệch tiền):</label>
                    <textarea id="root-end-note-input" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="endShiftRoot()">Chốt ca & Đăng xuất</button>
            </div>
        </div>
    </div>
</div>

<script>


    function endShiftRoot() {
        const cashInput = document.getElementById('root-end-cash-input');
        const noteInput = document.getElementById('root-end-note-input');

        // Validate nhập tiền
        if (!cashInput || cashInput.value.trim() === "") {
            alert("Vui lòng nhập số tiền mặt thực tế trong két!");
            cashInput?.focus();
            return;
        }

        if (!confirm("Xác nhận chốt ca và đăng xuất khỏi hệ thống?")) return;

        const cash = cashInput.value;
        const note = noteInput ? noteInput.value : '';

        // Gọi API chốt ca (Lưu ý đường dẫn: đang ở root nên vào folder core/)
        fetch('core/session_manager.php?action=end_shift', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ end_cash: cash, note: note })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Thông báo doanh thu
                window.location.href = 'login.php'; // Đá về trang login
            } else {
                alert("Lỗi Server: " + data.message);
            }
        })
        .catch(err => {
            console.error("Lỗi kết nối:", err);
            alert("Không thể kết nối đến Server. Vui lòng kiểm tra mạng.");
        });
    }
</script>

</body> 




<button class="btn btn-outline-danger btn-sm ms-2" onclick="confirmLogout()">
    <i class="fa-solid fa-right-from-bracket"></i> Thoát ca
</button>
    </div>

    <div class="row justify-content-center g-4">
        
        <?php if ($role === 'admin' || $role === 'staff'): ?>
        <div class="col-md-4 col-sm-6">
            <a href="pos/index.php" class="card feature-card bg-pos text-center p-5">
                <div class="card-body">
                    <i class="fa-solid fa-cash-register card-icon"></i>
                    <h3>Bán Hàng</h3>
                    <p>POS & Thu ngân</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($role === 'admin' || $role === 'wh-staff'): ?>
        <div class="col-md-4 col-sm-6">
            <a href="warehouse/index.php" class="card feature-card bg-warehouse text-center p-5">
                <div class="card-body">
                    <i class="fa-solid fa-boxes-stacked card-icon"></i>
                    <h3>Kho Hàng</h3>
                    <p>Nhập kho & Kiểm kê</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
        <div class="col-md-4 col-sm-6">
            <a href="admin/index.php" class="card feature-card bg-admin text-center p-5">
                <div class="card-body">
                    <i class="fa-solid fa-chart-line card-icon"></i>
                    <h3>Quản Trị</h3>
                    <p>Quản lý & Doanh thu</p>
                </div>
            </a>
        </div>
        <?php endif; ?>

    </div>
</div>
<script>
    function confirmLogout() {
    // 1. Kiểm tra xem user có đang trong ca làm việc không
    fetch('core/session_manager.php?action=check_status')
    .then(res => res.json())
    .then(data => {
        if (data.success && data.is_open) {
            // TRƯỜNG HỢP 1: ĐANG TRONG CA
            // Hiển thị modal bắt chốt tiền (id modal của bạn là modalEndShiftRoot)
            const modal = new bootstrap.Modal(document.getElementById('modalEndShiftRoot'));
            modal.show();
        } else {
            // TRƯỜNG HỢP 2: CHƯA VÀO CA (hoặc lỗi dữ liệu)
            // Đăng xuất thẳng luôn, không hiện modal lằng nhằng
            window.location.href = 'logout.php';
        }
    })
    .catch(err => {
        console.error("Lỗi kết nối:", err);
        // Nếu lỗi API, mặc định cho logout luôn để nhân viên không bị kẹt
        window.location.href = 'logout.php';
    });
}
</script>
<div id="origin-signature" 
     style="position: fixed; bottom: 30px; left: 0; width: 100%; text-align: center;
            font-family: 'Segoe UI', sans-serif; font-size: 14px; color: #888;
            opacity: 0; pointer-events: none; transition: opacity 1.5s ease-in-out; z-index: 99999;
            letter-spacing: 1px;">
    
    Built with <i class="fa-solid fa-heart" style="color: #ff4d6d; animation: heartbeat 1.5s infinite;"></i> for 
    
    <span style="color: #ebcade; font-weight: 700; text-shadow: 0 0 5px rgba(235, 202, 222, 0.5);">
        Elysia
    </span>
    
    <span style="margin: 0 5px;">&</span>
    
    <span style="color: #f7d1de; font-weight: 700; text-shadow: 0 0 8px rgba(247, 209, 222, 0.8);">
        Cyrene
    </span>
    <audio id="morse-audio" preload="auto" style="display: none;">
    <source src="./NewPage.mp3" type="audio/mpeg">
  </audio>
</div>

<style>
    @keyframes heartbeat {
        0% { transform: scale(1); }
        15% { transform: scale(1.3); }
        30% { transform: scale(1); }
        45% { transform: scale(1.15); }
        60% { transform: scale(1); }
    }
</style>

<script>
    (function() {
    const secretOrigin = "origin";
    const secretLoop = "loop";
    let input = "";
    
    const signature = document.getElementById('origin-signature');
    const audio = document.getElementById('morse-audio');
    
    let hideTimer;
    let isVisible = false;
    let isLooping = false;

    document.addEventListener('keydown', (e) => {
        if (e.key.length === 1 && e.key.match(/[a-z]/i)) {
            input += e.key.toLowerCase();
            
            // Giữ bộ nhớ đệm đủ dài cho cả "origin" và "loop"
            if (input.length > 10) input = input.slice(-10);

            // Kiểm tra lệnh ORIGIN
            if (input.endsWith(secretOrigin)) {
                toggleOrigin();
                input = ""; 
            }
            
            // Kiểm tra lệnh LOOP
            if (input.endsWith(secretLoop)) {
                toggleLoop();
                input = "";
            }
        }
    });

    function toggleOrigin() {
        if (!isVisible) {
            // HIỆN
            isVisible = true;
            signature.style.opacity = "1";
            signature.style.pointerEvents = "auto";
            
            if (audio) {
                audio.volume = 1;
                audio.currentTime = 0;
                audio.play();
                // Khi bật origin, mặc định sẽ tự tắt sau 46s TRỪ KHI đang bật loop
                startHideTimer();
            }
        } else {
            // TẮT NGAY LẬP TỨC
            fadeOutAndHide();
        }
    }

    function toggleLoop() {
        if (!isVisible) return; // Nếu chưa hiện chữ/nhạc thì lệnh loop không có tác dụng

        isLooping = !isLooping; // Đảo trạng thái loop
        
        if (audio) {
            audio.loop = isLooping;
        }

        if (isLooping) {
            console.log("Loop Enabled");
            if (hideTimer) clearTimeout(hideTimer); // Hủy đếm ngược tắt nhạc
            // Có thể thêm hiệu ứng nhẹ để biết loop đang bật (ví dụ: đổi màu tim)
            document.querySelector('.fa-heart').style.textShadow = "0 0 10px #ff4d6d";
        } else {
            console.log("Loop Disabled");
            document.querySelector('.fa-heart').style.textShadow = "none";
            startHideTimer(); // Bắt đầu lại đếm ngược để tắt sau khi bỏ loop
        }
    }

    function startHideTimer() {
        if (hideTimer) clearTimeout(hideTimer);
        // Chỉ chạy timer nếu KHÔNG ở chế độ loop
        if (!isLooping) {
            hideTimer = setTimeout(() => {
                fadeOutAndHide();
            }, 46000);
        }
    }

    function fadeOutAndHide() {
        isVisible = false;
        isLooping = false; // Reset luôn trạng thái loop
        if (audio) audio.loop = false;
        
        signature.style.opacity = "0";
        signature.style.pointerEvents = "none";
        
        if (hideTimer) clearTimeout(hideTimer);

        if (audio && !audio.paused) {
            let fadeInterval = setInterval(() => {
                if (audio.volume > 0.05) {
                    audio.volume -= 0.05;
                } else {
                    audio.pause();
                    audio.currentTime = 0;
                    audio.volume = 1;
                    clearInterval(fadeInterval);
                }
            }, 75);
        }
    }
})();
</script>
</body>
</html>