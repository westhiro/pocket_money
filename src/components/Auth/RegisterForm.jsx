import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import './RegisterForm.css';

const RegisterForm = ({ onSwitchToLogin, onClose }) => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [errors, setErrors] = useState({});
  const [isLoading, setIsLoading] = useState(false);
  const { register } = useAuth();

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    // エラーをクリア
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: null }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setErrors({});

    const result = await register(formData);
    
    if (result.success) {
      onClose();
    } else {
      setErrors({ 
        general: result.message,
        ...result.errors 
      });
    }
    
    setIsLoading(false);
  };

  return (
    <div className="register-form-container">
      <h2 className="register-form-title">新規登録</h2>

      {errors.general && (
        <div className="register-error-message">
          {errors.general}
        </div>
      )}

      <form onSubmit={handleSubmit} className="register-form">
        <div className="register-form-field">
          <label htmlFor="name" className="register-form-label">
            お名前
          </label>
          <input
            type="text"
            id="name"
            name="name"
            value={formData.name}
            onChange={handleChange}
            required
            className="register-form-input"
            placeholder="お名前を入力"
          />
          {errors.name && (
            <p className="register-field-error">{errors.name[0]}</p>
          )}
        </div>

        <div className="register-form-field">
          <label htmlFor="email" className="register-form-label">
            メールアドレス
          </label>
          <input
            type="email"
            id="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
            className="register-form-input"
            placeholder="example@email.com"
          />
          {errors.email && (
            <p className="register-field-error">{errors.email[0]}</p>
          )}
        </div>

        <div className="register-form-field">
          <label htmlFor="password" className="register-form-label">
            パスワード（6文字以上）
          </label>
          <input
            type="password"
            id="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            required
            className="register-form-input"
            placeholder="パスワードを入力"
          />
          {errors.password && (
            <p className="register-field-error">{errors.password[0]}</p>
          )}
        </div>

        <div className="register-form-field">
          <label htmlFor="password_confirmation" className="register-form-label">
            パスワード確認
          </label>
          <input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            value={formData.password_confirmation}
            onChange={handleChange}
            required
            className="register-form-input"
            placeholder="パスワードを再入力"
          />
        </div>

        <div className="register-bonus-box">
          <p className="register-bonus-text">
            🎉 新規登録すると<span className="register-bonus-highlight">10,000コイン</span>をプレゼント！
          </p>
        </div>

        <button
          type="submit"
          disabled={isLoading}
          className="register-form-button"
        >
          {isLoading ? '登録中...' : 'アカウント作成'}
        </button>
      </form>

      <div className="register-form-footer">
        <p className="register-form-footer-text">
          すでにアカウントをお持ちの方は{' '}
          <button
            onClick={onSwitchToLogin}
            className="register-form-link"
          >
            ログイン
          </button>
        </p>
      </div>
    </div>
  );
};

export default RegisterForm;