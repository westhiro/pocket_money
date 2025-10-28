import React, { useState, useEffect } from 'react'
import TradingModal from './TradingModal'
import { userAPI, tradingAPI } from '../services/api'
import { useAuth } from '../contexts/AuthContext'
import { formatCurrency } from '../utils/format'
import './StockList.css'

const StockList = () => {
  const { isAuthenticated, user } = useAuth()
  const [modalState, setModalState] = useState({
    isOpen: false,
    stock: null,
    tradeType: null
  })
  
  const [stocks, setStocks] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  // ユーザーの保有銘柄データを取得
  useEffect(() => {
    const fetchUserStocks = async () => {
      if (!isAuthenticated) {
        setStocks([])
        setLoading(false)
        return
      }

      try {
        setLoading(true)
        console.log('Auth state:', { isAuthenticated, user })
        console.log('Cookies:', document.cookie)
        const response = await userAPI.getStocks()
        
        // APIデータを画面用フォーマットに変換
        const formattedStocks = response.data.data.map(stock => ({
          id: stock.id,
          company: stock.company_name,
          code: stock.stock_symbol,
          shares: stock.quantity,
          price: parseFloat(stock.current_price),
          change: stock.price_change || 0,
          changePercent: stock.price_change || 0,
          totalValue: stock.total_value,
          profitLoss: stock.profit_loss,
          profitLossPercent: stock.profit_loss_percent,
          averagePrice: stock.average_price,
          industry: stock.industry
        }))
        
        setStocks(formattedStocks)
      } catch (err) {
        const errorMessage = err.response?.status === 404 
          ? 'APIエンドポイントが見つかりません (404)' 
          : '保有株式データの取得に失敗しました: ' + err.message
        setError(errorMessage)
        console.error('User stocks fetch error:', err)
        console.error('Error details:', err.response)
      } finally {
        setLoading(false)
      }
    }

    fetchUserStocks()
  }, [isAuthenticated])

  const handleBuy = (stock) => {
    setModalState({
      isOpen: true,
      stock: stock,
      tradeType: 'buy'
    })
  }

  const handleSell = (stock) => {
    setModalState({
      isOpen: true,
      stock: stock,
      tradeType: 'sell'
    })
  }

  const closeModal = () => {
    setModalState({
      isOpen: false,
      stock: null,
      tradeType: null
    })
  }

  // 未認証時の表示
  if (!isAuthenticated) {
    return (
      <div className="stock-list">
        <div className="stock-header">
          <h2>📈 保有株式</h2>
        </div>
        <div className="auth-message">ログインして保有株式を確認しましょう</div>
      </div>
    )
  }

  // ローディング表示
  if (loading) {
    return (
      <div className="stock-list">
        <div className="stock-header">
          <h2>📈 保有株式</h2>
        </div>
        <div className="loading">データを読み込み中...</div>
      </div>
    )
  }

  // エラー表示
  if (error) {
    return (
      <div className="stock-list">
        <div className="stock-header">
          <h2>📈 保有株式</h2>
        </div>
        <div className="error">{error}</div>
      </div>
    )
  }

  // 保有株式がない場合
  if (stocks.length === 0) {
    return (
      <div className="stock-list">
        <div className="stock-header">
          <h2>📈 保有株式</h2>
        </div>
        <div className="no-stocks">まだ株式を保有していません</div>
      </div>
    )
  }

  return (
    <div className="stock-list">
      <div className="stock-header">
        <h2>📈 保有株式 ({stocks.length}銘柄)</h2>
      </div>
      
      <div className="stock-table-container">
        <table className="stock-table">
          <thead>
            <tr>
              <th>銘柄</th>
              <th>保有数</th>
              <th>現在価格</th>
              <th>評価額</th>
              <th>損益</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            {stocks.map(stock => (
              <tr key={stock.id}>
                <td>
                  <div className="company-info">
                    <div className="company-name">{stock.company}</div>
                    <div className="company-code">{stock.code}</div>
                  </div>
                </td>
                <td className="shares">{stock.shares}株</td>
                <td className="price">{formatCurrency(stock.price)}</td>
                <td className="total-value">{formatCurrency(stock.totalValue)}</td>
                <td className={`profit-loss ${stock.profitLoss >= 0 ? 'positive' : 'negative'}`}>
                  <div className="profit-amount">
                    {stock.profitLoss >= 0 ? '+' : ''}{formatCurrency(Math.abs(stock.profitLoss))}
                  </div>
                  <div className="profit-percent">
                    ({stock.profitLossPercent >= 0 ? '+' : ''}{Math.round(stock.profitLossPercent)}%)
                  </div>
                </td>
                <td>
                  <div className="action-buttons">
                    <button 
                      className="buy-button"
                      onClick={() => handleBuy(stock)}
                    >
                      追加購入
                    </button>
                    <button 
                      className="sell-button"
                      onClick={() => handleSell(stock)}
                    >
                      売却
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <TradingModal
        isOpen={modalState.isOpen}
        onClose={closeModal}
        stock={modalState.stock}
        tradeType={modalState.tradeType}
        currentHoldings={modalState.stock?.shares || 0}
      />
    </div>
  )
}

export default StockList