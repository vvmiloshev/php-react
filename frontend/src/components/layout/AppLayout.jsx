import { Outlet } from 'react-router-dom'
import Header from './Header'
import Navigation from './Navigation'

export default function AppLayout() {

    const isAuthenticated = Boolean(localStorage.getItem('token'))

    return (
        <div className="min-h-screen bg-slate-100 text-slate-900">
            <Header />
            <Navigation isAuthenticated={isAuthenticated} />

            <main className="mx-auto max-w-6xl px-4 py-6">
                <Outlet />
            </main>
        </div>
    )
}