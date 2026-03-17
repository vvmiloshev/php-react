import { BrowserRouter, Routes, Route } from 'react-router-dom'
import HomePage from '../pages/HomePage'
import AuthPage from '../pages/AuthPage'
import AlbumsPage from '../pages/AlbumsPage'
import CreateAlbumPage from '../pages/CreateAlbumPage'
import AlbumDetailsPage from '../pages/AlbumDetailsPage'
import PollPage from '../pages/PollPage'
import PollResultsPage from '../pages/PollResultsPage'
import NotFoundPage from '../pages/NotFoundPage'
import ProtectedRoute from './ProtectedRoute'
import AppLayout from '../components/layout/AppLayout'

export default function AppRouter() {
    return (
        <BrowserRouter>
            <Routes>
                <Route element={<AppLayout />}>
                    <Route path="/" element={<HomePage />} />
                    <Route path="/auth" element={<AuthPage />} />

                    <Route
                        path="/albums"
                        element={
                            <ProtectedRoute>
                                <AlbumsPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route
                        path="/albums/create"
                        element={
                            <ProtectedRoute>
                                <CreateAlbumPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route
                        path="/albums/:id"
                        element={
                            <ProtectedRoute>
                                <AlbumDetailsPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route
                        path="/poll"
                        element={
                            <ProtectedRoute>
                                <PollPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route
                        path="/poll-results"
                        element={
                            <ProtectedRoute>
                                <PollResultsPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route path="*" element={<NotFoundPage />} />
                </Route>
            </Routes>
        </BrowserRouter>
    )
}