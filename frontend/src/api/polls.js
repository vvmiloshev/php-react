import { apiRequest } from './client'

export function getActivePoll() {
    return apiRequest('/polls/active')
}

export function getClosedPolls() {
    return apiRequest('/polls/closed')
}

export function getPoll(id) {
    return apiRequest(`/polls/${id}`)
}

export function createPoll(payload) {
    return apiRequest('/polls', {
        method: 'POST',
        body: JSON.stringify(payload),
    })
}

export function activatePoll(id) {
    return apiRequest(`/polls/${id}/activate`, {
        method: 'POST',
    })
}

export function closePoll(id) {
    return apiRequest(`/polls/${id}/close`, {
        method: 'POST',
    })
}

export function votePoll(id, optionId) {
    return apiRequest(`/polls/${id}/vote`, {
        method: 'POST',
        body: JSON.stringify({
            option_id: optionId,
        }),
    })
}

export function getPollResults(id) {
    return apiRequest(`/polls/${id}/results`)
}

export function getPolls() {
    return apiRequest('/polls')
}


export function updatePoll(id, payload) {
    return apiRequest(`/polls/${id}`, {
        method: 'PUT',
        body: JSON.stringify(payload),
    })
}