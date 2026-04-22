<template>
  <AdminLayout page-title="Activity Logs" page-description="Track all user activities in the system">
    <template #header-action>
      <button @click="fetchLogs" class="btn-secondary flex items-center gap-2">
         <RefreshIcon class="w-4 h-4"/> Refresh
      </button>
    </template>

    <div class="card p-6 overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-slate-200 dark:border-slate-700 text-slate-500 text-sm">
            <th class="py-3 px-4 font-semibold">User</th>
            <th class="py-3 px-4 font-semibold">Action</th>
            <th class="py-3 px-4 font-semibold">Description</th>
            <th class="py-3 px-4 font-semibold text-right">Timestamp</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <tr v-if="loading" class="border-b border-slate-100 dark:border-slate-800">
            <td colspan="4" class="py-8 text-center text-slate-400">Loading logs...</td>
          </tr>
          <tr v-else-if="logs.length === 0" class="border-b border-slate-100 dark:border-slate-800">
            <td colspan="4" class="py-8 text-center text-slate-400">No recent activity.</td>
          </tr>
          <tr v-else v-for="log in logs" :key="log.id" class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
            <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-100">
              {{ log.user?.full_name || 'System / Unknown' }} <br/>
              <span class="text-xs text-slate-400 font-normal">{{ log.user?.role || '' }}</span>
            </td>
            <td class="py-3 px-4 text-purple-600 font-semibold uppercase text-xs">{{ log.action }}</td>
            <td class="py-3 px-4 text-slate-600 dark:text-slate-300">{{ log.description }}</td>
            <td class="py-3 px-4 text-right text-slate-500 dark:text-slate-400">
              {{ new Date(log.created_at).toLocaleString() }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '../../layouts/AdminLayout.vue'
import api from '../../axios'
import { RefreshCcw as RefreshIcon } from 'lucide-vue-next'

const logs    = ref([])
const loading = ref(false)

async function fetchLogs() {
  loading.value = true
  try {
    const res = await api.get('/logs')
    logs.value = res.data
  } catch (e) {
    alert('Failed to fetch activity logs.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchLogs)
</script>