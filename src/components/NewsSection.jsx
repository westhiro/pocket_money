import React, { useState, useEffect } from 'react'
import { newsAPI } from '../services/api'
import './NewsSection.css'

const NewsSection = () => {
  const [news, setNews] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

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

  const getCategoryName = (newsType) => {
    const categoryMap = {
      'general': '一般',
      'event': 'イベント',
      'market': '市場',
      'good': 'ポジティブ',
      'bad': 'ネガティブ'
    }
    return categoryMap[newsType] || 'ニュース'
  }

  if (loading) {
    return (
      <div className="news-section">
        <div className="news-header">
          <h2>📰 最新ニュース</h2>
        </div>
        <div className="loading">データを読み込み中...</div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="news-section">
        <div className="news-header">
          <h2>📰 最新ニュース</h2>
        </div>
        <div className="error">{error}</div>
      </div>
    )
  }

  if (news.length === 0) {
    return (
      <div className="news-section">
        <div className="news-header">
          <h2>📰 最新ニュース</h2>
        </div>
        <div className="no-news">ニュースがありません</div>
      </div>
    )
  }

  return (
    <div className="news-section">
      <div className="news-header">
        <h2>📰 最新ニュース</h2>
      </div>
      <div className="news-list">
        {news.map(item => (
          <div key={item.id} className="news-item">
            <div className="news-content">
              <div className="news-category">{getCategoryName(item.news_type)}</div>
              <h3 className="news-title">{item.title}</h3>
              <div className="news-time">{formatDate(item.published_at)}</div>
              {item.event_impact && (
                <div className="event-impact">
                  影響度: {item.event_impact > 0 ? '+' : ''}{item.event_impact}%
                  {item.industry_affected && ` (対象: ${item.industry_affected})`}
                </div>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

export default NewsSection