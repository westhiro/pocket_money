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

  // 初回ローディング時にlocalStorageとサーバーからユーザー情報を取得
  useEffect(() => {
    const checkAuth = async () => {
      try {
        const storedUser = localStorage.getItem('user');
        const loginTime = localStorage.getItem('loginTime');

        if (storedUser && loginTime) {
          // 24時間（86400000ミリ秒）経過チェック
          const now = Date.now();
          const elapsed = now - parseInt(loginTime);
          const twentyFourHours = 24 * 60 * 60 * 1000;

          if (elapsed > twentyFourHours) {
            // 24時間超過 → 強制ログアウト
            console.log('Session expired (24 hours)');
            localStorage.removeItem('user');
            localStorage.removeItem('loginTime');
            setUser(null);
            setIsAuthenticated(false);
          } else {
            // 24時間以内 → ログイン状態を維持
            const userData = JSON.parse(storedUser);
            setUser(userData);
            setIsAuthenticated(true);

            // サーバーから最新のユーザー情報を取得（失敗しても無視）
            try {
              const response = await authAPI.getMe();
              if (response.data && response.data.success) {
                const latestUserData = response.data.data;
                // 最新データでstateとlocalStorageを更新
                setUser(latestUserData);
                localStorage.setItem('user', JSON.stringify(latestUserData));
              }
            } catch (apiError) {
              // API取得失敗時はlocalStorageのデータを使用（401も無視）
              console.log('Failed to fetch latest user data:', apiError);
            }
          }
        }
      } catch (error) {
        console.log('Not authenticated');
        localStorage.removeItem('user');
        localStorage.removeItem('loginTime');
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
      const response = await authAPI.login(credentials);
      if (response.data.success) {
        const userData = response.data.data.user;
        setUser(userData);
        setIsAuthenticated(true);
        // localStorageに保存（ログイン時刻も記録）
        localStorage.setItem('user', JSON.stringify(userData));
        localStorage.setItem('loginTime', Date.now().toString());
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
      const response = await authAPI.register(userData);
      if (response.data.success) {
        const newUser = response.data.data.user;
        setUser(newUser);
        setIsAuthenticated(true);
        // localStorageに保存（ログイン時刻も記録）
        localStorage.setItem('user', JSON.stringify(newUser));
        localStorage.setItem('loginTime', Date.now().toString());
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
      // localStorageから削除
      localStorage.removeItem('user');
      localStorage.removeItem('loginTime');
      setUser(null);
      setIsAuthenticated(false);
    }
  };

  // ユーザー情報を更新
  const updateUser = (updatedUserData) => {
    setUser(prevUser => {
      const updated = {
        ...prevUser,
        ...updatedUserData
      };
      // localStorageも更新
      localStorage.setItem('user', JSON.stringify(updated));
      return updated;
    });
  };

  // コイン残高を更新
  const updateCoinBalance = (newBalance) => {
    setUser(prevUser => {
      const updated = {
        ...prevUser,
        current_coins: newBalance
      };
      // localStorageも更新
      localStorage.setItem('user', JSON.stringify(updated));
      return updated;
    });
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