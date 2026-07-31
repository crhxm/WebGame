<template>
  <div class="home">
    <div class="hero-section">
      <h1>欢迎来到角色交易系统</h1>
      <p>安全、便捷的游戏角色交易平台</p>
      <div class="hero-buttons">
        <router-link to="/characters" class="btn-primary">浏览角色</router-link>
        <router-link v-if="!currentUser" to="/register" class="btn-secondary">立即注册</router-link>
      </div>
    </div>

    <div class="features-section">
      <h2>平台特色</h2>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">🛡️</div>
          <h3>安全可靠</h3>
          <p>所有交易都经过系统验证，保障买卖双方权益</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">⚡</div>
          <h3>快速交易</h3>
          <p>即时到账，无需等待，快速完成角色转移</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">💰</div>
          <h3>合理定价</h3>
          <p>市场自由定价，透明公正的交易环境</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">📊</div>
          <h3>交易记录</h3>
          <p>完整的交易历史记录，随时查看交易详情</p>
        </div>
      </div>
    </div>

    <div class="stats-section" v-if="stats.loaded">
      <h2>平台数据</h2>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">{{ stats.characterCount }}</div>
          <div class="stat-label">可交易角色</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ stats.transactionCount }}</div>
          <div class="stat-label">总交易数</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">{{ stats.userCount }}</div>
          <div class="stat-label">注册用户</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { characterApi, transactionApi, userApi } from '../api'

export default {
  name: 'Home',
  data() {
    return {
      currentUser: null,
      stats: {
        loaded: false,
        characterCount: 0,
        transactionCount: 0,
        userCount: 0
      }
    }
  },
  created() {
    this.loadCurrentUser()
    this.loadStats()
  },
  methods: {
    loadCurrentUser() {
      const user = localStorage.getItem('currentUser')
      if (user) {
        this.currentUser = JSON.parse(user)
      }
    },
    async loadStats() {
      try {
        const [charRes, transRes, userRes] = await Promise.all([
          characterApi.getCharacters(),
          transactionApi.getTransactions(),
          userApi.getUsers()
        ])
        
        if (charRes.data.success) {
          this.stats.characterCount = charRes.data.data.length
        }
        if (transRes.data.success) {
          this.stats.transactionCount = transRes.data.data.length
        }
        if (userRes.data.success) {
          this.stats.userCount = userRes.data.data.length
        }
        this.stats.loaded = true
      } catch (error) {
        console.error('Failed to load stats:', error)
        // 即使 API 调用失败也标记为已加载，避免一直显示加载中
        this.stats.loaded = true
      }
    }
  }
}
</script>

<style scoped>
.home {
  padding: 20px;
}

.hero-section {
  text-align: center;
  padding: 60px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 10px;
  margin-bottom: 40px;
}

.hero-section h1 {
  font-size: 2.5rem;
  margin-bottom: 15px;
}

.hero-section p {
  font-size: 1.2rem;
  margin-bottom: 30px;
  opacity: 0.9;
}

.hero-buttons {
  display: flex;
  gap: 20px;
  justify-content: center;
}

.btn-primary,
.btn-secondary {
  padding: 12px 30px;
  border-radius: 5px;
  text-decoration: none;
  font-weight: bold;
  transition: all 0.3s;
}

.btn-primary {
  background-color: white;
  color: #667eea;
}

.btn-primary:hover {
  background-color: #f0f0f0;
  transform: translateY(-2px);
}

.btn-secondary {
  background-color: transparent;
  color: white;
  border: 2px solid white;
}

.btn-secondary:hover {
  background-color: white;
  color: #667eea;
  transform: translateY(-2px);
}

.features-section {
  margin-bottom: 40px;
}

.features-section h2,
.stats-section h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #333;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.feature-card {
  background: white;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  text-align: center;
  transition: transform 0.3s;
}

.feature-card:hover {
  transform: translateY(-5px);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 15px;
}

.feature-card h3 {
  color: #667eea;
  margin-bottom: 10px;
}

.feature-card p {
  color: #666;
  line-height: 1.6;
}

.stats-section {
  background: white;
  padding: 40px;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  text-align: center;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 10px;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 10px;
}

.stat-label {
  font-size: 1rem;
  opacity: 0.9;
}
</style>
