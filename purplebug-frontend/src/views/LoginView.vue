<template>
  <div class="min-h-screen bg-[#f4f4f4] flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-lg">
      <!-- Card -->
      <div class="bg-white rounded-lg p-10 pt-16 pb-12 shadow-sm border border-gray-100">
        <!-- Logo -->
        <div class="flex justify-center mb-12">
          <img src="/logo.png" alt="PurpleBug Logo" class="h-20 w-auto object-contain" />
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" class="space-y-6 max-w-sm mx-auto">
          <!-- Email -->
          <div>
            <label class="block text-xs font-bold text-black mb-1.5 ml-1">Email</label>
            <div class="relative flex items-center">
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-black flex items-center justify-center">
                <!-- Circular background for Mail, simulating image -->
                <div class="bg-black text-white p-1 rounded-full flex items-center justify-center w-6 h-6">
                  <MailIcon class="w-3 h-3" stroke-width="3" />
                </div>
              </div>
              <input v-model="form.email" type="email" placeholder="" required 
                class="w-full border border-gray-600 rounded drop-shadow-sm text-sm text-black py-2.5 pl-12 pr-3 focus:outline-none focus:ring-1 focus:ring-purple-600" />
            </div>
          </div>

          <!-- Password -->
          <div>
            <label class="block text-xs font-bold text-black mb-1.5 ml-1">Password</label>
            <div class="relative flex items-center">
              <div class="absolute left-3 top-1/2 -translate-y-1/2 text-black flex items-center justify-center w-6 h-6">
                <!-- Simulating solid lock -->
                <LockIcon fill="currentColor" class="w-5 h-5 text-black" />
              </div>
              <input v-model="form.password" type="password" placeholder="" required 
                class="w-full border border-gray-600 rounded drop-shadow-sm text-sm text-black py-2.5 pl-12 pr-3 focus:outline-none focus:ring-1 focus:ring-purple-600" />
            </div>
          </div>

          <!-- Error -->
          <div v-if="error" class="bg-rose-50 border border-rose-200 text-rose-600 text-sm px-4 py-3 rounded-md">
            {{ error }}
          </div>

          <div class="flex justify-center pt-8">
            <button type="submit" 
              class="bg-[#8b3f98] hover:bg-[#722f7c] rounded-md text-white font-bold text-xs uppercase px-14 py-3 shadow-md transition-colors tracking-wide" 
              :disabled="loading">
              <span v-if="loading">Signing in...</span>
              <span v-else>LOGIN</span>
            </button>
          </div>
        </form>

        <p class="text-center text-sm text-slate-500 mt-10">
          Don't have an account?
          <router-link to="/register" class="text-[#8b3f98] font-semibold hover:underline">
            Register
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter }     from 'vue-router'
import { useAuthStore }  from '../stores/auth'
import { Mail as MailIcon, Lock as LockIcon } from 'lucide-vue-next'

const auth   = useAuthStore()
const router = useRouter()
const error   = ref('')
const loading = ref(false)
const form    = reactive({ email: '', password: '' })

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    const user = await auth.login(form.email, form.password)
    if (user.role === 'admin') router.push('/admin/products')
    else                        router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Invalid credentials.'
  } finally {
    loading.value = false
  }
}
</script>
