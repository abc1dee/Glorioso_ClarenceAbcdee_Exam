<template>
  <AdminLayout page-title="Orders" page-description="View and verify customer orders">

    <!-- Header Box -->
    <div class="bg-white rounded-md shadow-sm border border-gray-100 p-6 mb-6">
      <h2 class="text-[#8b3f98] font-bold text-lg">Orders</h2>
    </div>

    <!-- Table Container -->
    <div class="bg-transparent overflow-x-auto drop-shadow-sm rounded-lg">
      <table class="w-full text-center border-separate border-spacing-y-4">
        <!-- Purple header -->
        <thead>
          <tr class="bg-[#8b3f98] text-white text-sm font-bold shadow-md rounded-md h-12">
            <th class="px-4 py-3 rounded-l-lg w-1/4">Full Name</th>
            <th class="px-4 py-3">Product</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 rounded-r-lg w-32">Action</th>
          </tr>
        </thead>
        <tbody class="text-sm font-bold text-black" style="border-spacing: 0 10px;">
          <tr v-if="loading" class="bg-white shadow">
            <td colspan="4" class="py-6 text-center text-slate-400 rounded-lg">Loading...</td>
          </tr>
          <tr v-else-if="orders.length === 0" class="bg-white shadow">
            <td colspan="4" class="py-6 text-center text-slate-400 rounded-lg">No orders found.</td>
          </tr>
          <!-- Data rows -->
          <tr v-else v-for="order in orders" :key="order.id" class="bg-white shadow-sm hover:shadow-md transition-shadow">
            <td class="py-4 px-4 rounded-l-lg">{{ order.user?.full_name || 'Walk-in / Deleted User' }}</td>
            <td class="py-4 px-4">{{ getProductName(order) }}</td>
            <td class="py-4 px-4">
              <span class="px-4 py-1.5 text-[11px] font-bold text-white rounded shadow-sm" :class="statusBadge(order.status)">
                {{ formatStatus(order.status) }}
              </span>
            </td>
            <td class="py-4 px-4 rounded-r-lg">
              <div class="flex items-center justify-center gap-4 text-black">
                <button @click="openEditModal(order)" class="hover:text-purple-600 hover:scale-110 transition-transform">
                  <EyeIcon class="w-5 h-5"/>
                </button>
                <button @click="deleteOrder(order.id)" class="hover:text-red-500 hover:scale-110 transition-transform">
                  <TrashIcon class="w-4 h-4"/>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Custom View/Edit Modal matching the screenshot -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm" @click.self="showModal = false">
        <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl overflow-hidden flex flex-col mx-4 border border-gray-100">
          
          <!-- Purple Modal Header with Integrated Actions -->
          <div class="bg-[#8b3f98] px-8 py-4 flex items-center justify-between">
            <h3 class="text-white font-bold text-xl uppercase tracking-wide">{{ form.customerName }}</h3>
            <div class="flex gap-4">
              <button @click="saveOrder" :disabled="saving" 
                      class="bg-white text-[#8b3f98] font-bold text-xs uppercase px-10 py-3 rounded-md hover:bg-gray-100 transition-colors shadow">
                {{ saving ? 'SAVING...' : 'SAVE' }}
              </button>
              <button @click="showModal = false" 
                      class="bg-white text-[#8b3f98] font-bold text-xs uppercase px-8 py-3 rounded-md hover:bg-gray-100 transition-colors shadow">
                CLOSE
              </button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="p-12 pb-20 bg-[#fbfbfb]">
            <form @submit.prevent="saveOrder" class="grid grid-cols-2 gap-x-12 gap-y-8">
              
              <!-- Full Name -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-1 ml-1 text-left">Full Name</span>
                <input v-model="form.customerName" type="text" disabled readonly
                  class="w-full bg-[#e5e7eb] border border-gray-400 rounded-md shadow-sm text-sm font-bold text-black py-2.5 px-4 focus:outline-none" />
              </div>

              <!-- Product Ordered -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-1 ml-1 text-left">Product Ordered</span>
                <input v-model="form.productName" type="text" disabled readonly
                  class="w-full bg-[#e5e7eb] border border-gray-400 rounded-md shadow-sm text-sm font-bold text-black py-2.5 px-4 focus:outline-none" />
              </div>
              
              <!-- Status -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-1 ml-1 text-left">Status</span>
                <select v-model="form.status" 
                  class="w-full bg-white border border-gray-400 rounded-md shadow-sm text-sm font-bold text-black py-2.5 px-3 focus:outline-none appearance-none">
                  <option value="pending">Pending</option>
                  <option value="for_delivery">For Delivery</option>
                  <option value="delivered">Delivered</option>
                  <option value="canceled">Canceled</option>
                </select>
              </div>

              <!-- Quantity -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-1 ml-1 text-left">Quantity</span>
                <input v-model="form.quantity" type="number" disabled readonly
                  class="w-full bg-[#e5e7eb] border border-gray-400 rounded-md shadow-sm text-sm font-bold text-black py-2.5 px-4 focus:outline-none" />
              </div>

            </form>
          </div>
          
        </div>
      </div>
    </Teleport>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import api         from '../../axios'
import { Eye as EyeIcon, Trash2 as TrashIcon } from 'lucide-vue-next'

const orders  = ref([])
const loading = ref(false)
const showModal = ref(false)
const saving = ref(false)

const form = reactive({
  id: null,
  customerName: '',
  productName: '',
  status: 'pending',
  quantity: 1
})

function statusBadge(status) {
  return {
    pending:      'bg-[#ff9800]',
    for_delivery: 'bg-[#c0ca33]',
    delivered:    'bg-[#5cb85c]',
    canceled:     'bg-[#d9534f]',
  }[status] || 'bg-gray-500'
}

function formatStatus(status) {
  return {
    pending:      'Pending',
    for_delivery: 'For Delivery',
    delivered:    'Delivered',
    canceled:     'Canceled',
  }[status] || status
}

function getProductName(order) {
  if (order.items && order.items.length > 0 && order.items[0].product) {
    return order.items[0].product.name;
  }
  return 'Product ' + order.id;
}

function getProductQuantity(order) {
  if (order.items && order.items.length > 0) {
    return order.items[0].quantity || 1;
  }
  return 1;
}

async function fetchOrders() {
  loading.value = true
  try {
    const res = await api.get('/orders')
    orders.value = res.data
  } catch (e) {
    // silently fail or alert
  } finally {
    loading.value = false
  }
}

function openEditModal(order) {
  form.id = order.id
  form.customerName = order.user?.full_name || 'Walk-in User'
  form.productName = getProductName(order)
  form.status = order.status || 'pending'
  form.quantity = getProductQuantity(order)
  
  showModal.value = true
}

async function saveOrder() {
  saving.value = true
  try {
    // Only status is editable based on the UI screenshot (the rest are inputs but look disabled/informational except status dropdown)
    await api.patch(`/orders/${form.id}/status`, { status: form.status })
    showModal.value = false
    await fetchOrders()
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to update order')
  } finally {
    saving.value = false
  }
}

async function deleteOrder(id) {
  if (!confirm('Are you sure you want to delete this order?')) return
  try {
    await api.delete(`/orders/${id}`)
    await fetchOrders()
  } catch (e) {
    alert('Failed to delete order')
  }
}

onMounted(fetchOrders)
</script>
