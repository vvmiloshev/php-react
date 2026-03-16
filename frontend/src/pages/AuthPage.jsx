import { useSearchParams } from 'react-router-dom'
import LoginForm from '../components/auth/LoginForm'
import RegisterForm from '../components/auth/RegisterForm'

export default function AuthPage() {
    const [searchParams, setSearchParams] = useSearchParams()

    const activeTab =
        searchParams.get('tab') === 'register' ? 'register' : 'login'

    const switchTab = (tab) => {
        setSearchParams({ tab })
    }

    return (
        <div className="mx-auto max-w-6xl px-4 py-10">
            <div className="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h1 className="mb-6 text-center text-2xl font-bold text-slate-900">
                    Welcome
                </h1>

                <div className="mb-6 grid grid-cols-2 rounded-lg bg-slate-100 p-1">
                    <button
                        type="button"
                        onClick={() => switchTab('login')}
                        className={`rounded-md px-4 py-2 text-sm font-medium transition ${
                            activeTab === 'login'
                                ? 'bg-slate-900 text-white'
                                : 'text-slate-700 hover:bg-slate-200'
                        }`}
                    >
                        Login
                    </button>

                    <button
                        type="button"
                        onClick={() => switchTab('register')}
                        className={`rounded-md px-4 py-2 text-sm font-medium transition ${
                            activeTab === 'register'
                                ? 'bg-slate-900 text-white'
                                : 'text-slate-700 hover:bg-slate-200'
                        }`}
                    >
                        Register
                    </button>
                </div>

                {activeTab === 'login' ? <LoginForm /> : <RegisterForm />}
            </div>
        </div>
    )
}