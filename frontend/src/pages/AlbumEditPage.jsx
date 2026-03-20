import { useEffect, useMemo, useRef, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { getJsonAuthHeaders } from '../api/auth'
import { getFileUrl } from '../utils/files'
import { API_BASE_URL } from '../utils/env'

export default function AlbumEditPage() {
    const navigate = useNavigate()
    const { id } = useParams()

    const [albumId, setAlbumId] = useState(null)
    const [title, setTitle] = useState('')
    const [photos, setPhotos] = useState([])
    const [isLoading, setIsLoading] = useState(true)
    const [isSaving, setIsSaving] = useState(false)
    const [isUploading, setIsUploading] = useState(false)
    const [isDeletingAlbum, setIsDeletingAlbum] = useState(false)
    const [error, setError] = useState('')
    const [successMessage, setSuccessMessage] = useState('')
    const [activePhotoIndex, setActivePhotoIndex] = useState(null)

    const saveTimeoutRef = useRef(null)
    const fileInputRef = useRef(null)

    const token = localStorage.getItem('token')

    const authHeaders = {
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
    }

    const showSuccess = (message) => {
        setSuccessMessage(message)

        setTimeout(() => {
            setSuccessMessage('')
        }, 2000)
    }

    const loadAlbum = async () => {
        try {
            setIsLoading(true)
            setError('')

            const response = await fetch(`${API_BASE_URL}/albums/${id}`, {
                headers: authHeaders,
            })

            const data = await response.json().catch(() => null)

            if (!response.ok) {
                throw new Error(data?.message || 'Failed to load album.')
            }

            const album = data?.data ?? data

            setAlbumId(album.id)
            setTitle(album.title || '')
            setPhotos(album.photos || [])
        } catch (err) {
            setError(err.message || 'Failed to load album.')
        } finally {
            setIsLoading(false)
        }
    }

    const updateAlbum = async (currentAlbumId, albumTitle) => {
        const response = await fetch(`${API_BASE_URL}/albums/${currentAlbumId}`, {
            method: 'PUT',
            headers: {
                ...authHeaders,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title: albumTitle,
            }),
        })

        const data = await response.json().catch(() => null)

        if (!response.ok) {
            const validationErrors = data?.errors
                ? Object.values(data.errors).flat().join(' ')
                : ''

            throw new Error(validationErrors || data?.message || 'Failed to update album.')
        }

        return data
    }

    const saveAlbumTitle = async (nextTitle) => {
        try {
            setIsSaving(true)
            setError('')

            if (!albumId) {
                throw new Error('Album not found.')
            }

            await updateAlbum(albumId, nextTitle)
            showSuccess('Album saved.')
        } catch (err) {
            setError(err.message || 'Failed to save album.')
        } finally {
            setIsSaving(false)
        }
    }

    const scheduleSaveTitle = (nextTitle) => {
        if (saveTimeoutRef.current) {
            clearTimeout(saveTimeoutRef.current)
        }

        saveTimeoutRef.current = setTimeout(() => {
            saveAlbumTitle(nextTitle)
        }, 600)
    }

    const handleTitleChange = (event) => {
        const nextTitle = event.target.value

        setTitle(nextTitle)
        scheduleSaveTitle(nextTitle)
    }

    const uploadPhotos = async (files) => {
        if (!files?.length || !albumId) {
            return
        }

        try {
            setIsUploading(true)
            setError('')

            const uploadedPhotos = []

            for (const file of files) {
                const formData = new FormData()
                formData.append('album_id', String(albumId))
                formData.append('title', file.name)
                formData.append('description', '')
                formData.append('image', file)

                const response = await fetch(`${API_BASE_URL}/photos`, {
                    method: 'POST',
                    headers: {
                        ...(token ? { Authorization: `Bearer ${token}` } : {}),
                        Accept: 'application/json',
                    },
                    body: formData,
                })

                const data = await response.json().catch(() => ({}))

                if (!response.ok) {
                    throw new Error(data.message || `Failed to upload ${file.name}.`)
                }

                if (data.data) {
                    uploadedPhotos.push(data.data)
                }
            }

            setPhotos((prev) => [...prev, ...uploadedPhotos])
            showSuccess('Photos uploaded.')
        } catch (err) {
            setError(err.message || 'Failed to upload photos.')
        } finally {
            setIsUploading(false)

            if (fileInputRef.current) {
                fileInputRef.current.value = ''
            }
        }
    }

    const handleFilesSelected = async (event) => {
        const files = Array.from(event.target.files || [])
        await uploadPhotos(files)
    }

    const handleRemovePhoto = async (photoId) => {
        if (!albumId) {
            return
        }

        try {
            setError('')

            const response = await fetch(`${API_BASE_URL}/photos/${photoId}`, {
                method: 'DELETE',
                headers: authHeaders,
            })

            const data = await response.json().catch(() => null)

            if (!response.ok) {
                throw new Error(data?.message || 'Failed to remove photo.')
            }

            setPhotos((prev) => prev.filter((photo) => photo.id !== photoId))

            setActivePhotoIndex((prevIndex) => {
                if (prevIndex === null) {
                    return null
                }

                const removedIndex = photos.findIndex((photo) => photo.id === photoId)

                if (removedIndex === -1) {
                    return prevIndex
                }

                if (photos.length === 1) {
                    return null
                }

                if (prevIndex > removedIndex) {
                    return prevIndex - 1
                }

                if (prevIndex === removedIndex) {
                    return 0
                }

                return prevIndex
            })

            showSuccess('Photo removed.')
        } catch (err) {
            setError(err.message || 'Failed to remove photo.')
        }
    }

    const handleDeleteAlbum = async () => {
        if (!albumId) {
            navigate('/albums')
            return
        }

        const confirmed = window.confirm(
            'Are you sure you want to delete this album?'
        )

        if (!confirmed) {
            return
        }

        try {
            setIsDeletingAlbum(true)
            setError('')

            const response = await fetch(`${API_BASE_URL}/albums/${albumId}`, {
                method: 'DELETE',
                headers: authHeaders,
            })

            const data = await response.json().catch(() => null)

            if (!response.ok) {
                throw new Error(data?.message || 'Failed to delete album.')
            }

            navigate('/albums')
        } catch (err) {
            setError(err.message || 'Failed to delete album.')
        } finally {
            setIsDeletingAlbum(false)
        }
    }

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
        loadAlbum()
    }, [id])

    useEffect(() => {
        return () => {
            if (saveTimeoutRef.current) {
                clearTimeout(saveTimeoutRef.current)
            }
        }
    }, [])

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

    const activePhoto =
        activePhotoIndex !== null && photos[activePhotoIndex]
            ? photos[activePhotoIndex]
            : null

    const photoCountLabel = useMemo(() => {
        return `${photos.length} photo${photos.length === 1 ? '' : 's'}`
    }, [photos.length])

    if (isLoading) {
        return (
            <section className="rounded-xl bg-white p-6 shadow-sm">
                <p className="text-slate-600">Loading album...</p>
            </section>
        )
    }

    return (
        <>
            <section className="space-y-6">
                <div className="rounded-xl bg-white p-6 shadow-sm">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-semibold text-slate-900">
                                Edit Album
                            </h1>
                            <p className="mt-2 text-slate-600">
                                Update the album title and manage its photos.
                            </p>
                            <p className="mt-2 text-sm text-slate-500">
                                {photoCountLabel}
                            </p>
                        </div>

                        <div className="flex gap-2">
                            <Link
                                to={`/albums/${id}`}
                                className="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                View Album
                            </Link>

                            <Link
                                to="/albums"
                                className="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                            >
                                Back to Albums
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="rounded-xl bg-white p-6 shadow-sm">
                    <div className="space-y-6">
                        <div>
                            <label
                                htmlFor="album-title"
                                className="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Album name
                            </label>

                            <input
                                id="album-title"
                                type="text"
                                value={title}
                                onChange={handleTitleChange}
                                placeholder="Enter album name"
                                className="w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-slate-500"
                            />
                        </div>

                        <div>
                            <p className="mb-2 block text-sm font-medium text-slate-700">
                                Photos
                            </p>

                            <label className="flex min-h-40 cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:border-slate-400 hover:bg-slate-100">
                                <input
                                    ref={fileInputRef}
                                    type="file"
                                    multiple
                                    accept="image/*"
                                    onChange={handleFilesSelected}
                                    className="hidden"
                                />

                                <div>
                                    <p className="text-base font-medium text-slate-800">
                                        Click to upload photos
                                    </p>
                                    <p className="mt-2 text-sm text-slate-500">
                                        You can select one or multiple images.
                                    </p>
                                </div>
                            </label>
                        </div>

                        {(isSaving ||
                            isUploading ||
                            isDeletingAlbum ||
                            error ||
                            successMessage) && (
                            <div className="space-y-2">
                                {isSaving && (
                                    <p className="text-sm text-slate-500">
                                        Saving album...
                                    </p>
                                )}

                                {isUploading && (
                                    <p className="text-sm text-slate-500">
                                        Uploading photos...
                                    </p>
                                )}

                                {isDeletingAlbum && (
                                    <p className="text-sm text-slate-500">
                                        Deleting album...
                                    </p>
                                )}

                                {successMessage && (
                                    <p className="text-sm text-green-600">
                                        {successMessage}
                                    </p>
                                )}

                                {error && (
                                    <p className="text-sm text-red-600">{error}</p>
                                )}
                            </div>
                        )}
                    </div>
                </div>

                {photos.length > 0 && (
                    <div className="rounded-xl bg-white p-6 shadow-sm">
                        <h2 className="mb-4 text-xl font-semibold text-slate-900">
                            Album Photos
                        </h2>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {photos.map((photo, index) => (
                                <div
                                    key={photo.id}
                                    className="group relative overflow-hidden rounded-xl bg-slate-100"
                                >
                                    <button
                                        type="button"
                                        onClick={() => openModal(index)}
                                        className="block w-full text-left"
                                    >
                                        <div className="aspect-[4/3]">
                                            <img
                                                src={getFileUrl(photo.image_url)}
                                                alt={photo.title || 'Album photo'}
                                                className="h-full w-full object-cover"
                                            />
                                        </div>

                                        <div className="pointer-events-none absolute inset-0 bg-black/0 transition group-hover:bg-black/25" />
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => handleRemovePhoto(photo.id)}
                                        className="absolute right-3 top-3 rounded-md bg-white/90 px-3 py-2 text-sm font-medium text-red-600 opacity-0 shadow transition group-hover:opacity-100"
                                    >
                                        Remove
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <div className="rounded-xl bg-white p-6 shadow-sm">
                    <button
                        type="button"
                        onClick={handleDeleteAlbum}
                        disabled={isDeletingAlbum}
                        className="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Delete Album
                    </button>
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
                                    src={getFileUrl(photo.image_url)}
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