import { useEffect, useMemo, useState } from 'react'
import { Link, Navigate, useParams } from 'react-router-dom'
import { getPollResults } from '../api/polls'

export default function PollResultsPage() {
    const { id } = useParams()

    const [resultsData, setResultsData] = useState(null)
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState('')

    const isAuthenticated = useMemo(() => {
        return Boolean(localStorage.getItem('token'))
    }, [])

    useEffect(() => {
        if (!isAuthenticated) {
            return
        }

        loadResults()
    }, [id, isAuthenticated])

    async function loadResults() {
        try {
            setLoading(true)
            setError('')

            const response = await getPollResults(id)
            setResultsData(response?.data ?? null)
        } catch (requestError) {
            setError(requestError.message || 'Failed to load poll results.')
        } finally {
            setLoading(false)
        }
    }

    function formatDate(value) {
        if (!value) {
            return '-'
        }

        const date = new Date(value)

        if (Number.isNaN(date.getTime())) {
            return value
        }

        return date.toLocaleString()
    }

    if (!isAuthenticated) {
        return <Navigate to="/auth" replace />
    }

    if (loading) {
        return (
            <section className="space-y-4">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Poll Results</h1>
                        <p className="mt-1 text-sm text-slate-600">
                            Loading poll results...
                        </p>
                    </div>

                    <Link
                        to="/polls/manage"
                        className="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Back to Manage Polls
                    </Link>
                </div>

                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-slate-600">Loading results...</p>
                </div>
            </section>
        )
    }

    if (error) {
        return (
            <section className="space-y-4">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Poll Results</h1>
                        <p className="mt-1 text-sm text-slate-600">
                            Unable to load the selected poll results.
                        </p>
                    </div>

                    <Link
                        to="/polls/manage"
                        className="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Back to Manage Polls
                    </Link>
                </div>

                <div className="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {error}
                </div>
            </section>
        )
    }

    if (!resultsData?.poll) {
        return (
            <section className="space-y-4">
                <div className="flex items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Poll Results</h1>
                        <p className="mt-1 text-sm text-slate-600">
                            No results were found for this poll.
                        </p>
                    </div>

                    <Link
                        to="/polls/manage"
                        className="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Back to Manage Polls
                    </Link>
                </div>

                <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-slate-600">No results available.</p>
                </div>
            </section>
        )
    }

    const { poll, results } = resultsData

    return (
        <section className="space-y-6">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Poll Results</h1>
                    <p className="mt-1 text-sm text-slate-600">
                        Review the final results of the selected closed poll.
                    </p>
                </div>

                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        onClick={loadResults}
                        className="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Refresh
                    </button>

                    <Link
                        to="/polls/manage"
                        className="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Back to Manage Polls
                    </Link>
                </div>
            </div>

            <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex flex-wrap items-center gap-3">
                    <span className="inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-700">
                        {poll.status}
                    </span>

                    <span className="text-sm text-slate-500">
                        Poll ID: {poll.id}
                    </span>
                </div>

                <h2 className="mt-4 text-xl font-semibold text-slate-900">
                    {poll.question}
                </h2>

                <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Total votes
                        </p>
                        <p className="mt-2 text-2xl font-semibold text-slate-900">
                            {poll.total_votes}
                        </p>
                    </div>

                    <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Closed at
                        </p>
                        <p className="mt-2 text-sm font-medium text-slate-900">
                            {formatDate(poll.closed_at)}
                        </p>
                    </div>

                    <div className="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Options
                        </p>
                        <p className="mt-2 text-2xl font-semibold text-slate-900">
                            {results?.length ?? 0}
                        </p>
                    </div>
                </div>
            </div>

            <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">Results breakdown</h3>
                </div>

                {!results?.length ? (
                    <div className="p-6">
                        <p className="text-slate-600">No result rows available.</p>
                    </div>
                ) : (
                    <div className="divide-y divide-slate-200">
                        {results.map((result) => (
                            <div key={result.option_id} className="p-6">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="min-w-0 flex-1">
                                        <p className="text-base font-medium text-slate-900">
                                            {result.answer_text}
                                        </p>

                                        <div className="mt-3 h-3 w-full overflow-hidden rounded-full bg-slate-200">
                                            <div
                                                className="h-full rounded-full bg-slate-900 transition-all"
                                                style={{ width: `${Math.max(Number(result.percentage) || 0, 0)}%` }}
                                            />
                                        </div>
                                    </div>

                                    <div className="flex shrink-0 items-center gap-6 sm:pl-6">
                                        <div className="text-right">
                                            <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                                                Votes
                                            </p>
                                            <p className="mt-1 text-lg font-semibold text-slate-900">
                                                {result.votes}
                                            </p>
                                        </div>

                                        <div className="text-right">
                                            <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                                                Percentage
                                            </p>
                                            <p className="mt-1 text-lg font-semibold text-slate-900">
                                                {Number(result.percentage).toFixed(2)}%
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </section>
    )
}