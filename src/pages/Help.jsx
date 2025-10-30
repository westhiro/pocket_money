import React from 'react'
import './Help.css'

const Help = () => {
  return (
    <div className="help-page">
      <div className="page-header">
        <h1>ヘルプ</h1>
      </div>

      <div className="help-content">
        <div className="help-section">
          <h2>準備中</h2>
          <p>ヘルプページは現在準備中です。</p>
          <p>近日中に以下の内容を追加予定です：</p>
          <ul>
            <li>アプリの使い方</li>
            <li>よくある質問（FAQ）</li>
            <li>投資の基礎知識</li>
            <li>トラブルシューティング</li>
          </ul>
        </div>
      </div>
    </div>
  )
}

export default Help
