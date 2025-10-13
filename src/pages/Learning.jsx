import React, { useState } from 'react'
import { learningVideosData, learningCategories, learningStats } from '../data/mockData'
import VideoCard from '../components/VideoCard'
import LearningStats from '../components/LearningStats'
import './Learning.css'

const Learning = () => {
  const [selectedCategory, setSelectedCategory] = useState('all')
  const [sortBy, setSortBy] = useState('progress') // progress, title, duration
  
  const filteredVideos = learningVideosData.filter(video => {
    if (selectedCategory === 'all') return true
    return video.category.toLowerCase() === selectedCategory
  })

  const sortedVideos = [...filteredVideos].sort((a, b) => {
    switch (sortBy) {
      case 'progress':
        return b.progress - a.progress
      case 'title':
        return a.title.localeCompare(b.title)
      case 'duration':
        return parseInt(a.duration) - parseInt(b.duration)
      default:
        return 0
    }
  })

  const handleVideoClick = (video) => {
    if (video.progress === 100) {
      alert(`「${video.title}」は完了済みです。もう一度視聴しますか？`)
    } else if (video.isWatching) {
      alert(`「${video.title}」の続きから再生します。\n進捗: ${video.progress}%`)
    } else {
      alert(`「${video.title}」を開始します。`)
    }
  }

  return (
    <div className="learning-page">
      <div className="learning-header">
        <h1>📚 学習コンテンツ</h1>
        <p>投資の知識を動画で学びましょう</p>
      </div>

      <LearningStats stats={learningStats} />

      <div className="learning-content">
        <div className="learning-sidebar">
          <div className="category-filter">
            <h3>カテゴリー</h3>
            <div className="category-list">
              {learningCategories.map(category => (
                <button
                  key={category.id}
                  className={`category-btn ${selectedCategory === category.id ? 'active' : ''}`}
                  onClick={() => setSelectedCategory(category.id)}
                >
                  <span className="category-name">{category.name}</span>
                  <span className="category-count">{category.count}</span>
                </button>
              ))}
            </div>
          </div>

          <div className="sort-options">
            <h3>並び替え</h3>
            <select 
              value={sortBy} 
              onChange={(e) => setSortBy(e.target.value)}
              className="sort-select"
            >
              <option value="progress">進捗順</option>
              <option value="title">タイトル順</option>
              <option value="duration">時間順</option>
            </select>
          </div>
        </div>

        <div className="videos-section">
          <div className="videos-header">
            <h2>
              {selectedCategory === 'all' ? 
                `全ての動画 (${filteredVideos.length}件)` : 
                `${learningCategories.find(c => c.id === selectedCategory)?.name}の動画 (${filteredVideos.length}件)`
              }
            </h2>
          </div>
          
          <div className="videos-grid">
            {sortedVideos.map(video => (
              <VideoCard
                key={video.id}
                video={video}
                onClick={() => handleVideoClick(video)}
              />
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}

export default Learning