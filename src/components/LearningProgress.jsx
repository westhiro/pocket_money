import React from 'react'
import './LearningProgress.css'

const LearningProgress = () => {
  const currentVideo = {
    title: "投資の基本：リスクとリターンを理解しよう",
    progress: 65,
    duration: "15分",
    completed: "9分"
  }

  return (
    <div className="learning-progress">
      <div className="learning-header">
        <h2>📚 学習進捗</h2>
      </div>

      <div className="current-video">
        <h3>最新視聴動画</h3>
        <div className="video-info">
          <div className="video-title">{currentVideo.title}</div>
          <div className="video-time">
            {currentVideo.completed} / {currentVideo.duration}
          </div>
        </div>
        <div className="progress-container">
          <div className="progress-bar">
            <div
              className="progress-fill"
              style={{width: `${currentVideo.progress}%`}}
            ></div>
          </div>
          <div className="progress-percentage">{currentVideo.progress}%</div>
        </div>
      </div>
    </div>
  )
}

export default LearningProgress