import React, { useState, useEffect } from 'react'
import { notificationAPI } from '../services/api'
import './Notifications.css'

const Notifications = () => {
  const [notifications, setNotifications] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [selectedNotification, setSelectedNotification] = useState(null)

  useEffect(() => {
    fetchNotifications()
  }, [])

  const fetchNotifications = async () => {
    try {
      setLoading(true)
      const response = await notificationAPI.getAll()
      if (response.data.success) {
        setNotifications(response.data.data)
      } else {
        throw new Error(response.data.message || 'お知らせの取得に失敗しました')
      }
    } catch (err) {
      console.error('Notification fetch error:', err)
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const handleNotificationClick = async (notification) => {
    setSelectedNotification(notification)

    // 未読の場合は既読にする
    if (!notification.is_read) {
      try {
        await notificationAPI.markAsRead(notification.id)
        // ローカルの状態を更新
        setNotifications(notifications.map(n =>
          n.id === notification.id ? { ...n, is_read: true } : n
        ))
      } catch (err) {
        console.error('Mark as read error:', err)
      }
    }
  }

  const getTypeIcon = (type) => {
    switch (type) {
      case 'important':
        return '⚠️'
      case 'warning':
        return '⚡'
      case 'event':
        return '🎉'
      default:
        return 'ℹ️'
    }
  }

  const getTypeLabel = (type) => {
    switch (type) {
      case 'important':
        return '重要'
      case 'warning':
        return '警告'
      case 'event':
        return 'イベント'
      default:
        return 'お知らせ'
    }
  }

  if (loading) {
    return (
      <div className="notifications-page">
        <div className="loading">読み込み中...</div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="notifications-page">
        <div className="error">{error}</div>
      </div>
    )
  }

  return (
    <div className="notifications-page">
      <div className="page-header">
        <h1>お知らせ</h1>
      </div>

      {notifications.length === 0 ? (
        <div className="empty-message">
          お知らせはありません
        </div>
      ) : (
        <div className="notifications-list">
          {notifications.map(notification => (
            <div
              key={notification.id}
              className={`notification-item ${!notification.is_read ? 'unread' : ''}`}
              onClick={() => handleNotificationClick(notification)}
            >
              <div className="notification-header">
                <div className="notification-type">
                  <span className="type-icon">{getTypeIcon(notification.type)}</span>
                  <span className="type-label">{getTypeLabel(notification.type)}</span>
                </div>
                <div className="notification-date">
                  {new Date(notification.published_at).toLocaleDateString('ja-JP')}
                </div>
              </div>
              <div className="notification-title">
                {notification.title}
                {!notification.is_read && <span className="unread-badge">未読</span>}
              </div>
              <div className="notification-preview">
                {notification.content.substring(0, 100)}
                {notification.content.length > 100 && '...'}
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedNotification && (
        <div className="notification-modal" onClick={() => setSelectedNotification(null)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <div className="notification-type">
                <span className="type-icon">{getTypeIcon(selectedNotification.type)}</span>
                <span className="type-label">{getTypeLabel(selectedNotification.type)}</span>
              </div>
              <button className="close-btn" onClick={() => setSelectedNotification(null)}>×</button>
            </div>
            <h2>{selectedNotification.title}</h2>
            <div className="notification-date">
              {new Date(selectedNotification.published_at).toLocaleString('ja-JP')}
            </div>
            <div className="notification-content">
              {selectedNotification.content}
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Notifications
