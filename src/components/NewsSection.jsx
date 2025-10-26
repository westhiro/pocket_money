import React, { useState, useEffect } from 'react'
import { newsAPI } from '../services/api'
import NewsDetailModal from './NewsDetailModal'
import './NewsSection.css'

const NewsSection = () => {
  const [news, setNews] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [currentIndex, setCurrentIndex] = useState(0)
  const [isAnimating, setIsAnimating] = useState(false)
  const [isModalOpen, setIsModalOpen] = useState(false)
  const [selectedNews, setSelectedNews] = useState(null)

  useEffect(() => {
    const fetchNews = async () => {
      try {
        setLoading(true)
        const response = await newsAPI.getLatest()
        setNews(response.data.data || [])
      } catch (err) {
        console.error('News fetch error:', err)
        setError('ニュースの取得に失敗しました')
      } finally {
        setLoading(false)
      }
    }

    fetchNews()
  }, [])

  // 10秒ごとにニュースを切り替え
  useEffect(() => {
    if (news.length === 0) return

    const interval = setInterval(() => {
      setIsAnimating(true)

      setTimeout(() => {
        setCurrentIndex((prevIndex) => (prevIndex + 1) % news.length)
        setIsAnimating(false)
      }, 500) // アニメーション時間
    }, 10000) // 10秒

    return () => clearInterval(interval)
  }, [news.length])

  const formatDate = (dateString) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInMinutes = Math.floor((now - date) / (1000 * 60))
    
    if (diffInMinutes < 60) {
      return `${diffInMinutes}分前`
    } else if (diffInMinutes < 1440) { // 24時間以内
      const hours = Math.floor(diffInMinutes / 60)
      return `${hours}時間前`
    } else {
      const days = Math.floor(diffInMinutes / 1440)
      return `${days}日前`
    }
  }

  const getCategoryName = (genre, newsType) => {
    // genreがあればそれを使用、なければnews_typeをマッピング
    if (genre) {
      return genre
    }

    const categoryMap = {
      'general': '一般',
      'event': 'イベント',
      'market': '市場',
      'good': 'ポジティブ',
      'bad': 'ネガティブ'
    }
    return categoryMap[newsType] || 'ニュース'
  }

  const handleNewsClick = () => {
    setSelectedNews(news[currentIndex])
    setIsModalOpen(true)
  }

  const handleCloseModal = () => {
    setIsModalOpen(false)
    setSelectedNews(null)
  }

  if (loading) {
    return (
      <div className="news-ticker">
        <div className="loading">データを読み込み中...</div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="news-ticker">
        <div className="error">{error}</div>
      </div>
    )
  }

  if (news.length === 0) {
    return (
      <div className="news-ticker">
        <div className="no-news">ニュースがありません</div>
      </div>
    )
  }

  const currentNews = news[currentIndex]

  return (
    <>
      <div className="news-ticker" onClick={handleNewsClick}>
        <div className={`news-ticker-content ${isAnimating ? 'rotating' : ''}`}>
          <span className="news-icon">📰</span>
          <span className="news-category">{getCategoryName(currentNews.genre, currentNews.news_type)}</span>
          <span className="news-title">{currentNews.title}</span>
          <span className="news-time">{formatDate(currentNews.published_at)}</span>
        </div>
      </div>

      <NewsDetailModal
        isOpen={isModalOpen}
        onClose={handleCloseModal}
        news={selectedNews}
      />
    </>
  )
}

export default NewsSection