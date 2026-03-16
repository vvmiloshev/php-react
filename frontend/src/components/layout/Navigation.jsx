import { NavLink } from 'react-router-dom'

const linkClass = ({ isActive }) =>
    [
        'rounded-md px-3 py-2 text-sm font-medium transition',
        isActive
            ? 'bg-slate-900 text-white'
            : 'text-slate-700 hover:bg-slate-200',
    ].join(' ')

export default function Navigation({ isAuthenticated }) {

    const handleLogout = async () => {
        const token = localStorage.getItem('token')

        try {
            await fetch('http://localhost/api/logout', {
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
        window.location.href = '/'
    }

    return (
        <nav className="border-b border-slate-200 bg-slate-50">
            <div className="mx-auto flex max-w-6xl flex-wrap gap-2 px-4 py-3">
                <NavLink to="/" className={linkClass}>
                    Home
                </NavLink>

                {!isAuthenticated && (
                    <>
                        <NavLink to="/auth"  className={({ isActive }) =>
                                [
                                    linkClass({ isActive }),
                                    'ml-auto',
                                ].join(' ')
                            }>
                            Login / Register
                        </NavLink>
                    </>
                )}
                {isAuthenticated && (
                    <>
                        <NavLink to="/feed" className={linkClass}>
                            Feed
                        </NavLink>

                        <NavLink to="/photos" className={linkClass}>
                            Photos
                        </NavLink>

                        <NavLink to="/profile" className={linkClass}>
                            Profile
                        </NavLink>
                        <button
                            type="button"
                            onClick={handleLogout}
                            className={'ml-auto rounded-md px-3 py-2 text-sm font-medium transition text-slate-700 hover:bg-slate-200'}
                        >
                            Logout
                        </button>
                    </>
                )}



            </div>
        </nav>
    )
}