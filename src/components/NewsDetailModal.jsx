import React from 'react'
import './NewsDetailModal.css'

const NewsDetailModal = ({ isOpen, onClose, news }) => {
  if (!isOpen || !news) return null

  return (
    <div className="news-modal-overlay" onClick={onClose}>
      <div className="news-modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="news-modal-header">
          <div className="news-modal-genre">{news.genre}</div>
          <button className="news-modal-close" onClick={onClose}>×</button>
        </div>

        <h2 className="news-modal-title">{news.title}</h2>

        <div className="news-modal-body">
          <div className="news-detail-section">
            <h3>詳細</h3>
            <p className="news-description">{news.content}</p>
          </div>

          {news.affected_industries && news.affected_industries.length > 0 && (
            <div className="news-impact-section">
              <h3>影響を受ける業界</h3>
              <div className="impact-list">
                {news.affected_industries.map((industry, index) => (
                  <div
                    key={index}
                    className={`impact-item ${industry.direction}`}
                  >
                    <div className="impact-direction">
                      {industry.direction === 'up' ? '↑' : '↓'}
                    </div>
                    <div className="impact-info">
                      <div className="impact-name">{industry.name}</div>
                      <div className="impact-percentage">
                        {industry.impact_percentage > 0 ? '+' : ''}
                        {industry.impact_percentage}%
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>

        <div className="news-modal-footer">
          <button className="news-modal-btn-close" onClick={onClose}>
            閉じる
          </button>
        </div>
      </div>
    </div>
  )
}

export default NewsDetailModal
