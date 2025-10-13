import React from 'react'
import './LearningProgress.css'

const LearningProgress = () => {
  const currentVideo = {
    title: "投資の基本：リスクとリターンを理解しよう",
    progress: 65,
    duration: "15分",
    completed: "9分"
  }

  const recentCourses = [
    { title: "株式投資入門", progress: 100 },
    { title: "分散投資の重要性", progress: 80 },
    { title: "配当金について学ぶ", progress: 45 }
  ]

  return (
    <div className="learning-progress">
      <div className="learning-header">
        <h2>📚 学習進捗</h2>
      </div>
      
      <div className="current-video">
        <h3>現在視聴中</h3>
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

      <div className="recent-courses">
        <h3>最近の学習コース</h3>
        <div className="courses-list">
          {recentCourses.map((course, index) => (
            <div key={index} className="course-item">
              <div className="course-title">{course.title}</div>
              <div className="course-progress">
                <div className="mini-progress-bar">
                  <div 
                    className="mini-progress-fill" 
                    style={{width: `${course.progress}%`}}
                  ></div>
                </div>
                <span className="course-percentage">{course.progress}%</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}

export default LearningProgress