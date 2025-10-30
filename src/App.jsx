import React from 'react'
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom'
import { AuthProvider, useAuth } from './contexts/AuthContext'
import Header from './components/Header'
import NewsSection from './components/NewsSection'
import Home from './pages/Home'
import Investment from './pages/Investment'
import Learning from './pages/Learning'
import Notifications from './pages/Notifications'
import Inquiry from './pages/Inquiry'
import Profile from './pages/Profile'
import Help from './pages/Help'
import AuthModal from './components/Auth/AuthModal'
import './App.css'

function AppContent() {
  const { isAuthenticated, loading } = useAuth()

  if (loading) {
    return (
      <div className="loading-screen">
        <div className="loading-spinner">
          <h2>読み込み中...</h2>
        </div>
      </div>
    )
  }

  if (!isAuthenticated) {
    return (
      <div className="auth-screen">
        <div className="auth-container">
          <h1 className="app-title">Money Grow Kids</h1>
          <p className="app-description">投資シミュレーションで楽しく学ぼう！</p>
          <AuthModal isOpen={true} onClose={() => {}} />
        </div>
      </div>
    )
  }

  return (
    <div className="App">
      <Header />
      <NewsSection />
      <main className="main-content">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/home" element={<Home />} />
          <Route path="/investment" element={<Investment />} />
          <Route path="/learning" element={<Learning />} />
          <Route path="/notifications" element={<Notifications />} />
          <Route path="/inquiry" element={<Inquiry />} />
          <Route path="/profile" element={<Profile />} />
          <Route path="/help" element={<Help />} />
        </Routes>
      </main>
    </div>
  )
}

function App() {
  return (
    <AuthProvider>
      <Router>
        <AppContent />
      </Router>
    </AuthProvider>
  )
}

export default App