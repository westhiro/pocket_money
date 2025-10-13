import React, { createContext, useContext, useState, useEffect } from 'react';
import { authAPI, getCSRFToken } from '../services/api';

const AuthContext = createContext();

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // 初回ローディング時にCSRFトークンを取得してユーザー情報を取得
  useEffect(() => {
    const checkAuth = async () => {
      try {
        // CSRFトークンを先に取得
        await getCSRFToken();
        
        const response = await authAPI.getMe();
        if (response.data.success) {
          setUser(response.data.data);
          setIsAuthenticated(true);
        }
      } catch (error) {
        console.log('Not authenticated');
        setUser(null);
        setIsAuthenticated(false);
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, []);

  // ログイン
  const login = async (credentials) => {
    try {
      // ログイン前にCSRFトークンを取得
      await getCSRFToken();
      
      const response = await authAPI.login(credentials);
      if (response.data.success) {
        setUser(response.data.data.user);
        setIsAuthenticated(true);
        return { success: true, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || 'ログインに失敗しました';
      return { success: false, message };
    }
  };

  // 新規登録
  const register = async (userData) => {
    try {
      // 新規登録前にCSRFトークンを取得
      await getCSRFToken();
      
      const response = await authAPI.register(userData);
      if (response.data.success) {
        setUser(response.data.data.user);
        setIsAuthenticated(true);
        return { success: true, message: response.data.message };
      }
    } catch (error) {
      const message = error.response?.data?.message || '登録に失敗しました';
      return { success: false, message, errors: error.response?.data?.errors };
    }
  };

  // ログアウト
  const logout = async () => {
    try {
      await authAPI.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      setUser(null);
      setIsAuthenticated(false);
    }
  };

  // ユーザー情報を更新
  const updateUser = (updatedUserData) => {
    setUser(prevUser => ({
      ...prevUser,
      ...updatedUserData
    }));
  };

  // コイン残高を更新
  const updateCoinBalance = (newBalance) => {
    setUser(prevUser => ({
      ...prevUser,
      coin_balance: newBalance
    }));
  };

  const value = {
    user,
    isAuthenticated,
    loading,
    login,
    register,
    logout,
    updateUser,
    updateCoinBalance,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};