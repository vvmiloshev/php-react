import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { apiRequest } from '../api/client'
import { getFileUrl } from '../utils/files'

export default function AlbumDetailsPage() {
    const { id } = useParams()

    const [album, setAlbum] = useState(null)
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState('')
    const [activePhotoIndex, setActivePhotoIndex] = useState(null)

    const currentUser = JSON.parse(localStorage.getItem('user') || 'null')
    const isOwner =
        currentUser &&
        album &&
        Number(currentUser.id || currentUser.user_id) === Number(album.user_id)

    useEffect(() => {
        const fetchAlbum = async () => {
            try {
                setLoading(true)
                setError('')

                const response = await apiRequest(`/albums/${id}`)
                setAlbum(response.data)
            } catch (err) {
                setError(err.message || 'Failed to load album details.')
            } finally {
                setLoading(false)
            }
        }

        fetchAlbum()
    }, [id])

    const photos = useMemo(() => album?.photos ?? [], [album])

    const activePhoto =
        activePhotoIndex !== null && photos[activePhotoIndex]
            ? photos[activePhotoIndex]
            : null

    const openModal = (index) => {
        setActivePhotoIndex(index)
    }

    const closeModal = () => {
        setActivePhotoIndex(null)
    }

    const showPreviousPhoto = () => {
        if (!photos.length || activePhotoIndex === null) {
            return
        }

        setActivePhotoIndex((prevIndex) =>
            prevIndex === 0 ? photos.length - 1 : prevIndex - 1
        )
    }

    const showNextPhoto = () => {
        if (!photos.length || activePhotoIndex === null) {
            return
        }

        setActivePhotoIndex((prevIndex) =>
            prevIndex === photos.length - 1 ? 0 : prevIndex + 1
        )
    }

    useEffect(() => {
        if (activePhotoIndex === null) {
            return
        }

        const handleKeyDown = (event) => {
            if (event.key === 'Escape') {
                closeModal()
            }

            if (event.key === 'ArrowLeft') {
                showPreviousPhoto()
            }

            if (event.key === 'ArrowRight') {
                showNextPhoto()
            }
        }

        window.addEventListener('keydown', handleKeyDown)

        return () => {
            window.removeEventListener('keydown', handleKeyDown)
        }
    }, [activePhotoIndex, photos.length])

    if (loading) {
        return (
            <section className="rounded-xl bg-white p-6 shadow-sm">
                <p className="text-slate-600">Loading album...</p>
            </section>
        )
    }

    if (error) {
        return (
            <section className="rounded-xl bg-white p-6 shadow-sm">
                <p className="text-red-600">{error}</p>
            </section>
        )
    }

    if (!album) {
        return (
            <section className="rounded-xl bg-white p-6 shadow-sm">
                <p className="text-slate-600">Album not found.</p>
            </section>
        )
    }

    return (
        <>
            <section className="space-y-6">
                <div className="rounded-xl bg-white p-6 shadow-sm">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <h2 className="text-2xl font-semibold text-slate-900">
                                {album.title}
                            </h2>

                            {album.description && (
                                <p className="mt-2 text-slate-600">
                                    {album.description}
                                </p>
                            )}

                            <p className="mt-3 text-sm text-slate-500">
                                Photos: {photos.length}
                            </p>
                        </div>

                        <div className="flex gap-2">
                            {isOwner && (
                                <Link
                                    to={`/albums/${album.id}/edit`}
                                    className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
                                >
                                    Edit album
                                </Link>
                            )}

                            <Link
                                to="/albums"
                                className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                Back to albums
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="rounded-xl bg-white p-6 shadow-sm">
                    {photos.length === 0 ? (
                        <p className="text-slate-600">This album has no photos yet.</p>
                    ) : (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {photos.map((photo, index) => (
                                <button
                                    key={photo.id}
                                    type="button"
                                    onClick={() => openModal(index)}
                                    className="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 text-left transition hover:-translate-y-1 hover:shadow-md"
                                >
                                    <div className="aspect-square overflow-hidden bg-slate-100">
                                        <img
                                            src={getFileUrl(photo.image_url)}
                                            alt={photo.title || 'Album photo'}
                                            className="h-full w-full object-cover transition hover:scale-105"
                                        />
                                    </div>

                                    <div className="space-y-1 p-3">
                                        <h3 className="text-sm font-semibold text-slate-900">
                                            {photo.title || `Photo #${photo.id}`}
                                        </h3>

                                        {photo.description && (
                                            <p className="line-clamp-2 text-sm text-slate-600">
                                                {photo.description}
                                            </p>
                                        )}
                                    </div>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            </section>

            {activePhoto && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                    onClick={closeModal}
                >
                    <div
                        className="relative w-full max-w-6xl"
                        onClick={(event) => event.stopPropagation()}
                    >
                        <button
                            type="button"
                            onClick={closeModal}
                            className="absolute right-2 top-2 z-10 rounded-full bg-white/90 px-3 py-2 text-sm font-semibold text-slate-900 shadow hover:bg-white"
                        >
                            Close
                        </button>

                        {photos.length > 1 && (
                            <>
                                <button
                                    type="button"
                                    onClick={showPreviousPhoto}
                                    className="absolute left-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 px-4 py-3 text-lg font-bold text-slate-900 shadow hover:bg-white"
                                >
                                    ←
                                </button>

                                <button
                                    type="button"
                                    onClick={showNextPhoto}
                                    className="absolute right-2 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white/90 px-4 py-3 text-lg font-bold text-slate-900 shadow hover:bg-white"
                                >
                                    →
                                </button>
                            </>
                        )}

                        <div className="overflow-hidden rounded-2xl bg-white shadow-2xl">
                            <div className="flex max-h-[80vh] items-center justify-center bg-slate-950">
                                <img
                                    src={getFileUrl(activePhoto.image_url)}
                                    alt={activePhoto.title || 'Album photo'}
                                    className="max-h-[80vh] w-auto max-w-full object-contain"
                                />
                            </div>

                            <div className="space-y-2 p-4">
                                <div className="flex items-center justify-between gap-4">
                                    <h3 className="text-lg font-semibold text-slate-900">
                                        {activePhoto.title || `Photo #${activePhoto.id}`}
                                    </h3>

                                    <span className="text-sm text-slate-500">
                                        {activePhotoIndex + 1} / {photos.length}
                                    </span>
                                </div>

                                {activePhoto.description && (
                                    <p className="text-sm text-slate-600">
                                        {activePhoto.description}
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </>
    )
}