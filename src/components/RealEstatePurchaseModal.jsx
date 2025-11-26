import React, { useState, useEffect } from 'react'
import { formatCurrency } from '../utils/format'
import './RealEstatePurchaseModal.css'

const RealEstatePurchaseModal = ({
  isOpen,
  onClose,
  property,
  currentInterestRate = 2.5,
  onPurchaseSuccess
}) => {
  // 購入設定
  const [downPayment, setDownPayment] = useState(0) // 頭金（万円）
  const [loanPeriodMonths, setLoanPeriodMonths] = useState(480) // ローン期間（月）
  const [monthlyRent, setMonthlyRent] = useState(0) // 家賃（万円）
  const [loading, setLoading] = useState(false)

  // 計算結果
  const [loanAmount, setLoanAmount] = useState(0)
  const [weeklyPrincipal, setWeeklyPrincipal] = useState(0)
  const [weeklyInterest, setWeeklyInterest] = useState(0)
  const [weeklyNetRent, setWeeklyNetRent] = useState(0)
  const [weeklyManagementCost, setWeeklyManagementCost] = useState(0)
  const [weeklyCashFlow, setWeeklyCashFlow] = useState(0)

  // 物件価格とデフォルト家賃を設定
  useEffect(() => {
    if (property && isOpen) {
      // デフォルト家賃を設定（予想家賃）
      setMonthlyRent(property.estimated_monthly_rent || 0)

      // 頭金は0円からスタート
      setDownPayment(0)
    }
  }, [property, isOpen])

  // 内訳を計算
  useEffect(() => {
    if (!property) return

    const purchasePrice = property.purchase_price || 0
    const calculatedLoanAmount = Math.max(0, purchasePrice - downPayment)
    setLoanAmount(calculatedLoanAmount)

    // 週次元本支払い（月を4週で計算）
    const loanPeriodWeeks = loanPeriodMonths / 4
    const calculatedWeeklyPrincipal = loanPeriodWeeks > 0 ? calculatedLoanAmount / loanPeriodWeeks : 0
    setWeeklyPrincipal(calculatedWeeklyPrincipal)

    // 週次金利支払い（年利を週利に換算）
    const calculatedWeeklyInterest = (calculatedLoanAmount * currentInterestRate) / (100 * 52)
    setWeeklyInterest(calculatedWeeklyInterest)

    // 週次家賃収入（月を4週で計算、空室率を考慮）
    const vacancyRate = property.vacancy_rate || 0
    const calculatedWeeklyNetRent = (monthlyRent / 4) * (1 - vacancyRate / 100)
    setWeeklyNetRent(calculatedWeeklyNetRent)

    // 週次管理費（月次コストを4週で割る）
    const monthlyCost = property.monthly_cost || 0
    const calculatedWeeklyManagementCost = monthlyCost / 4 / 10000 // 円→万円
    setWeeklyManagementCost(calculatedWeeklyManagementCost)

    // 週次キャッシュフロー = 家賃収入 - 元本支払い - 金利支払い - 管理費
    const calculatedWeeklyCashFlow = calculatedWeeklyNetRent - calculatedWeeklyPrincipal - calculatedWeeklyInterest - calculatedWeeklyManagementCost
    setWeeklyCashFlow(calculatedWeeklyCashFlow)

  }, [property, downPayment, loanPeriodMonths, monthlyRent, currentInterestRate])

  const handleDownPaymentChange = (e) => {
    const value = parseFloat(e.target.value) || 0
    const maxDownPayment = property?.purchase_price || 0
    setDownPayment(Math.min(Math.max(0, value), maxDownPayment))
  }

  const handleLoanPeriodChange = (e) => {
    const value = parseInt(e.target.value) || 0
    setLoanPeriodMonths(Math.max(0, value))
  }

  const handleMonthlyRentChange = (e) => {
    const value = parseFloat(e.target.value) || 0
    setMonthlyRent(Math.max(0, value))
  }

  const handleConfirm = async () => {
    if (!property || !onPurchaseSuccess) return

    setLoading(true)
    try {
      // 購入データを親コンポーネントに渡す
      await onPurchaseSuccess({
        property_id: property.id,
        down_payment: downPayment,
        loan_period_weeks: loanPeriodMonths / 4,
        monthly_rent: monthlyRent
      })

      onClose()
    } catch (error) {
      console.error('Purchase error:', error)
    } finally {
      setLoading(false)
    }
  }

  const isValid = property && downPayment >= 0 && loanAmount >= 0 && loanPeriodMonths > 0 && monthlyRent > 0

  if (!isOpen || !property) return null

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="real-estate-purchase-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2>🏢 不動産購入シミュレーション</h2>
          <button className="close-button" onClick={onClose}>
            ✕
          </button>
        </div>

        <div className="modal-body">
          {/* 物件情報 */}
          <div className="property-summary">
            <h3>{property.property_name}</h3>
            <div className="summary-row">
              <span className="label">物件価格:</span>
              <span className="value price">{formatCurrency(property.purchase_price)}</span>
            </div>
            <div className="summary-row">
              <span className="label">管理費・修繕積立金:</span>
              <span className="value">{formatCurrency(property.monthly_cost / 10000)}/月</span>
            </div>
            <div className="summary-row">
              <span className="label">現在の金利:</span>
              <span className="value">{currentInterestRate}%</span>
            </div>
          </div>

          {/* 購入設定 */}
          <div className="purchase-settings">
            <h4>購入設定</h4>

            {/* 頭金 */}
            <div className="setting-row">
              <label htmlFor="downPayment">頭金（万円）</label>
              <div className="input-with-info">
                <input
                  id="downPayment"
                  type="number"
                  value={downPayment}
                  onChange={handleDownPaymentChange}
                  min="0"
                  max={property.purchase_price}
                  step="10"
                  className="setting-input"
                />
                <span className="input-info">
                  最大: {formatCurrency(property.purchase_price)}
                </span>
              </div>
              <div className="preset-buttons">
                <button onClick={() => setDownPayment(0)}>0円</button>
                <button onClick={() => setDownPayment(property.purchase_price * 0.1)}>10%</button>
                <button onClick={() => setDownPayment(property.purchase_price * 0.2)}>20%</button>
                <button onClick={() => setDownPayment(property.purchase_price * 0.3)}>30%</button>
              </div>
            </div>

            {/* ローン期間 */}
            <div className="setting-row">
              <label htmlFor="loanPeriod">ローン期間（月）</label>
              <div className="input-with-info">
                <input
                  id="loanPeriod"
                  type="number"
                  value={loanPeriodMonths}
                  onChange={handleLoanPeriodChange}
                  min="12"
                  max="480"
                  step="12"
                  className="setting-input"
                />
                <span className="input-info">
                  {(loanPeriodMonths / 12).toFixed(1)}年
                </span>
              </div>
              <div className="preset-buttons">
                <button onClick={() => setLoanPeriodMonths(120)}>10年</button>
                <button onClick={() => setLoanPeriodMonths(240)}>20年</button>
                <button onClick={() => setLoanPeriodMonths(360)}>30年</button>
                <button onClick={() => setLoanPeriodMonths(480)}>40年</button>
              </div>
            </div>

            {/* 家賃 */}
            <div className="setting-row">
              <label htmlFor="monthlyRent">月額家賃（万円）</label>
              <div className="input-with-info">
                <input
                  id="monthlyRent"
                  type="number"
                  value={monthlyRent}
                  onChange={handleMonthlyRentChange}
                  min="0"
                  step="0.5"
                  className="setting-input"
                />
                <span className="input-info">
                  推奨: {formatCurrency(property.estimated_monthly_rent)}
                </span>
              </div>
            </div>
          </div>

          {/* 内訳 */}
          <div className="breakdown-section">
            <h4>週次キャッシュフロー内訳</h4>

            <div className="breakdown-table">
              <div className="breakdown-row">
                <span className="breakdown-label">ローン金額:</span>
                <span className="breakdown-value">{formatCurrency(loanAmount)}</span>
              </div>
              <div className="breakdown-row income">
                <span className="breakdown-label">家賃収入（実質）:</span>
                <span className="breakdown-value positive">+{formatCurrency(weeklyNetRent)}</span>
              </div>
              <div className="breakdown-row expense">
                <span className="breakdown-label">元本支払い:</span>
                <span className="breakdown-value negative">-{formatCurrency(weeklyPrincipal)}</span>
              </div>
              <div className="breakdown-row expense">
                <span className="breakdown-label">金利支払い:</span>
                <span className="breakdown-value negative">-{formatCurrency(weeklyInterest)}</span>
              </div>
              <div className="breakdown-row expense">
                <span className="breakdown-label">管理費:</span>
                <span className="breakdown-value negative">-¥{weeklyManagementCost.toFixed(2)}</span>
              </div>
              <div className="breakdown-row total">
                <span className="breakdown-label">週次純利益:</span>
                <span className={`breakdown-value ${weeklyCashFlow >= 0 ? 'positive' : 'negative'}`}>
                  {weeklyCashFlow >= 0 ? '+' : ''}{formatCurrency(weeklyCashFlow)}
                </span>
              </div>
            </div>

            {/* 月次・年次の予想 */}
            <div className="projection">
              <div className="projection-item">
                <span className="projection-label">月次純利益（予想）:</span>
                <span className={`projection-value ${weeklyCashFlow * 4 >= 0 ? 'positive' : 'negative'}`}>
                  {weeklyCashFlow * 4 >= 0 ? '+' : ''}{formatCurrency(weeklyCashFlow * 4)}
                </span>
              </div>
              <div className="projection-item">
                <span className="projection-label">年次純利益（予想）:</span>
                <span className={`projection-value ${weeklyCashFlow * 52 >= 0 ? 'positive' : 'negative'}`}>
                  {weeklyCashFlow * 52 >= 0 ? '+' : ''}{formatCurrency(weeklyCashFlow * 52)}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div className="modal-footer">
          <button className="cancel-btn" onClick={onClose} disabled={loading}>
            キャンセル
          </button>
          <button
            className="confirm-btn buy"
            onClick={handleConfirm}
            disabled={!isValid || loading}
          >
            {loading ? '購入中...' : '購入確定'}
          </button>
        </div>
      </div>
    </div>
  )
}

export default RealEstatePurchaseModal
