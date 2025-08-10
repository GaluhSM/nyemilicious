<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NYEMIL - Cemilan Tradisional</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <!-- Header -->
    <header>
        <div>
            <div>
                <h1>NYEMIL</h1>
                <input type="text" placeholder="Cari produk..." id="searchInput">
                <button onclick="window.location.href='{{ route('cart') }}'" id="cartBtn">
                    Keranjang (<span id="cartCount">0</span>)
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero">
        <div>
            <h2>Cemilan Tradisional Berkualitas</h2>
            <p>Nikmati kelezatan cemilan tradisional yang dibuat dengan bahan-bahan pilihan</p>
            <button onclick="scrollToProducts()">Lihat Produk</button>
        </div>
    </section>

    <!-- Siapa Kami -->
    <section id="about">
        <div>
            <h3>Siapa Kami?</h3>
            <p>NYEMIL adalah toko cemilan tradisional yang menghadirkan cita rasa autentik Indonesia. Kami berkomitmen menyediakan cemilan berkualitas tinggi dengan resep turun temurun yang telah terjaga keasliannya.</p>
        </div>
    </section>

    <!-- Produk -->
    <section id="products">
        <div>
            <h3>Produk Kami</h3>
            <div id="productList">
                @foreach($products as $product)
                <div class="product-item">
                    <img src="{{ $product->image ?? 'https://via.placeholder.com/200x150' }}" alt="{{ $product->name }}">
                    <h4>{{ $product->name }}</h4>
                    <p>{{ $product->description }}</p>
                    <p><strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong></p>
                    <div>
                        <button onclick="decreaseQty({{ $product->id }})">-</button>
                        <span id="qty-{{ $product->id }}">1</span>
                        <button onclick="increaseQty({{ $product->id }})">+</button>
                    </div>
                    <button onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})">
                        Tambahkan ke Keranjang
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Kenapa Harus Kami -->
    <section id="why-us">
        <div>
            <h3>Kenapa Harus Kami?</h3>
            <ul>
                <li>Bahan-bahan berkualitas tinggi</li>
                <li>Resep tradisional turun temurun</li>
                <li>Proses produksi higienis</li>
                <li>Harga terjangkau</li>
                <li>Pelayanan ramah dan cepat</li>
            </ul>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div>
            <p>&copy; 2025 NYEMIL. Semua hak dilindungi undang-undang.</p>
        </div>
    </footer>

    <script>
        // Set base URL for JavaScript
        const baseUrl = '{{ url('/') }}';
        
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        updateCartCount();

        function scrollToProducts() {
            document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
        }

        function increaseQty(productId) {
            let qtyElement = document.getElementById(`qty-${productId}`);
            let currentQty = parseInt(qtyElement.textContent);
            qtyElement.textContent = currentQty + 1;
        }

        function decreaseQty(productId) {
            let qtyElement = document.getElementById(`qty-${productId}`);
            let currentQty = parseInt(qtyElement.textContent);
            if (currentQty > 1) {
                qtyElement.textContent = currentQty - 1;
            }
        }

        function addToCart(productId, productName, price) {
            let qty = parseInt(document.getElementById(`qty-${productId}`).textContent);
            
            let existingItem = cart.find(item => item.id == productId);
            if (existingItem) {
                existingItem.quantity += qty;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: price,
                    quantity: qty
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            
            // Reset quantity to 1
            document.getElementById(`qty-${productId}`).textContent = '1';
            
            alert('Produk berhasil ditambahkan ke keranjang!');
        }

        function updateCartCount() {
            let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = totalItems;
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            let searchTerm = this.value.toLowerCase();
            let products = document.querySelectorAll('.product-item');
            
            products.forEach(product => {
                let productName = product.querySelector('h4').textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>