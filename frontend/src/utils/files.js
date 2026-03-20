import { BASE_URL } from './env'

export function getFileUrl(path) {
    if (!path) {
        return ''
    }

    if (path.startsWith('http://') || path.startsWith('https://')) {
        return path
    }

    return `${BASE_URL}${path}`
}