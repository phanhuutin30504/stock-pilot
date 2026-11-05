import axios from 'axios'

const baseURL = import.meta.env.VITE_API_BASE_URL

if (!baseURL) {
  console.warn('VITE_API_BASE_URL is not set. API calls will fail until configured.')
}

export const http = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
  },
  timeout: 10000,
})
