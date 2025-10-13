import React from 'react'
import './LearningStats.css'

const LearningStats = ({ stats }) => {
  const completionRate = Math.round((stats.completedVideos / stats.totalVideos) * 100)
  const watchTimeProgress = Math.round((stats.watchedTime / stats.totalWatchTime) * 100)

  return (
    <div className="learning-stats">
      <div className="stats-header">
        <h2>📊 学習統計</h2>
      </div>
      
      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-icon">🎯</div>
          <div className="stat-content">
            <div className="stat-label">完了率</div>
            <div className="stat-value">{completionRate}%</div>
            <div className="stat-detail">{stats.completedVideos}/{stats.totalVideos} 動画完了</div>
          </div>
          <div className="stat-progress">
            <div className="mini-progress-bar">
              <div 
                className="mini-progress-fill completion" 
                style={{width: `${completionRate}%`}}
              ></div>
            </div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon">⏱️</div>
          <div className="stat-content">
            <div className="stat-label">視聴時間</div>
            <div className="stat-value">{stats.watchedTime}分</div>
            <div className="stat-detail">総時間 {stats.totalWatchTime}分</div>
          </div>
          <div className="stat-progress">
            <div className="mini-progress-bar">
              <div 
                className="mini-progress-fill watch-time" 
                style={{width: `${watchTimeProgress}%`}}
              ></div>
            </div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon">🔥</div>
          <div className="stat-content">
            <div className="stat-label">連続学習</div>
            <div className="stat-value">{stats.currentStreak}日</div>
            <div className="stat-detail">継続中</div>
          </div>
        </div>

        <div className="stat-card">
          <div className="stat-icon">💎</div>
          <div className="stat-content">
            <div className="stat-label">獲得ポイント</div>
            <div className="stat-value">{Math.round(stats.totalPoints || 0).toLocaleString()}</div>
            <div className="stat-detail">コイン交換可能</div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default LearningStats