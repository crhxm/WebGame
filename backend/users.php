<?php
// users.php - 用户管理接口
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        if ($action === 'detail' && isset($_GET['id'])) {
            // 获取用户详情
            $stmt = $pdo->prepare("SELECT id, username, email, balance, created_at FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'User not found']);
            }
        } elseif ($action === 'list') {
            // 获取所有用户列表（不包含密码）
            $stmt = $pdo->query("SELECT id, username, email, balance, created_at FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $users]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'register') {
            // 注册用户
            $required = ['username', 'email', 'password'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    echo json_encode(['success' => false, 'error' => "Missing field: $field"]);
                    exit();
                }
            }
            
            // 检查用户名或邮箱是否已存在
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$data['username'], $data['email']]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
                exit();
            }
            
            // 密码加密
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, balance) VALUES (?, ?, ?, 1000.00)");
            $stmt->execute([$data['username'], $data['email'], $passwordHash]);
            
            echo json_encode(['success' => true, 'message' => 'Registration successful', 'id' => $pdo->lastInsertId()]);
        } elseif ($action === 'login') {
            // 用户登录
            if (!isset($data['username']) || !isset($data['password'])) {
                echo json_encode(['success' => false, 'error' => 'Missing username or password']);
                exit();
            }
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$data['username'], $data['username']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($data['password'], $user['password_hash'])) {
                // 登录成功，返回用户信息（不返回密码）
                unset($user['password_hash']);
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
            }
        } elseif ($action === 'update_balance') {
            // 更新用户余额（用于测试或充值）
            if (!isset($data['user_id']) || !isset($data['balance'])) {
                echo json_encode(['success' => false, 'error' => 'Missing user_id or balance']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$data['balance'], $data['user_id']]);
            echo json_encode(['success' => true, 'message' => 'Balance updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'update' && isset($data['id'])) {
            // 更新用户信息
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$data['username'], $data['email'], $data['id']]);
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>
