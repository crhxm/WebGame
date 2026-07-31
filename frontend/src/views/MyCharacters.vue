<template>
  <div class="my-characters-page">
    <h1>我的角色</h1>

    <div v-if="loading" class="loading">加载中...</div>
    
    <div v-else-if="characters.length === 0" class="no-data">
      暂无角色，去<a router-link to="/characters">角色市场</a>购买吧！
    </div>

    <div v-else class="characters-grid">
      <div v-for="character in characters" :key="character.id" class="character-card">
        <div class="character-image">
          <img :src="character.image_url || 'https://via.placeholder.com/200x200?text=Character'" :alt="character.name" />
        </div>
        <div class="character-info">
          <h3>{{ character.name }}</h3>
          <p class="character-class">{{ character.class }} - Lv.{{ character.level }}</p>
          <p class="character-rarity" :class="character.rarity">{{ getRarityText(character.rarity) }}</p>
          <p class="character-status">
            状态：
            <span :class="character.status">{{ getStatusText(character.status) }}</span>
          </p>
          <p class="character-price">估价：¥{{ character.price }}</p>
          <div class="card-actions">
            <button @click="editCharacter(character)" class="btn-edit">编辑</button>
            <button 
              v-if="character.status === 'available'" 
              @click="listCharacter(character)" 
              class="btn-list"
            >
              上架出售
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 编辑角色模态框 -->
    <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
      <div class="modal-content" @click.stop>
        <h2>编辑角色</h2>
        <form @submit.prevent="saveCharacter">
          <div class="form-group">
            <label>角色名称</label>
            <input type="text" v-model="editForm.name" required />
          </div>
          <div class="form-group">
            <label>等级</label>
            <input type="number" v-model.number="editForm.level" required min="1" />
          </div>
          <div class="form-group">
            <label>职业</label>
            <input type="text" v-model="editForm.class" required />
          </div>
          <div class="form-group">
            <label>稀有度</label>
            <select v-model="editForm.rarity" required>
              <option value="common">普通</option>
              <option value="rare">稀有</option>
              <option value="epic">史诗</option>
              <option value="legendary">传说</option>
            </select>
          </div>
          <div class="form-group">
            <label>价格</label>
            <input type="number" v-model.number="editForm.price" required min="0" step="0.01" />
          </div>
          <div class="form-group">
            <label>描述</label>
            <textarea v-model="editForm.description" rows="4"></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeEditModal" class="btn-cancel">取消</button>
            <button type="submit" class="btn-save" :disabled="saving">
              {{ saving ? '保存中...' : '保存' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { characterApi } from '../api'

export default {
  name: 'MyCharacters',
  data() {
    return {
      characters: [],
      loading: true,
      currentUser: null,
      showEditModal: false,
      saving: false,
      editForm: {
        id: null,
        name: '',
        level: 1,
        class: '',
        rarity: 'common',
        price: 0,
        description: ''
      }
    }
  },
  created() {
    this.loadCurrentUser()
  },
  methods: {
    loadCurrentUser() {
      const user = localStorage.getItem('currentUser')
      if (user) {
        this.currentUser = JSON.parse(user)
        this.loadCharacters()
      } else {
        this.$router.push('/login')
      }
    },
    async loadCharacters() {
      try {
        const response = await characterApi.getMyCharacters(this.currentUser.id)
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
    getStatusText(status) {
      const map = {
        available: '未上架',
        listed: '已上架',
        sold: '已售出'
      }
      return map[status] || status
    },
    editCharacter(character) {
      this.editForm = { ...character }
      this.showEditModal = true
    },
    closeEditModal() {
      this.showEditModal = false
      this.editForm = {
        id: null,
        name: '',
        level: 1,
        class: '',
        rarity: 'common',
        price: 0,
        description: ''
      }
    },
    async saveCharacter() {
      this.saving = true
      try {
        const response = await characterApi.updateCharacter(this.editForm)
        if (response.data.success) {
          alert('更新成功！')
          this.closeEditModal()
          this.loadCharacters()
        } else {
          alert('更新失败：' + (response.data.error || '未知错误'))
        }
      } catch (error) {
        console.error('Update failed:', error)
        alert('更新失败：' + (error.response?.data?.error || '网络错误'))
      } finally {
        this.saving = false
      }
    },
    async listCharacter(character) {
      if (!confirm(`确定要上架 "${character.name}" 吗？价格：¥${character.price}`)) {
        return
      }

      try {
        const response = await characterApi.listCharacter(character.id)
        if (response.data.success) {
          alert('上架成功！')
          this.loadCharacters()
        } else {
          alert('上架失败：' + (response.data.error || '未知错误'))
        }
      } catch (error) {
        console.error('List failed:', error)
        alert('上架失败：' + (error.response?.data?.error || '网络错误'))
      }
    }
  }
}
</script>

<style scoped>
.my-characters-page h1 {
  margin-bottom: 20px;
  color: #333;
}

.loading,
.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.no-data a {
  color: #667eea;
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
}

.character-card:hover {
  transform: translateY(-5px);
}

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

.character-status {
  margin-bottom: 10px;
  font-size: 0.9rem;
}

.character-status span {
  font-weight: bold;
  padding: 3px 8px;
  border-radius: 3px;
}

.character-status span.available { background-color: #e8f5e9; color: #2e7d32; }
.character-status span.listed { background-color: #fff3e0; color: #f57c00; }
.character-status span.sold { background-color: #ffebee; color: #c62828; }

.character-price {
  font-size: 1.2rem;
  font-weight: bold;
  color: #e74c3c;
  margin-bottom: 15px;
}

.card-actions {
  display: flex;
  gap: 10px;
}

.btn-edit,
.btn-list {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-edit {
  background-color: #f0f0f0;
  color: #333;
}

.btn-edit:hover {
  background-color: #e0e0e0;
}

.btn-list {
  background-color: #3498db;
  color: white;
}

.btn-list:hover {
  background-color: #2980b9;
}

/* 模态框样式 */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h2 {
  margin-bottom: 20px;
  color: #333;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #333;
  font-weight: bold;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
}

.form-group textarea {
  resize: vertical;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.btn-cancel,
.btn-save {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
}

.btn-cancel {
  background-color: #f0f0f0;
  color: #333;
}

.btn-save {
  background-color: #667eea;
  color: white;
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
</style>
