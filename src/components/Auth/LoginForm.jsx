import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import './LoginForm.css';

const LoginForm = ({ onSwitchToRegister, onClose }) => {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  const [errors, setErrors] = useState({});
  const [isLoading, setIsLoading] = useState(false);
  const { login } = useAuth();

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

    const result = await login(formData);
    
    if (result.success) {
      onClose();
    } else {
      setErrors({ general: result.message });
    }
    
    setIsLoading(false);
  };

  return (
    <div className="login-form-container">
      <h2 className="login-form-title">ログイン</h2>

      {errors.general && (
        <div className="login-error-message">
          {errors.general}
        </div>
      )}

      <form onSubmit={handleSubmit} className="login-form">
        <div className="login-form-field">
          <label htmlFor="email" className="login-form-label">
            メールアドレス
          </label>
          <input
            type="email"
            id="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
            className="login-form-input"
            placeholder="example@email.com"
          />
        </div>

        <div className="login-form-field">
          <label htmlFor="password" className="login-form-label">
            パスワード
          </label>
          <input
            type="password"
            id="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            required
            className="login-form-input"
            placeholder="パスワードを入力"
          />
        </div>

        <button
          type="submit"
          disabled={isLoading}
          className="login-form-button"
        >
          {isLoading ? 'ログイン中...' : 'ログイン'}
        </button>
      </form>

      <div className="login-form-footer">
        <p className="login-form-footer-text">
          アカウントをお持ちでない方は{' '}
          <button
            onClick={onSwitchToRegister}
            className="login-form-link"
          >
            新規登録
          </button>
        </p>
      </div>
    </div>
  );
};

export default LoginForm;