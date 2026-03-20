import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { getActivePoll, votePoll } from '../api/polls'

export default function PollPage() {
    const [poll, setPoll] = useState(null)
    const [selectedOptionId, setSelectedOptionId] = useState('')
    const [loading, setLoading] = useState(true)
    const [submitting, setSubmitting] = useState(false)
    const [error, setError] = useState('')
    const [successMessage, setSuccessMessage] = useState('')

    const isAuthenticated = useMemo(() => {
        return Boolean(localStorage.getItem('token'))
    }, [])

    useEffect(() => {
        loadActivePoll()
    }, [])

    async function loadActivePoll() {
        try {
            setLoading(true)
            setError('')
            setSuccessMessage('')

            const response = await getActivePoll()
            const pollData = response?.data ?? null

            setPoll(pollData)

            if (pollData?.options?.length) {
                setSelectedOptionId(String(pollData.options[0].id))
            } else {
                setSelectedOptionId('')
            }
        } catch (requestError) {
            setError(requestError.message || 'Failed to load active poll.')
        } finally {
            setLoading(false)
        }
    }

    async function handleSubmit(event) {
        event.preventDefault()

        if (!isAuthenticated) {
            setError('You must be logged in to vote.')
            return
        }

        if (!poll?.id) {
            setError('No active poll found.')
            return
        }

        if (!selectedOptionId) {
            setError('Please select an option.')
            return
        }

        try {
            setSubmitting(true)
            setError('')
            setSuccessMessage('')

            await votePoll(poll.id, Number(selectedOptionId))

            setSuccessMessage('Your vote was submitted successfully.')
        } catch (requestError) {
            setError(requestError.message || 'Failed to submit vote.')
        } finally {
            setSubmitting(false)
        }
    }

    if (loading) {
        return (
            <section className="space-y-4">
                <h1 className="text-2xl font-semibold text-slate-900">Poll</h1>
                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-slate-600">Loading active poll...</p>
                </div>
            </section>
        )
    }

    if (!poll) {
        return (
            <section className="space-y-4">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Poll</h1>
                    <p className="mt-1 text-sm text-slate-600">
                        There is no active poll at the moment.
                    </p>
                </div>

                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-slate-600">
                        No active poll is currently available.
                    </p>

                    {isAuthenticated && (
                        <div className="mt-4">
                            <Link
                                to="/polls/create"
                                className="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                            >
                                Create Poll
                            </Link>
                        </div>
                    )}
                </div>
            </section>
        )
    }

    return (
        <section className="space-y-4">
            <div>
                <h1 className="text-2xl font-semibold text-slate-900">Poll</h1>
                <p className="mt-1 text-sm text-slate-600">
                    Publicly visible active poll. Voting is available only for logged-in users.
                </p>
            </div>

            <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div className="mb-4">
                    <span className="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                        {poll.status}
                    </span>
                </div>

                <h2 className="text-xl font-semibold text-slate-900">
                    {poll.question}
                </h2>

                <form className="mt-6 space-y-4" onSubmit={handleSubmit}>
                    <div className="space-y-3">
                        {poll.options?.map((option) => (
                            <label
                                key={option.id}
                                className="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-slate-300"
                            >
                                <input
                                    type="radio"
                                    name="poll-option"
                                    value={option.id}
                                    checked={String(option.id) === selectedOptionId}
                                    onChange={(event) => setSelectedOptionId(event.target.value)}
                                    disabled={!isAuthenticated || submitting}
                                    className="h-4 w-4"
                                />
                                <span className="text-slate-800">{option.answer_text}</span>
                            </label>
                        ))}
                    </div>

                    {!isAuthenticated && (
                        <div className="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            You need to be logged in to vote.
                        </div>
                    )}

                    {error && (
                        <div className="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {error}
                        </div>
                    )}

                    {successMessage && (
                        <div className="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {successMessage}
                        </div>
                    )}

                    <div className="flex items-center gap-3">
                        <button
                            type="submit"
                            disabled={!isAuthenticated || submitting}
                            className="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {submitting ? 'Submitting...' : 'Vote'}
                        </button>

                        <button
                            type="button"
                            onClick={loadActivePoll}
                            disabled={loading || submitting}
                            className="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Refresh
                        </button>
                    </div>
                </form>
            </div>
        </section>
    )
}