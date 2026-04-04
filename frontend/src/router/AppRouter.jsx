import { BrowserRouter, Routes, Route } from 'react-router-dom'
import HomePage from '../pages/HomePage'
import AuthPage from '../pages/AuthPage'

import AlbumsPage from '../pages/AlbumsPage'
import AlbumCreatePage from '../pages/AlbumCreatePage'
import AlbumDetailsPage from '../pages/AlbumDetailsPage'
import AlbumEditPage from "../pages/AlbumEditPage";

import PollPage from '../pages/PollPage'
import PollResultsPage from '../pages/PollResultsPage'
import NotFoundPage from '../pages/NotFoundPage'
import ProtectedRoute from './ProtectedRoute'
import AppLayout from '../components/layout/AppLayout'
import CreatePollPage from "../pages/CreatePollPage";
import ManagePollsPage from "../pages/ManagePollsPage";
import EditPollPage from "../pages/EditPollPage";

export default function AppRouter() {
    return (
        <BrowserRouter>
            <Routes>
                <Route element={<AppLayout />}>
                    <Route path="/" element={<HomePage />} />
                    <Route path="/auth" element={<AuthPage />} />
                    <Route path="/albums" element={<AlbumsPage />} />
                    <Route path="/poll" element={<PollPage />} />

                    {/*<Route path="/albums"
                        element={
                            <ProtectedRoute>
                                <AlbumsPage />
                            </ProtectedRoute>
                        }
                    />*/}

                    <Route path="/albums/create"
                        element={
                            <ProtectedRoute>
                                <AlbumCreatePage />
                            </ProtectedRoute>
                        }
                    />

                    <Route path="/albums/:id"
                        element={
                            <ProtectedRoute>
                                <AlbumDetailsPage />
                            </ProtectedRoute>
                        }
                    />
                    <Route path="/albums/:id/edit"
                        element={
                            <ProtectedRoute>
                                <AlbumEditPage />
                            </ProtectedRoute>
                        }
                    />

                    {/*<Route path="/poll"
                        element={
                            <ProtectedRoute>
                                <PollPage />
                            </ProtectedRoute>
                        }
                    />
*/}
                    <Route path="/poll-results"
                        element={
                            <ProtectedRoute>
                                <PollResultsPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route
                        path="/polls/create"
                        element={
                            <ProtectedRoute>
                                <CreatePollPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route
                        path="/polls/manage"
                        element={
                            <ProtectedRoute>
                                <ManagePollsPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route
                        path="/polls/:id/results"
                        element={
                            <ProtectedRoute>
                                <PollResultsPage />
                            </ProtectedRoute>
                        }
                    />

                    <Route
                        path="/manage-polls/:id/edit"
                        element={
                            <ProtectedRoute>
                                <EditPollPage />
                            </ProtectedRoute>
                        }
                    />
                    <Route
                        path="/polls/:id/results"
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