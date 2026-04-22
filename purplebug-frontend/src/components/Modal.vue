<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="$emit('update:modelValue', false)">

        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition-all duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div v-if="modelValue"
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full border border-slate-200 dark:border-slate-700"
            :class="maxWidthClass">

            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
              <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ title }}</h3>
              <button @click="$emit('update:modelValue', false)"
                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all duration-200">
                <XIcon class="w-5 h-5 text-slate-500" />
              </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto" :style="{ maxHeight: '70vh' }">
              <slot />
            </div>

            <!-- Footer -->
            <div v-if="$slots.footer"
              class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
              <slot name="footer" />
            </div>

          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { X as XIcon } from 'lucide-vue-next'

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  title:      { type: String, default: '' },
  size:       { type: String, default: 'md' }
})

defineEmits(['update:modelValue'])

const maxWidthClass = computed(() => ({
  sm:  'max-w-sm',
  md:  'max-w-lg',
  lg:  'max-w-2xl',
  xl:  'max-w-4xl',
}[props.size] || 'max-w-lg'))
</script>