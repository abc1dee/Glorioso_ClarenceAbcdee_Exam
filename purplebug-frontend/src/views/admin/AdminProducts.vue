<template>
  <AdminLayout page-title="Products Management" page-description="Manage your product catalog">

    <!-- Header Box -->
    <div class="bg-white rounded-md shadow-sm border border-gray-100 flex items-center justify-between p-6 mb-6">
      <h2 class="text-[#8b3f98] font-bold text-lg">Products Management</h2>
      <button @click="openAddModal" class="bg-[#65558f] hover:bg-[#524479] text-white text-xs font-bold px-8 py-3 rounded-full shadow-md transition-colors">
        Add Product
      </button>
    </div>

    <!-- Table Container -->
    <div class="bg-transparent overflow-x-auto drop-shadow-sm rounded-lg">
      <table class="w-full text-center border-separate border-spacing-y-4">
        <!-- Purple header -->
        <thead>
          <tr class="bg-[#8b3f98] text-white text-sm font-bold shadow-md rounded-md h-12">
            <th class="px-4 py-3 rounded-l-lg w-1/4">Product Name</th>
            <th class="px-4 py-3">Price</th>
            <th class="px-4 py-3">Number of Stocks</th>
            <th class="px-4 py-3 rounded-r-lg w-32">Action</th>
          </tr>
        </thead>
        <tbody class="text-sm font-bold text-black" style="border-spacing: 0 10px;">
          <tr v-if="loading" class="bg-white shadow">
            <td colspan="4" class="py-6 text-center text-slate-400 rounded-lg">Loading...</td>
          </tr>
          <tr v-else-if="products.length === 0" class="bg-white shadow">
            <td colspan="4" class="py-6 text-center text-slate-400 rounded-lg">No products found.</td>
          </tr>
          <!-- Data rows -->
          <tr v-else v-for="p in products" :key="p.id" class="bg-white shadow-sm hover:shadow-md transition-shadow">
            <td class="py-4 px-4 rounded-l-lg">{{ p.name }}</td>
            <td class="py-4 px-4">P {{ Number(p.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
            <td class="py-4 px-4">{{ p.stock }}</td>
            <td class="py-4 px-4 rounded-r-lg">
              <div class="flex items-center justify-center gap-3 text-gray-700">
                <button @click="openEditModal(p)" class="hover:text-black hover:scale-110 transition-transform">
                  <EditIcon class="w-4 h-4"/>
                </button>
                <button @click="deleteProduct(p.id)" class="hover:text-red-500 hover:scale-110 transition-transform">
                  <TrashIcon class="w-4 h-4"/>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Custom Add/Edit Modal matching screenshot 2 -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm" @click.self="showModal = false">
        <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden flex flex-col mx-4 border border-gray-100">
          
          <!-- Purple Modal Header with Integrated Actions -->
          <div class="bg-[#8b3f98] px-8 py-5 flex items-center justify-between">
            <h3 class="text-white font-bold text-xl ml-4">{{ isEditing ? 'Edit Product' : 'Add Product' }}</h3>
            <div class="flex gap-4">
              <button @click="saveProduct" :disabled="saving" 
                      class="bg-white text-[#8b3f98] font-bold text-xs uppercase px-10 py-3 rounded-md hover:bg-gray-100 transition-colors shadow">
                {{ saving ? 'SAVING...' : 'SAVE' }}
              </button>
              <button @click="showModal = false" 
                      class="bg-white text-[#8b3f98] font-bold text-xs uppercase px-7 py-3 rounded-md hover:bg-gray-100 transition-colors shadow">
                CANCEL
              </button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="p-12 pb-20 bg-[#fbfbfb]">
            <form @submit.prevent="saveProduct" class="grid grid-cols-2 gap-12">
              
              <!-- Left side (Image) -->
              <div>
                <label class="block text-sm font-bold text-black mb-3 ml-1 text-center">Product Image</label>
                <div class="border-2 border-slate-200 rounded-xl bg-[#e2e8f0] flex items-center justify-center h-[280px] overflow-hidden relative group cursor-pointer hover:bg-slate-300 transition-colors" @click="$refs.fileInput.click()">
                  <img v-if="previewImage" :src="previewImage" class="w-full h-full object-cover" />
                  <ImageIcon v-else class="w-24 h-24 text-white opacity-90 absolute" />
                  
                  <div v-if="previewImage" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-white text-sm font-bold">
                    Change Image
                  </div>
                  
                  <!-- Hidden File Input -->
                  <input ref="fileInput" type="file" @change="onFileChange" accept="image/*" class="hidden" />
                </div>
              </div>

              <!-- Right side (Inputs) -->
              <div class="space-y-6 flex flex-col justify-center">
                <!-- Price -->
                <div>
                  <label class="block text-[13px] font-bold text-black mb-2 ml-1 hidden">Price</label>
                  <span class="block text-[13px] font-bold text-black mb-1 ml-1 text-left">Price</span>
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 font-bold text-black text-sm">P</span>
                    <input v-model.number="form.price" type="number" step="0.01" min="0" required 
                      class="w-full border border-gray-600 rounded-md shadow-sm text-sm text-black py-2.5 pl-8 pr-3 focus:outline-none" />
                  </div>
                </div>
                
                <!-- Product Name -->
                <div>
                  <span class="block text-[13px] font-bold text-black mb-1 ml-1 text-left">Product Name</span>
                  <input v-model="form.name" type="text" required 
                    class="w-full border border-gray-600 rounded-md shadow-sm text-sm text-black py-2.5 px-3 focus:outline-none" />
                </div>
                
                <!-- Number of Stocks -->
                <div>
                  <span class="block text-[13px] font-bold text-black mb-1 ml-1 text-left">Number of Stocks</span>
                  <input v-model.number="form.stock" type="number" min="0" required 
                    class="w-full border border-gray-600 rounded-md shadow-sm text-sm text-black py-2.5 px-3 focus:outline-none" />
                </div>
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
import { Plus as PlusIcon, Pencil as EditIcon, Trash2 as TrashIcon, Image as ImageIcon } from 'lucide-vue-next'

const products = ref([])
const loading  = ref(false)
const showModal= ref(false)
const saving   = ref(false)
const isEditing= ref(false)

const fileInput = ref(null)
const previewImage = ref(null)

const form = reactive({ id: null, name: '', price: null, stock: null })
const file = ref(null)

async function fetchProducts() {
  loading.value = true
  const res = await api.get('/products')
  products.value = res.data
  loading.value = false
}

function openAddModal() {
  isEditing.value = false
  form.id = null; form.name = ''; form.price = null; form.stock = null; 
  file.value = null; previewImage.value = null;
  showModal.value = true
}

function openEditModal(p) {
  isEditing.value = true
  form.id = p.id; form.name = p.name; form.price = p.price; form.stock = p.stock; 
  file.value = null;
  // Make preview image available if changing
  previewImage.value = p.image ? `http://localhost:8000/storage/${p.image}` : null
  showModal.value = true
}

function onFileChange(e) {
  const selected = e.target.files[0]
  if (selected) {
    file.value = selected
    previewImage.value = URL.createObjectURL(selected)
  }
}

async function saveProduct() {
  saving.value = true
  try {
    const fd = new FormData()
    fd.append('name', form.name)
    fd.append('price', form.price)
    fd.append('stock', form.stock)
    if (file.value) fd.append('image', file.value)

    if (isEditing.value) {
      fd.append('_method', 'PUT') 
      await api.post(`/products/${form.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' }})
    } else {
      await api.post('/products', fd, { headers: { 'Content-Type': 'multipart/form-data' }})
    }
    showModal.value = false
    await fetchProducts()
  } catch (e) {
    alert(e.response?.data?.message || 'Error saving product')
  } finally {
    saving.value = false
  }
}

async function deleteProduct(id) {
  if (!confirm('Are you sure you want to delete this product?')) return
  await api.delete(`/products/${id}`)
  await fetchProducts()
}

onMounted(fetchProducts)
</script>
