<template>
  <div class="min-h-screen bg-[#f4f4f4] flex flex-col items-center justify-center p-4">
    <div class="w-full max-w-2xl">
      <!-- Card -->
      <div class="bg-white rounded-lg p-10 pt-16 pb-12 shadow-sm border border-gray-100">
        <!-- Logo -->
        <div class="flex justify-center mb-12">
          <img src="/logo.png" alt="PurpleBug Logo" class="h-20 w-auto object-contain" />
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" class="space-y-6 max-w-xl mx-auto">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-6">
            <!-- Full Name -->
            <div>
              <label class="block text-xs font-bold text-black mb-1.5 ml-1">Full Name</label>
              <div class="relative flex items-center">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-black flex items-center justify-center">
                  <div class="bg-black text-white p-1 rounded-full flex items-center justify-center w-6 h-6">
                    <UserIcon class="w-3 h-3" stroke-width="3" />
                  </div>
                </div>
                <input v-model="form.full_name" type="text" placeholder="Name:" required 
                  class="w-full border border-gray-600 rounded drop-shadow-sm text-sm text-black py-2.5 pl-12 pr-3 focus:outline-none focus:ring-1 focus:ring-purple-600 placeholder-gray-300" />
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-bold text-black mb-1.5 ml-1">Email</label>
              <div class="relative flex items-center">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-black flex items-center justify-center">
                  <div class="bg-black text-white p-1 rounded-full flex items-center justify-center w-6 h-6">
                    <MailIcon class="w-3 h-3" stroke-width="3" />
                  </div>
                </div>
                <input v-model="form.email" type="email" placeholder="Email:" required 
                  class="w-full border border-gray-600 rounded drop-shadow-sm text-sm text-black py-2.5 pl-12 pr-3 focus:outline-none focus:ring-1 focus:ring-purple-600 placeholder-gray-300" />
              </div>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-xs font-bold text-black mb-1.5 ml-1">Password</label>
              <div class="relative flex items-center">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-black flex items-center justify-center w-6 h-6">
                  <LockIcon fill="currentColor" class="w-5 h-5 text-black" />
                </div>
                <input v-model="form.password" type="password" placeholder="Password:" required 
                  class="w-full border border-gray-600 rounded drop-shadow-sm text-sm text-black py-2.5 pl-12 pr-3 focus:outline-none focus:ring-1 focus:ring-purple-600 placeholder-gray-300" />
              </div>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-xs font-bold text-black mb-1.5 ml-1">Confirm Password</label>
              <div class="relative flex items-center">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-black flex items-center justify-center w-6 h-6">
                  <LockIcon fill="currentColor" class="w-5 h-5 text-black" />
                </div>
                <input v-model="form.password_confirmation" type="password" placeholder="Confirm password:" required 
                  class="w-full border border-gray-600 rounded drop-shadow-sm text-sm text-black py-2.5 pl-12 pr-3 focus:outline-none focus:ring-1 focus:ring-purple-600 placeholder-gray-300" />
              </div>
            </div>
          </div>

          <!-- Error -->
          <div v-if="error" class="bg-rose-50 border border-rose-200 text-rose-600 text-sm px-4 py-3 rounded-md">
            {{ error }}
          </div>

          <!-- Success -->
          <div v-if="success" class="bg-emerald-50 border border-emerald-200 text-emerald-600 text-sm px-4 py-3 rounded-md">
            {{ success }}
          </div>

          <div class="flex justify-center pt-6">
            <button type="submit" 
              class="bg-[#8b3f98] hover:bg-[#722f7c] rounded-md text-white font-bold text-xs uppercase px-14 py-3 shadow-md transition-colors tracking-wide" 
              :disabled="loading">
              <span v-if="loading">Creating...</span>
              <span v-else>REGISTER</span>
            </button>
          </div>
        </form>

        <p class="text-center text-sm text-slate-500 mt-10">
          Already have an account?
          <router-link to="/login" class="text-[#8b3f98] font-semibold hover:underline">
            Sign In
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter }     from 'vue-router'
import api               from '../axios'
import { Mail as MailIcon, Lock as LockIcon, User as UserIcon } from 'lucide-vue-next'

const router  = useRouter()
const error   = ref('')
const success = ref('')
const loading = ref(false)
const form    = reactive({
  full_name: '', email: '', password: '', password_confirmation: ''
})

async function handleRegister() {
  error.value   = ''
  success.value = ''
  loading.value = true
  try {
    await api.post('/register', form)
    success.value = 'Account created! Redirecting to login...'
    setTimeout(() => router.push('/login'), 1500)
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join(' ')
    } else {
      error.value = e.response?.data?.message || 'Registration failed.'
    }
  } finally {
    loading.value = false
  }
}
</script>
