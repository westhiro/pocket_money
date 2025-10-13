import React, { useState, useEffect, useRef } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import AuthModal from './Auth/AuthModal'
import './Header.css'

const Header = () => {
  const location = useLocation()
  const { user, isAuthenticated, logout } = useAuth()
  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false)
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const menuRef = useRef(null)

  const isActiveTab = (path) => {
    return location.pathname === path || 
           (path === '/home' && location.pathname === '/')
  }

  const handleAuthClick = () => {
    setIsAuthModalOpen(true)
  }

  const handleLogout = () => {
    logout()
    setIsMenuOpen(false)
  }

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen)
  }

  // メニュー外クリックで閉じる
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setIsMenuOpen(false)
      }
    }

    if (isMenuOpen) {
      document.addEventListener('mousedown', handleClickOutside)
      return () => document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [isMenuOpen])

  return (
    <>
      <header className="header">
        <div className="header-container">
          <div className="header-title">
            <h1>Money Grow Kids</h1>
          </div>
          
          {/* ナビゲーションタブ - 中央配置 */}
          <nav className="header-nav">
            <Link 
              to="/home" 
              className={`nav-tab ${isActiveTab('/home') || isActiveTab('/') ? 'active' : ''}`}
            >
              <span className="nav-icon">🏠</span>
              <span className="nav-text">ホーム</span>
            </Link>
            <Link 
              to="/investment" 
              className={`nav-tab ${isActiveTab('/investment') ? 'active' : ''}`}
            >
              <span className="nav-icon">📊</span>
              <span className="nav-text">投資</span>
            </Link>
            <Link 
              to="/learning" 
              className={`nav-tab ${isActiveTab('/learning') ? 'active' : ''}`}
            >
              <span className="nav-icon">📹</span>
              <span className="nav-text">学習</span>
            </Link>
          </nav>

          {/* 右側: コイン残高 + ユーザーメニュー */}
          <div className="right-section">
            {/* コイン残高（ログイン時のみ） */}
            {isAuthenticated && (
              <div className="coin-balance">
                <span className="coin-icon">🪙</span>
                <span className="coin-amount">{Math.round(user?.coin_balance || 0).toLocaleString()}</span>
              </div>
            )}

            {/* ユーザーメニューまたはログインボタン */}
            <div className="user-section">
              {isAuthenticated ? (
                <div className="user-menu-container" ref={menuRef}>
                  <button onClick={toggleMenu} className="hamburger-btn">
                    <span className="hamburger-icon">☰</span>
                  </button>
                  {isMenuOpen && (
                    <div className="dropdown-menu">
                      <button onClick={handleLogout} className="logout-btn">
                        ログアウト
                      </button>
                    </div>
                  )}
                </div>
              ) : (
                <button onClick={handleAuthClick} className="login-btn">
                  ログイン / 新規登録
                </button>
              )}
            </div>
          </div>
        </div>
      </header>

      {/* 認証モーダル */}
      <AuthModal 
        isOpen={isAuthModalOpen}
        onClose={() => setIsAuthModalOpen(false)}
      />
    </>
  )
}

export default Header