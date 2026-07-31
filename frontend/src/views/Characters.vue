<template>
  <div class="characters-page">
    <h1>角色市场</h1>
    
    <div class="filters" v-if="characters.length > 0">
      <input 
        v-model="searchQuery" 
        placeholder="搜索角色名称..." 
        class="search-input"
      />
      <select v-model="filterRarity" class="filter-select">
        <option value="">所有稀有度</option>
        <option value="common">普通</option>
        <option value="rare">稀有</option>
        <option value="epic">史诗</option>
        <option value="legendary">传说</option>
      </select>
    </div>

    <div v-if="loading" class="loading">加载中...</div>
    
    <div v-else-if="filteredCharacters.length === 0" class="no-data">
      暂无可交易角色
    </div>

    <div v-else class="characters-grid">
      <div 
        v-for="character in filteredCharacters" 
        :key="character.id" 
        class="character-card"
        :class="character.rarity"
      >
        <div class="character-image">
          <img :src="character.image_url || 'https://via.placeholder.com/200x200?text=Character'" :alt="character.name" />
        </div>
        <div class="character-info">
          <h3>{{ character.name }}</h3>
          <p class="character-class">{{ character.class }} - Lv.{{ character.level }}</p>
          <p class="character-rarity" :class="character.rarity">{{ getRarityText(character.rarity) }}</p>
          <p class="character-price">¥{{ character.price }}</p>
          <p class="character-owner">卖家：{{ character.owner_name }}</p>
          <div class="card-actions">
            <router-link :to="`/character/${character.id}`" class="btn-detail">查看详情</router-link>
            <button 
              v-if="currentUser" 
              @click="buyCharacter(character)" 
              class="btn-buy"
              :disabled="!canBuy(character)"
            >
              购买
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { characterApi } from '../api'

export default {
  name: 'Characters',
  data() {
    return {
      characters: [],
      loading: true,
      searchQuery: '',
      filterRarity: '',
      currentUser: null
    }
  },
  computed: {
    filteredCharacters() {
      return this.characters.filter(char => {
        const matchSearch = char.name.toLowerCase().includes(this.searchQuery.toLowerCase())
        const matchRarity = !this.filterRarity || char.rarity === this.filterRarity
        return matchSearch && matchRarity
      })
    }
  },
  created() {
    this.loadCurrentUser()
    this.loadCharacters()
  },
  methods: {
    loadCurrentUser() {
      const user = localStorage.getItem('currentUser')
      if (user) {
        this.currentUser = JSON.parse(user)
      }
    },
    async loadCharacters() {
      try {
        const response = await characterApi.getCharacters()
        if (response.data.success) {
          this.characters = response.data.data
        }
      } catch (error) {
        console.error('Failed to load characters:', error)
        alert('加载角色列表失败')
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
    canBuy(character) {
      if (!this.currentUser) return false
      return this.currentUser.balance >= character.price
    },
    async buyCharacter(character) {
      if (!confirm(`确定要购买 "${character.name}" 吗？价格：¥${character.price}`)) {
        return
      }

      try {
        const response = await characterApi.buyCharacter(character.id, this.currentUser.id)
        if (response.data.success) {
          alert('购买成功！')
          // 更新本地用户余额
          this.currentUser.balance -= character.price
          localStorage.setItem('currentUser', JSON.stringify(this.currentUser))
          // 重新加载角色列表
          this.loadCharacters()
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
.characters-page h1 {
  margin-bottom: 20px;
  color: #333;
}

.filters {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.search-input,
.filter-select {
  padding: 10px 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
}

.search-input {
  flex: 1;
  max-width: 300px;
}

.loading,
.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #666;
  font-size: 1.2rem;
}

.characters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.character-card {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
  border-left: 4px solid #ccc;
}

.character-card:hover {
  transform: translateY(-5px);
}

.character-card.common { border-left-color: #999; }
.character-card.rare { border-left-color: #3498db; }
.character-card.epic { border-left-color: #9b59b6; }
.character-card.legendary { border-left-color: #f39c12; }

.character-image img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.character-info {
  padding: 15px;
}

.character-info h3 {
  margin-bottom: 8px;
  color: #333;
}

.character-class {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 5px;
}

.character-rarity {
  font-size: 0.85rem;
  font-weight: bold;
  margin-bottom: 10px;
  display: inline-block;
  padding: 3px 8px;
  border-radius: 3px;
}

.character-rarity.common { background-color: #e0e0e0; color: #666; }
.character-rarity.rare { background-color: #e3f2fd; color: #1976d2; }
.character-rarity.epic { background-color: #f3e5f5; color: #7b1fa2; }
.character-rarity.legendary { background-color: #fff3e0; color: #f57c00; }

.character-price {
  font-size: 1.3rem;
  font-weight: bold;
  color: #e74c3c;
  margin-bottom: 10px;
}

.character-owner {
  color: #999;
  font-size: 0.85rem;
  margin-bottom: 15px;
}

.card-actions {
  display: flex;
  gap: 10px;
}

.btn-detail,
.btn-buy {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 5px;
  text-align: center;
  text-decoration: none;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-detail {
  background-color: #f0f0f0;
  color: #333;
}

.btn-detail:hover {
  background-color: #e0e0e0;
}

.btn-buy {
  background-color: #27ae60;
  color: white;
}

.btn-buy:hover:not(:disabled) {
  background-color: #229954;
}

.btn-buy:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
</style>
