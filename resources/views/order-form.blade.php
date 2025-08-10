<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan - NYEMIL</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <!-- Header -->
    <header>
        <div>
            <button onclick="window.location.href='{{ route('cart') }}'">&larr; Kembali ke Keranjang</button>
            <h1>Form Pemesanan</h1>
        </div>
    </header>

    <!-- Order Form -->
    <main style="padding-bottom: 150px;">
        <!-- Order Summary -->
        <section>
            <h3>Ringkasan Pesanan</h3>
            <div id="orderSummary">
                <!-- Order summary will be populated by JavaScript -->
            </div>
        </section>

        <!-- Customer Form -->
        <section>
            <h3>Data Pelanggan</h3>
            <form id="orderForm">
                <div>
                    <label for="customerName">Nama <span style="color: red;">*</span></label>
                    <input type="text" id="customerName" name="customer_name" required>
                </div>
                
                <div>
                    <label for="customerPhone">Nomor Telepon <span style="color: red;">*</span></label>
                    <input type="tel" id="customerPhone" name="customer_phone" required>
                </div>
                
                <div>
                    <label for="customerAddress">Alamat <span style="color: red;">*</span></label>
                    <textarea id="customerAddress" name="customer_address" rows="3" required></textarea>
                </div>

                <!-- Delivery Options -->
                <div>
                    <h4>Pilihan Pengiriman</h4>
                    <div>
                        <input type="radio" id="pickup" name="delivery_type" value="pickup" checked onchange="updateDeliveryType()">
                        <label for="pickup">Booking (Ambil di Bazar)</label>
                    </div>
                    <div>
                        <input type="radio" id="delivery" name="delivery_type" value="delivery" onchange="updateDeliveryType()">
                        <label for="delivery">Antar ke Rumah (Hanya Wilayah KBB)</label>
                    </div>
                </div>

                <!-- Shipping Info Modal (appears when delivery is selected) -->
                <div id="shippingInfo" style="display: none; padding: 15px; border: 1px solid #ddd; margin-top: 15px; background-color: #f9f9f9;">
                    <h4>Perkiraan Ongkir</h4>
                    <p>Ongkos kirim untuk wilayah KBB: <strong>Rp 2.000</strong></p>
                    <p><em>Catatan: Pastikan alamat Anda berada dalam wilayah Kabupaten Bandung Barat (KBB)</em></p>
                </div>
            </form>
        </section>
    </main>

    <!-- Sticky Bottom -->
    <div id="checkoutSummary" style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #ccc; padding: 20px;">
        <div>
            <div id="customerSummary">
                <small>Ringkasan akan muncul setelah form diisi</small>
            </div>
            <div style="margin-top: 10px;">
                <strong>Total: Rp <span id="finalTotal">0</span></strong>
            </div>
            <button onclick="submitOrder()" id="checkoutBtn">Checkout</button>
        </div>
    </div>

    <script>
        // Set base URL for JavaScript
        const baseUrl = '{{ url('/') }}';
        
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let subtotal = 0;
        let shippingFee = 0;
        let total = 0;

        function displayOrderSummary() {
            if (cart.length === 0) {
                window.location.href = '{{ route('cart') }}';
                return;
            }

            const summaryDiv = document.getElementById('orderSummary');
            let summaryHTML = '';
            subtotal = 0;

            cart.forEach(item => {
                let itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                summaryHTML += `
                    <div style="border-bottom: 1px solid #eee; padding: 10px 0;">
                        <div style="display: flex; justify-content: between;">
                            <span>${item.name} (x${item.quantity})</span>
                            <span>Rp ${number_format(itemTotal)}</span>
                        </div>
                    </div>
                `;
            });

            summaryHTML += `
                <div style="padding: 10px 0; font-weight: bold;">
                    <div style="display: flex; justify-content: between;">
                        <span>Subtotal</span>
                        <span>Rp ${number_format(subtotal)}</span>
                    </div>
                </div>
            `;

            summaryDiv.innerHTML = summaryHTML;
            updateTotal();
        }

        function updateDeliveryType() {
            const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
            const shippingInfoDiv = document.getElementById('shippingInfo');
            
            if (deliveryType === 'delivery') {
                shippingInfoDiv.style.display = 'block';
                shippingFee = 2000;
            } else {
                shippingInfoDiv.style.display = 'none';
                shippingFee = 0;
            }
            
            updateTotal();
        }

        function updateTotal() {
            total = subtotal + shippingFee;
            document.getElementById('finalTotal').textContent = number_format(total);
        }

        function updateCustomerSummary() {
            const name = document.getElementById('customerName').value;
            const phone = document.getElementById('customerPhone').value;
            const address = document.getElementById('customerAddress').value;
            const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
            
            if (name && phone && address) {
                const deliveryText = deliveryType === 'pickup' ? 'Ambil di Bazar' : 'Antar ke Rumah';
                document.getElementById('customerSummary').innerHTML = `
                    <div><strong>${name}</strong> - ${phone}</div>
                    <div>${address}</div>
                    <div><em>${deliveryText}</em></div>
                `;
            }
        }

        function submitOrder() {
            const form = document.getElementById('orderForm');
            const formData = new FormData(form);
            
            // Validate form
            if (!form.checkValidity()) {
                alert('Mohon lengkapi semua data yang diperlukan');
                return;
            }

            // Prepare order data
            const orderData = {
                customer_name: formData.get('customer_name'),
                customer_phone: formData.get('customer_phone'),
                customer_address: formData.get('customer_address'),
                delivery_type: formData.get('delivery_type'),
                items: cart.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity
                }))
            };

            // Submit order
            fetch('{{ route('order.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear cart
                    localStorage.removeItem('cart');
                    // Redirect to status page
                    window.location.href = data.redirect_url;
                } else {
                    alert('Terjadi kesalahan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memproses pesanan');
            });
        }

        function number_format(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        // Event listeners for form fields
        document.getElementById('customerName').addEventListener('input', updateCustomerSummary);
        document.getElementById('customerPhone').addEventListener('input', updateCustomerSummary);
        document.getElementById('customerAddress').addEventListener('input', updateCustomerSummary);

        // Initialize
        displayOrderSummary();
    </script>
</body>
</html>