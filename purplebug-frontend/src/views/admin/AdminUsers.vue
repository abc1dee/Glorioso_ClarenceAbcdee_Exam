<template>
  <AdminLayout page-title="Users Management" page-description="Manage system users">

    <!-- Header Box -->
    <div class="bg-white rounded-md shadow-sm border border-gray-100 flex items-center justify-between p-6 mb-6">
      <h2 class="text-[#8b3f98] font-bold text-lg">Users Management</h2>
      <button @click="openAddModal" class="bg-[#65558f] hover:bg-[#524479] text-white text-[13px] font-bold px-10 py-3 rounded-full shadow-md transition-colors tracking-wide">
        Add User
      </button>
    </div>

    <!-- Table Container -->
    <div class="bg-transparent overflow-x-auto drop-shadow-sm rounded-lg">
      <table class="w-full text-center border-separate border-spacing-y-4">
        <!-- Purple header -->
        <thead>
          <tr class="bg-[#8b3f98] text-white text-sm font-bold shadow-md rounded-md h-12">
            <th class="px-4 py-3 rounded-l-lg w-1/4">Full Name</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 rounded-r-lg w-32">Action</th>
          </tr>
        </thead>
        <tbody class="text-sm font-bold text-black" style="border-spacing: 0 10px;">
          <tr v-if="loading" class="bg-white shadow">
            <td colspan="4" class="py-6 text-center text-slate-400 rounded-lg">Loading...</td>
          </tr>
          <tr v-else-if="users.length === 0" class="bg-white shadow">
            <td colspan="4" class="py-6 text-center text-slate-400 rounded-lg">No users found.</td>
          </tr>
          <!-- Data rows -->
          <tr v-else v-for="user in users" :key="user.id" class="bg-white shadow-sm hover:shadow-md transition-shadow">
            <td class="py-4 px-4 rounded-l-lg">{{ user.full_name }}</td>
            <td class="py-4 px-4">{{ user.email }}</td>
            <td class="py-4 px-4">
              <span class="px-4 py-1.5 text-[11px] font-bold text-white rounded shadow-sm" 
                 :class="user.is_active ? 'bg-[#5cb85c]' : 'bg-[#d9534f]'">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="py-4 px-4 rounded-r-lg">
              <div class="flex items-center justify-center gap-4 text-black">
                <button @click="openEditModal(user)" class="hover:text-purple-600 hover:scale-110 transition-transform">
                  <EditIcon class="w-4 h-4"/>
                </button>
                <button @click="deleteUser(user.id)" class="hover:text-red-500 hover:scale-110 transition-transform">
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
            <h3 class="text-white font-bold text-xl ml-2">{{ isEditing ? 'Edit User' : 'Add User' }}</h3>
            <div class="flex gap-4">
              <button @click="saveUser" :disabled="saving" 
                      class="bg-white text-[#8b3f98] font-bold text-xs uppercase px-10 py-3 rounded-md hover:bg-gray-100 transition-colors shadow">
                {{ saving ? 'SAVING...' : 'SAVE' }}
              </button>
              <button @click="showModal = false" 
                      class="bg-white text-[#8b3f98] font-bold text-xs uppercase px-8 py-3 rounded-md hover:bg-gray-100 transition-colors shadow">
                CANCEL
              </button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="p-12 pb-24 bg-[#fbfbfb]">
            <form @submit.prevent="saveUser" class="grid grid-cols-2 gap-x-12 gap-y-8 max-w-3xl mx-auto">
              
              <!-- Full Name -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-2 ml-1 text-left">Full Name</span>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black bg-white pr-2 border-r border-black/30">
                    <UserCircleIcon class="w-5 h-5"/>
                  </span>
                  <input v-model="form.full_name" type="text" required 
                    class="w-full bg-white border border-gray-500 rounded-md shadow-sm text-sm font-bold text-black py-3 pl-12 pr-4 focus:outline-none" />
                </div>
              </div>

              <!-- Email -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-2 ml-1 text-left">Email</span>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black bg-white pr-2 border-r border-black/30">
                    <MailIcon class="w-5 h-5"/>
                  </span>
                  <input v-model="form.email" type="email" required 
                    class="w-full bg-white border border-gray-500 rounded-md shadow-sm text-sm font-bold text-black py-3 pl-12 pr-4 focus:outline-none" />
                </div>
              </div>

              <!-- Password -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-2 ml-1 text-left">Password</span>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black bg-white pr-2 border-r border-black/30">
                    <LockIcon class="w-5 h-5"/>
                  </span>
                  <input v-model="form.password" type="password" :required="!isEditing"
                    class="w-full bg-white border border-gray-500 rounded-md shadow-sm text-sm font-bold text-black py-3 pl-12 pr-4 focus:outline-none" />
                </div>
              </div>
              
              <!-- Confirm Password -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-2 ml-1 text-left">Confirm Password</span>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black bg-white pr-2 border-r border-black/30">
                    <LockIcon class="w-5 h-5"/>
                  </span>
                  <input v-model="form.password_confirmation" type="password" :required="!isEditing"
                    class="w-full bg-white border border-gray-500 rounded-md shadow-sm text-sm font-bold text-black py-3 pl-12 pr-4 focus:outline-none" />
                </div>
              </div>

              <!-- Role -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-2 ml-1 text-left">Role</span>
                <div class="relative w-full">
                  <select v-model="form.role"
                    class="w-full bg-white border border-gray-500 rounded-md shadow-sm text-sm font-bold text-black py-3 px-4 focus:outline-none cursor-pointer">
                    <option value="admin">Admin</option>
                    <option value="guest">Guest</option>
                  </select>
                </div>
              </div>

              <!-- Status -->
              <div>
                <span class="block text-[13px] font-bold text-black mb-2 ml-1 text-left">Status</span>
                <div class="relative w-full">
                  <select v-model="form.is_active"
                    class="w-full bg-white border border-gray-500 rounded-md shadow-sm text-sm font-bold text-black py-3 px-4 focus:outline-none cursor-pointer">
                    <option :value="true">Active</option>
                    <option :value="false">Inactive</option>
                  </select>
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
import { Pencil as EditIcon, Trash2 as TrashIcon, CircleUserRound as UserCircleIcon, Mail as MailIcon, LockKeyhole as LockIcon } from 'lucide-vue-next'

const users     = ref([])
const loading   = ref(false)
const showModal = ref(false)
const saving    = ref(false)
const isEditing = ref(false)

const form = reactive({ id: null, full_name: '', email: '', password: '', password_confirmation: '', role: 'guest', is_active: true })

async function fetchUsers() {
  loading.value = true
  try {
    const res = await api.get('/users')
    users.value = res.data
  } catch (e) {
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  isEditing.value = false
  form.id = null; form.full_name = ''; form.email = ''; form.password = ''; form.password_confirmation = ''; form.role = 'guest'; form.is_active = true;
  showModal.value = true
}

function openEditModal(u) {
  isEditing.value = true
  form.id = u.id; form.full_name = u.full_name; form.email = u.email; form.password = ''; form.password_confirmation = ''; form.role = u.role; form.is_active = u.is_active;
  showModal.value = true
}

async function saveUser() {
  saving.value = true
  // Check password confirmation if entered
  if (form.password && form.password !== form.password_confirmation) {
    alert("Passwords do not match!")
    saving.value = false
    return
  }

  const payload = { 
    full_name: form.full_name, 
    email: form.email, 
    role: form.role, 
    is_active: form.is_active 
  }
  
  if (form.password) {
    payload.password = form.password
    payload.password_confirmation = form.password_confirmation
  }

  try {
    if (isEditing.value) {
      await api.put(`/users/${form.id}`, payload)
    } else {
      await api.post('/users', payload)
    }
    showModal.value = false
    await fetchUsers()
  } catch (e) {
    alert(e.response?.data?.message || 'Error saving user.')
  } finally {
    saving.value = false
  }
}

async function deleteUser(id) {
  if (!confirm('Are you sure you want to delete this user?')) return
  try {
    await api.delete(`/users/${id}`)
    await fetchUsers()
  } catch (e) {
    alert('Failed to delete user')
  }
}

onMounted(fetchUsers)
</script>
