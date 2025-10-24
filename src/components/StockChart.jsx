import React, { useState, useEffect } from 'react'
import { formatCurrency, formatNumber } from '../data/mockData'
import { stocksAPI } from '../services/api'
import './StockChart.css'

const StockChart = ({ stock, onBuy, onSell }) => {
  const [chartData, setChartData] = useState([])
  const [selectedPeriod, setSelectedPeriod] = useState('1w')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState(null)

  // チャートデータを取得
  useEffect(() => {
    const fetchChartData = async () => {
      if (!stock?.id) return
      
      try {
        setLoading(true)
        setError(null)
        const response = await stocksAPI.getChart(stock.id, selectedPeriod)
        
        if (response.data.success) {
          const chartDataArray = response.data.data.chart_data.map(item => item.price)
          setChartData(chartDataArray)
        } else {
          throw new Error(response.data.message || 'チャートデータの取得に失敗しました')
        }
      } catch (err) {
        console.error('Chart data fetch error:', err)
        setError(err.message)
        // エラー時はフォールバック用の仮データを使用
        setChartData(stock.chartData || [])
      } finally {
        setLoading(false)
      }
    }

    fetchChartData()
  }, [stock?.id, selectedPeriod])

  if (!stock) return null

  const currentChartData = chartData.length > 0 ? chartData : (stock.chartData || [])
  const maxPrice = Math.max(...currentChartData)
  const minPrice = Math.min(...currentChartData)
  const priceRange = maxPrice - minPrice || 1 // 0除算回避
  
  const chartWidth = 300
  const chartHeight = 150
  const padding = 20

  const generatePath = () => {
    if (currentChartData.length === 0) return ''
    
    return currentChartData.map((price, index) => {
      const x = (index / (currentChartData.length - 1)) * (chartWidth - 2 * padding) + padding
      const y = chartHeight - padding - ((price - minPrice) / priceRange) * (chartHeight - 2 * padding)
      return `${index === 0 ? 'M' : 'L'} ${x} ${y}`
    }).join(' ')
  }

  // 期間表示名のマッピング
  const periodNames = {
    '1w': '1週間',
    '2w': '半月',
    '1m': '1ヶ月'
  }

  const handlePeriodChange = (period) => {
    setSelectedPeriod(period)
  }

  const isPositive = stock.change >= 0

  return (
    <div className="stock-chart">
      <div className="chart-header">
        <div className="stock-detail">
          <h3>{stock.company}</h3>
          <div className="stock-code-large">{stock.code}</div>
          <div className="sector-info">{stock.sector}</div>
        </div>
        <div className="price-info">
          <div className="current-price">{formatCurrency(stock.price)}</div>
          <div className={`price-change ${isPositive ? 'positive' : 'negative'}`}>
            {isPositive ? '+' : ''}{stock.change} ({isPositive ? '+' : ''}{stock.changePercent}%)
          </div>
        </div>
      </div>

      <div className="chart-container">
        <div className="chart-title">
          {periodNames[selectedPeriod]}の株価推移
          {loading && <span className="loading-text"> (読み込み中...)</span>}
          {error && <span className="error-text"> (エラー: {error})</span>}
        </div>
        
        <div className="period-buttons">
          {Object.entries(periodNames).map(([period, name]) => (
            <button 
              key={period}
              className={`period-btn ${selectedPeriod === period ? 'active' : ''}`}
              onClick={() => handlePeriodChange(period)}
              disabled={loading}
            >
              {name}
            </button>
          ))}
        </div>
        <svg 
          width={chartWidth} 
          height={chartHeight} 
          viewBox={`0 0 ${chartWidth} ${chartHeight}`}
          className="price-chart"
        >
          <defs>
            <linearGradient id="stockGradient" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stopColor={isPositive ? "#4CAF50" : "#f44336"} stopOpacity="0.3"/>
              <stop offset="100%" stopColor={isPositive ? "#4CAF50" : "#f44336"} stopOpacity="0.1"/>
            </linearGradient>
            <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" stopColor="#2196F3"/>
              <stop offset="100%" stopColor="#1976D2"/>
            </linearGradient>
          </defs>
          
          <rect 
            width={chartWidth} 
            height={chartHeight} 
            fill="#fafafa" 
            stroke="#e0e0e0" 
            strokeWidth="1"
            rx="8"
          />
          
          <path
            d={`${generatePath()} L ${chartWidth - padding} ${chartHeight - padding} L ${padding} ${chartHeight - padding} Z`}
            fill="url(#stockGradient)"
          />
          
          <path
            d={generatePath()}
            stroke="url(#lineGradient)"
            strokeWidth="2"
            fill="none"
          />
          
          {currentChartData.map((price, index) => {
            if (currentChartData.length === 0) return null
            const x = (index / (currentChartData.length - 1)) * (chartWidth - 2 * padding) + padding
            const y = chartHeight - padding - ((price - minPrice) / priceRange) * (chartHeight - 2 * padding)
            return (
              <circle
                key={index}
                cx={x}
                cy={y}
                r="3"
                fill="#2196F3"
                stroke="white"
                strokeWidth="2"
              />
            )
          })}
        </svg>
        
        <div className="chart-labels">
          <span>{periodNames[selectedPeriod]}前</span>
          <span>現在</span>
        </div>
      </div>

      <div className="stock-details">
        <div className="detail-row">
          <span className="detail-label">出来高:</span>
          <span className="detail-value">{formatNumber(stock.volume)}</span>
        </div>
        <div className="detail-row">
          <span className="detail-label">時価総額:</span>
          <span className="detail-value">{stock.marketCap}</span>
        </div>
        <div className="detail-row">
          <span className="detail-label">高値:</span>
          <span className="detail-value">{formatCurrency(maxPrice)}</span>
        </div>
        <div className="detail-row">
          <span className="detail-label">安値:</span>
          <span className="detail-value">{formatCurrency(minPrice)}</span>
        </div>
      </div>

      <div className="chart-actions">
        <button 
          className="chart-buy-btn"
          onClick={onBuy}
        >
          🛒 買い注文
        </button>
        <button 
          className="chart-sell-btn"
          onClick={onSell}
        >
          💰 売り注文
        </button>
      </div>
    </div>
  )
}

export default StockChart