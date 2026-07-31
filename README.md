# 角色交易系统

一个基于 Vue 3.0 + PHP 的游戏角色交易平台。

## 技术栈

### 前端
- Vue 3.0
- Vue Router
- Axios
- Vite

### 后端
- PHP 7.4+
- MySQL/MariaDB
- PDO (数据库访问)

## 项目结构

```
/workspace
├── backend/                 # 后端 PHP 代码
│   ├── config.php          # 数据库配置
│   ├── database.sql        # 数据库初始化脚本
│   ├── users.php           # 用户管理接口
│   ├── characters.php      # 角色管理接口
│   └── transactions.php    # 交易记录接口
└── frontend/               # 前端 Vue 代码
    ├── src/
    │   ├── api/            # API 调用封装
    │   ├── router/         # 路由配置
    │   ├── views/          # 页面组件
    │   ├── App.vue         # 根组件
    │   └── main.js         # 入口文件
    └── package.json
```

## 安装步骤

### 1. 后端设置

#### 1.1 配置数据库
```bash
# 使用 MySQL 客户端执行
mysql -u root -p < /workspace/backend/database.sql
```

#### 1.2 修改数据库配置
编辑 `/workspace/backend/config.php`，修改数据库连接信息：
```php
$host = 'localhost';
$dbname = 'character_trading';
$username = 'root';
$password = 'your_password';
```

#### 1.3 启动 PHP 内置服务器
```bash
cd /workspace/backend
php -S localhost:8080
```

### 2. 前端设置

#### 2.1 安装依赖
```bash
cd /workspace/frontend
npm install
```

#### 2.2 启动开发服务器
```bash
npm run dev
```

#### 2.3 配置 API 地址
如果需要修改后端 API 地址，编辑 `/workspace/frontend/src/api/index.js`：
```javascript
const API_BASE_URL = 'http://localhost:8080';
```

## 功能特性

### 用户系统
- 用户注册/登录
- 用户余额管理
- 个人信息查看

### 角色系统
- 角色列表展示（支持搜索和筛选）
- 角色详情查看
- 角色创建/编辑
- 角色上架/下架

### 交易系统
- 角色购买
- 自动余额扣除/增加
- 交易记录查询
- 交易状态跟踪

### 稀有度系统
- 普通 (Common)
- 稀有 (Rare)
- 史诗 (Epic)
- 传说 (Legendary)

## API 接口

### 用户接口 (`users.php`)
- `GET /users.php?action=list` - 获取用户列表
- `GET /users.php?action=detail&id={id}` - 获取用户详情
- `POST /users.php?action=register` - 用户注册
- `POST /users.php?action=login` - 用户登录
- `POST /users.php?action=update_balance` - 更新余额

### 角色接口 (`characters.php`)
- `GET /characters.php?action=list` - 获取可交易角色列表
- `GET /characters.php?action=detail&id={id}` - 获取角色详情
- `GET /characters.php?action=my&user_id={id}` - 获取用户角色
- `POST /characters.php?action=create` - 创建角色
- `PUT /characters.php?action=update` - 更新角色
- `POST /characters.php?action=buy` - 购买角色
- `POST /characters.php?action=list` - 上架角色

### 交易接口 (`transactions.php`)
- `GET /transactions.php?action=list` - 获取所有交易记录
- `GET /transactions.php?action=user&user_id={id}` - 获取用户交易记录
- `GET /transactions.php?action=detail&id={id}` - 获取交易详情
- `POST /transactions.php?action=cancel` - 取消交易

## 默认测试账号

数据库初始化后会创建两个测试账号：

| 用户名 | 邮箱 | 密码 | 初始余额 |
|--------|------|------|----------|
| player1 | player1@example.com | password | ¥1000 |
| player2 | player2@example.com | password | ¥500 |

## 注意事项

1. **CORS 配置**: 后端已配置 CORS 允许跨域请求
2. **密码安全**: 密码使用 bcrypt 加密存储
3. **事务处理**: 购买操作使用数据库事务保证数据一致性
4. **输入验证**: 前后端都进行了必要的输入验证

## 开发说明

### 添加新功能
1. 在后端添加相应的 PHP 接口
2. 在前端 `api/index.js` 中添加 API 调用方法
3. 创建对应的 Vue 组件
4. 在路由中配置新页面

### 样式定制
所有组件都使用了 scoped CSS，可以直接在组件内修改样式。
主题色为紫色渐变 (#667eea - #764ba2)，可以根据需要修改。

## License

MIT License
