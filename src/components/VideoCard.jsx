import React from 'react'
import './VideoCard.css'

const VideoCard = ({ video, onClick }) => {
  const getProgressColor = (progress) => {
    if (progress === 100) return '#4CAF50'
    if (progress >= 75) return '#2196F3'
    if (progress >= 50) return '#FF9800'
    if (progress >= 25) return '#FFC107'
    return '#E0E0E0'
  }

  const getLevelColor = (level) => {
    switch (level) {
      case '初級': return '#4CAF50'
      case '中級': return '#FF9800'
      case '上級': return '#f44336'
      default: return '#2196F3'
    }
  }

  const getStatusBadge = (video) => {
    if (video.progress === 100) {
      return <span className="status-badge completed">✅ 完了</span>
    } else if (video.isWatching) {
      return <span className="status-badge watching">🎬 視聴中</span>
    } else if (video.progress > 0) {
      return <span className="status-badge started">▶️ 開始済</span>
    } else {
      return <span className="status-badge new">🆕 未視聴</span>
    }
  }

  return (
    <div 
      className={`video-card ${video.isWatching ? 'watching' : ''} ${video.progress === 100 ? 'completed' : ''}`}
      onClick={onClick}
    >
      <div className="video-thumbnail">
        <div className="thumbnail-emoji">{video.thumbnail}</div>
        <div className="video-duration">{video.duration}</div>
        <div className="video-level" style={{backgroundColor: getLevelColor(video.level)}}>
          {video.level}
        </div>
      </div>
      
      <div className="video-content">
        <div className="video-header">
          <h3 className="video-title">{video.title}</h3>
          {getStatusBadge(video)}
        </div>
        
        <p className="video-description">{video.description}</p>
        
        <div className="video-meta">
          <span className="video-category">{video.category}</span>
        </div>
        
        <div className="progress-section">
          <div className="progress-info">
            <span className="progress-text">進捗</span>
            <span className="progress-percentage">{video.progress}%</span>
          </div>
          <div className="progress-bar">
            <div 
              className="progress-fill" 
              style={{
                width: `${video.progress}%`,
                backgroundColor: getProgressColor(video.progress)
              }}
            ></div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default VideoCard