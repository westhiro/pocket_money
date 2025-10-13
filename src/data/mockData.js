// ニュースデータ
export const newsData = [
  {
    id: 1,
    title: "今週の市場動向：テック株が好調な推移を見せています",
    time: "2時間前",
    category: "市場分析"
  },
  {
    id: 2,
    title: "投資の基本：分散投資の重要性について学ぼう",
    time: "5時間前",
    category: "投資教育"
  },
  {
    id: 3,
    title: "新規上場銘柄のご紹介：成長が期待される企業",
    time: "1日前",
    category: "銘柄情報"
  }
]

// 学習進捗データ
export const learningData = {
  currentVideo: {
    title: "投資の基本：リスクとリターンを理解しよう",
    progress: 65,
    duration: "15分",
    completed: "9分"
  },
  recentCourses: [
    { title: "株式投資入門", progress: 100 },
    { title: "分散投資の重要性", progress: 80 },
    { title: "配当金について学ぶ", progress: 45 }
  ]
}

// 資産データ
export const assetsData = {
  totalAssets: 1250000,
  assetAllocation: [
    { name: '株式', value: 750000, percentage: 60, color: '#2196F3' },
    { name: '現金', value: 350000, percentage: 28, color: '#4CAF50' },
    { name: '債券', value: 150000, percentage: 12, color: '#FF9800' }
  ],
  performanceData: [
    { month: '1月', value: 1000000 },
    { month: '2月', value: 1050000 },
    { month: '3月', value: 1100000 },
    { month: '4月', value: 1080000 },
    { month: '5月', value: 1200000 },
    { month: '6月', value: 1250000 }
  ]
}

// 株式データ
export const stocksData = [
  {
    id: 1,
    company: 'トヨタ自動車',
    code: '7203',
    shares: 100,
    price: 2850,
    change: 45,
    changePercent: 1.6,
    totalValue: 285000
  },
  {
    id: 2,
    company: 'ソフトバンクG',
    code: '9984',
    shares: 50,
    price: 7240,
    change: -120,
    changePercent: -1.6,
    totalValue: 362000
  },
  {
    id: 3,
    company: '任天堂',
    code: '7974',
    shares: 20,
    price: 5890,
    change: 80,
    changePercent: 1.4,
    totalValue: 117800
  },
  {
    id: 4,
    company: 'キーエンス',
    code: '6861',
    shares: 5,
    price: 68500,
    change: -500,
    changePercent: -0.7,
    totalValue: 342500
  }
]

// 投資ページ用の銘柄データ
export const investmentStocksData = [
  {
    id: 1,
    company: 'トヨタ自動車',
    code: '7203',
    sector: '自動車・輸送機器',
    price: 2850,
    change: 45,
    changePercent: 1.6,
    volume: 15420000,
    marketCap: '28.5兆円',
    chartData: [2680, 2720, 2750, 2800, 2830, 2850]
  },
  {
    id: 2,
    company: 'ソフトバンクグループ',
    code: '9984',
    sector: '情報・通信業',
    price: 7240,
    change: -120,
    changePercent: -1.6,
    volume: 8930000,
    marketCap: '11.8兆円',
    chartData: [7400, 7350, 7300, 7280, 7260, 7240]
  },
  {
    id: 3,
    company: '任天堂',
    code: '7974',
    sector: 'その他製品',
    price: 5890,
    change: 80,
    changePercent: 1.4,
    volume: 2150000,
    marketCap: '7.7兆円',
    chartData: [5750, 5800, 5820, 5850, 5870, 5890]
  },
  {
    id: 4,
    company: 'キーエンス',
    code: '6861',
    sector: '電気機器',
    price: 68500,
    change: -500,
    changePercent: -0.7,
    volume: 185000,
    marketCap: '6.5兆円',
    chartData: [69200, 69000, 68800, 68600, 68500, 68500]
  },
  {
    id: 5,
    company: 'ファーストリテイリング',
    code: '9983',
    sector: '小売業',
    price: 89400,
    change: 1200,
    changePercent: 1.4,
    volume: 420000,
    marketCap: '10.5兆円',
    chartData: [87500, 88000, 88500, 89000, 89200, 89400]
  },
  {
    id: 6,
    company: '信越化学工業',
    code: '4063',
    sector: '化学',
    price: 24850,
    change: -180,
    changePercent: -0.7,
    volume: 890000,
    marketCap: '10.7兆円',
    chartData: [25200, 25100, 25000, 24950, 24900, 24850]
  },
  {
    id: 7,
    company: 'ASML Holding',
    code: 'ASML',
    sector: '半導体製造装置',
    price: 85200,
    change: 2400,
    changePercent: 2.9,
    volume: 320000,
    marketCap: '$350B',
    chartData: [80000, 81500, 83000, 84200, 84800, 85200]
  },
  {
    id: 8,
    company: 'Tesla',
    code: 'TSLA',
    sector: '自動車・EV',
    price: 42800,
    change: -850,
    changePercent: -1.9,
    volume: 2100000,
    marketCap: '$1.3T',
    chartData: [44500, 44000, 43500, 43200, 43000, 42800]
  }
]

// 人気銘柄（ランキング用）
export const popularStocks = [
  { code: '7203', company: 'トヨタ自動車', trend: 'up' },
  { code: '9984', company: 'ソフトバンクG', trend: 'down' },
  { code: '7974', company: '任天堂', trend: 'up' },
  { code: '6861', company: 'キーエンス', trend: 'down' },
  { code: '9983', company: 'ファストリ', trend: 'up' }
]

// 学習動画データ
export const learningVideosData = [
  {
    id: 1,
    title: "投資の基本：リスクとリターンを理解しよう",
    description: "投資におけるリスクとリターンの関係について学びます",
    duration: "15分",
    progress: 65,
    category: "基礎",
    level: "初級",
    thumbnail: "💰",
    isWatching: true
  },
  {
    id: 2,
    title: "株式投資入門：株とは何か？",
    description: "株式の仕組みと株式投資の基本を学習します",
    duration: "12分",
    progress: 100,
    category: "基礎",
    level: "初級",
    thumbnail: "📈"
  },
  {
    id: 3,
    title: "分散投資の重要性",
    description: "リスクを分散させる投資手法について詳しく解説",
    duration: "18分",
    progress: 80,
    category: "戦略",
    level: "中級",
    thumbnail: "📊"
  },
  {
    id: 4,
    title: "配当金について学ぶ",
    description: "配当金の仕組みと配当投資の魅力を解説します",
    duration: "14分",
    progress: 45,
    category: "基礎",
    level: "初級",
    thumbnail: "💎"
  },
  {
    id: 5,
    title: "チャート分析の基本",
    description: "株価チャートの読み方と分析手法を学習",
    duration: "22分",
    progress: 30,
    category: "分析",
    level: "中級",
    thumbnail: "📉"
  },
  {
    id: 6,
    title: "投資信託とETFの違い",
    description: "投資信託とETFの特徴と使い分けについて",
    duration: "16分",
    progress: 0,
    category: "商品",
    level: "中級",
    thumbnail: "🏦"
  },
  {
    id: 7,
    title: "NISA制度を活用しよう",
    description: "NISA・つみたてNISAの仕組みと活用法",
    duration: "20分",
    progress: 75,
    category: "制度",
    level: "初級",
    thumbnail: "📋"
  },
  {
    id: 8,
    title: "企業分析の方法",
    description: "財務諸表の読み方と企業評価の基本",
    duration: "25分",
    progress: 15,
    category: "分析",
    level: "上級",
    thumbnail: "🔍"
  },
  {
    id: 9,
    title: "長期投資の心構え",
    description: "長期投資の考え方とメンタル管理について",
    duration: "13分",
    progress: 90,
    category: "心構え",
    level: "初級",
    thumbnail: "🎯"
  },
  {
    id: 10,
    title: "暗号資産（仮想通貨）入門",
    description: "ビットコインなど暗号資産の基本知識",
    duration: "19分",
    progress: 10,
    category: "商品",
    level: "中級",
    thumbnail: "⚡"
  }
]

// 学習カテゴリー
export const learningCategories = [
  { id: 'all', name: '全て', count: 10 },
  { id: 'basic', name: '基礎', count: 4 },
  { id: 'strategy', name: '戦略', count: 1 },
  { id: 'analysis', name: '分析', count: 2 },
  { id: 'products', name: '商品', count: 2 },
  { id: 'system', name: '制度', count: 1 }
]

// 学習統計
export const learningStats = {
  totalVideos: 10,
  completedVideos: 2,
  totalWatchTime: 174, // 分
  watchedTime: 95, // 分
  currentStreak: 5, // 連続学習日数
  totalPoints: 2400
}

// ユーティリティ関数
export const formatCurrency = (amount) => {
  return new Intl.NumberFormat('ja-JP', {
    style: 'currency',
    currency: 'JPY',
    maximumFractionDigits: 0
  }).format(amount)
}

export const formatNumber = (amount) => {
  return new Intl.NumberFormat('ja-JP').format(amount)
}