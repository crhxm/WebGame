<template>
  <div id="app">
    <nav class="navbar">
      <div class="container">
        <router-link to="/" class="logo">角色交易系统</router-link>
        <div class="nav-links">
          <router-link to="/">首页</router-link>
          <router-link to="/characters">角色市场</router-link>
          <router-link v-if="currentUser" to="/my-characters">我的角色</router-link>
          <router-link v-if="currentUser" to="/transactions">交易记录</router-link>
          <span v-if="currentUser" class="user-info">
            {{ currentUser.username }} | 余额：¥{{ currentUser.balance }}
          </span>
          <router-link v-if="!currentUser" to="/login">登录</router-link>
          <router-link v-if="!currentUser" to="/register">注册</router-link>
          <button v-if="currentUser" @click="logout" class="btn-logout">退出</button>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <router-view />
    </main>

    <footer class="footer">
      <p>&copy; 2024 角色交易系统 - Vue3 + PHP</p>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
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
      }
    },
    logout() {
      localStorage.removeItem('currentUser')
      this.currentUser = null
      this.$router.push('/login')
    }
  },
  watch: {
    '$route'(to, from) {
      this.loadCurrentUser()
    }
  }
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Microsoft YaHei', Arial, sans-serif;
  background-color: #f5f5f5;
  min-height: 100vh;
}

#app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1rem 0;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.navbar .container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  text-decoration: none;
}

.nav-links {
  display: flex;
  gap: 20px;
  align-items: center;
}

.nav-links a {
  color: white;
  text-decoration: none;
  padding: 8px 16px;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  background-color: rgba(255, 255, 255, 0.2);
}

.user-info {
  background-color: rgba(255, 255, 255, 0.2);
  padding: 8px 16px;
  border-radius: 5px;
}

.btn-logout {
  background-color: #ff6b6b;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-logout:hover {
  background-color: #ee5a5a;
}

.main-content {
  flex: 1;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  width: 100%;
}

.footer {
  background-color: #333;
  color: white;
  text-align: center;
  padding: 20px;
  margin-top: auto;
}
</style>
