import React, { useState } from 'react';
import { useAuth } from '../../contexts/AuthContext';

// Updated: 2025-10-24 - CSS improvements applied
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
    <div className="max-w-md w-full mx-auto bg-white p-8 rounded-2xl shadow-2xl border border-gray-100">
      <h2 className="text-3xl font-bold text-center mb-8 text-blue-700">ログイン</h2>

      {errors.general && (
        <div className="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
          {errors.general}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
            メールアドレス
          </label>
          <input
            type="email"
            id="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
            placeholder="example@email.com"
          />
        </div>

        <div>
          <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
            パスワード
          </label>
          <input
            type="password"
            id="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            required
            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
            placeholder="パスワードを入力"
          />
        </div>

        <button
          type="submit"
          disabled={isLoading}
          className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 font-semibold text-base shadow-md hover:shadow-lg"
        >
          {isLoading ? 'ログイン中...' : 'ログイン'}
        </button>
      </form>

      <div className="text-center mt-6 pt-6 border-t border-gray-200">
        <p className="text-sm text-gray-600">
          アカウントをお持ちでない方は{' '}
          <button
            onClick={onSwitchToRegister}
            className="text-blue-600 hover:text-blue-700 font-medium hover:underline transition duration-200"
          >
            新規登録
          </button>
        </p>
      </div>
    </div>
  );
};

export default LoginForm;