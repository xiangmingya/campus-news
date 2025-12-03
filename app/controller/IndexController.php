<?php
namespace App\Controller;

require_once __DIR__ . '/BaseController.php';

/**
 * 首页控制器
 */
class IndexController extends BaseController {
    
    /**
     * 首页
     */
    public function index() {
        // 获取已发布的稿件列表
        $stmt = db()->prepare('
            SELECT a.*, u.real_name as author_name, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = "published"
            ORDER BY a.publish_time DESC
            LIMIT 20
        ');
        $stmt->execute();
        $articles = $stmt->fetchAll();
        
        view('index/index', ['articles' => $articles]);
    }
    
    /**
     * 稿件详情
     */
    public function detail() {
        $id = get('id');
        if (!$id) {
            echo '稿件不存在';
            exit;
        }
        
        $stmt = db()->prepare('
            SELECT a.*, u.real_name as author_name, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.id = ? AND a.status = "published"
        ');
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if (!$article) {
            echo '稿件不存在';
            exit;
        }
        
        view('index/detail', ['article' => $article]);
    }
}
