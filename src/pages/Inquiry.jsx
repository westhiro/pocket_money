import React, { useState, useEffect } from 'react'
import { inquiryAPI } from '../services/api'
import './Inquiry.css'

const Inquiry = () => {
  const [inquiries, setInquiries] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [showForm, setShowForm] = useState(false)
  const [formData, setFormData] = useState({
    subject: '',
    message: ''
  })
  const [submitting, setSubmitting] = useState(false)
  const [selectedInquiry, setSelectedInquiry] = useState(null)

  useEffect(() => {
    fetchInquiries()
  }, [])

  const fetchInquiries = async () => {
    try {
      setLoading(true)
      const response = await inquiryAPI.getAll()
      if (response.data.success) {
        setInquiries(response.data.data)
      } else {
        throw new Error(response.data.message || 'お問い合わせの取得に失敗しました')
      }
    } catch (err) {
      console.error('Inquiry fetch error:', err)
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const handleInputChange = (e) => {
    const { name, value } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: value
    }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    if (!formData.subject.trim() || !formData.message.trim()) {
      alert('件名とメッセージを入力してください')
      return
    }

    try {
      setSubmitting(true)
      const response = await inquiryAPI.create(formData)
      if (response.data.success) {
        alert('お問い合わせを送信しました')
        setFormData({ subject: '', message: '' })
        setShowForm(false)
        fetchInquiries() // 一覧を再取得
      } else {
        throw new Error(response.data.message || 'お問い合わせの送信に失敗しました')
      }
    } catch (err) {
      console.error('Inquiry submit error:', err)
      alert('お問い合わせの送信に失敗しました: ' + err.message)
    } finally {
      setSubmitting(false)
    }
  }

  const getStatusLabel = (status) => {
    switch (status) {
      case 'pending':
        return '未対応'
      case 'in_progress':
        return '対応中'
      case 'resolved':
        return '解決済み'
      default:
        return status
    }
  }

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending':
        return '#ff9800'
      case 'in_progress':
        return '#2196F3'
      case 'resolved':
        return '#4CAF50'
      default:
        return '#666'
    }
  }

  if (loading) {
    return (
      <div className="inquiry-page">
        <div className="loading">読み込み中...</div>
      </div>
    )
  }

  return (
    <div className="inquiry-page">
      <div className="page-header">
        <h1>お問い合わせ</h1>
        <button
          className="new-inquiry-btn"
          onClick={() => setShowForm(!showForm)}
        >
          {showForm ? 'キャンセル' : '新しいお問い合わせ'}
        </button>
      </div>

      {showForm && (
        <div className="inquiry-form-container">
          <form onSubmit={handleSubmit} className="inquiry-form">
            <div className="form-group">
              <label htmlFor="subject">件名</label>
              <input
                type="text"
                id="subject"
                name="subject"
                value={formData.subject}
                onChange={handleInputChange}
                placeholder="お問い合わせの件名を入力"
                disabled={submitting}
              />
            </div>
            <div className="form-group">
              <label htmlFor="message">メッセージ</label>
              <textarea
                id="message"
                name="message"
                value={formData.message}
                onChange={handleInputChange}
                placeholder="お問い合わせ内容を詳しく入力してください"
                rows="6"
                disabled={submitting}
              />
            </div>
            <button
              type="submit"
              className="submit-btn"
              disabled={submitting}
            >
              {submitting ? '送信中...' : '送信'}
            </button>
          </form>
        </div>
      )}

      <div className="inquiries-section">
        <h2>お問い合わせ履歴</h2>
        {error && <div className="error">{error}</div>}

        {inquiries.length === 0 ? (
          <div className="empty-message">
            お問い合わせ履歴がありません
          </div>
        ) : (
          <div className="inquiries-list">
            {inquiries.map(inquiry => (
              <div
                key={inquiry.id}
                className="inquiry-item"
                onClick={() => setSelectedInquiry(inquiry)}
              >
                <div className="inquiry-header">
                  <div className="inquiry-subject">{inquiry.subject}</div>
                  <div
                    className="inquiry-status"
                    style={{ color: getStatusColor(inquiry.status) }}
                  >
                    {getStatusLabel(inquiry.status)}
                  </div>
                </div>
                <div className="inquiry-date">
                  {new Date(inquiry.created_at).toLocaleString('ja-JP')}
                </div>
                <div className="inquiry-preview">
                  {inquiry.message.substring(0, 100)}
                  {inquiry.message.length > 100 && '...'}
                </div>
                {inquiry.admin_reply && (
                  <div className="has-reply-badge">返信あり</div>
                )}
              </div>
            ))}
          </div>
        )}
      </div>

      {selectedInquiry && (
        <div className="inquiry-modal" onClick={() => setSelectedInquiry(null)}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>お問い合わせ詳細</h2>
              <button className="close-btn" onClick={() => setSelectedInquiry(null)}>×</button>
            </div>

            <div className="inquiry-detail">
              <div className="detail-header">
                <h3>{selectedInquiry.subject}</h3>
                <div
                  className="inquiry-status"
                  style={{
                    color: getStatusColor(selectedInquiry.status),
                    fontSize: '14px',
                    fontWeight: 'bold'
                  }}
                >
                  {getStatusLabel(selectedInquiry.status)}
                </div>
              </div>
              <div className="detail-date">
                送信日: {new Date(selectedInquiry.created_at).toLocaleString('ja-JP')}
              </div>

              <div className="detail-section">
                <h4>お問い合わせ内容</h4>
                <div className="detail-message">{selectedInquiry.message}</div>
              </div>

              {selectedInquiry.admin_reply && (
                <div className="detail-section reply-section">
                  <h4>運営からの返信</h4>
                  <div className="detail-reply">{selectedInquiry.admin_reply}</div>
                  {selectedInquiry.replied_at && (
                    <div className="reply-date">
                      返信日: {new Date(selectedInquiry.replied_at).toLocaleString('ja-JP')}
                    </div>
                  )}
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Inquiry
