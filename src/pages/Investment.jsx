import React, { useState, useEffect } from 'react'
import { formatCurrency, formatNumber, popularStocks } from '../data/mockData'
import { stocksAPI } from '../services/api'
import StockChart from '../components/StockChart'
import TradingModal from '../components/TradingModal'
import './Investment.css'

const Investment = () => {
  const [stocks, setStocks] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [selectedStock, setSelectedStock] = useState(null)
  const [sortBy, setSortBy] = useState('company')
  const [sortOrder, setSortOrder] = useState('asc')
  const [modalState, setModalState] = useState({
    isOpen: false,
    stock: null,
    tradeType: null
  })

  // APIから株式データを取得
  useEffect(() => {
    const fetchStocks = async () => {
      try {
        setLoading(true)
        const response = await stocksAPI.getAll()
        
        // APIデータを投資ページ用フォーマットに変換
        const formattedStocks = response.data.data.map(stock => ({
          id: stock.id,
          company: stock.company_name,
          code: stock.stock_symbol,
          sector: stock.industry.name,
          price: parseFloat(stock.current_price),
          change: stock.price_change || 0,
          changePercent: stock.price_change_percent || 0,
          volume: Math.floor(Math.random() * 10000000) + 100000, // 仮の出来高
          marketCap: `${Math.round(Math.random() * 50 + 5)}兆円`, // 仮の時価総額
          chartData: (stock.price_history || []).map(item => parseFloat(item.price))
        }))
        
        setStocks(formattedStocks)
        if (formattedStocks.length > 0) {
          setSelectedStock(formattedStocks[0])
        }
      } catch (err) {
        setError('株式データの取得に失敗しました: ' + err.message)
        console.error('Stock fetch error:', err)
      } finally {
        setLoading(false)
      }
    }

    fetchStocks()
  }, [])

  const handleSort = (column) => {
    if (sortBy === column) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc')
    } else {
      setSortBy(column)
      setSortOrder('asc')
    }
  }

  const sortedStocks = [...stocks].sort((a, b) => {
    let aVal = a[sortBy]
    let bVal = b[sortBy]
    
    if (typeof aVal === 'string') {
      aVal = aVal.toLowerCase()
      bVal = bVal.toLowerCase()
    }
    
    if (sortOrder === 'asc') {
      return aVal > bVal ? 1 : -1
    } else {
      return aVal < bVal ? 1 : -1
    }
  })

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

  // ローディング表示
  if (loading) {
    return (
      <div className="investment-page">
        <div className="investment-header">
          <h1>📊 投資・銘柄検索</h1>
        </div>
        <div className="loading" style={{ textAlign: 'center', padding: '50px' }}>
          データを読み込み中...
        </div>
      </div>
    )
  }

  // エラー表示
  if (error) {
    return (
      <div className="investment-page">
        <div className="investment-header">
          <h1>📊 投資・銘柄検索</h1>
        </div>
        <div className="error" style={{ textAlign: 'center', padding: '50px', color: 'red' }}>
          {error}
        </div>
      </div>
    )
  }

  return (
    <div className="investment-page">
      <div className="investment-header">
        <h1>📊 投資・銘柄検索</h1>
        <div className="market-summary">
          <div className="market-item">
            <span className="market-label">日経平均</span>
            <span className="market-value">32,850円</span>
            <span className="market-change positive">+150 (+0.46%)</span>
          </div>
          <div className="market-item">
            <span className="market-label">TOPIX</span>
            <span className="market-value">2,385</span>
            <span className="market-change negative">-8 (-0.33%)</span>
          </div>
        </div>
      </div>

      <div className="investment-content">
        <div className="stocks-section">
          <div className="section-header">
            <h2>銘柄一覧</h2>
            <div className="popular-stocks">
              <span className="popular-label">人気:</span>
              {popularStocks.map((stock, index) => (
                <span key={index} className={`popular-stock ${stock.trend}`}>
                  {stock.code}
                </span>
              ))}
            </div>
          </div>

          <div className="stocks-table-container">
            <table className="stocks-table">
              <thead>
                <tr>
                  <th onClick={() => handleSort('company')} className="sortable">
                    銘柄 {sortBy === 'company' && (sortOrder === 'asc' ? '↑' : '↓')}
                  </th>
                  <th>業種</th>
                  <th onClick={() => handleSort('price')} className="sortable">
                    株価 {sortBy === 'price' && (sortOrder === 'asc' ? '↑' : '↓')}
                  </th>
                  <th onClick={() => handleSort('changePercent')} className="sortable">
                    前日比 {sortBy === 'changePercent' && (sortOrder === 'asc' ? '↑' : '↓')}
                  </th>
                  <th onClick={() => handleSort('volume')} className="sortable">
                    出来高 {sortBy === 'volume' && (sortOrder === 'asc' ? '↑' : '↓')}
                  </th>
                  <th>時価総額</th>
                  <th>チャート</th>
                  <th>売買</th>
                </tr>
              </thead>
              <tbody>
                {sortedStocks.map(stock => (
                  <tr key={stock.id} className={selectedStock?.id === stock.id ? 'selected' : ''}>
                    <td>
                      <div className="stock-info">
                        <div className="stock-name">{stock.company}</div>
                        <div className="stock-code">{stock.code}</div>
                      </div>
                    </td>
                    <td className="sector">{stock.sector}</td>
                    <td className="price">{formatCurrency(stock.price)}</td>
                    <td className={`change ${stock.change >= 0 ? 'positive' : 'negative'}`}>
                      {stock.change >= 0 ? '+' : ''}{stock.change}
                      <br />
                      ({stock.changePercent >= 0 ? '+' : ''}{stock.changePercent}%)
                    </td>
                    <td className="volume">{formatNumber(stock.volume)}</td>
                    <td className="market-cap">{stock.marketCap}</td>
                    <td>
                      <button 
                        className="chart-button"
                        onClick={() => setSelectedStock(stock)}
                      >
                        📈
                      </button>
                    </td>
                    <td>
                      <div className="trade-buttons">
                        <button 
                          className="buy-btn"
                          onClick={() => handleBuy(stock)}
                        >
                          買
                        </button>
                        <button 
                          className="sell-btn"
                          onClick={() => handleSell(stock)}
                        >
                          売
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        <div className="chart-section">
          {selectedStock && (
            <StockChart 
              stock={selectedStock}
              onBuy={() => handleBuy(selectedStock)}
              onSell={() => handleSell(selectedStock)}
            />
          )}
        </div>
      </div>

      <TradingModal
        isOpen={modalState.isOpen}
        onClose={closeModal}
        stock={modalState.stock}
        tradeType={modalState.tradeType}
        currentHoldings={Math.floor(Math.random() * 500) + 50} // 仮の保有数
      />
    </div>
  )
}

export default Investment