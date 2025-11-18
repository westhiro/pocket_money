import React, { useState, useEffect } from 'react'
import { formatCurrency } from '../utils/format'
import { realEstateAPI, realEstateTradingAPI } from '../services/api'
import { useAuth } from '../contexts/AuthContext'
import RealEstatePurchaseModal from './RealEstatePurchaseModal'
import './RealEstateMap.css'

const RealEstateMap = () => {
  const { isAuthenticated, user, updateCoinBalance } = useAuth()
  const [hoveredProperty, setHoveredProperty] = useState(null)
  const [properties, setProperties] = useState([])
  const [userProperties, setUserProperties] = useState([])
  const [loading, setLoading] = useState(true)
  const [purchasing, setPurchasing] = useState(false)
  const [showPurchaseModal, setShowPurchaseModal] = useState(false)
  const [selectedPropertyForPurchase, setSelectedPropertyForPurchase] = useState(null)
  const [currentInterestRate, setCurrentInterestRate] = useState(2.5)

  // 物件データを取得
  useEffect(() => {
    const fetchProperties = async () => {
      try {
        setLoading(true)

        // 購入可能な物件を取得
        const availableResponse = await realEstateAPI.getAll({ status: 'available' })
        const availableProperties = availableResponse.data.data || []

        // ユーザーの保有物件を取得（ログインしている場合）
        let userPropertyIds = []
        if (isAuthenticated && user) {
          try {
            const portfolioResponse = await realEstateTradingAPI.getPortfolio(user.id)
            if (portfolioResponse.data.success) {
              const holdings = portfolioResponse.data.data.holdings || []
              // 保有物件のreal_estate_idを取得する必要があるため、
              // UserRealEstateモデルから元のreal_estate_idを参照
              // ここでは簡易的にproperty_nameで判定（本来はAPIレスポンスにreal_estate_idを含めるべき）
              userPropertyIds = holdings.map(h => h.property_name)
            }
          } catch (err) {
            console.error('User portfolio fetch error:', err)
          }
        }

        setUserProperties(userPropertyIds)
        setProperties(availableProperties)
      } catch (err) {
        console.error('Properties fetch error:', err)
      } finally {
        setLoading(false)
      }
    }

    fetchProperties()
  }, [isAuthenticated, user])

  // 現在の金利を取得
  useEffect(() => {
    const fetchInterestRate = async () => {
      try {
        const response = await realEstateAPI.getCurrentInterestRate()
        if (response.data.success) {
          setCurrentInterestRate(response.data.data.interest_rate)
        }
      } catch (err) {
        console.error('Interest rate fetch error:', err)
        // エラー時はデフォルト値（2.5%）を使用
      }
    }

    fetchInterestRate()
  }, [])

  const handlePinClick = (property, event) => {
    event.stopPropagation() // マップのクリックイベントに伝播しないように
    // 同じ物件をクリックしたら閉じる、違う物件なら開く
    if (hoveredProperty?.id === property.id) {
      setHoveredProperty(null)
    } else {
      setHoveredProperty(property)
    }
  }

  const handleMapClick = () => {
    // マップの背景をクリックしたら閉じる
    setHoveredProperty(null)
  }

  const handleOpenPurchaseModal = () => {
    if (!hoveredProperty || !isAuthenticated || !user) {
      alert('ログインが必要です')
      return
    }

    setSelectedPropertyForPurchase(hoveredProperty)
    setShowPurchaseModal(true)
  }

  const handleClosePurchaseModal = () => {
    setShowPurchaseModal(false)
    setSelectedPropertyForPurchase(null)
  }

  const handlePurchaseConfirm = async (purchaseData) => {
    if (!user) return

    setPurchasing(true)
    try {
      const response = await realEstateTradingAPI.buy(
        purchaseData.property_id,
        user.id,
        purchaseData.down_payment,
        purchaseData.loan_period_weeks,
        purchaseData.monthly_rent
      )

      if (response.data.success) {
        const data = response.data.data
        alert(
          `${data.property_name}を購入しました！\n\n` +
          `購入価格: ${formatCurrency(data.purchase_price)}\n` +
          `頭金: ${formatCurrency(purchaseData.down_payment)}\n` +
          `ローン: ${formatCurrency(data.loan_amount)}\n` +
          `週次返済額: ${formatCurrency(data.weekly_principal)}\n` +
          `月次家賃: ${formatCurrency(data.monthly_rent)}\n` +
          `残りコイン: ${formatCurrency(data.remaining_coins)}`
        )

        // コイン残高を更新
        if (updateCoinBalance) {
          updateCoinBalance(data.remaining_coins)
        }

        // 物件リストを再取得
        const availableResponse = await realEstateAPI.getAll({ status: 'available' })
        setProperties(availableResponse.data.data || [])

        // ユーザー保有物件を更新
        if (user) {
          try {
            const portfolioResponse = await realEstateTradingAPI.getPortfolio(user.id)
            if (portfolioResponse.data.success) {
              const holdings = portfolioResponse.data.data.holdings || []
              setUserProperties(holdings.map(h => h.property_name))
            }
          } catch (err) {
            console.error('Portfolio refresh error:', err)
          }
        }

        setHoveredProperty(null)
      } else {
        alert('購入に失敗しました: ' + response.data.message)
      }
    } catch (err) {
      console.error('Purchase error:', err)
      alert('購入処理中にエラーが発生しました: ' + (err.response?.data?.message || err.message))
    } finally {
      setPurchasing(false)
    }
  }

  // 物件がユーザーに保有されているかチェック
  const isPropertyOwned = (propertyName) => {
    return userProperties.includes(propertyName)
  }

  // 物件の表示情報を整形
  const formatPropertyForMap = (property) => {
    const demandText = property.land_demand === 'rising' ? '上昇中' :
                      property.land_demand === 'falling' ? '減少中' : '普通'

    const ageText = property.building_age === 'new' ? '築浅' :
                   property.building_age === 'semi_new' ? '築15年' : '築古'

    return {
      ...property,
      name: property.property_name,
      x: property.location.x,
      y: property.location.y,
      price: property.purchase_price,
      demand: demandText,
      management: property.monthly_cost / 10000, // 円→万円
      rent: property.estimated_monthly_rent,
      age: ageText,
      status: isPropertyOwned(property.property_name) ? 'owned' : 'available'
    }
  }

  const formattedProperties = properties.map(formatPropertyForMap)

  if (loading) {
    return (
      <div className="real-estate-map">
        <div className="map-header">
          <h2>物件マップ</h2>
        </div>
        <div className="loading" style={{ textAlign: 'center', padding: '50px' }}>
          データを読み込み中...
        </div>
      </div>
    )
  }

  return (
    <div className="real-estate-map">
      <div className="map-header">
        <h2>物件マップ</h2>
      </div>

      <div className="map-container">
        <svg viewBox="0 0 800 500" className="map-svg" preserveAspectRatio="xMidYMid slice" onClick={handleMapClick}>
          {/* 背景（街並み） */}
          <rect width="800" height="500" fill="#e8e4d8" />

          {/* 海（左上） */}
          <path
            d="M 0 0 Q 80 20 120 50 Q 160 80 180 120 L 180 150 Q 160 110 120 80 Q 80 50 0 30 Z"
            fill="#87CEEB"
          />
          <path
            d="M 0 0 Q 60 15 100 40 Q 140 70 160 110 Q 140 80 100 55 Q 60 30 0 15 Z"
            fill="#ADD8E6"
            opacity="0.6"
          />

          {/* 海（右上） */}
          <path
            d="M 620 0 Q 680 40 720 80 L 760 120 L 800 140 L 800 0 Z"
            fill="#87CEEB"
          />
          <path
            d="M 640 0 Q 700 35 740 75 Q 700 50 660 20 L 640 0 Z"
            fill="#ADD8E6"
            opacity="0.6"
          />

          {/* 公園（大きい緑地・左下） */}
          <ellipse cx="200" cy="420" rx="130" ry="75" fill="#9ACD32" />
          <circle cx="180" cy="400" r="20" fill="#6B8E23" opacity="0.3" />
          <circle cx="220" cy="440" r="15" fill="#6B8E23" opacity="0.3" />

          {/* 公園（右下） */}
          <ellipse cx="650" cy="420" rx="110" ry="70" fill="#9ACD32" />
          <circle cx="650" cy="420" r="25" fill="#6B8E23" opacity="0.3" />

          {/* 公園（中央上） */}
          <ellipse cx="400" cy="100" rx="80" ry="60" fill="#9ACD32" />
          <circle cx="400" cy="100" r="20" fill="#6B8E23" opacity="0.3" />

          {/* 道路（白い線） */}
          <line x1="180" y1="150" x2="800" y2="150" stroke="#fff" strokeWidth="7" />
          <line x1="120" y1="250" x2="800" y2="250" stroke="#fff" strokeWidth="7" />
          <line x1="120" y1="350" x2="760" y2="350" stroke="#fff" strokeWidth="7" />

          <line x1="280" y1="150" x2="280" y2="500" stroke="#fff" strokeWidth="7" />
          <line x1="420" y1="0" x2="420" y2="500" stroke="#fff" strokeWidth="7" />
          <line x1="560" y1="0" x2="560" y2="500" stroke="#fff" strokeWidth="7" />

          {/* 建物（ブロック）*/}
          <rect x="200" y="30" width="60" height="70" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="300" y="50" width="80" height="60" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="480" y="30" width="60" height="80" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="600" y="50" width="70" height="70" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="700" y="40" width="80" height="80" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />

          <rect x="140" y="170" width="100" height="60" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="300" y="180" width="90" height="50" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="480" y="170" width="60" height="65" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="620" y="180" width="100" height="55" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />

          <rect x="140" y="270" width="110" height="65" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="300" y="280" width="90" height="50" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="480" y="270" width="60" height="60" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />
          <rect x="620" y="280" width="110" height="55" fill="#d4cfc0" stroke="#a09880" strokeWidth="2" />

          {/* 物件ピン */}
          {formattedProperties.map((property) => (
            <g
              key={property.id}
              onClick={(e) => handlePinClick(property, e)}
              style={{ cursor: 'pointer' }}
              className="property-pin"
            >
              {/* ピンの影 */}
              <ellipse
                cx={property.x}
                cy={property.y + 35}
                rx="12"
                ry="4"
                fill="#000"
                opacity="0.3"
              />
              {/* ピン本体 */}
              <path
                d={`M ${property.x} ${property.y}
                   C ${property.x - 15} ${property.y} ${property.x - 20} ${property.y + 10} ${property.x - 20} ${property.y + 20}
                   C ${property.x - 20} ${property.y + 30} ${property.x} ${property.y + 40} ${property.x} ${property.y + 40}
                   C ${property.x} ${property.y + 40} ${property.x + 20} ${property.y + 30} ${property.x + 20} ${property.y + 20}
                   C ${property.x + 20} ${property.y + 10} ${property.x + 15} ${property.y} ${property.x} ${property.y} Z`}
                fill={property.status === 'owned' ? '#2196F3' : '#E53935'}
                stroke="#fff"
                strokeWidth="2"
              />
              {/* ピン内のアイコン */}
              <text
                x={property.x}
                y={property.y + 22}
                textAnchor="middle"
                fill="#fff"
                fontSize="16"
                fontWeight="bold"
              >
                🏢
              </text>
            </g>
          ))}
        </svg>

        {/* 物件情報ポップアップ */}
        {hoveredProperty && (() => {
          // ポップアップの位置を計算してマップ内に収める
          const mapWidth = 800
          const mapHeight = 500
          const popupWidth = 320
          const popupHeight = 260
          const marginTop = 50
          const pinHeight = 40

          const pinX = hoveredProperty.x
          const pinY = hoveredProperty.y

          let leftPercent = (pinX / mapWidth) * 100
          const halfPopupWidth = popupWidth / 2

          if (pinX - halfPopupWidth < 10) {
            leftPercent = ((halfPopupWidth + 10) / mapWidth) * 100
          } else if (pinX + halfPopupWidth > mapWidth - 10) {
            leftPercent = ((mapWidth - halfPopupWidth - 10) / mapWidth) * 100
          }

          let topPercent = (pinY / mapHeight) * 100
          let className = 'property-popup'

          const popupTop = pinY - popupHeight - marginTop

          if (popupTop < 10) {
            topPercent = ((pinY + pinHeight + 10) / mapHeight) * 100
            className = 'property-popup popup-below'
          } else if (pinY + pinHeight + 10 + popupHeight > mapHeight - 10) {
            if (pinY - popupHeight - marginTop >= 10) {
              topPercent = (pinY / mapHeight) * 100
              className = 'property-popup'
            }
          }

          return (
            <div
              className={className}
              style={{
                left: `${leftPercent}%`,
                top: `${topPercent}%`
              }}
              onClick={(e) => e.stopPropagation()}
            >
            <div className="popup-content">
              <h3>【{hoveredProperty.name}】</h3>
              <div className="popup-details">
                <div className="popup-row">
                  <span className="popup-label">物件価格</span>
                  <span className="popup-value">: {formatCurrency(hoveredProperty.price)}</span>
                </div>
                <div className="popup-row">
                  <span className="popup-label">土地需要</span>
                  <span className={`popup-value ${hoveredProperty.demand === '上昇中' ? 'demand-up' : hoveredProperty.demand === '減少中' ? 'demand-down' : ''}`}>
                    : {hoveredProperty.demand}
                  </span>
                </div>
                <div className="popup-row">
                  <span className="popup-label">管理費・修繕積立金</span>
                  <span className="popup-value">: {formatCurrency(hoveredProperty.management)}/月</span>
                </div>
                <div className="popup-row">
                  <span className="popup-label">予想家賃</span>
                  <span className="popup-value">: {formatCurrency(hoveredProperty.rent)}/月</span>
                </div>
                <div className="popup-row">
                  <span className="popup-label">築年数</span>
                  <span className="popup-value">: {hoveredProperty.age}</span>
                </div>
              </div>
              {hoveredProperty.status === 'available' && (
                <button
                  className="btn-purchase-popup"
                  onClick={handleOpenPurchaseModal}
                  disabled={!isAuthenticated}
                >
                  {!isAuthenticated ? 'ログインが必要' : '購入シミュレーション'}
                </button>
              )}
              {hoveredProperty.status === 'owned' && (
                <div className="owned-badge">保有中</div>
              )}
            </div>
            <div className="popup-arrow"></div>
          </div>
          )
        })()}
      </div>

      {/* 購入モーダル */}
      <RealEstatePurchaseModal
        isOpen={showPurchaseModal}
        onClose={handleClosePurchaseModal}
        property={selectedPropertyForPurchase}
        currentInterestRate={currentInterestRate}
        onPurchaseSuccess={handlePurchaseConfirm}
      />
    </div>
  )
}

export default RealEstateMap
