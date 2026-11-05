import { http } from './http'

export type HealthResponse = {
  success: boolean
  application: string
  environment: string
  timestamp: string
  database?: 'ok' | 'error'
}

export async function fetchHealth(): Promise<HealthResponse> {
  const { data } = await http.get<HealthResponse>('/health')
  return data
}
