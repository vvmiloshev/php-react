import { useEffect, useMemo, useState } from 'react'
import { Navigate, Link ,useNavigate} from 'react-router-dom'
import { activatePoll, closePoll, getPolls } from '../api/polls'

export default function ManagePollsPage() {
    const navigate = useNavigate()
    const [polls, setPolls] = useState([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState('')
    const [successMessage, setSuccessMessage] = useState('')
    const [processingPollId, setProcessingPollId] = useState(null)

    const isAuthenticated = useMemo(() => {
        return Boolean(localStorage.getItem('token'))
    }, [])

    useEffect(() => {
        if (!isAuthenticated) {
            return
        }

        loadPolls()
    }, [isAuthenticated])

    async function loadPolls() {
        try {
            setLoading(true)
            setError('')
            setSuccessMessage('')

            const response = await getPolls()
            setPolls(response?.data ?? [])
        } catch (requestError) {
            setError(requestError.message || 'Failed to load polls.')
        } finally {
            setLoading(false)
        }
    }

    async function handleActivate(pollId) {
        try {
            setProcessingPollId(pollId)
            setError('')
            setSuccessMessage('')

            await activatePoll(pollId)
            setSuccessMessage('Poll activated successfully.')
            await loadPolls()
        } catch (requestError) {
            setError(requestError.message || 'Failed to activate poll.')
        } finally {
            setProcessingPollId(null)
        }
    }

    async function handleClose(pollId) {
        try {
            setProcessingPollId(pollId)
            setError('')
            setSuccessMessage('')

            await closePoll(pollId)
            setSuccessMessage('Poll closed successfully.')
            await loadPolls()
        } catch (requestError) {
            setError(requestError.message || 'Failed to close poll.')
        } finally {
            setProcessingPollId(null)
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

    function statusBadgeClass(status) {
        switch (status) {
            case 'active':
                return 'bg-emerald-100 text-emerald-700'
            case 'inactive':
                return 'bg-amber-100 text-amber-700'
            case 'closed':
                return 'bg-slate-200 text-slate-700'
            default:
                return 'bg-slate-100 text-slate-700'
        }
    }

    if (!isAuthenticated) {
        return <Navigate to="/auth" replace />
    }

    return (
        <section className="space-y-6">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Manage Polls</h1>
                    <p className="mt-1 text-sm text-slate-600">
                        Create, activate and close polls.
                    </p>
                </div>

                <Link
                    to="/polls/create"
                    className="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                >
                    Create Poll
                </Link>
            </div>

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

            <div className="rounded-lg border border-slate-200 bg-white shadow-sm">
                {loading ? (
                    <div className="p-6">
                        <p className="text-slate-600">Loading polls...</p>
                    </div>
                ) : polls.length === 0 ? (
                    <div className="p-6">
                        <p className="text-slate-600">No polls found.</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    ID
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Question
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Created
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Activated
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Closed
                                </th>
                                <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">
                                    Actions
                                </th>
                            </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-200">
                            {polls.map((poll) => {
                                const isProcessing = processingPollId === poll.id

                                return (
                                    <tr key={poll.id} className="align-top">
                                        <td className="px-4 py-4 text-sm text-slate-700">
                                            {poll.id}
                                        </td>

                                        <td className="px-4 py-4">
                                            <div className="max-w-xl">
                                                <p className="text-sm font-medium text-slate-900">
                                                    {poll.question}
                                                </p>
                                            </div>
                                        </td>

                                        <td className="px-4 py-4">
                                                <span
                                                    className={[
                                                        'inline-flex rounded-full px-3 py-1 text-xs font-medium',
                                                        statusBadgeClass(poll.status),
                                                    ].join(' ')}
                                                >
                                                    {poll.status}
                                                </span>
                                        </td>

                                        <td className="px-4 py-4 text-sm text-slate-600">
                                            {formatDate(poll.created_at)}
                                        </td>

                                        <td className="px-4 py-4 text-sm text-slate-600">
                                            {formatDate(poll.activated_at)}
                                        </td>

                                        <td className="px-4 py-4 text-sm text-slate-600">
                                            {formatDate(poll.closed_at)}
                                        </td>

                                        <td className="px-6 py-4">
                                            <div className="flex justify-end gap-3">
                                                {poll.status === 'inactive' && (
                                                    <>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleActivate(poll.id)}
                                                            className="rounded-xl bg-emerald-500 px-4 py-2 font-semibold text-white transition hover:bg-emerald-600"
                                                        >
                                                            Activate
                                                        </button>
                                                    </>
                                                )}
                                                {poll.status !== 'closed' && (
                                                    <>
                                                        <button
                                                            type="button"
                                                            onClick={() => navigate(`/manage-polls/${poll.id}/edit`)}
                                                            className="rounded-xl border border-slate-300 px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-100"
                                                        >
                                                            Edit
                                                        </button>
                                                    </>
                                                )}

                                                {poll.status === 'active' && (
                                                    <button
                                                        type="button"
                                                        onClick={() => handleClose(poll.id)}
                                                        className="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-700"
                                                    >
                                                        Close
                                                    </button>
                                                )}

                                                <button
                                                    type="button"
                                                    onClick={() => navigate(`/polls/${poll.id}/results`)}
                                                    className="rounded-xl border border-slate-300 px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-100"
                                                >
                                                    Results
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                )
                            })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </section>
    )
}