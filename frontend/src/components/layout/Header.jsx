export default function Header() {
    return (
        <header className="border-b border-slate-200 bg-white">
            <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <div>
                    <h1 className="text-xl font-bold text-slate-900">Photo Social App</h1>
                    <p className="text-sm text-slate-500">
                        PHP backend + React frontend
                    </p>
                </div>
            </div>
        </header>
    )
}