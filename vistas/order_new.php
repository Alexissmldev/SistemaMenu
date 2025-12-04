<?php
require_once "./php/main.php";

$conexion = conexion();

// 1. OBTENER CATEGORÍAS
$categorias = $conexion->query("SELECT * FROM categoria WHERE categoria_estado = 1 ORDER BY categoria_nombre ASC")->fetchAll();

// 2. OBTENER PRODUCTOS (Solo activos)
$productos = $conexion->query("SELECT * FROM producto WHERE producto_estado = 1 ORDER BY producto_nombre ASC")->fetchAll();

// 3. TASA DEL DÍA (Simulada, conecta esto a tu config real)
$tasa_bcv = 00.00;

$conexion = null;
?>

<div class="w-full min-h-screen bg-slate-50 flex flex-col pb-20 lg:pb-0 font-sans">

    <div class="bg-white border-b border-slate-200 px-4 py-3 sticky top-16 z-20 shadow-sm">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800 leading-tight">Nuevo Pedido</h1>
                    <p class="text-xs text-slate-500 hidden sm:block">Punto de Venta (POS)</p>
                </div>
            </div>

            <div class="text-right bg-slate-100 px-3 py-1 rounded-lg">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tasa BCV</p>
                <p class="text-sm font-bold text-green-600"><?php echo number_format($tasa_bcv, 2); ?> Bs</p>
            </div>
        </div>
    </div>

    <div class="flex-1 max-w-7xl mx-auto w-full p-3 lg:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <div class="lg:col-span-8 flex flex-col gap-4">

            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 sticky top-[135px] z-10">
                <div class="flex gap-3">
                    <div class="relative flex-grow">
                        <i class="fas fa-search absolute left-3 top-3 text-slate-400"></i>
                        <input type="text" id="searchProduct" placeholder="Buscar platillo..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all text-sm">
                    </div>
                    <select id="categoryFilter" class="w-1/3 p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none text-slate-700 text-sm font-medium">
                        <option value="all">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['categoria_id']; ?>"><?php echo $cat['categoria_nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3" id="productsContainer">

                <?php foreach ($productos as $prod): ?>
                    <div class="product-card group bg-white p-4 rounded-xl shadow-sm border border-slate-200 hover:border-orange-400 hover:shadow-md transition-all cursor-pointer relative h-28 flex flex-col justify-between overflow-hidden select-none"
                        onclick="addToCart(<?php echo $prod['producto_id']; ?>, '<?php echo $prod['producto_nombre']; ?>', <?php echo $prod['producto_precio']; ?>)">

                        <div class="absolute -right-4 -top-4 w-16 h-16 bg-slate-50 rounded-full group-hover:bg-orange-50 transition-colors"></div>

                        <div class="relative z-10">
                            <h3 class="text-sm font-bold text-slate-700 leading-snug group-hover:text-orange-700 line-clamp-2">
                                <?php echo $prod['producto_nombre']; ?>
                            </h3>
                        </div>

                        <div class="relative z-10 flex justify-between items-end mt-2">
                            <span class="text-lg font-black text-slate-800 group-hover:text-orange-600">
                                $<?php echo number_format($prod['producto_precio'], 2); ?>
                            </span>

                            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center group-hover:bg-orange-600 group-hover:text-white transition-colors shadow-sm">
                                <i class="fas fa-plus text-xs"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

        <div class="lg:col-span-4 lg:sticky lg:top-36 h-fit">

            <form action="./php/pedido_guardar.php" method="POST" class="FormularioAjax bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden flex flex-col h-[calc(100vh-160px)]">

                <div class="p-4 border-b border-slate-100 bg-slate-50/80">

                    <div class="grid grid-cols-3 bg-slate-200 p-1 rounded-lg mb-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="tipo_pedido" value="mesa" class="peer sr-only" checked>
                            <span class="block text-center py-1.5 text-xs font-bold text-slate-500 rounded-md peer-checked:bg-white peer-checked:text-orange-600 peer-checked:shadow-sm transition-all">Mesa</span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="tipo_pedido" value="llevar" class="peer sr-only">
                            <span class="block text-center py-1.5 text-xs font-bold text-slate-500 rounded-md peer-checked:bg-white peer-checked:text-orange-600 peer-checked:shadow-sm transition-all">Llevar</span>
                        </label>
                        
                    </div>

                    <div class="mb-2">
                        <div class="relative">
                            <input type="text" name="cliente_nombre" class="w-full pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 outline-none" placeholder="Nombre del cliente..." required>
                            <i class="fas fa-user absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                        </div>
                    </div>

                    <div>
                        <input type="text" name="mesa_nota" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 outline-none" placeholder="Nota o N° Mesa">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-3 custom-scrollbar bg-white relative" id="cartItemsContainer">

                    <div id="emptyCartState" class="absolute inset-0 flex flex-col items-center justify-center text-center opacity-60">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3 text-slate-300">
                            <i class="fas fa-basket-shopping text-2xl"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-400">Orden Vacía</p>
                        <p class="text-xs text-slate-400">Agrega productos</p>
                    </div>

                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase">Total a Pagar</p>
                            <p class="text-xs text-slate-400 mt-0.5">Bs <span id="cartTotalBS">0.00</span></p>
                        </div>
                        <span class="block text-3xl font-black text-slate-800 leading-none tracking-tight">$<span id="cartTotalUSD">0.00</span></span>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold shadow-lg shadow-slate-300 transition-all transform hover:-translate-y-1 flex justify-center items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        Confirmar Pedido
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

<script>
    let cart = [];
    const tasaBCV = <?php echo $tasa_bcv; ?>;

    function addToCart(id, name, price) {
        const existingItem = cart.find(item => item.id === id);

        if (existingItem) {
            existingItem.qty++;
        } else {
            cart.push({
                id,
                name,
                price,
                qty: 1
            });
        }
        updateCartUI();
    }

    function removeFromCart(id) {
        cart = cart.filter(item => item.id !== id);
        updateCartUI();
    }

    function changeQty(id, change) {
        const item = cart.find(item => item.id === id);
        if (item) {
            item.qty += change;
            if (item.qty <= 0) removeFromCart(id);
            else updateCartUI();
        }
    }

    function updateCartUI() {
        const container = document.getElementById('cartItemsContainer');
        const emptyState = document.getElementById('emptyCartState');
        const totalUSDEl = document.getElementById('cartTotalUSD');
        const totalBSEl = document.getElementById('cartTotalBS');

        container.innerHTML = ''; // Limpiar lista visual

        if (cart.length === 0) {
            container.appendChild(emptyState);
            totalUSDEl.innerText = '0.00';
            totalBSEl.innerText = '0.00';
            return;
        }

        let total = 0;

        cart.forEach(item => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;

            // Generamos iniciales para el icono
            const iniciales = item.name.substring(0, 2).toUpperCase();

            const itemDiv = document.createElement('div');
            itemDiv.className = 'flex justify-between items-center mb-3 pb-3 border-b border-slate-100 last:border-0 last:pb-0 last:mb-0 animate-fade-in-down';

            // Diseño del Item en el carrito (Sin foto, con iniciales)
            itemDiv.innerHTML = `
                <div class="flex items-center gap-3 w-full overflow-hidden">
                    
                    <div class="w-9 h-9 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        ${iniciales}
                    </div>
                    
                    <div class="flex-grow min-w-0">
                        <div class="flex justify-between items-start">
                            <p class="text-sm font-bold text-slate-700 truncate pr-2">${item.name}</p>
                            <p class="text-sm font-bold text-slate-800">$${itemTotal.toFixed(2)}</p>
                        </div>
                        
                        <div class="flex justify-between items-center mt-1">
                            <p class="text-[10px] text-slate-400">$${item.price.toFixed(2)} c/u</p>
                            
                            <div class="flex items-center bg-slate-100 rounded-md h-6">
                                <button type="button" onclick="changeQty(${item.id}, -1)" class="w-6 flex items-center justify-center text-slate-500 hover:text-red-500 transition-colors text-xs font-bold">-</button>
                                <span class="text-xs font-bold w-4 text-center text-slate-700">${item.qty}</span>
                                <button type="button" onclick="changeQty(${item.id}, 1)" class="w-6 flex items-center justify-center text-slate-500 hover:text-green-600 transition-colors text-xs font-bold">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(itemDiv);
        });

        // Totales
        totalUSDEl.innerText = total.toFixed(2);
        totalBSEl.innerText = (total * tasaBCV).toFixed(2);
    }

    // Buscador Simple en JS para filtrar las tarjetas
    document.getElementById('searchProduct').addEventListener('keyup', function(e) {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const name = card.querySelector('h3').innerText.toLowerCase();
            if (name.includes(term)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>