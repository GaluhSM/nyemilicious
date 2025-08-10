<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - NYEMIL</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <!-- Header -->
    <header>
        <div>
            <button onclick="window.location.href='{{ route('landing') }}'">&larr; Kembali ke Beranda</button>
            <h1>Keranjang Belanja</h1>
        </div>
    </header>

    <!-- Cart Items -->
    <main>
        <div id="cartItems">
            <!-- Cart items will be populated by JavaScript -->
        </div>
        
        <div id="emptyCart" style="display: none;">
            <p>Keranjang Anda kosong</p>
            <button onclick="window.location.href='{{ route('landing') }}'">Mulai Belanja</button>
        </div>
    </main>

    <!-- Sticky Bottom -->
    <div id="cartSummary" style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #ccc; padding: 20px;">
        <div>
            <div>
                <strong>Total: Rp <span id="totalPrice">0</span></strong>
            </div>
            <button onclick="proceedToOrder()" id="nextBtn">Next</button>
        </div>
    </div>

    <script>
        // Set base URL for JavaScript
        const baseUrl = '{{ url('/') }}';
        
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        function displayCart() {
            const cartItemsDiv = document.getElementById('cartItems');
            const emptyCartDiv = document.getElementById('emptyCart');
            const cartSummaryDiv = document.getElementById('cartSummary');
            
            if (cart.length === 0) {
                cartItemsDiv.style.display = 'none';
                emptyCartDiv.style.display = 'block';
                cartSummaryDiv.style.display = 'none';
                return;
            }
            
            cartItemsDiv.style.display = 'block';
            emptyCartDiv.style.display = 'none';
            cartSummaryDiv.style.display = 'block';
            
            let cartHTML = '';
            let totalPrice = 0;
            
            cart.forEach((item, index) => {
                let itemTotal = item.price * item.quantity;
                totalPrice += itemTotal;
                
                cartHTML += `
                    <div class="cart-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px;">
                        <div>
                            <h4>${item.name}</h4>
                            <p>Harga: Rp ${number_format(item.price)}</p>
                            <div style="display: flex; align-items: center; gap: 10px; margin: 10px 0;">
                                <button onclick="updateQuantity(${index}, -1)">-</button>
                                <span>Jumlah: ${item.quantity}</span>
                                <button onclick="updateQuantity(${index}, 1)">+</button>
                            </div>
                            <p><strong>Subtotal: Rp ${number_format(itemTotal)}</strong></p>
                            <button onclick="removeFromCart(${index})" style="color: red;">Hapus</button>
                        </div>
                    </div>
                `;
            });
            
            cartItemsDiv.innerHTML = cartHTML;
            document.getElementById('totalPrice').textContent = number_format(totalPrice);
        }
        
        function updateQuantity(index, change) {
            if (cart[index].quantity + change <= 0) {
                removeFromCart(index);
                return;
            }
            
            cart[index].quantity += change;
            localStorage.setItem('cart', JSON.stringify(cart));
            displayCart();
        }
        
        function removeFromCart(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            displayCart();
        }
        
        function proceedToOrder() {
            if (cart.length === 0) {
                alert('Keranjang Anda kosong!');
                return;
            }
            window.location.href = '{{ route('order.form') }}';
        }
        
        function number_format(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
        
        // Initialize cart display
        displayCart();
    </script>
</body>
</html>