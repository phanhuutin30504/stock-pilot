<script setup lang="ts">
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useHealthStore } from '../../stores/health'

const health = useHealthStore()
const { status, payload, errorMessage, isConnected } = storeToRefs(health)

onMounted(() => {
  void health.check()
})
</script>

<template>
  <main class="page">
    <header class="hero">
      <p class="eyebrow">StockPilot · Sprint 1</p>
      <h1>Trading Assistant nền tảng</h1>
      <p class="lede">
        Kiểm tra kết nối frontend Vue 3 với Laravel API health endpoint.
      </p>
    </header>

    <section class="status-panel" aria-live="polite">
      <h2>Trạng thái backend</h2>

      <p class="badge" :data-state="status">
        <template v-if="status === 'checking'">Đang kiểm tra…</template>
        <template v-else-if="isConnected">Đã kết nối</template>
        <template v-else-if="status === 'disconnected'">Không kết nối được</template>
        <template v-else>Chưa kiểm tra</template>
      </p>

      <dl v-if="payload" class="meta">
        <div>
          <dt>Application</dt>
          <dd>{{ payload.application }}</dd>
        </div>
        <div>
          <dt>Environment</dt>
          <dd>{{ payload.environment }}</dd>
        </div>
        <div>
          <dt>Database</dt>
          <dd>{{ payload.database }}</dd>
        </div>
        <div>
          <dt>Timestamp</dt>
          <dd>{{ payload.timestamp }}</dd>
        </div>
      </dl>

      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <button type="button" class="retry" :disabled="status === 'checking'" @click="health.check()">
        Kiểm tra lại
      </button>
    </section>
  </main>
</template>

<style scoped>
.page {
  max-width: 42rem;
  margin: 0 auto;
  padding: 3rem 1.25rem 4rem;
  color: #12201a;
}

.hero h1 {
  font-size: clamp(1.8rem, 4vw, 2.4rem);
  margin: 0.35rem 0 0.75rem;
  letter-spacing: -0.02em;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.75rem;
  color: #3d6b55;
  margin: 0;
}

.lede {
  margin: 0;
  color: #3a4a42;
  line-height: 1.5;
}

.status-panel {
  margin-top: 2rem;
  padding: 1.25rem 0;
  border-top: 1px solid #d5e0d9;
}

.status-panel h2 {
  margin: 0 0 0.75rem;
  font-size: 1.1rem;
}

.badge {
  display: inline-block;
  margin: 0 0 1rem;
  font-weight: 600;
}

.badge[data-state='connected'] {
  color: #0f7a45;
}

.badge[data-state='disconnected'] {
  color: #a12828;
}

.badge[data-state='checking'] {
  color: #6a5a16;
}

.meta {
  display: grid;
  gap: 0.65rem;
  margin: 0 0 1rem;
}

.meta div {
  display: grid;
  grid-template-columns: 8rem 1fr;
  gap: 0.5rem;
}

.meta dt {
  color: #5a6b62;
}

.meta dd {
  margin: 0;
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
  font-size: 0.92rem;
}

.error {
  color: #a12828;
  margin: 0 0 1rem;
}

.retry {
  border: 1px solid #2f5b45;
  background: #2f5b45;
  color: #fff;
  padding: 0.55rem 1rem;
  cursor: pointer;
}

.retry:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
