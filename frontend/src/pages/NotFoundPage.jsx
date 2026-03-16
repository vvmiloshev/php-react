import { Link } from 'react-router-dom'

export default function NotFoundPage() {
    return (
        <section className="mx-auto max-w-lg rounded-xl bg-white p-6 text-center shadow-sm">
            <h2 className="text-2xl font-semibold text-slate-900">404</h2>
            <p className="mt-2 text-slate-600">Page not found.</p>

            <Link
                to="/"
                className="mt-4 inline-flex rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
            >
                Back to home
            </Link>
        </section>
    )
}