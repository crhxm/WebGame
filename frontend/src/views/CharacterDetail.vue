<template>
  <div class="character-detail">
    <div v-if="loading" class="loading">加载中...</div>
    
    <div v-else-if="!character" class="not-found">
      <h2>角色不存在</h2>
      <router-link to="/characters" class="btn-back">返回角色市场</router-link>
    </div>

    <div v-else class="detail-card">
      <div class="detail-header">
        <button @click="$router.back()" class="btn-back-small">← 返回</button>
        <span class="rarity-badge" :class="character.rarity">{{ getRarityText(character.rarity) }}</span>
      </div>

      <div class="detail-content">
        <div class="detail-image">
          <img :src="character.image_url || 'https://via.placeholder.com/400x400?text=Character'" :alt="character.name" />
        </div>

        <div class="detail-info">
          <h1>{{ character.name }}</h1>
          
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">职业</span>
              <span class="info-value">{{ character.class }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">等级</span>
              <span class="info-value">{{ character.level }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">价格</span>
              <span class="info-value price">¥{{ character.price }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">卖家</span>
              <span class="info-value">{{ character.owner_name }}</span>
            </div>
          </div>

          <div class="description-section">
            <h3>角色描述</h3>
            <p>{{ character.description || '暂无描述' }}</p>
          </div>

          <div class="action-section">
            <button 
              v-if="currentUser && currentUser.id !== character.user_id" 
              @click="buyCharacter" 
              class="btn-buy"
              :disabled="currentUser.balance < character.price"
            >
              立即购买 (¥{{ character.price }})
            </button>
            <p v-else-if="currentUser && currentUser.id === character.user_id" class="owner-note">
              这是您的角色，无法购买
            </p>
            <p v-else class="login-note">
              请登录后购买
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { characterApi } from '../api'

export default {
  name: 'CharacterDetail',
  data() {
    return {
      character: null,
      loading: true,
      currentUser: null
    }
  },
  created() {
    this.loadCurrentUser()
    this.loadCharacter()
  },
  methods: {
    loadCurrentUser() {
      const user = localStorage.getItem('currentUser')
      if (user) {
        this.currentUser = JSON.parse(user)
      }
    },
    async loadCharacter() {
      try {
        const response = await characterApi.getCharacter(this.$route.params.id)
        if (response.data.success) {
          this.character = response.data.data
        } else {
          this.character = null
        }
      } catch (error) {
        console.error('Failed to load character:', error)
        this.character = null
      } finally {
        this.loading = false
      }
    },
    getRarityText(rarity) {
      const map = {
        common: '普通',
        rare: '稀有',
        epic: '史诗',
        legendary: '传说'
      }
      return map[rarity] || rarity
    },
    async buyCharacter() {
      if (!confirm(`确定要购买 "${this.character.name}" 吗？价格：¥${this.character.price}`)) {
        return
      }

      try {
        const response = await characterApi.buyCharacter(this.character.id, this.currentUser.id)
        if (response.data.success) {
          alert('购买成功！')
          // 更新本地用户余额
          this.currentUser.balance -= this.character.price
          localStorage.setItem('currentUser', JSON.stringify(this.currentUser))
          // 重新加载角色详情
          this.loadCharacter()
          // 触发 App 组件更新用户信息
          window.dispatchEvent(new Event('storage'))
        } else {
          alert('购买失败：' + (response.data.error || '未知错误'))
        }
      } catch (error) {
        console.error('Purchase failed:', error)
        alert('购买失败：' + (error.response?.data?.error || '网络错误'))
      }
    }
  }
}
</script>

<style scoped>
.character-detail {
  padding: 20px;
}

.loading,
.not-found {
  text-align: center;
  padding: 60px 20px;
}

.not-found h2 {
  color: #666;
  margin-bottom: 20px;
}

.btn-back {
  display: inline-block;
  padding: 10px 20px;
  background-color: #667eea;
  color: white;
  text-decoration: none;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.btn-back:hover {
  background-color: #5a6fd6;
}

.detail-card {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

.detail-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-back-small {
  background: transparent;
  border: none;
  color: white;
  font-size: 1rem;
  cursor: pointer;
  padding: 5px 10px;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.btn-back-small:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.rarity-badge {
  padding: 5px 15px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 0.9rem;
}

.rarity-badge.common { background-color: #999; color: white; }
.rarity-badge.rare { background-color: #3498db; color: white; }
.rarity-badge.epic { background-color: #9b59b6; color: white; }
.rarity-badge.legendary { background-color: #f39c12; color: white; }

.detail-content {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 30px;
  padding: 30px;
}

.detail-image img {
  width: 100%;
  border-radius: 10px;
}

.detail-info h1 {
  color: #333;
  margin-bottom: 20px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
  margin-bottom: 25px;
}

.info-item {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
}

.info-label {
  display: block;
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 5px;
}

.info-value {
  display: block;
  color: #333;
  font-weight: bold;
  font-size: 1.1rem;
}

.info-value.price {
  color: #e74c3c;
  font-size: 1.4rem;
}

.description-section {
  margin-bottom: 25px;
}

.description-section h3 {
  color: #333;
  margin-bottom: 10px;
}

.description-section p {
  color: #666;
  line-height: 1.8;
}

.action-section {
  text-align: center;
}

.btn-buy {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.2rem;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.3s;
}

.btn-buy:hover:not(:disabled) {
  transform: translateY(-2px);
}

.btn-buy:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.owner-note,
.login-note {
  color: #666;
  font-size: 1rem;
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

@media (max-width: 768px) {
  .detail-content {
    grid-template-columns: 1fr;
  }
}
</style>
