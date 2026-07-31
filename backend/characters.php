<?php
// characters.php - 角色管理接口
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            // 获取所有可交易角色
            $stmt = $pdo->query("
                SELECT c.*, u.username as owner_name 
                FROM characters c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.status = 'available'
                ORDER BY c.created_at DESC
            ");
            $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $characters]);
        } elseif ($action === 'detail' && isset($_GET['id'])) {
            // 获取角色详情
            $stmt = $pdo->prepare("
                SELECT c.*, u.username as owner_name 
                FROM characters c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.id = ?
            ");
            $stmt->execute([$_GET['id']]);
            $character = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($character) {
                echo json_encode(['success' => true, 'data' => $character]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Character not found']);
            }
        } elseif ($action === 'my' && isset($_GET['user_id'])) {
            // 获取用户自己的角色
            $stmt = $pdo->prepare("
                SELECT * FROM characters WHERE user_id = ? ORDER BY created_at DESC
            ");
            $stmt->execute([$_GET['user_id']]);
            $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $characters]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'create') {
            // 创建新角色
            $required = ['user_id', 'name', 'level', 'class', 'rarity', 'price'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    echo json_encode(['success' => false, 'error' => "Missing field: $field"]);
                    exit();
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO characters (user_id, name, level, class, rarity, price, description, image_url, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available')
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['name'],
                $data['level'],
                $data['class'],
                $data['rarity'],
                $data['price'],
                $data['description'] ?? '',
                $data['image_url'] ?? ''
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Character created successfully', 'id' => $pdo->lastInsertId()]);
        } elseif ($action === 'buy') {
            // 购买角色
            if (!isset($data['character_id']) || !isset($data['buyer_id'])) {
                echo json_encode(['success' => false, 'error' => 'Missing character_id or buyer_id']);
                exit();
            }
            
            $pdo->beginTransaction();
            try {
                // 获取角色信息
                $stmt = $pdo->prepare("SELECT * FROM characters WHERE id = ? AND status = 'available' FOR UPDATE");
                $stmt->execute([$data['character_id']]);
                $character = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$character) {
                    throw new Exception('Character not available');
                }
                
                // 检查买家余额
                $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
                $stmt->execute([$data['buyer_id']]);
                $buyer = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($buyer['balance'] < $character['price']) {
                    throw new Exception('Insufficient balance');
                }
                
                // 更新买家余额
                $newBalance = $buyer['balance'] - $character['price'];
                $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmt->execute([$newBalance, $data['buyer_id']]);
                
                // 更新卖家余额
                $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$character['price'], $character['user_id']]);
                
                // 更新角色状态
                $stmt = $pdo->prepare("UPDATE characters SET status = 'sold', user_id = ? WHERE id = ?");
                $stmt->execute([$data['buyer_id'], $data['character_id']]);
                
                // 创建交易记录
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (character_id, seller_id, buyer_id, price, status, completed_at)
                    VALUES (?, ?, ?, ?, 'completed', NOW())
                ");
                $stmt->execute([
                    $data['character_id'],
                    $character['user_id'],
                    $data['buyer_id'],
                    $character['price']
                ]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Purchase successful']);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } elseif ($action === 'list') {
            // 上架角色
            if (!isset($data['character_id'])) {
                echo json_encode(['success' => false, 'error' => 'Missing character_id']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE characters SET status = 'listed' WHERE id = ?");
            $stmt->execute([$data['character_id']]);
            echo json_encode(['success' => true, 'message' => 'Character listed for sale']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'update' && isset($data['id'])) {
            // 更新角色信息
            $stmt = $pdo->prepare("
                UPDATE characters 
                SET name = ?, level = ?, class = ?, rarity = ?, price = ?, description = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['name'],
                $data['level'],
                $data['class'],
                $data['rarity'],
                $data['price'],
                $data['description'],
                $data['id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Character updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>
