import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import { fetchHealth, type HealthResponse } from '../api/health'
import type { BackendConnectionStatus } from '../types/health'

export const useHealthStore = defineStore('health', () => {
  const status = ref<BackendConnectionStatus>('idle')
  const payload = ref<HealthResponse | null>(null)
  const errorMessage = ref<string | null>(null)

  const isConnected = computed(() => status.value === 'connected')

  async function check(): Promise<void> {
    status.value = 'checking'
    errorMessage.value = null

    try {
      const data = await fetchHealth()
      payload.value = data
      status.value = data.success ? 'connected' : 'disconnected'
      if (!data.success) {
        errorMessage.value = 'Backend trả về trạng thái không sẵn sàng.'
      }
    } catch (error) {
      payload.value = null
      status.value = 'disconnected'
      errorMessage.value =
        error instanceof Error ? error.message : 'Không kết nối được tới backend.'
    }
  }

  return {
    status,
    payload,
    errorMessage,
    isConnected,
    check,
  }
})
