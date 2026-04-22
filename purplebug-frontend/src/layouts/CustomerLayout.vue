<template>
  <div class="min-h-screen bg-white flex flex-col">

    <!-- TOP NAVBAR -->
    <header class="sticky top-0 z-40 bg-white border-b border-gray-200">
      <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-[90px]">

          <!-- Logo -->
          <div class="flex items-center cursor-pointer" @click="router.push('/')">
            <img src="/logo.png" alt="PurpleBug Logo" class="h-[45px] w-auto object-contain" />
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-8">

            <!-- Profile Info -->
            <div class="flex items-center gap-3">
              <div class="w-11 h-11 rounded-full overflow-hidden border border-gray-100 flex-shrink-0 flex items-center justify-center bg-purple-100">
                <UserIcon class="w-6 h-6 text-[#8b3f98]" />
              </div>
              <div class="flex flex-col">
                <span class="text-[15px] font-bold text-gray-800 leading-tight">
                  Hi, {{ auth.isLoggedIn ? (auth.user?.full_name?.split(' ')[0] || 'User') : 'Guest' }}!
                </span>
                <span class="text-[13px] text-gray-400 font-medium leading-tight">
                  Description
                </span>
              </div>
            </div>

            <!-- My Orders Icon (logged-in users only) -->
            <button v-if="auth.isLoggedIn" @click="handleOrdersClick" class="relative text-[#8b3f98] hover:text-purple-900 transition-colors" title="My Orders">
              <ClipboardListIcon class="w-8 h-8" stroke-width="2" />
            </button>

            <!-- Cart Icon and Action -->
            <button @click="handleCartClick" class="relative text-[#8b3f98] hover:text-purple-900 transition-colors">
              <ShoppingCartIcon class="w-9 h-9" stroke-width="2.5" />
              <span v-if="cart.itemCount > 0"
                class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-bold">
                {{ cart.itemCount }}
              </span>
            </button>

            <!-- Guest Auth Buttons -->
            <div v-if="!auth.isLoggedIn" class="flex items-center gap-3 ml-2">
              <button @click="router.push('/login')" class="flex items-center gap-2 bg-[#8b3f98] hover:bg-[#72337d] text-white text-xs font-bold px-6 py-2.5 rounded shadow-md transition-colors uppercase tracking-wider">
                <LockIcon class="w-4 h-4" /> LOGIN
              </button>
              <button @click="router.push('/register')" class="flex items-center gap-2 bg-[#65558f] hover:bg-[#524479] text-white text-xs font-bold px-6 py-2.5 rounded shadow-md transition-colors uppercase tracking-wider">
                <PenToolIcon class="w-4 h-4" /> SIGN UP
              </button>
            </div>

            <!-- Logged In Action: Logout -->
            <div v-else class="flex items-center ml-2">
              <button @click="handleLogout"
                class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 text-white text-xs font-bold px-5 py-2.5 rounded shadow-md transition-colors uppercase tracking-wider" title="Logout">
                <LogOutIcon class="w-4 h-4" /> LOGOUT
              </button>
            </div>

          </div>
        </div>
      </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="flex-1 w-full max-w-[1400px] mx-auto px-6 lg:px-8 py-10">
      <slot />
    </main>

  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { useCartStore }  from '../stores/cart'
import { useRouter }     from 'vue-router'
import {
  ShoppingCart as ShoppingCartIcon,
  User as UserIcon,
  LogOut as LogOutIcon,
  LockKeyhole as LockIcon,
  PenLine as PenToolIcon,
  ClipboardList as ClipboardListIcon
} from 'lucide-vue-next'

const emit = defineEmits(['open-cart', 'open-orders'])

const auth   = useAuthStore()
const cart   = useCartStore()
const router = useRouter()

function handleCartClick() {
  if (!auth.isLoggedIn) {
    alert("Please log in to view your cart.");
    router.push('/login');
    return;
  }
  emit('open-cart');
}

function handleOrdersClick() {
  emit('open-orders');
}

async function handleLogout() {
  await auth.logout()
  router.push('/')
}
</script>
