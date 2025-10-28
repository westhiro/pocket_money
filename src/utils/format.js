/**
 * 通貨フォーマット関数
 * 数値を日本円の通貨形式（¥1,000）に変換します
 * @param {number} amount - フォーマットする金額
 * @returns {string} フォーマットされた通貨文字列
 */
export const formatCurrency = (amount) => {
  return new Intl.NumberFormat('ja-JP', {
    style: 'currency',
    currency: 'JPY',
    maximumFractionDigits: 0
  }).format(amount)
}

/**
 * 数値フォーマット関数
 * 数値をカンマ区切りの文字列（1,000）に変換します
 * @param {number} amount - フォーマットする数値
 * @returns {string} フォーマットされた数値文字列
 */
export const formatNumber = (amount) => {
  return new Intl.NumberFormat('ja-JP').format(amount)
}
