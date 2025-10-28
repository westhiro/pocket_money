import React, { useState, useEffect } from 'react'
import { formatCurrency, formatNumber } from '../utils/format'
import { tradingAPI } from '../services/api'
import { useAuth } from '../contexts/AuthContext'
import './TradingModal.css'

const TradingModal = ({ 
  isOpen, 
  onClose, 
  stock, 
  tradeType, // 'buy' or 'sell'
  currentHoldings = 0 
}) => {
  const { user, updateCoinBalance } = useAuth()
  const [quantity, setQuantity] = useState(1)
  const [totalPrice, setTotalPrice] = useState(0)
  const [profit, setProfit] = useState(0)
  const [loading, setLoading] = useState(false)
  const [showResultModal, setShowResultModal] = useState(false)
  const [resultMessage, setResultMessage] = useState('')
  const [resultType, setResultType] = useState('success') // 'success' or 'error'

  useEffect(() => {
    if (stock) {
      const total = stock.price * quantity
      setTotalPrice(total)
      
      // 売却時の利益計算（仮の購入価格から計算）
      if (tradeType === 'sell') {
        const avgPurchasePrice = stock.price * 0.9 // 仮の平均取得価格
        const profitAmount = (stock.price - avgPurchasePrice) * quantity
        setProfit(profitAmount)
      }
    }
  }, [stock, quantity, tradeType])

  const handleQuantityChange = (e) => {
    const value = parseInt(e.target.value) || 0
    if (tradeType === 'sell' && value > currentHoldings) {
      setQuantity(currentHoldings)
    } else if (value >= 0) {
      setQuantity(value)
    }
  }

  const handleConfirm = async () => {
    if (!user || !user.id) {
      setResultMessage('ユーザー認証が必要です')
      setResultType('error')
      setShowResultModal(true)
      return
    }

    setLoading(true)
    try {
      const response = await tradingAPI.trade(stock.id, tradeType, quantity, user.id)
      
      if (response.data.success) {
        const action = tradeType === 'buy' ? '購入' : '売却'
        
        // コイン残高を更新
        updateCoinBalance(response.data.data.remaining_coins)
        
        setResultMessage(
          `${stock.company}を${quantity}株${action}しました！\n` +
          `総額: ${formatCurrency(response.data.data.total_amount)}\n` +
          `残りコイン: ${formatCurrency(response.data.data.remaining_coins)}`
        )
        setResultType('success')
      } else {
        setResultMessage(response.data.message || '取引に失敗しました')
        setResultType('error')
      }
    } catch (error) {
      console.error('Trading error:', error)
      setResultMessage(
        error.response?.data?.message || 
        '取引処理中にエラーが発生しました'
      )
      setResultType('error')
    }
    
    setLoading(false)
    setShowResultModal(true)
  }

  const handleResultModalClose = () => {
    setShowResultModal(false)
    onClose() // メインモーダルも閉じる
  }

  const isValid = quantity > 0 && (tradeType === 'buy' || quantity <= currentHoldings)

  if (!isOpen || !stock) return null

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="trading-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>
            {tradeType === 'buy' ? '📈 購入' : '📉 売却'} - {stock.company}
          </h2>
          <button className="close-button" onClick={onClose}>
            ✕
          </button>
        </div>

        <div className="modal-body">
          <div className="stock-info-section">
            <div className="stock-details">
              <div className="stock-name">{stock.company}</div>
              <div className="stock-code">{stock.code}</div>
            </div>
            <div className="price-info">
              <div className="current-price">{formatCurrency(stock.price)}</div>
              <div className={`price-change ${stock.change >= 0 ? 'positive' : 'negative'}`}>
                {stock.change >= 0 ? '+' : ''}{stock.change} ({stock.changePercent >= 0 ? '+' : ''}{stock.changePercent}%)
              </div>
            </div>
          </div>

          {tradeType === 'sell' && (
            <div className="holdings-info">
              <span className="holdings-label">保有株数:</span>
              <span className="holdings-value">{formatNumber(currentHoldings)}株</span>
            </div>
          )}

          <div className="quantity-section">
            <label htmlFor="quantity">数量</label>
            <div className="quantity-input-container">
              <button 
                className="quantity-btn"
                onClick={() => handleQuantityChange({ target: { value: quantity - 1 } })}
                disabled={quantity <= 1}
              >
                -
              </button>
              <input
                id="quantity"
                type="number"
                value={quantity}
                onChange={handleQuantityChange}
                min="1"
                max={tradeType === 'sell' ? currentHoldings : 9999}
                className="quantity-input"
              />
              <button 
                className="quantity-btn"
                onClick={() => handleQuantityChange({ target: { value: quantity + 1 } })}
                disabled={tradeType === 'sell' && quantity >= currentHoldings}
              >
                +
              </button>
            </div>
            <div className="quantity-buttons">
              <button 
                className="preset-btn"
                onClick={() => setQuantity(10)}
              >
                10株
              </button>
              <button 
                className="preset-btn"
                onClick={() => setQuantity(100)}
              >
                100株
              </button>
              {tradeType === 'sell' && (
                <button 
                  className="preset-btn all-btn"
                  onClick={() => setQuantity(currentHoldings)}
                >
                  全株
                </button>
              )}
            </div>
          </div>

          <div className="calculation-section">
            <div className="calc-row">
              <span className="calc-label">単価:</span>
              <span className="calc-value">{formatCurrency(stock.price)}</span>
            </div>
            <div className="calc-row">
              <span className="calc-label">数量:</span>
              <span className="calc-value">{formatNumber(quantity)}株</span>
            </div>
            <div className="calc-row total">
              <span className="calc-label">
                {tradeType === 'buy' ? '購入金額:' : '売却金額:'}
              </span>
              <span className="calc-value total-amount">
                {formatCurrency(totalPrice)}
              </span>
            </div>
            
            {tradeType === 'sell' && (
              <div className="calc-row profit">
                <span className="calc-label">予想利益:</span>
                <span className={`calc-value ${profit >= 0 ? 'positive' : 'negative'}`}>
                  {profit >= 0 ? '+' : ''}{formatCurrency(profit)}
                </span>
              </div>
            )}
          </div>
        </div>

        <div className="modal-footer">
          <button className="cancel-btn" onClick={onClose}>
            キャンセル
          </button>
          <button 
            className={`confirm-btn ${tradeType === 'buy' ? 'buy' : 'sell'}`}
            onClick={handleConfirm}
            disabled={!isValid || loading}
          >
            {loading ? '処理中...' : (tradeType === 'buy' ? '購入確定' : '売却確定')}
          </button>
        </div>
      </div>

      {/* 取引結果モーダル */}
      {showResultModal && (
        <div className="result-modal-overlay">
          <div className="result-modal">
            <div className="result-header">
              <div className={`result-icon ${resultType}`}>
                {resultType === 'success' ? '✅' : '❌'}
              </div>
              <h3>{resultType === 'success' ? '取引完了' : '取引エラー'}</h3>
            </div>
            <div className="result-body">
              <p className="result-message">
                {resultMessage.split('\n').map((line, index) => (
                  <span key={index}>
                    {line}
                    {index < resultMessage.split('\n').length - 1 && <br />}
                  </span>
                ))}
              </p>
            </div>
            <div className="result-footer">
              <button 
                className={`result-btn ${resultType}`}
                onClick={handleResultModalClose}
              >
                OK
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default TradingModal