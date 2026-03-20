import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { setAuthData } from '../../api/auth'

export default function LoginForm() {
    const navigate = useNavigate()

    const [formData, setFormData] = useState({
        email: '',
        password: '',
    })

    const [errors, setErrors] = useState({})
    const [serverError, setServerError] = useState('')
    const [isSubmitting, setIsSubmitting] = useState(false)

    const handleChange = (event) => {
        const { name, value } = event.target

        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }))

        setErrors((prev) => ({
            ...prev,
            [name]: '',
        }))

        setServerError('')
    }

    const validate = () => {
        const nextErrors = {}

        if (!formData.email.trim()) {
            nextErrors.email = 'Email is required.'
        }

        if (!formData.password.trim()) {
            nextErrors.password = 'Password is required.'
        }

        return nextErrors
    }

    const handleSubmit = async (event) => {
        event.preventDefault()

        const validationErrors = validate()
        setErrors(validationErrors)

        if (Object.keys(validationErrors).length > 0) {
            return
        }

        setIsSubmitting(true)
        setServerError('')

        try {
            const response = await fetch('http://localhost/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify(formData),
            })

            const result = await response.json()

            if (!response.ok) {
                throw new Error(result.message || 'Login failed.')
            }

            setAuthData(result.data.token, result.data.user)

            navigate('/')
            window.location.reload()
        } catch (error) {
            setServerError(error.message)
        } finally {
            setIsSubmitting(false)
        }
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            {serverError && (
                <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {serverError}
                </div>
            )}

            <div>
                <label
                    htmlFor="login-email"
                    className="mb-1 block text-sm font-medium text-slate-700"
                >
                    Email
                </label>
                <input
                    id="login-email"
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    className="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none transition focus:border-slate-500"
                    placeholder="Enter your email"
                />
                {errors.email && (
                    <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                )}
            </div>

            <div>
                <label
                    htmlFor="login-password"
                    className="mb-1 block text-sm font-medium text-slate-700"
                >
                    Password
                </label>
                <input
                    id="login-password"
                    type="password"
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                    className="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none transition focus:border-slate-500"
                    placeholder="Enter your password"
                />
                {errors.password && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.password}
                    </p>
                )}
            </div>

            <button
                type="submit"
                disabled={isSubmitting}
                className="w-full rounded-lg bg-slate-900 px-4 py-2 font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {isSubmitting ? 'Logging in...' : 'Login'}
            </button>
        </form>
    )
}