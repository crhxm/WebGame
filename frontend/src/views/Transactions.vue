<template>
  <div class="transactions-page">
    <h1>交易记录</h1>

    <div v-if="loading" class="loading">加载中...</div>
    
    <div v-else-if="transactions.length === 0" class="no-data">
      暂无交易记录
    </div>

    <div v-else class="transactions-list">
      <div v-for="transaction in transactions" :key="transaction.id" class="transaction-card">
        <div class="transaction-header">
          <span class="transaction-id">#{{ transaction.id }}</span>
          <span class="transaction-status" :class="transaction.status">{{ getStatusText(transaction.status) }}</span>
        </div>
        
        <div class="transaction-body">
          <div class="transaction-info">
            <p><strong>角色名称:</strong> {{ transaction.character_name }}</p>
            <p><strong>卖家:</strong> {{ transaction.seller_name }}</p>
            <p><strong>买家:</strong> {{ transaction.buyer_name }}</p>
            <p><strong>交易价格:</strong> <span class="price">¥{{ transaction.price }}</span></p>
          </div>
          <div class="transaction-time">
            <p><strong>创建时间:</strong> {{ formatDate(transaction.created_at) }}</p>
            <p v-if="transaction.completed_at"><strong>完成时间:</strong> {{ formatDate(transaction.completed_at) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { transactionApi } from '../api'

export default {
  name: 'Transactions',
  data() {
    return {
      transactions: [],
      loading: true,
      currentUser: null
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
        this.loadTransactions()
      } else {
        this.$router.push('/login')
      }
    },
    async loadTransactions() {
      try {
        const response = await transactionApi.getUserTransactions(this.currentUser.id)
        if (response.data.success) {
          this.transactions = response.data.data
        }
      } catch (error) {
        console.error('Failed to load transactions:', error)
        alert('加载交易记录失败')
      } finally {
        this.loading = false
      }
    },
    getStatusText(status) {
      const map = {
        pending: '待处理',
        completed: '已完成',
        cancelled: '已取消'
      }
      return map[status] || status
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return date.toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>

<style scoped>
.transactions-page h1 {
  margin-bottom: 20px;
  color: #333;
}

.loading,
.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.transactions-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.transaction-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.transaction-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;
}

.transaction-id {
  font-weight: bold;
  color: #667eea;
}

.transaction-status {
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: bold;
}

.transaction-status.pending { background-color: #fff3e0; color: #f57c00; }
.transaction-status.completed { background-color: #e8f5e9; color: #2e7d32; }
.transaction-status.cancelled { background-color: #ffebee; color: #c62828; }

.transaction-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.transaction-info p,
.transaction-time p {
  margin-bottom: 8px;
  color: #666;
}

.transaction-info strong,
.transaction-time strong {
  color: #333;
}

.price {
  color: #e74c3c;
  font-weight: bold;
  font-size: 1.1rem;
}

@media (max-width: 768px) {
  .transaction-body {
    grid-template-columns: 1fr;
  }
}
</style>
