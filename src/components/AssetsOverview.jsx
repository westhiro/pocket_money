import React, { useState, useEffect } from 'react'
import { userAPI, tradingAPI } from '../services/api'
import { useAuth } from '../contexts/AuthContext'
import { formatCurrency } from '../utils/format'
import './AssetsOverview.css'

const AssetsOverview = () => {
  const { isAuthenticated } = useAuth()
  const [assetData, setAssetData] = useState(null)
  const [portfolioData, setPortfolioData] = useState(null)
  const [assetHistory, setAssetHistory] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    const fetchAssetData = async () => {
      if (!isAuthenticated) {
        setAssetData(null)
        setPortfolioData(null)
        setAssetHistory([])
        setLoading(false)
        return
      }

      try {
        setLoading(true)
        const [assetsResponse, portfolioResponse, historyResponse] = await Promise.all([
          userAPI.getAssets(),
          tradingAPI.getPortfolio(),
          userAPI.getAssetHistory(30)
        ])

        console.log('Assets response:', assetsResponse.data)
        console.log('Portfolio response:', portfolioResponse.data)
        console.log('History response:', historyResponse.data)

        setAssetData(assetsResponse.data.data)
        setPortfolioData(portfolioResponse.data.data)
        setAssetHistory(historyResponse.data.data || [])
      } catch (err) {
        console.error('Asset data fetch error:', err)
        console.error('Error details:', err.response?.data)
        setError('資産データの取得に失敗しました: ' + (err.response?.data?.message || err.message))
      } finally {
        setLoading(false)
      }
    }

    fetchAssetData()
  }, [isAuthenticated])

  if (!isAuthenticated) {
    return (
      <div className="assets-overview">
        <div className="assets-header">
          <h2>💰 保有資産</h2>
          <div className="auth-message">ログインして資産状況を確認しましょう</div>
        </div>
      </div>
    )
  }

  if (loading) {
    return (
      <div className="assets-overview">
        <div className="assets-header">
          <h2>💰 保有資産</h2>
          <div className="loading">データを読み込み中...</div>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="assets-overview">
        <div className="assets-header">
          <h2>💰 保有資産</h2>
          <div className="error">{error}</div>
        </div>
      </div>
    )
  }

  const totalAssets = assetData?.total_assets || 0
  const stockValue = assetData?.stock_value || 0
  const coinBalance = assetData?.current_coins || 0
  
  // 資産配分の計算
  const assetAllocation = []
  if (stockValue > 0) {
    assetAllocation.push({
      name: '株式',
      value: stockValue,
      percentage: Math.round((stockValue / totalAssets) * 100),
      color: '#2196F3'
    })
  }
  if (coinBalance > 0) {
    assetAllocation.push({
      name: 'コイン',
      value: coinBalance,
      percentage: Math.round((coinBalance / totalAssets) * 100),
      color: '#4CAF50'
    })
  }

  // 資産履歴データは既にstateで管理されています

  return (
    <div className="assets-overview">
      <div className="assets-header">
        <h2>💰 保有資産</h2>
        <div className="total-amount">{formatCurrency(totalAssets)}</div>
        {portfolioData && portfolioData.total_profit_loss !== 0 && (
          <div className={`profit-loss-summary ${portfolioData.total_profit_loss >= 0 ? 'positive' : 'negative'}`}>
            {portfolioData.total_profit_loss >= 0 ? '+' : ''}{formatCurrency(portfolioData.total_profit_loss)}
            <span className="profit-percent">
              ({portfolioData.total_profit_loss_percent >= 0 ? '+' : ''}{Math.round(portfolioData.total_profit_loss_percent)}%)
            </span>
          </div>
        )}
      </div>

      <div className="assets-content">
        <div className="asset-allocation">
          <h3>資産配分</h3>
          {assetAllocation.length > 0 ? (
            <>
              <div className="pie-chart">
                <div className="pie-chart-container">
                  <svg width="120" height="120" viewBox="0 0 120 120">
                    {assetAllocation.length === 1 ? (
                      <circle cx="60" cy="60" r="50" fill={assetAllocation[0].color} />
                    ) : (
                      assetAllocation.map((asset, index) => {
                        let cumulativePercentage = 0
                        for (let i = 0; i < index; i++) {
                          cumulativePercentage += assetAllocation[i].percentage
                        }
                        const startAngle = (cumulativePercentage / 100) * 360 - 90
                        const endAngle = ((cumulativePercentage + asset.percentage) / 100) * 360 - 90
                        
                        const x1 = 60 + 50 * Math.cos((startAngle * Math.PI) / 180)
                        const y1 = 60 + 50 * Math.sin((startAngle * Math.PI) / 180)
                        const x2 = 60 + 50 * Math.cos((endAngle * Math.PI) / 180)
                        const y2 = 60 + 50 * Math.sin((endAngle * Math.PI) / 180)
                        
                        const largeArcFlag = asset.percentage > 50 ? 1 : 0
                        
                        return (
                          <path
                            key={index}
                            d={`M 60 60 L ${x1} ${y1} A 50 50 0 ${largeArcFlag} 1 ${x2} ${y2} Z`}
                            fill={asset.color}
                          />
                        )
                      })
                    )}
                  </svg>
                </div>
              </div>
              <div className="allocation-legend">
                {assetAllocation.map((asset, index) => (
                  <div key={index} className="legend-item">
                    <span 
                      className="legend-color" 
                      style={{backgroundColor: asset.color}}
                    ></span>
                    <span className="legend-text">
                      {asset.name}: {asset.percentage}% ({formatCurrency(asset.value)})
                    </span>
                  </div>
                ))}
              </div>
            </>
          ) : (
            <div className="no-assets">資産データがありません</div>
          )}
        </div>

        <div className="portfolio-summary">
          <h3>保有銘柄サマリー</h3>
          {portfolioData && portfolioData.holdings.length > 0 ? (
            <div className="holdings-summary">
              <div className="summary-item">
                <span className="label">保有銘柄数:</span>
                <span className="value">{portfolioData.holdings.length}銘柄</span>
              </div>
              <div className="summary-item">
                <span className="label">投資額:</span>
                <span className="value">{formatCurrency(portfolioData.total_invested)}</span>
              </div>
              <div className="summary-item">
                <span className="label">評価額:</span>
                <span className="value">{formatCurrency(portfolioData.total_current_value)}</span>
              </div>
            </div>
          ) : (
            <div className="no-stocks">まだ株式を保有していません</div>
          )}
        </div>

        <div className="asset-history">
          <h3>資産推移（1ヶ月）</h3>
          {assetHistory.length > 0 ? (
            <>
              <div className="line-chart">
                <svg width="100%" height="100%" viewBox="0 0 300 100" preserveAspectRatio="none">
                  {/* グリッドライン */}
                  <line x1="0" y1="25" x2="300" y2="25" stroke="#e0e0e0" strokeWidth="0.5" />
                  <line x1="0" y1="50" x2="300" y2="50" stroke="#e0e0e0" strokeWidth="0.5" />
                  <line x1="0" y1="75" x2="300" y2="75" stroke="#e0e0e0" strokeWidth="0.5" />

                  {/* 折れ線グラフ */}
                  {assetHistory.length > 1 && (() => {
                    const maxValue = Math.max(...assetHistory.map(d => d.total_assets))
                    const minValue = Math.min(...assetHistory.map(d => d.total_assets))
                    const range = maxValue - minValue || 1

                    const points = assetHistory.map((point, index) => {
                      const x = (index / (assetHistory.length - 1)) * 300
                      const y = 90 - ((point.total_assets - minValue) / range) * 80
                      return `${x},${y}`
                    }).join(' ')

                    return (
                      <>
                        <polyline
                          points={points}
                          fill="none"
                          stroke="#2196F3"
                          strokeWidth="2"
                        />
                        {/* 面積の塗りつぶし */}
                        <polygon
                          points={`0,90 ${points} 300,90`}
                          fill="url(#gradient)"
                          opacity="0.2"
                        />
                        <defs>
                          <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stopColor="#2196F3" stopOpacity="0.6" />
                            <stop offset="100%" stopColor="#2196F3" stopOpacity="0" />
                          </linearGradient>
                        </defs>
                      </>
                    )
                  })()}
                </svg>
              </div>
              <div className="chart-labels">
                <span className="chart-label">{assetHistory[0]?.date}</span>
                <span className="chart-label">{assetHistory[Math.floor(assetHistory.length / 2)]?.date}</span>
                <span className="chart-label">{assetHistory[assetHistory.length - 1]?.date}</span>
              </div>
            </>
          ) : (
            <div className="no-assets">資産履歴データがありません</div>
          )}
        </div>
      </div>
    </div>
  )
}

export default AssetsOverview