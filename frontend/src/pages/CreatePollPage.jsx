import { useMemo, useState } from 'react'
import { Navigate, useNavigate } from 'react-router-dom'
import { createPoll } from '../api/polls'

export default function CreatePollPage() {
    const navigate = useNavigate()

    const [question, setQuestion] = useState('')
    const [options, setOptions] = useState(['', ''])
    const [submitting, setSubmitting] = useState(false)
    const [error, setError] = useState('')
    const [successMessage, setSuccessMessage] = useState('')

    const isAuthenticated = useMemo(() => {
        return Boolean(localStorage.getItem('token'))
    }, [])

    if (!isAuthenticated) {
        return <Navigate to="/auth" replace />
    }

    function handleOptionChange(index, value) {
        setOptions((currentOptions) =>
            currentOptions.map((option, currentIndex) =>
                currentIndex === index ? value : option
            )
        )
    }

    function handleAddOption() {
        setOptions((currentOptions) => [...currentOptions, ''])
    }

    function handleRemoveOption(index) {
        setOptions((currentOptions) => {
            if (currentOptions.length <= 2) {
                return currentOptions
            }

            return currentOptions.filter((_, currentIndex) => currentIndex !== index)
        })
    }

    function sanitizeOptions(values) {
        return [...new Set(
            values
                .map((value) => value.trim())
                .filter((value) => value !== '')
        )]
    }

    async function handleSubmit(event) {
        event.preventDefault()

        const trimmedQuestion = question.trim()
        const sanitizedOptions = sanitizeOptions(options)

        setError('')
        setSuccessMessage('')

        if (trimmedQuestion === '') {
            setError('Question is required.')
            return
        }

        if (trimmedQuestion.length > 500) {
            setError('Question must not exceed 500 characters.')
            return
        }

        if (sanitizedOptions.length < 2) {
            setError('At least 2 non-empty unique options are required.')
            return
        }

        const tooLongOption = sanitizedOptions.find((option) => option.length > 255)

        if (tooLongOption) {
            setError('Each option must not exceed 255 characters.')
            return
        }

        try {
            setSubmitting(true)

            await createPoll({
                question: trimmedQuestion,
                options: sanitizedOptions,
            })

            setSuccessMessage('Poll created successfully.')

            navigate('/polls/manage')
        } catch (requestError) {
            setError(requestError.message || 'Failed to create poll.')
        } finally {
            setSubmitting(false)
        }
    }

    return (
        <section className="space-y-6">
            <div>
                <h1 className="text-2xl font-semibold text-slate-900">Create Poll</h1>
                <p className="mt-1 text-sm text-slate-600">
                    Create a new poll. It will be saved as inactive until activated.
                </p>
            </div>

            <div className="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <form className="space-y-6" onSubmit={handleSubmit}>
                    <div className="space-y-2">
                        <label
                            htmlFor="question"
                            className="block text-sm font-medium text-slate-700"
                        >
                            Question
                        </label>
                        <input
                            id="question"
                            type="text"
                            value={question}
                            onChange={(event) => setQuestion(event.target.value)}
                            placeholder="Enter your poll question"
                            maxLength={500}
                            disabled={submitting}
                            className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
                        />
                    </div>

                    <div className="space-y-3">
                        <div className="flex items-center justify-between gap-3">
                            <h2 className="text-sm font-medium text-slate-700">Options</h2>
                            <button
                                type="button"
                                onClick={handleAddOption}
                                disabled={submitting}
                                className="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Add Option
                            </button>
                        </div>

                        <div className="space-y-3">
                            {options.map((option, index) => (
                                <div key={index} className="flex items-center gap-3">
                                    <input
                                        type="text"
                                        value={option}
                                        onChange={(event) => handleOptionChange(index, event.target.value)}
                                        placeholder={`Option ${index + 1}`}
                                        maxLength={255}
                                        disabled={submitting}
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-slate-900 outline-none transition focus:border-slate-500"
                                    />

                                    <button
                                        type="button"
                                        onClick={() => handleRemoveOption(index)}
                                        disabled={submitting || options.length <= 2}
                                        className="inline-flex items-center rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        Remove
                                    </button>
                                </div>
                            ))}
                        </div>

                        <p className="text-xs text-slate-500">
                            At least 2 options are required.
                        </p>
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

                    <div className="flex items-center gap-3">
                        <button
                            type="submit"
                            disabled={submitting}
                            className="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {submitting ? 'Creating...' : 'Create Poll'}
                        </button>

                        <button
                            type="button"
                            onClick={() => navigate('/poll')}
                            disabled={submitting}
                            className="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </section>
    )
}