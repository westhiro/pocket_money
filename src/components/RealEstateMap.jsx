import React, { useState } from 'react'
import { formatCurrency } from '../utils/format'
import './RealEstateMap.css'

const RealEstateMap = () => {
  const [hoveredProperty, setHoveredProperty] = useState(null)

  // 物件データ（モック）
  const properties = [
    {
      id: 1,
      name: 'マンション①',
      x: 320,
      y: 140,
      price: 5.3,
      demand: '上昇中',
      management: 18,
      rent: 0.0089,
      age: '築浅',
      status: 'available'
    },
    {
      id: 2,
      name: 'マンション②',
      x: 220,
      y: 240,
      price: 4.2,
      demand: '減少中',
      management: 15,
      rent: 0.0072,
      age: '築15年',
      status: 'available'
    },
    {
      id: 3,
      name: 'マンション③',
      x: 450,
      y: 220,
      price: 6.8,
      demand: '上昇中',
      management: 22,
      rent: 0.0105,
      age: '築浅',
      status: 'owned'
    }
  ]

  const handlePinClick = (property) => {
    // クリック時は何もしない（ホバーで表示するため）
  }

  const handlePinHover = (property) => {
    setHoveredProperty(property)
  }

  const handlePinLeave = () => {
    setHoveredProperty(null)
  }

  const handlePurchase = () => {
    alert(`${hoveredProperty.name}を購入しました！`)
    setHoveredProperty(null)
  }

  return (
    <div className="real-estate-map">
      <div className="map-header">
        <h2>物件マップ</h2>
      </div>

      <div className="map-container">
        <svg viewBox="0 0 800 500" className="map-svg" preserveAspectRatio="xMidYMid slice">
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
          {properties.map((property) => (
            <g
              key={property.id}
              onMouseEnter={() => handlePinHover(property)}
              onMouseLeave={handlePinLeave}
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
          const popupHeight = 260 // ポップアップの実際の高さ
          const marginTop = 50 // CSSのmargin-topの値
          const pinHeight = 40 // ピンの高さ

          // 基本位置（ピンの位置をSVG座標で取得）
          const pinX = hoveredProperty.x
          const pinY = hoveredProperty.y

          // 左右の位置計算（transform: translate(-50%, -100%)を考慮）
          let leftPercent = (pinX / mapWidth) * 100
          const halfPopupWidth = popupWidth / 2

          // 左にはみ出る場合
          if (pinX - halfPopupWidth < 10) {
            leftPercent = ((halfPopupWidth + 10) / mapWidth) * 100
          }
          // 右にはみ出る場合
          else if (pinX + halfPopupWidth > mapWidth - 10) {
            leftPercent = ((mapWidth - halfPopupWidth - 10) / mapWidth) * 100
          }

          // 上下の位置計算
          let topPercent = (pinY / mapHeight) * 100
          let className = 'property-popup'

          // ピンの上にポップアップを表示した場合の上端位置
          // transform: translate(-50%, -100%) により、topの位置からポップアップの高さ分上に移動
          // さらにmargin-top: -50pxで50px上に移動
          const popupTop = pinY - popupHeight - marginTop

          // 上にはみ出る場合：ピンの下に表示
          if (popupTop < 10) {
            // ピンの下に表示する場合
            topPercent = ((pinY + pinHeight + 10) / mapHeight) * 100
            className = 'property-popup popup-below'
          }
          // 下にはみ出る場合もチェック（ピンの下に表示した場合）
          else if (pinY + pinHeight + 10 + popupHeight > mapHeight - 10) {
            // 上に表示できるスペースがある場合は上に表示
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
              onMouseEnter={() => setHoveredProperty(hoveredProperty)}
              onMouseLeave={handlePinLeave}
            >
            <div className="popup-content">
              <h3>【{hoveredProperty.name}】</h3>
              <div className="popup-details">
                <div className="popup-row">
                  <span className="popup-label">物件価格</span>
                  <span className="popup-value">: {hoveredProperty.price}万円</span>
                </div>
                <div className="popup-row">
                  <span className="popup-label">需要</span>
                  <span className={`popup-value ${hoveredProperty.demand === '上昇中' ? 'demand-up' : 'demand-down'}`}>
                    : {hoveredProperty.demand}
                  </span>
                </div>
                <div className="popup-row">
                  <span className="popup-label">管理費・修繕積立金</span>
                  <span className="popup-value">: {hoveredProperty.management.toLocaleString()}円</span>
                </div>
                <div className="popup-row">
                  <span className="popup-label">家賃相場</span>
                  <span className="popup-value">: {hoveredProperty.rent}万円</span>
                </div>
                <div className="popup-row">
                  <span className="popup-label">築年数</span>
                  <span className="popup-value">: {hoveredProperty.age}</span>
                </div>
              </div>
              {hoveredProperty.status === 'available' && (
                <button className="btn-purchase-popup" onClick={handlePurchase}>
                  購入する
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
    </div>
  )
}

export default RealEstateMap
