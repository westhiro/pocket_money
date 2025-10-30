import React, { useState, useEffect } from 'react'
import { profileAPI } from '../services/api'
import { formatCurrency } from '../utils/format'
import './Profile.css'

const Profile = () => {
  const [profile, setProfile] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [isEditing, setIsEditing] = useState(false)
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  })
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    fetchProfile()
  }, [])

  const fetchProfile = async () => {
    try {
      setLoading(true)
      const response = await profileAPI.get()
      if (response.data.success) {
        const data = response.data.data
        setProfile(data)
        setFormData({
          name: data.name,
          email: data.email,
          current_password: '',
          new_password: '',
          new_password_confirmation: ''
        })
      } else {
        throw new Error(response.data.message || 'プロフィールの取得に失敗しました')
      }
    } catch (err) {
      console.error('Profile fetch error:', err)
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

    // パスワード変更時のバリデーション
    if (formData.new_password) {
      if (!formData.current_password) {
        alert('現在のパスワードを入力してください')
        return
      }
      if (formData.new_password !== formData.new_password_confirmation) {
        alert('新しいパスワードが一致しません')
        return
      }
      if (formData.new_password.length < 8) {
        alert('新しいパスワードは8文字以上にしてください')
        return
      }
    }

    try {
      setSubmitting(true)
      const updateData = {
        name: formData.name,
        email: formData.email
      }

      // パスワード変更が指定されている場合のみ含める
      if (formData.new_password) {
        updateData.current_password = formData.current_password
        updateData.new_password = formData.new_password
        updateData.new_password_confirmation = formData.new_password_confirmation
      }

      const response = await profileAPI.update(updateData)
      if (response.data.success) {
        alert('プロフィールを更新しました')
        setIsEditing(false)
        // パスワードフィールドをクリア
        setFormData(prev => ({
          ...prev,
          current_password: '',
          new_password: '',
          new_password_confirmation: ''
        }))
        fetchProfile() // 最新の情報を再取得
      } else {
        throw new Error(response.data.message || 'プロフィールの更新に失敗しました')
      }
    } catch (err) {
      console.error('Profile update error:', err)
      alert('プロフィールの更新に失敗しました: ' + err.message)
    } finally {
      setSubmitting(false)
    }
  }

  const handleCancel = () => {
    setIsEditing(false)
    if (profile) {
      setFormData({
        name: profile.name,
        email: profile.email,
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      })
    }
  }

  if (loading) {
    return (
      <div className="profile-page">
        <div className="loading">読み込み中...</div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="profile-page">
        <div className="error">{error}</div>
      </div>
    )
  }

  return (
    <div className="profile-page">
      <div className="page-header">
        <h1>プロフィール</h1>
        {!isEditing && (
          <button className="edit-btn" onClick={() => setIsEditing(true)}>
            編集
          </button>
        )}
      </div>

      {isEditing ? (
        <div className="profile-form-container">
          <form onSubmit={handleSubmit} className="profile-form">
            <div className="form-group">
              <label htmlFor="name">ユーザー名</label>
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleInputChange}
                disabled={submitting}
                required
              />
            </div>

            <div className="form-group">
              <label htmlFor="email">メールアドレス</label>
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleInputChange}
                disabled={submitting}
                required
              />
            </div>

            <div className="form-section">
              <h3>パスワード変更（任意）</h3>
              <p className="form-note">パスワードを変更する場合のみ入力してください</p>

              <div className="form-group">
                <label htmlFor="current_password">現在のパスワード</label>
                <input
                  type="password"
                  id="current_password"
                  name="current_password"
                  value={formData.current_password}
                  onChange={handleInputChange}
                  disabled={submitting}
                  placeholder="現在のパスワードを入力"
                />
              </div>

              <div className="form-group">
                <label htmlFor="new_password">新しいパスワード</label>
                <input
                  type="password"
                  id="new_password"
                  name="new_password"
                  value={formData.new_password}
                  onChange={handleInputChange}
                  disabled={submitting}
                  placeholder="8文字以上"
                />
              </div>

              <div className="form-group">
                <label htmlFor="new_password_confirmation">新しいパスワード（確認）</label>
                <input
                  type="password"
                  id="new_password_confirmation"
                  name="new_password_confirmation"
                  value={formData.new_password_confirmation}
                  onChange={handleInputChange}
                  disabled={submitting}
                  placeholder="もう一度入力"
                />
              </div>
            </div>

            <div className="form-actions">
              <button
                type="button"
                className="cancel-btn"
                onClick={handleCancel}
                disabled={submitting}
              >
                キャンセル
              </button>
              <button
                type="submit"
                className="submit-btn"
                disabled={submitting}
              >
                {submitting ? '保存中...' : '保存'}
              </button>
            </div>
          </form>
        </div>
      ) : (
        <div className="profile-view">
          <div className="profile-section">
            <h2>基本情報</h2>
            <div className="profile-info-grid">
              <div className="info-item">
                <div className="info-label">ユーザー名</div>
                <div className="info-value">{profile?.name}</div>
              </div>
              <div className="info-item">
                <div className="info-label">メールアドレス</div>
                <div className="info-value">{profile?.email}</div>
              </div>
              <div className="info-item">
                <div className="info-label">アカウント作成日</div>
                <div className="info-value">
                  {profile?.created_at && new Date(profile.created_at).toLocaleDateString('ja-JP')}
                </div>
              </div>
            </div>
          </div>

          <div className="profile-section">
            <h2>コイン情報</h2>
            <div className="profile-info-grid">
              <div className="info-item">
                <div className="info-label">現在の保有コイン</div>
                <div className="info-value coin-value">
                  {formatCurrency(profile?.current_coins || 0)}
                </div>
              </div>
              <div className="info-item">
                <div className="info-label">累計獲得コイン</div>
                <div className="info-value coin-value">
                  {formatCurrency(profile?.total_earned_coins || 0)}
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default Profile
