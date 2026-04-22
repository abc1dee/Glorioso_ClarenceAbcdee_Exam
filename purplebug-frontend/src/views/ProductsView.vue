<template>
  <CustomerLayout @open-cart="showCart = true" @open-orders="openMyOrders">
    <div class="flex flex-col min-h-full pb-16">

      <!-- Search & Sort Bar -->
      <div class="flex justify-between items-center mb-10 mt-4 w-full">
        <!-- Search -->
        <div class="relative w-80">
          <input v-model="search" placeholder="Search" 
            class="w-full text-sm border border-gray-200 rounded-full py-2.5 px-6 pr-10 focus:outline-none text-gray-600" />
          <SearchIcon class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-600 pointer-events-none" />
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
          <button @click="setSort('asc')" 
            :class="sortOrder === 'asc' ? 'bg-[#8b3f98] text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
            class="text-xs font-bold px-4 py-2.5 rounded-md transition-colors">
            Price ascending
          </button>
          <button @click="setSort('desc')" 
            :class="sortOrder === 'desc' ? 'bg-[#8b3f98] text-white shadow-sm' : 'bg-[#e5e7eb] text-gray-500 hover:bg-gray-300'"
            class="text-xs font-bold px-4 py-2.5 rounded-md transition-colors">
            Price descending
          </button>
        </div>
      </div>

      <!-- Product Grid -->
      <div v-if="loading" class="text-center py-20 text-gray-400">Loading products...</div>
      <div v-else-if="filteredProducts.length === 0" class="text-center py-20 text-gray-400">No products found.</div>
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10 mb-16">
        <div v-for="product in paginatedProducts" :key="product.id"
          class="border border-gray-100 rounded-md overflow-hidden bg-white shadow-[0_2px_8px_rgba(0,0,0,0.04)] cursor-pointer hover:shadow-md transition-shadow group flex flex-col"
          @click="openAddToCart(product)">
          
          <!-- Image Container -->
          <div class="bg-[#e5e7eb] aspect-square flex items-center justify-center m-2 rounded-sm overflow-hidden relative">
            <img v-if="product.image"
              :src="`http://localhost:8000/storage/${product.image}`"
              :alt="product.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
            <ImageIcon v-else class="w-24 h-24 text-gray-300/50" />
            
            <!-- Out of Stock Overlay -->
            <div v-if="product.stock < 1" class="absolute inset-0 bg-white/60 flex items-center justify-center font-bold text-gray-500">
              Sold Out
            </div>
          </div>
          
          <!-- Text Info -->
          <div class="px-4 py-3 bg-white">
            <h3 class="text-[13px] font-bold text-[#8b3f98] mb-1 truncate">{{ product.name || 'Text' }}</h3>
            <p class="text-xs font-bold text-black flex gap-1 items-center">
              <span>P</span>
              <span>{{ Number(product.price).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 }) }}</span>
            </p>
          </div>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="totalPages > 0" class="flex justify-center items-center gap-3 text-[13px] font-medium text-gray-500 mb-20 mt-auto">
        <button @click="currentPage > 1 && currentPage--" :disabled="currentPage === 1" class="flex items-center gap-1 hover:text-gray-800 disabled:opacity-50 transition-colors cursor-pointer">
          <ArrowLeftIcon class="w-3 h-3" /> Previous
        </button>
        
        <div class="flex items-center gap-2">
          <button v-for="page in totalPages" :key="page" @click="currentPage = page"
            :class="currentPage === page ? 'bg-gray-900 text-white' : 'hover:bg-gray-100'"
            class="w-8 h-8 rounded flex items-center justify-center transition-colors">
            {{ page }}
          </button>
        </div>

        <button @click="currentPage < totalPages && currentPage++" :disabled="currentPage === totalPages" class="flex items-center gap-1 text-black hover:text-gray-800 font-bold ml-2 pl-2 disabled:opacity-50 transition-colors cursor-pointer">
          Next <ArrowRightIcon class="w-4 h-4 ml-1" />
        </button>
      </div>
      
      <!-- Logo Footer -->
      <div class="border-t border-gray-400 pt-8 mt-10 md:mt-24 text-center">
        <img src="/logo.png" alt="PurpleBug Logo" class="h-10 mx-auto mb-3" />
        <p class="text-xs text-black font-medium">Copyright 2025 PurpleBug Inc.</p>
      </div>

    </div>

    <!-- MODAL: ADD TO CART -->
    <Teleport to="body">
      <div v-if="showAddToCart" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-[1px]" @click.self="showAddToCart = false">
        <div class="bg-white w-full max-w-3xl rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.1)] overflow-hidden flex mx-4 p-6">
          <div class="flex w-full gap-8">
            
            <!-- Left: Image -->
            <div class="w-1/2 bg-[#e2e8f0] aspect-[4/3] rounded-sm flex items-center justify-center overflow-hidden">
              <img v-if="selectedProduct?.image"
                :src="`http://localhost:8000/storage/${selectedProduct.image}`"
                class="w-full h-full object-cover" />
              <ImageIcon v-else class="w-32 h-32 text-[#cbd5e1] opacity-75" />
            </div>

            <!-- Right: Details -->
            <div class="w-1/2 flex flex-col justify-center py-4">
              <h4 class="font-bold text-[22px] text-[#8b3f98] mb-1">{{ selectedProduct?.name || 'Text Heading' }}</h4>
              
              <p class="text-black font-extrabold text-[40px] leading-none mb-8 tracking-tight flex items-start gap-1">
                <span class="text-xl mt-1.5 font-bold">P</span>
                {{ Number(selectedProduct?.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
              </p>
              
              <div class="mb-4 px-2">
                <label class="block text-[13px] font-bold text-[#444] mb-2 text-left">Quantity</label>
                <div class="relative w-full">
                  <select v-model.number="qty" 
                    class="w-full appearance-none border border-gray-300 rounded py-2.5 pl-3 pr-8 text-[13px] font-bold shadow-sm focus:outline-none focus:border-purple-300 bg-white cursor-pointer select-none">
                    <option v-for="n in Math.min(20, Math.max(1, selectedProduct?.stock || 1))" :key="n" :value="n">{{ n }}</option>
                  </select>
                  <ChevronDownIcon class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none" />
                </div>
                <p class="text-[10px] text-gray-400 mt-1">Available: {{ selectedProduct?.stock }}</p>
              </div>

              <div class="mt-auto pt-4 border-t border-transparent px-2">
                <button @click="confirmAddToCart" :disabled="cartLoading || selectedProduct?.stock < 1" 
                  class="w-full bg-[#8b3f98] hover:bg-[#72337d] text-[#e8d5ec] text-sm font-semibold py-3.5 rounded-[4px] transition-colors disabled:opacity-50">
                  {{ cartLoading ? 'Adding...' : (selectedProduct?.stock < 1 ? 'Out of Stock' : 'Add To Cart') }}
                </button>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal: Cart Formatted to Match Screenshot 1 -->
    <Teleport to="body">
      <div v-if="showCart" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-[1px]" @click.self="showCart = false">
        <div class="bg-white w-full max-w-[800px] shadow-2xl flex flex-col h-[75vh] relative m-4 border border-gray-100">
          
          <button @click="showCart = false" class="absolute top-8 right-8 text-gray-400 hover:text-gray-700 z-10 transition-colors">
            <XIcon class="w-6 h-6" />
          </button>

          <!-- Header -->
          <div class="flex justify-between items-center px-12 py-8 mt-2">
            <div class="flex items-center gap-3 text-[#8b3f98]">
              <ShoppingCartIcon class="w-8 h-8" />
              <h2 class="text-3xl font-extrabold tracking-tight">Cart</h2>
            </div>
            
            <button @click="handleCheckout" :disabled="cart.items.length === 0 || checkoutLoading" 
              class="bg-[#8b3f98] text-white px-8 py-3 rounded-md font-bold text-[11px] uppercase tracking-wide hover:bg-[#72337d] disabled:opacity-50 transition-colors mr-10 shadow-sm">
              {{ checkoutLoading ? 'Processing...' : 'Place Order' }}
            </button>
          </div>
          
          <div class="px-12">
            <hr class="border-gray-500 mb-6">
          </div>

          <!-- Cart Items Container -->
          <div class="flex-1 overflow-y-auto px-12 pb-8">
            <div v-if="cart.items.length === 0" class="text-center py-20 text-gray-400 font-bold">Cart is empty.</div>
            <div v-else class="space-y-10">
              <div v-for="item in cart.items" :key="item.id" class="flex gap-8 group">
                <!-- Image Box -->
                <div class="w-[220px] h-[150px] bg-[#e2e8f0] flex items-center justify-center flex-shrink-0">
                  <img v-if="item.product?.image" :src="`http://localhost:8000/storage/${item.product.image}`" class="w-full h-full object-cover" />
                  <ImageIcon v-else class="w-16 h-16 text-[#cbd5e1] opacity-75" />
                </div>
                
                <!-- Item Info -->
                <div class="flex flex-col justify-start">
                  <div class="flex items-start justify-between w-full">
                    <h4 class="font-bold text-[17px] text-[#8b3f98] mb-1.5">{{ item.product?.name }}</h4>
                    <button @click="cart.removeItem(item.id)" class="text-red-400 hover:text-red-600 text-[10px] font-bold uppercase tracking-wider ml-6 opacity-0 group-hover:opacity-100 transition-opacity">Remove</button>
                  </div>
                  
                  <p class="text-black font-extrabold text-[32px] leading-none mb-6 flex items-start gap-1 tracking-tight">
                    <span class="text-base mt-1.5">P</span>
                    {{ Number(item.quantity * item.product?.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                  </p>
                  
                  <div>
                    <label class="block text-[10px] font-semibold text-gray-700 mb-1">Quantity</label>
                    <div class="border border-gray-200 rounded-sm py-1.5 px-4 w-40 text-xs text-gray-600 bg-white">
                      {{ item.quantity }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Solid Purple Footer -->
          <div class="bg-[#8b3f98] text-white px-12 py-8 flex items-center gap-6 mt-auto">
            <span class="font-bold text-sm uppercase tracking-wider">Total:</span>
            <span class="font-extrabold text-[32px] flex items-start gap-1 leading-none">
              <span class="text-xl mt-1.5">P</span> 
              {{ Number(cart.total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </span>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal: Checkout Success to Match Screenshot 2 -->
    <Teleport to="body">
      <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-[1px]" @click.self="showSuccessModal = false">
        <div class="bg-white w-full max-w-[500px] rounded-[20px] shadow-2xl flex flex-col items-center pt-10 pb-8 px-6 border border-gray-100">
          
          <img src="/logo.png" alt="PurpleBug Logo" class="h-10 mb-16" />
          
          <!-- Thank You Illustration -->
          <div class="mb-14 flex justify-center w-full">
             <img src="/thankyou.png" alt="Thank You" class="w-48 h-auto object-contain" />
          </div>
          
          <h2 class="text-[#8b3f98] font-bold text-[19px] mb-12">Thank you for shopping with us.</h2>
          
          <button @click="showSuccessModal = false" class="text-[#8b3f98] hover:text-[#6a2f74] hover:scale-110 transition-transform">
            <XCircleIcon class="w-[42px] h-[42px]" stroke-width="2" />
          </button>

        </div>
      </div>
    </Teleport>

    <!-- Modal: My Orders -->
    <Teleport to="body">
      <div v-if="showOrders" class="fixed inset-0 z-50 flex items-center justify-center bg-black/10 backdrop-blur-[1px]" @click.self="showOrders = false">
        <div class="bg-white w-full max-w-[800px] shadow-2xl flex flex-col h-[75vh] relative m-4 border border-gray-100">
          
          <button @click="showOrders = false" class="absolute top-8 right-8 text-gray-400 hover:text-gray-700 z-10 transition-colors">
            <XIcon class="w-6 h-6" />
          </button>

          <!-- Header -->
          <div class="flex justify-between items-center px-12 py-8 mt-2">
            <div class="flex items-center gap-3 text-[#8b3f98]">
              <ClipboardListIcon class="w-8 h-8" />
              <h2 class="text-3xl font-extrabold tracking-tight">My Orders</h2>
            </div>
          </div>
          
          <div class="px-12">
            <hr class="border-gray-500 mb-6">
          </div>

          <!-- Orders List -->
          <div class="flex-1 overflow-y-auto px-12 pb-8">
            <div v-if="ordersLoading" class="text-center py-20 text-gray-400 font-bold">Loading orders...</div>
            <div v-else-if="myOrders.length === 0" class="text-center py-20 text-gray-400 font-bold">No orders yet.</div>
            <div v-else class="space-y-8">
              <div v-for="order in myOrders" :key="order.id" class="border border-gray-100 rounded-lg shadow-sm p-6">
                <!-- Order Header -->
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-3">
                    <span class="text-[13px] font-bold text-gray-500">Order #{{ order.id }}</span>
                    <span class="text-[10px] text-gray-400">{{ new Date(order.created_at).toLocaleString() }}</span>
                  </div>
                  <span class="px-4 py-1.5 text-[11px] font-bold text-white rounded shadow-sm" :class="orderStatusBadge(order.status)">
                    {{ formatOrderStatus(order.status) }}
                  </span>
                </div>
                <!-- Order Items -->
                <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 py-2 border-t border-gray-50">
                  <div class="w-[60px] h-[60px] bg-[#e2e8f0] rounded flex items-center justify-center flex-shrink-0 overflow-hidden">
                    <img v-if="item.product?.image" :src="`http://localhost:8000/storage/${item.product.image}`" class="w-full h-full object-cover" />
                    <ImageIcon v-else class="w-6 h-6 text-[#cbd5e1]" />
                  </div>
                  <div class="flex-1">
                    <h4 class="text-[13px] font-bold text-[#8b3f98]">{{ item.product?.name || 'Product' }}</h4>
                    <p class="text-[11px] text-gray-500">Qty: {{ item.quantity }} &times; P{{ Number(item.price).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</p>
                  </div>
                  <span class="text-sm font-extrabold text-black">P{{ Number(item.quantity * item.price).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
                </div>
                <!-- Order Total -->
                <div class="flex justify-end pt-3 mt-2 border-t border-gray-200">
                  <span class="text-sm font-bold text-gray-500 mr-2">Total:</span>
                  <span class="text-lg font-extrabold text-black">P{{ Number(order.total).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </Teleport>

  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import CustomerLayout from '../layouts/CustomerLayout.vue'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'
import api from '../axios'
import {
  Search as SearchIcon,
  Image as ImageIcon,
  ArrowLeft as ArrowLeftIcon,
  ArrowRight as ArrowRightIcon,
  ChevronDown as ChevronDownIcon,
  ShoppingCart as ShoppingCartIcon,
  X as XIcon,
  XCircle as XCircleIcon,
  ShoppingBag as ShoppingBagIcon,
  ClipboardList as ClipboardListIcon
} from 'lucide-vue-next'

const auth = useAuthStore()
const cart = useCartStore()
const router = useRouter()

const products = ref([])
const loading  = ref(false)
const cartLoading     = ref(false)
const checkoutLoading = ref(false)

const search    = ref('')
const sortOrder = ref('asc')

const showAddToCart    = ref(false)
const showCart         = ref(false)
const showSuccessModal = ref(false)
const showOrders       = ref(false)

const myOrders       = ref([])
const ordersLoading  = ref(false)

const selectedProduct = ref(null)
const qty             = ref(1)

const currentPage = ref(1)
const itemsPerPage = 8

const filteredProducts = computed(() => {
  let list = products.value.filter(p =>
    p.name.toLowerCase().includes(search.value.toLowerCase())
  )
  if (sortOrder.value === 'asc')  list = [...list].sort((a, b) => a.price - b.price)
  if (sortOrder.value === 'desc') list = [...list].sort((a, b) => b.price - a.price)
  return list
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage))

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredProducts.value.slice(start, end)
})

watch([search, sortOrder], () => {
  currentPage.value = 1
})

function setSort(order) {
  sortOrder.value = order
}

function openAddToCart(product) {
  selectedProduct.value = product
  qty.value = 1
  showAddToCart.value = true
}

async function confirmAddToCart() {
  if (!auth.isLoggedIn) {
    alert('Please login first to add items to cart.')
    router.push('/login')
    return
  }
  cartLoading.value = true
  try {
    await cart.addToCart(selectedProduct.value.id, qty.value)
    showAddToCart.value = false
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to add to cart.')
  } finally {
    cartLoading.value = false
  }
}

async function handleCheckout() {
  checkoutLoading.value = true
  try {
    await cart.checkout()
    showCart.value = false
    showSuccessModal.value = true
    await fetchProducts() // refresh stock
  } catch (e) {
    alert('Checkout failed.')
  } finally {
    checkoutLoading.value = false
  }
}

async function fetchProducts() {
  loading.value = true
  const res = await api.get('/products')
  products.value = res.data
  loading.value = false
}

async function fetchMyOrders() {
  ordersLoading.value = true
  try {
    const res = await api.get('/my-orders')
    myOrders.value = res.data
  } catch (e) {
    // silently fail
  } finally {
    ordersLoading.value = false
  }
}

async function openMyOrders() {
  showOrders.value = true
  await fetchMyOrders()
}

function orderStatusBadge(status) {
  return {
    pending:      'bg-[#ff9800]',
    for_delivery: 'bg-[#c0ca33]',
    delivered:    'bg-[#5cb85c]',
    canceled:     'bg-[#d9534f]',
  }[status] || 'bg-gray-500'
}

function formatOrderStatus(status) {
  return {
    pending:      'Pending',
    for_delivery: 'For Delivery',
    delivered:    'Delivered',
    canceled:     'Canceled',
  }[status] || status
}

onMounted(async () => {
  await fetchProducts()
  if (auth.isLoggedIn) {
    await cart.fetchCart()
  }
})
</script>

<style>
/* Remove standard scrollbar from cart modal to keep UI clean */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #e5e7eb;
  border-radius: 4px;
}
</style>
