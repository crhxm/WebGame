<?php
// transactions.php - 交易记录接口
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            // 获取所有交易记录
            $stmt = $pdo->query("
                SELECT 
                    t.*,
                    c.name as character_name,
                    seller.username as seller_name,
                    buyer.username as buyer_name
                FROM transactions t
                JOIN characters c ON t.character_id = c.id
                JOIN users seller ON t.seller_id = seller.id
                JOIN users buyer ON t.buyer_id = buyer.id
                ORDER BY t.created_at DESC
            ");
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $transactions]);
        } elseif ($action === 'user' && isset($_GET['user_id'])) {
            // 获取用户的交易记录（作为买家或卖家）
            $stmt = $pdo->prepare("
                SELECT 
                    t.*,
                    c.name as character_name,
                    seller.username as seller_name,
                    buyer.username as buyer_name
                FROM transactions t
                JOIN characters c ON t.character_id = c.id
                JOIN users seller ON t.seller_id = seller.id
                JOIN users buyer ON t.buyer_id = buyer.id
                WHERE t.seller_id = ? OR t.buyer_id = ?
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([$_GET['user_id'], $_GET['user_id']]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $transactions]);
        } elseif ($action === 'detail' && isset($_GET['id'])) {
            // 获取交易详情
            $stmt = $pdo->prepare("
                SELECT 
                    t.*,
                    c.name as character_name,
                    seller.username as seller_name,
                    buyer.username as buyer_name
                FROM transactions t
                JOIN characters c ON t.character_id = c.id
                JOIN users seller ON t.seller_id = seller.id
                JOIN users buyer ON t.buyer_id = buyer.id
                WHERE t.id = ?
            ");
            $stmt->execute([$_GET['id']]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($transaction) {
                echo json_encode(['success' => true, 'data' => $transaction]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Transaction not found']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($action === 'cancel' && isset($data['transaction_id'])) {
            // 取消 pending 状态的交易
            $stmt = $pdo->prepare("UPDATE transactions SET status = 'cancelled' WHERE id = ? AND status = 'pending'");
            $stmt->execute([$data['transaction_id']]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Transaction cancelled']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Transaction not found or cannot be cancelled']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>
