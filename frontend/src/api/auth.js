export function getToken() {
    return localStorage.getItem('token')
}

export function setAuthData(token, user) {
    localStorage.setItem('token', token)
    localStorage.setItem('user', JSON.stringify(user))
}

export function clearAuthData() {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
}

export function getUser() {
    const user = localStorage.getItem('user')

    if (!user) {
        return null
    }

    try {
        return JSON.parse(user)
    } catch (error) {
        return null
    }
}

export function isAuthenticated() {
    return Boolean(getToken())
}

export function getAuthHeaders() {
    const token = getToken()

    return {
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
    }
}

export function getJsonAuthHeaders() {
    return {
        ...getAuthHeaders(),
        'Content-Type': 'application/json',
    }
}