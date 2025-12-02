  <div id="cart-sidebar" class="fixed top-0 right-0 h-full w-full max-w-[400px] bg-white shadow-2xl z-[70] transform translate-x-full flex flex-col font-sans">
    <div class="flex-shrink-0 px-5 py-4 border-b border-gray-100 bg-white z-30 flex justify-between items-center shadow-sm h-16">
      <h2 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
        <span id="cart-title-step">Tu Pedido</span>
      </h2>
      <button onclick="closeCart()" class="bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 w-9 h-9 rounded-full flex items-center justify-center transition-all">
        <i class="fa fa-times text-lg"></i>
      </button>
    </div>

    <div id="cart-step-1" class="flex flex-col flex-grow overflow-hidden h-full">
      <div id="cart-items-container" class="flex-grow overflow-y-auto p-5 bg-gray-50/50 space-y-3"></div>
      <div class="flex-shrink-0 bg-white border-t border-gray-200 p-5 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] z-20">
        <div class="mb-3">
          <label class="flex items-center gap-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
            <i class="fa fa-comment-dots"></i> Nota General
          </label>
          <textarea id="cart-general-note" rows="1" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-red-500 outline-none resize-none" placeholder="Ej: Vuelto de 50$"></textarea>
        </div>
        <div class="flex justify-between items-end mb-3">
          <div class="text-xs text-gray-500">Ref: <span id="step1-usd" class="font-semibold">$0.00</span></div>
          <div class="text-2xl font-black text-red-600 leading-none" id="step1-bs">Bs. 0,00</div>
        </div>
        <button onclick="goToCheckout()" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3.5 rounded-xl shadow-lg flex items-center justify-center gap-2 transform active:scale-[0.98] transition-all">
          <span>Continuar</span> <i class="fa fa-arrow-right"></i>
        </button>
      </div>
    </div>

    <div id="cart-step-2" class="hidden flex-col flex-grow overflow-hidden h-full bg-white">

      <div class="flex-grow overflow-y-auto p-5 space-y-4">

        <button onclick="backToCart()" class="text-gray-500 hover:text-gray-800 text-xs font-bold flex items-center gap-1 mb-2">
          <i class="fa fa-arrow-left"></i> Volver
        </button>

        <div class="space-y-3">

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Cédula</label>
            <input type="text" id="client-id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none font-medium" placeholder="V-12345678">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Tu Nombre</label>
            <input type="text" id="client-name" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none font-medium" placeholder="Nombre y Apellido">
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Teléfono</label>
            <input type="tel" id="client-phone" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none font-medium" placeholder="0412-0000000">
          </div>
        </div>

        <hr class="border-gray-100">

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-2 uppercase">Tipo de Orden</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="relative cursor-pointer group">
              <input type="radio" name="order_type" value="comer" class="peer hidden" checked>
              <div class="py-2 px-3 text-center border-2 border-gray-100 rounded-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-700 text-gray-500 font-bold text-sm transition-all hover:bg-gray-50">
                <i class="fa fa-utensils mr-1"></i> Comer Aquí
              </div>
            </label>
            <label class="relative cursor-pointer group">
              <input type="radio" name="order_type" value="llevar" class="peer hidden">
              <div class="py-2 px-3 text-center border-2 border-gray-100 rounded-lg peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-700 text-gray-500 font-bold text-sm transition-all hover:bg-gray-50">
                <i class="fa fa-shopping-bag mr-1"></i> Para Llevar
              </div>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-2 uppercase">Método de Pago</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="relative cursor-pointer group">
              <input type="radio" name="payment_method" value="pago_movil" class="peer hidden" onchange="togglePaymentDetails()">
              <div class="h-full flex flex-col items-center justify-center p-3 border-2 border-gray-100 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50/30 hover:border-gray-300 transition-all">
                <i class="fa fa-mobile-alt text-xl mb-1 text-gray-400 peer-checked:text-green-600"></i>
                <span class="text-xs font-bold text-gray-600 peer-checked:text-gray-900">Pago Móvil</span>
              </div>
              <div class="absolute top-2 right-2 text-green-500 opacity-0 peer-checked:opacity-100 transition-opacity"><i class="fa fa-check-circle"></i></div>
            </label>

            <label class="relative cursor-pointer group">
              <input type="radio" name="payment_method" value="en_caja" class="peer hidden" onchange="togglePaymentDetails()">
              <div class="h-full flex flex-col items-center justify-center p-3 border-2 border-gray-100 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50/30 hover:border-gray-300 transition-all">
                <i class="fa fa-cash-register text-xl mb-1 text-gray-400 peer-checked:text-blue-600"></i>
                <span class="text-xs font-bold text-gray-600 peer-checked:text-gray-900">En Caja</span>
              </div>
              <div class="absolute top-2 right-2 text-blue-500 opacity-0 peer-checked:opacity-100 transition-opacity"><i class="fa fa-check-circle"></i></div>
            </label>
          </div>
        </div>

        <div id="pago-movil-details" class="hidden animate-fade-in-down">
          <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 space-y-3">

            <div class="space-y-2">
              <div class="flex justify-between items-center mb-1">
                <p class="text-[10px] font-bold text-gray-400 uppercase">Datos Transferencia</p>
                <button onclick="copyAllPagoMovil()" class="text-[10px] bg-white border border-gray-200 px-2 py-1 rounded hover:text-green-600 font-bold flex items-center gap-1 transition-colors">
                  <i class="fa fa-copy"></i> Copiar
                </button>
              </div>

              <div class="flex justify-between items-center bg-white px-3 py-2 rounded border border-gray-100">
                <div>
                  <p class="text-[10px] text-gray-400">Banco</p>
                  <p class="text-sm font-bold text-gray-800" id="pm-bank">Banesco</p>
                </div>
                <button onclick="copyToClipboard('Banesco')" class="text-gray-400 hover:text-green-600"><i class="fa fa-clone"></i></button>
              </div>
              <div class="flex justify-between items-center bg-white px-3 py-2 rounded border border-gray-100">
                <div>
                  <p class="text-[10px] text-gray-400">Teléfono</p>
                  <p class="text-sm font-bold text-gray-800" id="pm-phone">0414-1234567</p>
                </div>
                <button onclick="copyToClipboard('04141234567')" class="text-gray-400 hover:text-green-600"><i class="fa fa-clone"></i></button>
              </div>
              <div class="flex justify-between items-center bg-white px-3 py-2 rounded border border-gray-100">
                <div>
                  <p class="text-[10px] text-gray-400">Cédula</p>
                  <p class="text-sm font-bold text-gray-800" id="pm-id">V-12345678</p>
                </div>
                <button onclick="copyToClipboard('12345678')" class="text-gray-400 hover:text-green-600"><i class="fa fa-clone"></i></button>
              </div>
            </div>

            <div class="pt-2 border-t border-gray-200">
              <label class="block text-xs font-bold text-green-700 mb-1 uppercase">
                Ref (Últimos 4 Nros)
              </label>
              <input
                type="text"
                id="pm-reference"
                inputmode="numeric"
                maxlength="4"
                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)"
                class="w-full px-3 py-2 bg-white border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none text-sm font-bold tracking-widest placeholder-gray-300"
                placeholder="0000">
            </div>

          </div>
        </div>

        <div id="en-caja-details" class="hidden animate-fade-in-down">
          <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100">
            <label class="block text-xs font-bold text-blue-800 mb-2 uppercase">¿Cómo pagarás en caja?</label>
            <div class="flex gap-3">
              <label class="flex-1 cursor-pointer">
                <input type="radio" name="cash_type" value="efectivo" class="peer hidden" checked>
                <div class="py-2 px-3 text-center bg-white border border-blue-200 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 text-blue-600 text-sm font-bold transition-all shadow-sm">
                  <i class="fa fa-money-bill-wave mr-1"></i> Efectivo
                </div>
              </label>
              <label class="flex-1 cursor-pointer">
                <input type="radio" name="cash_type" value="tarjeta" class="peer hidden">
                <div class="py-2 px-3 text-center bg-white border border-blue-200 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 text-blue-600 text-sm font-bold transition-all shadow-sm">
                  <i class="fa fa-credit-card mr-1"></i> Tarjeta
                </div>
              </label>
            </div>
          </div>
        </div>

      </div>

      <div class="flex-shrink-0 bg-white border-t border-gray-200 p-5 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] z-20 pb-8 md:pb-5">
        <div class="flex justify-between items-end mb-3">
          <span class="text-sm text-gray-500 font-medium">Total a pagar:</span>
          <span id="step2-bs" class="text-2xl font-black text-gray-900">Bs. 0,00</span>
        </div>
        <button onclick="sendOrder()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-200 flex items-center justify-center gap-2 transform active:scale-[0.98] transition-all">
          <i class="fa fa-whatsapp text-xl"></i>
          <span>Confirmar y Enviar</span>
        </button>
      </div>
    </div>
  </div>