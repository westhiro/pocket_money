import React from 'react'
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
  // 直近8ヶ月分のモックデータ
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

  // モックデータ（各月のローン、金利、管理費・修繕積立金、利益）
  const mockData = {
    loan: [80, 82, 78, 85, 81, 83, 79, 84],
    interest: [20, 19.5, 21, 20.5, 20, 19.8, 20.3, 20.1],
    management: [30, 30, 30, 30, 30, 30, 30, 30],
    profit: [50, 48.5, 51, 44.5, 49, 47.2, 50.7, 45.9]
  }

  const chartData = {
    labels: months,
    datasets: [
      {
        label: 'ローン',
        data: mockData.loan,
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
        data: mockData.interest,
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
        data: mockData.management,
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
        data: mockData.profit,
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
            }).format(context.parsed.y)
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
            }).format(sum)
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
            }).format(value)
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
