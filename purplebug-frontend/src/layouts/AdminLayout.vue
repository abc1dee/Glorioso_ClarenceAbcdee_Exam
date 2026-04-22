<template>
  <div class="flex min-h-screen bg-[#e9e4ed]">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white fixed h-full flex flex-col z-30 shadow-md border-r border-[#d4cbd9]">

      <!-- Logo -->
      <div class="p-6 border-b border-[#d4cbd9] mt-2 mb-2 flex justify-center">
        <img src="/logo.png" alt="PurpleBug Logo" class="h-12 w-auto object-contain drop-shadow" />
      </div>

      <!-- Nav Links -->
      <nav class="flex-1 py-4 space-y-0">
        <router-link
          v-for="link in navLinks" :key="link.to"
          :to="link.to"
          class="flex items-center gap-4 px-6 py-4 font-bold text-sm transition-all duration-200"
          :class="isActive(link.to)
            ? 'bg-[#a379b3] text-white'
            : 'text-black hover:bg-slate-50'"
        >
          <component :is="link.icon" class="w-6 h-6" :stroke-width="isActive(link.to) ? 2 : 2.5" />
          {{ link.label }}
        </router-link>
      </nav>

      <!-- Admin profile at bottom -->
      <div class="px-6 py-6 pb-8">
        <!-- Profile Bar -->
        <div class="border-t border-[#d4cbd9] pt-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center border-2 border-purple-200">
              <UserIcon class="w-6 h-6 text-[#8b3f98]" />
            </div>
            <div class="text-sm">
              <span class="text-black">Hi, </span>
              <span class="font-bold text-black">Admin!</span>
            </div>
          </div>
          <!-- Logout -->
          <button @click="handleLogout"
            class="text-red-500 hover:text-red-600 transition-colors p-1" title="Logout">
            <LogOutIcon class="w-6 h-6" stroke-width="2.5" />
          </button>
        </div>

      </div>
    </aside>

    <!-- MAIN CONTENT (offset by sidebar width) -->
    <div class="ml-64 flex-1 flex flex-col min-h-screen">
      <!-- Content -->
      <main class="flex-1 p-10 pt-12">
        <slot />
      </main>
    </div>

  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore }  from '../stores/auth'
import {
  Monitor as PackageIcon,
  ShoppingBag as ShoppingBagIcon,
  Users as UsersIcon,
  ClipboardList as ClipboardListIcon,
  LogOut as LogOutIcon,
  User as UserIcon,
  Image as ImageIcon
} from 'lucide-vue-next'

defineProps({
  pageTitle:       { type: String, default: 'Dashboard' },
  pageDescription: { type: String, default: '' },
})

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

// Updated Icons to match design closer
const navLinks = [
  { to: '/admin/products', label: 'Products Management',   icon: ImageIcon       },
  { to: '/admin/orders',   label: 'Orders',                icon: PackageIcon   },
  { to: '/admin/users',    label: 'Users Management',      icon: UsersIcon         },
  { to: '/admin/logs',     label: 'Activity Logs',         icon: ClipboardListIcon },
]

function isActive(path) {
  return route.path === path
}

async function handleLogout() {
  await auth.logout()
  router.push('/')
}
</script>
