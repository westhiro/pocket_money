import React, { useState, useEffect } from 'react'
import { formatCurrency } from '../utils/format'
import { realEstateTradingAPI } from '../services/api'
import { useAuth } from '../contexts/AuthContext'
import RealEstatePortfolio from './RealEstatePortfolio'
import RealEstateMap from './RealEstateMap'
import './RealEstateList.css'

const RealEstateList = () => {
  const { isAuthenticated, user } = useAuth()
  const [sortBy, setSortBy] = useState('property_name')
  const [sortOrder, setSortOrder] = useState('asc')
  const [selectedProperty, setSelectedProperty] = useState(null)
  const [properties, setProperties] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [showSellModal, setShowSellModal] = useState(false)
  const [propertyToSell, setPropertyToSell] = useState(null)
  const [selling, setSelling] = useState(false)

  // ユーザーの保有物件を取得
  useEffect(() => {
    const fetchProperties = async () => {
      if (!isAuthenticated || !user) {
        setProperties([])
        setLoading(false)
        return
      }

      try {
        setLoading(true)
        const response = await realEstateTradingAPI.getPortfolio(user.id)

        if (response.data.success) {
          setProperties(response.data.data.holdings || [])
        }
      } catch (err) {
        console.error('Properties fetch error:', err)
        setError('保有物件の取得に失敗しました: ' + err.message)
      } finally {
        setLoading(false)
      }
    }

    fetchProperties()
  }, [isAuthenticated, user])

  const handleSort = (column) => {
    if (sortBy === column) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc')
    } else {
      setSortBy(column)
      setSortOrder('asc')
    }
  }

  const sortedProperties = [...properties].sort((a, b) => {
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

  const handleSell = (property) => {
    setPropertyToSell(property)
    setShowSellModal(true)
  }

  const confirmSell = async () => {
    if (!propertyToSell || !user) return

    setSelling(true)
    try {
      const response = await realEstateTradingAPI.sell(propertyToSell.id, user.id)

      if (response.data.success) {
        alert(`${propertyToSell.property_name}を売却しました！\n売却益: ${formatCurrency(response.data.data.net_proceeds)}`)

        // 物件リストを再取得
        const portfolioResponse = await realEstateTradingAPI.getPortfolio(user.id)
        if (portfolioResponse.data.success) {
          setProperties(portfolioResponse.data.data.holdings || [])
        }

        setShowSellModal(false)
        setPropertyToSell(null)
      } else {
        alert('売却に失敗しました: ' + response.data.message)
      }
    } catch (err) {
      console.error('Sell error:', err)
      alert('売却処理中にエラーが発生しました: ' + (err.response?.data?.message || err.message))
    } finally {
      setSelling(false)
    }
  }

  const cancelSell = () => {
    setShowSellModal(false)
    setPropertyToSell(null)
  }

  // ローディング表示
  if (loading) {
    return (
      <>
        <div className="real-estate-section">
          <div className="section-header">
            <h2>保有物件</h2>
          </div>
          <div className="loading" style={{ textAlign: 'center', padding: '50px' }}>
            データを読み込み中...
          </div>
        </div>
        <RealEstatePortfolio />
        <RealEstateMap />
      </>
    )
  }

  // エラー表示
  if (error) {
    return (
      <>
        <div className="real-estate-section">
          <div className="section-header">
            <h2>保有物件</h2>
          </div>
          <div className="error" style={{ textAlign: 'center', padding: '50px', color: 'red' }}>
            {error}
          </div>
        </div>
        <RealEstatePortfolio />
        <RealEstateMap />
      </>
    )
  }

  // 未認証表示
  if (!isAuthenticated) {
    return (
      <>
        <div className="real-estate-section">
          <div className="section-header">
            <h2>保有物件</h2>
          </div>
          <div className="no-properties">
            <p>ログインして保有物件を確認しましょう</p>
          </div>
        </div>
        <RealEstatePortfolio />
        <RealEstateMap />
      </>
    )
  }

  return (
    <>
      <div className="real-estate-section">
        <div className="section-header">
          <h2>保有物件</h2>
        </div>

        <div className="properties-table-container">
          <table className="properties-table">
            <thead>
              <tr>
                <th onClick={() => handleSort('property_name')} className="sortable">
                  物件 {sortBy === 'property_name' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th onClick={() => handleSort('vacancy_rate')} className="sortable">
                  物件状態 {sortBy === 'vacancy_rate' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th onClick={() => handleSort('weekly_net_rent')} className="sortable">
                  家賃収入 {sortBy === 'weekly_net_rent' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th onClick={() => handleSort('loan_balance')} className="sortable">
                  ローン残高 {sortBy === 'loan_balance' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th onClick={() => handleSort('land_demand')} className="sortable">
                  需要 {sortBy === 'land_demand' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th>売却</th>
              </tr>
            </thead>
            <tbody>
              {sortedProperties.map(property => (
                <tr key={property.id}>
                  <td>
                    <div className="property-info">
                      <div className="property-name">{property.property_name}</div>
                    </div>
                  </td>
                  <td>
                    <span className={`status-badge ${property.vacancy_rate < 10 ? 'occupied' : 'vacant'}`}>
                      {property.vacancy_rate < 10 ? '居住中' : '空室中'}
                    </span>
                  </td>
                  <td className="rental-income">
                    {property.weekly_net_rent > 0 ? formatCurrency(property.weekly_net_rent) : '-'}
                  </td>
                  <td className="loan-balance">{formatCurrency(property.loan_balance)}</td>
                  <td>
                    <span className={`demand-indicator ${property.land_demand === 'rising' ? 'demand-up' : property.land_demand === 'falling' ? 'demand-down' : 'demand-normal'}`}>
                      {property.land_demand === 'rising' ? '上昇中 ↑' : property.land_demand === 'falling' ? '減少中 ↓' : '普通 →'}
                    </span>
                  </td>
                  <td>
                    <button
                      className="sell-property-btn"
                      onClick={() => handleSell(property)}
                    >
                      売却
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {sortedProperties.length === 0 && (
          <div className="no-properties">
            <p>保有物件がありません</p>
          </div>
        )}
      </div>

      <RealEstatePortfolio />

      <RealEstateMap />

      {/* 売却確認モーダル */}
      {showSellModal && propertyToSell && (
        <div className="modal-overlay" onClick={cancelSell}>
          <div className="sell-modal" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>物件売却確認</h2>
              <button className="close-button" onClick={cancelSell}>
                ✕
              </button>
            </div>
            <div className="modal-body">
              <p>以下の物件を売却しますか？</p>
              <div className="property-details">
                <div className="detail-row">
                  <span className="label">物件名:</span>
                  <span className="value">{propertyToSell.property_name}</span>
                </div>
                <div className="detail-row">
                  <span className="label">購入価格:</span>
                  <span className="value">{formatCurrency(propertyToSell.purchase_price)}</span>
                </div>
                <div className="detail-row">
                  <span className="label">ローン残高:</span>
                  <span className="value">{formatCurrency(propertyToSell.loan_balance)}</span>
                </div>
                <div className="detail-row">
                  <span className="label">予想売却価格:</span>
                  <span className="value">{formatCurrency(propertyToSell.current_value)}</span>
                </div>
                <div className="detail-row profit-loss">
                  <span className="label">予想純利益:</span>
                  <span className={`value ${propertyToSell.equity >= 0 ? 'positive' : 'negative'}`}>
                    {propertyToSell.equity >= 0 ? '+' : ''}{formatCurrency(propertyToSell.equity)}
                  </span>
                </div>
              </div>
            </div>
            <div className="modal-footer">
              <button className="cancel-btn" onClick={cancelSell} disabled={selling}>
                キャンセル
              </button>
              <button className="confirm-btn sell" onClick={confirmSell} disabled={selling}>
                {selling ? '売却中...' : '売却確定'}
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  )
}

export default RealEstateList
