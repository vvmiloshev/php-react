import { useState } from 'react'
import { useNavigate } from 'react-router-dom'

const getPasswordChecks = (password) => ({
    minLength: password.length >= 6,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password),
    special: /[^A-Za-z0-9]/.test(password),
})

export default function RegisterForm() {
    const navigate = useNavigate()

    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        passwordConfirmation: '',
    })

    const [errors, setErrors] = useState({})
    const [serverError, setServerError] = useState('')
    const [successMessage, setSuccessMessage] = useState('')
    const [isSubmitting, setIsSubmitting] = useState(false)
    const [touched, setTouched] = useState({
        password: false,
        passwordConfirmation: false,
    })

    const passwordChecks = getPasswordChecks(formData.password)
    const allPasswordChecksPassed = Object.values(passwordChecks).every(Boolean)
    const passwordsMatch =
        formData.passwordConfirmation.length > 0 &&
        formData.password === formData.passwordConfirmation

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
        setSuccessMessage('')
    }

    const handleBlur = (event) => {
        const { name } = event.target

        if (name === 'password' || name === 'passwordConfirmation') {
            setTouched((prev) => ({
                ...prev,
                [name]: true,
            }))
        }
    }

    const validate = () => {
        const nextErrors = {}

        if (!formData.name.trim()) {
            nextErrors.name = 'Name is required.'
        }

        if (!formData.email.trim()) {
            nextErrors.email = 'Email is required.'
        }

        if (!formData.password.trim()) {
            nextErrors.password = 'Password is required.'
        } else if (!allPasswordChecksPassed) {
            nextErrors.password = 'Password does not meet all requirements.'
        }

        if (!formData.passwordConfirmation.trim()) {
            nextErrors.passwordConfirmation =
                'Password confirmation is required.'
        } else if (formData.password !== formData.passwordConfirmation) {
            nextErrors.passwordConfirmation = 'Passwords do not match.'
        }

        return nextErrors
    }

    const handleSubmit = async (event) => {
        event.preventDefault()

        setTouched({
            password: true,
            passwordConfirmation: true,
        })

        const validationErrors = validate()
        setErrors(validationErrors)

        if (Object.keys(validationErrors).length > 0) {
            return
        }

        setIsSubmitting(true)
        setServerError('')
        setSuccessMessage('')

        try {
            const response = await fetch('http://localhost/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: formData.name,
                    email: formData.email,
                    password: formData.password,
                    password_confirmation: formData.passwordConfirmation,
                }),
            })

            const data = await response.json()

            if (!response.ok) {
                throw new Error(data.message || 'Registration failed.')
            }

            setSuccessMessage('Registration successful. You can now log in.')

            setFormData({
                name: '',
                email: '',
                password: '',
                passwordConfirmation: '',
            })

            setTouched({
                password: false,
                passwordConfirmation: false,
            })

            setTimeout(() => {
                navigate('/auth?tab=login')
            }, 3000)
        } catch (error) {
            setServerError(error.message)
        } finally {
            setIsSubmitting(false)
        }
    }

    const getRuleClassName = (isValid) => {
        if (isValid) {
            return 'text-green-600'
        }

        if (touched.password) {
            return 'text-red-600'
        }

        return 'text-slate-500'
    }

    const getConfirmClassName = () => {
        if (!formData.passwordConfirmation) {
            return touched.passwordConfirmation ? 'text-red-600' : 'text-slate-500'
        }

        return passwordsMatch ? 'text-green-600' : 'text-red-600'
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            {serverError && (
                <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {serverError}
                </div>
            )}

            {successMessage && (
                <div className="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {successMessage}
                </div>
            )}

            <div>
                <label
                    htmlFor="register-name"
                    className="mb-1 block text-sm font-medium text-slate-700"
                >
                    Name
                </label>
                <input
                    id="register-name"
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    className="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none transition focus:border-slate-500"
                    placeholder="Enter your name"
                />
                {errors.name && (
                    <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                )}
            </div>

            <div>
                <label
                    htmlFor="register-email"
                    className="mb-1 block text-sm font-medium text-slate-700"
                >
                    Email
                </label>
                <input
                    id="register-email"
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
                    htmlFor="register-password"
                    className="mb-1 block text-sm font-medium text-slate-700"
                >
                    Password
                </label>
                <input
                    id="register-password"
                    type="password"
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    className="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none transition focus:border-slate-500"
                    placeholder="Enter your password"
                />
                {errors.password && (
                    <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                )}

                <ul className="mt-2 space-y-1 text-xs">
                    <li className={getRuleClassName(passwordChecks.minLength)}>
                        At least 6 characters
                    </li>
                    <li className={getRuleClassName(passwordChecks.uppercase)}>
                        At least 1 uppercase letter
                    </li>
                    <li className={getRuleClassName(passwordChecks.lowercase)}>
                        At least 1 lowercase letter
                    </li>
                    <li className={getRuleClassName(passwordChecks.number)}>
                        At least 1 number
                    </li>
                    <li className={getRuleClassName(passwordChecks.special)}>
                        At least 1 special character
                    </li>
                </ul>
            </div>

            <div>
                <label
                    htmlFor="register-passwordConfirmation"
                    className="mb-1 block text-sm font-medium text-slate-700"
                >
                    Confirm password
                </label>
                <input
                    id="register-passwordConfirmation"
                    type="password"
                    name="passwordConfirmation"
                    value={formData.passwordConfirmation}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    className="w-full rounded-lg border border-slate-300 px-3 py-2 outline-none transition focus:border-slate-500"
                    placeholder="Confirm your password"
                />
                {errors.passwordConfirmation && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.passwordConfirmation}
                    </p>
                )}

                <p className={`mt-2 text-xs ${getConfirmClassName()}`}>
                    Passwords must match
                </p>
            </div>

            <button
                type="submit"
                disabled={isSubmitting}
                className="w-full rounded-lg bg-slate-900 px-4 py-2 font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {isSubmitting ? 'Registering...' : 'Register'}
            </button>
        </form>
    )
}