import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'

const PER_PAGE = 12

function AlbumCard({ album }) {
    return (
        <Link
            to={`/albums/${album.id}`}
            className="overflow-hidden rounded-xl bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <div className="aspect-[4/3] bg-slate-200">
                {album.cover_image_url ? (
                    /*<img
                        src={album.cover_image_url}
                        alt={album.title}
                        className="h-full w-full object-cover"
                    />*/
                    <img
                        src={`http://localhost${album.cover_image_url}`}
                        alt={album.title}
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center text-sm text-slate-500">
                        No cover image
                    </div>
                )}
            </div>

            <div className="p-4">
                <h3 className="text-lg font-semibold text-slate-900">
                    {album.title}
                </h3>

                <p className="mt-1 text-sm text-slate-600">
                    {album.photos_count ?? 0} photos
                </p>
            </div>
        </Link>
    )
}

export default function AlbumsPage() {
    const [albums, setAlbums] = useState([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState('')
    const [currentPage, setCurrentPage] = useState(1)

    useEffect(() => {
        const fetchAlbums = async () => {
            try {
                setLoading(true)
                setError('')

                const response = await fetch('http://localhost/api/albums', {
                    headers: {
                        Accept: 'application/json',
                    },
                })

                if (!response.ok) {
                    throw new Error('Failed to load albums.')
                }

                const data = await response.json()

                setAlbums(Array.isArray(data) ? data : data.data || [])
            } catch (err) {
                setError(err.message || 'Something went wrong.')
            } finally {
                setLoading(false)
            }
        }

        fetchAlbums()
    }, [])

    const totalPages = Math.max(1, Math.ceil(albums.length / PER_PAGE))

    const paginatedAlbums = useMemo(() => {
        const start = (currentPage - 1) * PER_PAGE
        const end = start + PER_PAGE

        return albums.slice(start, end)
    }, [albums, currentPage])

    const handlePrevPage = () => {
        setCurrentPage((prev) => Math.max(1, prev - 1))
    }

    const handleNextPage = () => {
        setCurrentPage((prev) => Math.min(totalPages, prev + 1))
    }

    return (
        <section className="space-y-6">
            <div className="rounded-xl bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-semibold text-slate-900">
                            Albums
                        </h2>
                        <p className="mt-2 text-slate-600">
                            Browse photo albums and open any album to view its photos.
                        </p>
                    </div>

                    <Link
                        to="/albums/create"
                        className="inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700"
                    >
                        Create Album
                    </Link>
                </div>
            </div>

            {loading && (
                <div className="rounded-xl bg-white p-6 text-slate-600 shadow-sm">
                    Loading albums...
                </div>
            )}

            {!loading && error && (
                <div className="rounded-xl bg-white p-6 text-red-600 shadow-sm">
                    {error}
                </div>
            )}

            {!loading && !error && paginatedAlbums.length === 0 && (
                <div className="rounded-xl bg-white p-6 text-slate-600 shadow-sm">
                    No albums found.
                </div>
            )}

            {!loading && !error && paginatedAlbums.length > 0 && (
                <>
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                        {paginatedAlbums.map((album) => (
                            <AlbumCard key={album.id} album={album} />
                        ))}
                    </div>

                    <div className="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm">
                        <button
                            type="button"
                            onClick={handlePrevPage}
                            disabled={currentPage === 1}
                            className="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>

                        <span className="text-sm text-slate-600">
                            Page {currentPage} of {totalPages}
                        </span>

                        <button
                            type="button"
                            onClick={handleNextPage}
                            disabled={currentPage === totalPages}
                            className="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </>
            )}
        </section>
    )
}