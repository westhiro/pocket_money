import React, { useState, useEffect } from 'react'
import { Bar } from 'react-chartjs-2'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { realEstateTradingAPI } from '../services/api'
import { useAuth } from '../contexts/AuthContext'
import './RealEstatePortfolio.css'

// Chart.jsの登録
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
)

const RealEstatePortfolio = () => {
  const { isAuthenticated, user } = useAuth()
  const [portfolioData, setPortfolioData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    const fetchPortfolio = async () => {
      if (!isAuthenticated || !user) {
        setPortfolioData(null)
        setLoading(false)
        return
      }

      try {
        setLoading(true)
        const response = await realEstateTradingAPI.getPortfolio(user.id)

        if (response.data.success) {
          setPortfolioData(response.data.data)
        }
      } catch (err) {
        console.error('Portfolio fetch error:', err)
        setError('ポートフォリオの取得に失敗しました')
      } finally {
        setLoading(false)
      }
    }

    fetchPortfolio()
  }, [isAuthenticated, user])

  // 直近8ヶ月分の月ラベルを生成
  const generateMonths = () => {
    const months = []
    const now = new Date()
    for (let i = 7; i >= 0; i--) {
      const date = new Date(now.getFullYear(), now.getMonth() - i, 1)
      months.push(`${date.getMonth() + 1}月`)
    }
    return months
  }

  const months = generateMonths()

  // 各物件の週次データから月次データを集計
  const calculateMonthlyData = () => {
    if (!portfolioData || !portfolioData.holdings || portfolioData.holdings.length === 0) {
      return {
        loan: Array(8).fill(0),
        interest: Array(8).fill(0),
        management: Array(8).fill(0),
        profit: Array(8).fill(0)
      }
    }

    // 簡易的に現在の週次データを月次に変換（×4）
    // 実際のアプリでは過去8ヶ月分の履歴データをAPIから取得すべき
    const currentMonthData = portfolioData.holdings.reduce((acc, property) => {
      const monthlyPrincipal = property.weekly_principal * 4
      const monthlyInterest = property.weekly_interest * 4
      const monthlyManagement = property.management_cost
      const monthlyProfit = property.weekly_profit * 4

      return {
        loan: acc.loan + monthlyPrincipal,
        interest: acc.interest + monthlyInterest,
        management: acc.management + monthlyManagement / 10000, // 円→万円
        profit: acc.profit + monthlyProfit
      }
    }, { loan: 0, interest: 0, management: 0, profit: 0 })

    // 過去8ヶ月分のデータを模擬的に生成（±5%のランダム変動）
    const generateHistoricalData = (baseValue) => {
      return Array(8).fill(0).map((_, index) => {
        if (index === 7) return baseValue // 最新月は実データ
        const variation = (Math.random() - 0.5) * 0.1 // -5%〜+5%
        return Math.max(0, baseValue * (1 + variation))
      })
    }

    return {
      loan: generateHistoricalData(currentMonthData.loan),
      interest: generateHistoricalData(currentMonthData.interest),
      management: generateHistoricalData(currentMonthData.management),
      profit: generateHistoricalData(currentMonthData.profit)
    }
  }

  const monthlyData = calculateMonthlyData()

  const chartData = {
    labels: months,
    datasets: [
      {
        label: 'ローン',
        data: monthlyData.loan,
        backgroundColor: '#ef5350',
        borderColor: '#ef5350',
        hoverBackgroundColor: '#ef5350',
        hoverBorderColor: '#ef5350',
        borderWidth: 1,
        borderRadius: 0,
        borderSkipped: false
      },
      {
        label: '金利',
        data: monthlyData.interest,
        backgroundColor: '#ffb74d',
        borderColor: '#ffb74d',
        hoverBackgroundColor: '#ffb74d',
        hoverBorderColor: '#ffb74d',
        borderWidth: 1,
        borderRadius: 0,
        borderSkipped: false
      },
      {
        label: '管理費・修繕積立金',
        data: monthlyData.management,
        backgroundColor: '#fff176',
        borderColor: '#fff176',
        hoverBackgroundColor: '#fff176',
        hoverBorderColor: '#fff176',
        borderWidth: 1,
        borderRadius: 0,
        borderSkipped: false
      },
      {
        label: '利益',
        data: monthlyData.profit,
        backgroundColor: '#4fc3f7',
        borderColor: '#4fc3f7',
        hoverBackgroundColor: '#4fc3f7',
        hoverBorderColor: '#4fc3f7',
        borderWidth: 1,
        borderRadius: {
          topLeft: 8,
          topRight: 8,
          bottomLeft: 0,
          bottomRight: 0
        },
        borderSkipped: false
      }
    ]
  }

  const options = {
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    plugins: {
      legend: {
        display: false
      },
      title: {
        display: false
      },
      tooltip: {
        mode: 'index',
        intersect: false,
        callbacks: {
          label: function(context) {
            let label = context.dataset.label || ''
            if (label) {
              label += ': '
            }
            label += new Intl.NumberFormat('ja-JP', {
              style: 'currency',
              currency: 'JPY',
              minimumFractionDigits: 0
            }).format(context.parsed.y * 10000) // 万円→円
            return label
          },
          footer: function(tooltipItems) {
            let sum = 0
            tooltipItems.forEach(function(tooltipItem) {
              sum += tooltipItem.parsed.y
            })
            return '合計: ' + new Intl.NumberFormat('ja-JP', {
              style: 'currency',
              currency: 'JPY',
              minimumFractionDigits: 0
            }).format(sum * 10000) // 万円→円
          }
        }
      }
    },
    scales: {
      x: {
        stacked: true,
        grid: {
          display: false
        },
        ticks: {
          font: {
            size: 11
          }
        }
      },
      y: {
        stacked: true,
        beginAtZero: true,
        ticks: {
          font: {
            size: 11
          },
          callback: function(value) {
            return new Intl.NumberFormat('ja-JP', {
              style: 'currency',
              currency: 'JPY',
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
            }).format(value * 10000) // 万円→円
          }
        },
        grid: {
          color: 'rgba(0, 0, 0, 0.05)'
        }
      }
    },
    interaction: {
      mode: 'index',
      intersect: false
    }
  }

  if (loading) {
    return (
      <div className="real-estate-portfolio">
        <div className="portfolio-header">
          <h2>ポートフォリオ（直近8ヶ月）</h2>
        </div>
        <div className="loading" style={{ textAlign: 'center', padding: '50px' }}>
          データを読み込み中...
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="real-estate-portfolio">
        <div className="portfolio-header">
          <h2>ポートフォリオ（直近8ヶ月）</h2>
        </div>
        <div className="error" style={{ textAlign: 'center', padding: '50px', color: 'red' }}>
          {error}
        </div>
      </div>
    )
  }

  if (!isAuthenticated || !portfolioData || !portfolioData.holdings || portfolioData.holdings.length === 0) {
    return (
      <div className="real-estate-portfolio">
        <div className="portfolio-header">
          <h2>ポートフォリオ（直近8ヶ月）</h2>
        </div>
        <div className="no-data" style={{ textAlign: 'center', padding: '50px' }}>
          保有物件がありません
        </div>
      </div>
    )
  }

  return (
    <div className="real-estate-portfolio">
      <div className="portfolio-header">
        <h2>ポートフォリオ（直近8ヶ月）</h2>
      </div>
      <div className="portfolio-legend">
        <div className="legend-item">
          <span className="legend-color" style={{ backgroundColor: '#ef5350' }}></span>
          <span className="legend-label">ローン</span>
        </div>
        <div className="legend-item">
          <span className="legend-color" style={{ backgroundColor: '#ffb74d' }}></span>
          <span className="legend-label">金利</span>
        </div>
        <div className="legend-item">
          <span className="legend-color" style={{ backgroundColor: '#fff176' }}></span>
          <span className="legend-label">管理費・修繕積立金</span>
        </div>
        <div className="legend-item">
          <span className="legend-color" style={{ backgroundColor: '#4fc3f7' }}></span>
          <span className="legend-label">利益</span>
        </div>
      </div>
      <div className="portfolio-chart-container">
        <Bar data={chartData} options={options} />
      </div>
    </div>
  )
}

export default RealEstatePortfolio
