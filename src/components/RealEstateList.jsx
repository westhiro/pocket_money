import React, { useState } from 'react'
import { formatCurrency } from '../utils/format'
import RealEstatePortfolio from './RealEstatePortfolio'
import RealEstateMap from './RealEstateMap'
import './RealEstateList.css'

const RealEstateList = () => {
  const [sortBy, setSortBy] = useState('property')
  const [sortOrder, setSortOrder] = useState('asc')
  const [selectedProperty, setSelectedProperty] = useState(null)

  // モックデータ
  const mockProperties = [
    {
      id: 1,
      name: '渋谷マンション',
      status: '居住中',
      rentalIncome: 150,
      loanBalance: 25000,
      demand: 'up',
      purchasePrice: 30000,
      currentValue: 32000
    },
    {
      id: 2,
      name: '新宿アパート',
      status: '空室中',
      rentalIncome: 0,
      loanBalance: 18000,
      demand: 'down',
      purchasePrice: 20000,
      currentValue: 19500
    },
    {
      id: 3,
      name: '池袋オフィスビル',
      status: '居住中',
      rentalIncome: 300,
      loanBalance: 45000,
      demand: 'up',
      purchasePrice: 50000,
      currentValue: 55000
    }
  ]

  const handleSort = (column) => {
    if (sortBy === column) {
      setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc')
    } else {
      setSortBy(column)
      setSortOrder('asc')
    }
  }

  const sortedProperties = [...mockProperties].sort((a, b) => {
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
    setSelectedProperty(property)
    // TODO: 売却モーダルを表示
    alert(`${property.name}の売却機能は実装予定です`)
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
                <th onClick={() => handleSort('name')} className="sortable">
                  物件 {sortBy === 'name' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th onClick={() => handleSort('status')} className="sortable">
                  物件状態 {sortBy === 'status' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th onClick={() => handleSort('rentalIncome')} className="sortable">
                  家賃収入 {sortBy === 'rentalIncome' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th onClick={() => handleSort('loanBalance')} className="sortable">
                  ローン残高 {sortBy === 'loanBalance' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th onClick={() => handleSort('demand')} className="sortable">
                  需要 {sortBy === 'demand' && (sortOrder === 'asc' ? '↑' : '↓')}
                </th>
                <th>売却</th>
              </tr>
            </thead>
            <tbody>
              {sortedProperties.map(property => (
                <tr key={property.id}>
                  <td>
                    <div className="property-info">
                      <div className="property-name">{property.name}</div>
                    </div>
                  </td>
                  <td>
                    <span className={`status-badge ${property.status === '居住中' ? 'occupied' : 'vacant'}`}>
                      {property.status}
                    </span>
                  </td>
                  <td className="rental-income">
                    {property.rentalIncome > 0 ? formatCurrency(property.rentalIncome) : '-'}
                  </td>
                  <td className="loan-balance">{formatCurrency(property.loanBalance)}</td>
                  <td>
                    <span className={`demand-indicator ${property.demand === 'up' ? 'demand-up' : 'demand-down'}`}>
                      {property.demand === 'up' ? '上昇中 ↑' : '減少中 ↓'}
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
    </>
  )
}

export default RealEstateList
