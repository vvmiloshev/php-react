import { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import {getJsonAuthHeaders} from "../api/auth";

export default function CreateAlbumPage() {
    const navigate = useNavigate()

    const [albumId, setAlbumId] = useState(null)
    const [title, setTitle] = useState('')
    const [photos, setPhotos] = useState([])
    const [isSaving, setIsSaving] = useState(false)
    const [isUploading, setIsUploading] = useState(false)
    const [isDeletingAlbum, setIsDeletingAlbum] = useState(false)
    const [error, setError] = useState('')
    const [successMessage, setSuccessMessage] = useState('')

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

    const createAlbum = async (albumTitle = '') => {
        const response = await fetch('http://localhost/api/albums', {
            method: 'POST',
            headers: getJsonAuthHeaders(),
            body: JSON.stringify({
                title: albumTitle,
            }),
        })

        const data = await response.json()

        if (!response.ok) {
            const validationErrors = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : ''

            throw new Error(validationErrors || data.message || 'Failed to create album.')
        }

        const album = data.data ?? data

        setAlbumId(album.id)

        return album.id
    }

    const updateAlbum = async (id, albumTitle) => {
        const response = await fetch(`http://localhost/api/albums/${id}`, {
            method: 'PUT',
            headers: {
                ...authHeaders,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title: albumTitle,
            }),
        })

        if (!response.ok) {
            throw new Error('Failed to update album.')
        }

        return response.json()
    }

    const saveAlbumTitle = async (nextTitle) => {
        try {
            setIsSaving(true)
            setError('')

            let currentAlbumId = albumId

            if (!currentAlbumId) {
                currentAlbumId = await createAlbum(nextTitle)
                showSuccess('Album created.')
            } else {
                await updateAlbum(currentAlbumId, nextTitle)
                showSuccess('Album saved.')
            }
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
        if (!files?.length) {
            return
        }

        try {
            setIsUploading(true)
            setError('')

            let currentAlbumId = albumId

            if (!currentAlbumId) {
                currentAlbumId = await createAlbum(title)
            }

            const uploadedPhotos = []

            for (const file of files) {
                const formData = new FormData()
                formData.append('album_id', String(currentAlbumId))
                formData.append('title', file.name)
                formData.append('description', '')
                formData.append('image', file)

                const response = await fetch('http://localhost/api/photos', {
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

            const response = await fetch(
                `http://localhost/api/albums/${albumId}/photos/${photoId}`,
                {
                    method: 'DELETE',
                    headers: authHeaders,
                }
            )

            if (!response.ok) {
                throw new Error('Failed to remove photo.')
            }

            setPhotos((prev) => prev.filter((photo) => photo.id !== photoId))
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

            const response = await fetch(`http://localhost/api/albums/${albumId}`, {
                method: 'DELETE',
                headers: authHeaders,
            })

            if (!response.ok) {
                throw new Error('Failed to delete album.')
            }

            navigate('/albums')
        } catch (err) {
            setError(err.message || 'Failed to delete album.')
        } finally {
            setIsDeletingAlbum(false)
        }
    }

    useEffect(() => {
        return () => {
            if (saveTimeoutRef.current) {
                clearTimeout(saveTimeoutRef.current)
            }
        }
    }, [])

    return (
        <section className="space-y-6">
            <div className="rounded-xl bg-white p-6 shadow-sm">
                <h1 className="text-2xl font-semibold text-slate-900">
                    Create Album
                </h1>
                <p className="mt-2 text-slate-600">
                    The album is saved automatically when you change the title or
                    add and remove photos.
                </p>
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

                    {(isSaving || isUploading || isDeletingAlbum || error || successMessage) && (
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
                        {photos.map((photo) => (
                            <div
                                key={photo.id}
                                className="group relative overflow-hidden rounded-xl bg-slate-100"
                            >
                                <div className="aspect-[4/3]">
                                    <img
                                        src={`http://localhost/api/files/photos/${photo.path.split('/').pop()}`}
                                        alt={photo.title || 'Album photo'}
                                        className="h-full w-full object-cover"
                                    />
                                </div>

                                <div className="pointer-events-none absolute inset-0 bg-black/0 transition group-hover:bg-black/35" />

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
    )
}