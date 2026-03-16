const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/api'

export async function apiRequest(path, options = {}) {
    const token = localStorage.getItem('token')

    const response = await fetch(`${API_BASE_URL}${path}`, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...(options.headers || {}),
        },
    })

    const data = await response.json().catch(() => null)

    if (!response.ok) {
        throw new Error(data?.message || 'Request failed')
    }

    return data
}