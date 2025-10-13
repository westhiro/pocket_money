import React from 'react'
import NewsSection from '../components/NewsSection'
import LearningProgress from '../components/LearningProgress'
import AssetsOverview from '../components/AssetsOverview'
import StockList from '../components/StockList'
import './Home.css'

const Home = () => {
  return (
    <div className="home">
      <div className="home-grid">
        <div className="news-section-wrapper">
          <NewsSection />
        </div>
        <div className="learning-section-wrapper">
          <LearningProgress />
        </div>
        <div className="assets-section-wrapper">
          <AssetsOverview />
        </div>
        <div className="stocks-section-wrapper">
          <StockList />
        </div>
      </div>
    </div>
  )
}

export default Home