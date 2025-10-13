import axios from 'axios';

// APIベースURL  
const API_BASE_URL = 'http://localhost:8000/api';

// axiosインスタンス作成
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // セッション認証のため
});

// レスポンスインターセプター
api.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error);
    return Promise.reject(error);
  }
);

// 株式関連API
export const stocksAPI = {
  // 全株式データ取得
  getAll: () => api.get('/stocks'),
  
  // 個別株式詳細取得
  getById: (id) => api.get(`/stocks/${id}`),
  
  // チャートデータ取得（期間別）
  getChart: (id, period = '1m') => api.get(`/stocks/${id}/chart/${period}`),
  
  // 業界別株式取得
  getByIndustry: (industryId) => api.get(`/stocks/industry/${industryId}`),
};

// CSRFトークン取得とヘッダー設定
export const getCSRFToken = async () => {
  try {
    const response = await api.get('/csrf-token');
    const token = response.data.csrf_token;
    // CSRFトークンをヘッダーに設定
    api.defaults.headers.common['X-CSRF-TOKEN'] = token;
    return token;
  } catch (error) {
    console.log('CSRF token fetch failed:', error);
  }
};

// ユーザー関連API
export const userAPI = {
  // ユーザー資産取得
  getAssets: () => api.get('/user/assets'),
  
  // ユーザー保有株取得  
  getStocks: () => api.get('/user/stocks'),
  
  // 株式購入
  buyStock: (stockId, quantity) => 
    api.post('/user/stocks/buy', { stock_id: stockId, quantity }),
  
  // 株式売却
  sellStock: (stockId, quantity) => 
    api.post('/user/stocks/sell', { stock_id: stockId, quantity }),
};

// 投資取引API
export const tradingAPI = {
  // 株式売買処理
  trade: (stockId, tradeType, quantity, userId = 1) => 
    api.post('/test/trade', { 
      user_id: userId,
      stock_id: stockId, 
      trade_type: tradeType, 
      quantity 
    }),
  
  // ポートフォリオ取得（認証あり）
  getPortfolio: () => api.get('/trading/portfolio'),
  
  // ポートフォリオ取得（テスト用）
  getPortfolioTest: (userId = 1) => api.get(`/test/portfolio/${userId}`),
  
  // 取引履歴取得
  getHistory: (limit = 50) => api.get(`/trading/history?limit=${limit}`),
};

// ニュース関連API
export const newsAPI = {
  // ニュース一覧取得
  getAll: (params = {}) => {
    const queryParams = new URLSearchParams()
    if (params.limit) queryParams.append('limit', params.limit)
    if (params.type) queryParams.append('type', params.type)
    return api.get(`/news?${queryParams}`)
  },
  
  // 最新ニュース取得（ホームページ用）
  getLatest: () => api.get('/news/latest'),
  
  // 個別ニュース取得
  getById: (id) => api.get(`/news/${id}`),
  
  // イベント関連ニュース取得
  getEventNews: () => api.get('/news/events'),
};

// 認証関連API
export const authAPI = {
  // 新規登録
  register: (userData) => api.post('/auth/register', userData),
  
  // ログイン
  login: (credentials) => api.post('/auth/login', credentials),
  
  // ログアウト
  logout: () => api.post('/auth/logout'),
  
  // ユーザー情報取得
  getMe: () => api.get('/user'),
};

export default api;