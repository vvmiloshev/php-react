import { NavLink, useNavigate } from 'react-router-dom'
import { useEffect, useState } from 'react'

const linkClass = ({ isActive }) =>
    [
        'rounded-md px-3 py-2 text-sm font-medium transition',
        isActive
            ? 'bg-slate-900 text-white'
            : 'text-slate-700 hover:bg-slate-200',
    ].join(' ')

export default function Navigation() {
    const navigate = useNavigate()
    const [isAuthenticated, setIsAuthenticated] = useState(
        Boolean(localStorage.getItem('token'))
    )

    useEffect(() => {
        const syncAuthState = () => {
            setIsAuthenticated(Boolean(localStorage.getItem('token')))
        }

        window.addEventListener('storage', syncAuthState)
        window.addEventListener('auth-changed', syncAuthState)

        return () => {
            window.removeEventListener('storage', syncAuthState)
            window.removeEventListener('auth-changed', syncAuthState)
        }
    }, [])

    const handleLogout = async () => {
        const token = localStorage.getItem('token')

        try {
            await fetch('http://localhost/api/auth/logout', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    ...(token ? { Authorization: `Bearer ${token}` } : {}),
                },
            })
        } catch (error) {
            console.error('Logout request failed:', error)
        }

        localStorage.removeItem('token')
        localStorage.removeItem('user')
        setIsAuthenticated(false)
        window.dispatchEvent(new Event('auth-changed'))
        navigate('/')
    }

    return (
        <nav className="border-b border-slate-200 bg-slate-50">
            <div className="mx-auto flex max-w-6xl flex-wrap gap-2 px-4 py-3">
                <NavLink to="/" className={linkClass}>
                    Home
                </NavLink>

                <NavLink to="/albums" className={linkClass}>
                    Albums
                </NavLink>

                <NavLink to="/poll" className={linkClass}>
                    Poll
                </NavLink>

                {!isAuthenticated ? (
                    <>
                        <NavLink to="/gallery" className={linkClass}>
                            Gallery
                        </NavLink>

                        <NavLink
                            to="/auth"
                            className={({ isActive }) =>
                                [linkClass({ isActive }), 'ml-auto'].join(' ')
                            }
                        >
                            Login / Register
                        </NavLink>
                    </>
                ) : (
                    <>
                        <NavLink to="/polls/manage" className={linkClass}>
                            Manage Polls
                        </NavLink>

                        <button
                            type="button"
                            onClick={handleLogout}
                            className="ml-auto rounded-md px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200"
                        >
                            Logout
                        </button>
                    </>
                )}
            </div>
        </nav>
    )
}