const order_id = document.getElementById('order-id');
const mon = document.querySelectorAll('.card.product-item.text-center.p-2');
const mon_container = document.querySelector('#product-list-container');
mon_container.addEventListener('click',function(event){ 
    const clickedElement = event.target.closest('.card.product-item.text-center.p-2');
 
    if(clickedElement){
        console.log('Bạn vừa click: ' + clickedElement.querySelector('.card-body.p-1').querySelector('h6').textContent);
    }

});


let cartItems = [];
const CART_STORAGE_KEY = 'pos_current_order';
const totalAmountElement = document.getElementById('total-amount'); 

function loadCartFromStorage() {
    console.log('--- 🚀 KHỞI TẠO: Bắt đầu tải dữ liệu giỏ hàng ---');
    const storedCart = localStorage.getItem(CART_STORAGE_KEY);
    
    if (storedCart) {
        try {
            cartItems = JSON.parse(storedCart);
            console.log(' LOCAL STORAGE LOADED. Dữ liệu được tìm thấy.');
            console.log('   Dữ liệu khởi tạo:', cartItems);
        } catch (e) {
            console.error('LỖI PHÂN TÍCH JSON:', e);
            cartItems = [];
        }
    } else {
        cartItems = [];
        console.log(' LOCAL STORAGE: Không tìm thấy dữ liệu cũ. Khởi tạo giỏ hàng trống.');
    }
    
    
    renderCart();
    updateTotalAmount();
}

function saveCartToStorage() {
    const cartJson = JSON.stringify(cartItems);
    localStorage.setItem(CART_STORAGE_KEY, cartJson);


    console.log(' LOCAL STORAGE SAVED. Giỏ hàng đã được lưu trữ.');
    console.log('   Nội dung JSON vừa lưu:', cartJson);
}

function updateTotalAmount() {
    let total = 0;
    cartItems.forEach(item => {
        total += item.price * item.quantity;
    });

    const formattedTotal = total.toLocaleString('vi-VN') + ' đ';
    if (totalAmountElement) {
        totalAmountElement.textContent = formattedTotal;
    }
    console.log(`TỔNG TIỀN MỚI: ${formattedTotal}`);
}

function renderCart() {
    const cartList = document.getElementById('cart-list');
    if (!cartList) return;
    
    cartList.innerHTML = ''; 

    cartItems.forEach((item, index) => {
        const itemPrice = item.price.toLocaleString('vi-VN');
        
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center p-2';
        li.dataset.index = index; 

        li.innerHTML = `
            <div>
                <span class="fw-bold">${item.name}</span> <br>
                <small class="text-muted">${item.quantity} x ${itemPrice} đ</small>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-outline-secondary me-2 btn-minus" data-index="${index}">-</button>
                <span class="fw-bold me-2" data-quantity="${item.quantity}">${item.quantity}</span>
                <button class="btn btn-sm btn-outline-secondary btn-plus" data-index="${index}">+</button>
                <button class="btn btn-sm btn-danger ms-3 btn-remove" data-index="${index}">Xóa</button>
            </div>
        `;
        cartList.appendChild(li);
    });
}

function addItemToCart(id, name, price) {
   
    const itemIndex = cartItems.findIndex(item => item.id === id); 

    if (itemIndex > -1) {
        
        cartItems[itemIndex].quantity += 1; 
        console.log(`Đã tăng số lượng món ${name}. Số lượng mới: ${cartItems[itemIndex].quantity}`);
    } else {
        
        cartItems.push({
            id: id,
            name: name,
            price: price,
            quantity: 1
        });
        console.log(` Đã thêm món mới: ${name} (ID: ${id}) vào giỏ hàng.`);
    }
    
    
    renderCart();
    updateTotalAmount();
    saveCartToStorage(); 
}

function handleCartInteraction(event) {
    const target = event.target;
   
    const index = parseInt(target.dataset.index); 
    
    if (isNaN(index)) return; 

    if (target.matches('.btn-plus')) {
        cartItems[index].quantity += 1;
        console.log(`▲ Tăng số lượng món: ${cartItems[index].name}`);
    } else if (target.matches('.btn-minus')) {
        cartItems[index].quantity -= 1;
        console.log(`▼ Giảm số lượng món: ${cartItems[index].name}`);

        if (cartItems[index].quantity <= 0) {
           
            const removedItem = cartItems.splice(index, 1);
            console.log(`Xóa hoàn toàn món: ${removedItem[0].name}`);
        }
    } else if (target.matches('.btn-remove')) {
        
        const removedItem = cartItems.splice(index, 1);
        console.log(` Xóa hoàn toàn món: ${removedItem[0].name} bằng nút Xóa.`);
    }
    

    renderCart();
    updateTotalAmount();
    saveCartToStorage();
}


document.querySelector('#product-list-container')?.addEventListener('click', function(event) {
    const productCard = event.target.closest('.card.product-item');
    
    if (productCard) {
        const id = parseInt(productCard.dataset.id);
        const price = parseInt(productCard.dataset.price);
        const name = productCard.querySelector('.card-title').textContent.trim();
        
        if (isNaN(id) || isNaN(price)) {
            console.error('LỖI DỮ LIỆU: data-id hoặc data-price không phải là số hợp lệ.');
            return;
        }

        addItemToCart(id, name, price);
    }
});


document.getElementById('cart-list')?.addEventListener('click', handleCartInteraction);


document.getElementById('checkout-btn')?.addEventListener('click', function() {
    console.log(' BƯỚC AJAX: Nút THANH TOÁN được nhấp. Dữ liệu cuối cùng chuẩn bị gửi đi:');
    console.log(cartItems);
   
});


document.addEventListener('DOMContentLoaded', loadCartFromStorage);





function handleCheckout() {
    if (cartItems.length === 0) {
        alert("Giỏ hàng rỗng! Vui lòng chọn món trước khi thanh toán.");
        return;
    }


    if(confirm("Xác nhận thanh toán đơn hàng?")) {
        const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    const checkoutData = {
        action: 'checkout', 
        table_id: 1, 
        total_price: total,
        items: cartItems 
    };

console.log('BƯỚC AJAX: Dữ liệu gửi đi:', checkoutData);
console.log('   Dữ liệu JSON thô (body):', JSON.stringify(checkoutData)); 
    
    fetch('../core/order_processor.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(checkoutData)
})
.then(response => {
    if (!response.ok) {
        throw new Error('Lỗi Mạng hoặc Server ' + response.status);
    }
    return response.json(); 
}) 
.then(data => {
    if (data.success === false) { 
        alert(` LỖI XỬ LÝ ĐƠN HÀNG: ${data.message}`);
        return;
    }
    
    alert(`Thanh toán thành công! Order ID: ${data.order_id}. Vui lòng in hóa đơn.`);
    
    cartItems = [];
    localStorage.removeItem(CART_STORAGE_KEY);
    renderCart();
    updateTotalAmount();
    console.log('id: ' + data.order_id);
    order_id.textContent = Number(data.order_id)+1;
})
.catch(error => {
    console.error('LỖI AJAX/KẾT NỐI:', error);
    alert('Đã xảy ra lỗi trong quá trình xử lý. Vui lòng thử lại.');
});
    }
    else {
        return; 
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


document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', function() {
        
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('btn-dark', 'active');
            btn.classList.add('btn-outline-dark');
        });

        this.classList.remove('btn-outline-dark');
        this.classList.add('btn-dark', 'active');

        const filterValue = this.getAttribute('data-filter'); 
        const allProducts = document.querySelectorAll('.product-item'); 

        allProducts.forEach(productCard => {
            const columnContainer = productCard.closest('.col-4'); 
            
            const productCategoryId = productCard.getAttribute('data-category-id');

            if (filterValue === 'all') {
                columnContainer.style.display = 'block';
            } else {
                if (productCategoryId === filterValue) {
                    columnContainer.style.display = 'block';
                } else {
                    columnContainer.style.display = 'none';
                }
            }
        });
    });
});


document.getElementById('search-input')?.addEventListener('keyup', function(event) {
    const searchText = event.target.value.toLowerCase().trim(); 
    const allProducts = document.querySelectorAll('.product-item');

    allProducts.forEach(productCard => {
        const columnContainer = productCard.closest('.col-4'); 
        
        const productName = productCard.querySelector('.card-title').textContent.toLowerCase();

        if (productName.includes(searchText)) {
            columnContainer.style.display = 'block';
        } else {
            columnContainer.style.display = 'none';
        }
    });

    if (searchText.length > 0) {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('btn-dark', 'active');
            btn.classList.add('btn-outline-dark');
        });
        const allBtn = document.querySelector('.filter-btn[data-filter="all"]');
        if(allBtn) {
            allBtn.classList.remove('btn-outline-dark');
            allBtn.classList.add('btn-dark', 'active');
        }
    }
});