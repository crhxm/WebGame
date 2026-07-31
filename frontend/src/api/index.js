import axios from 'axios';

const API_BASE_URL = 'http://localhost:8080/backend';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json'
  }
});

// 用户相关 API
export const userApi = {
  // 获取用户列表
  getUsers: () => api.get('/users.php?action=list'),
  
  // 获取用户详情
  getUser: (id) => api.get(`/users.php?action=detail&id=${id}`),
  
  // 注册
  register: (userData) => api.post('/users.php?action=register', userData),
  
  // 登录
  login: (credentials) => api.post('/users.php?action=login', credentials),
  
  // 更新余额
  updateBalance: (userId, balance) => api.post('/users.php?action=update_balance', { user_id: userId, balance })
};

// 角色相关 API
export const characterApi = {
  // 获取可交易角色列表
  getCharacters: () => api.get('/characters.php?action=list'),
  
  // 获取角色详情
  getCharacter: (id) => api.get(`/characters.php?action=detail&id=${id}`),
  
  // 获取用户的角色
  getMyCharacters: (userId) => api.get(`/characters.php?action=my&user_id=${userId}`),
  
  // 创建角色
  createCharacter: (characterData) => api.post('/characters.php?action=create', characterData),
  
  // 更新角色
  updateCharacter: (characterData) => api.put('/characters.php?action=update', characterData),
  
  // 购买角色
  buyCharacter: (characterId, buyerId) => api.post('/characters.php?action=buy', { character_id: characterId, buyer_id: buyerId }),
  
  // 上架角色
  listCharacter: (characterId) => api.post('/characters.php?action=list', { character_id: characterId })
};

// 交易记录相关 API
export const transactionApi = {
  // 获取所有交易记录
  getTransactions: () => api.get('/transactions.php?action=list'),
  
  // 获取用户交易记录
  getUserTransactions: (userId) => api.get(`/transactions.php?action=user&user_id=${userId}`),
  
  // 获取交易详情
  getTransaction: (id) => api.get(`/transactions.php?action=detail&id=${id}`),
  
  // 取消交易
  cancelTransaction: (transactionId) => api.post('/transactions.php?action=cancel', { transaction_id: transactionId })
};

export default api;
