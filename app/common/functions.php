<?php
/**
 * 公共函数库
 */

/**
 * 获取配置值
 * @param string $key 配置键，支持点号分隔
 * @param mixed $default 默认值
 * @return mixed
 */
function config($key, $default = null) {
    static $config = [];
    
    if (empty($config)) {
        $config_path = ROOT_PATH . 'config/';
        $files = ['app.php', 'database.php'];
        
        foreach ($files as $file) {
            if (file_exists($config_path . $file)) {
                $file_config = require $config_path . $file;
                $config = array_merge($config, $file_config);
            }
        }
    }
    
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }
    
    return $value;
}

/**
 * 数据库连接
 * @return PDO
 */
function db() {
    static $pdo = null;
    
    if ($pdo === null) {
        $db_config = config('connections.mysql');
        
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $db_config['hostname'],
            $db_config['hostport'],
            $db_config['database'],
            $db_config['charset']
        );
        
        try {
            $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * JSON响应
 * @param int $code 状态码
 * @param string $msg 消息
 * @param mixed $data 数据
 */
function json_response($code = 200, $msg = 'success', $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 获取POST参数
 * @param string $key 键名
 * @param mixed $default 默认值
 * @return mixed
 */
function post($key = null, $default = null) {
    if ($key === null) {
        return $_POST;
    }
    return $_POST[$key] ?? $default;
}

/**
 * 获取GET参数
 * @param string $key 键名
 * @param mixed $default 默认值
 * @return mixed
 */
function get($key = null, $default = null) {
    if ($key === null) {
        return $_GET;
    }
    return $_GET[$key] ?? $default;
}

/**
 * 获取Session值
 * @param string $key 键名
 * @param mixed $default 默认值
 * @return mixed
 */
function session($key = null, $default = null) {
    if ($key === null) {
        return $_SESSION;
    }
    return $_SESSION[$key] ?? $default;
}

/**
 * 设置Session值
 * @param string $key 键名
 * @param mixed $value 值
 */
function set_session($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * 删除Session值
 * @param string $key 键名
 */
function unset_session($key) {
    unset($_SESSION[$key]);
}

/**
 * 渲染视图
 * @param string $view 视图文件名
 * @param array $data 数据
 */
function view($view, $data = []) {
    extract($data);
    $view_file = ROOT_PATH . 'view/' . $view . '.php';
    
    if (!file_exists($view_file)) {
        die('View file not found: ' . $view);
    }
    
    require $view_file;
    exit;
}

/**
 * 重定向
 * @param string $url URL地址
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * 获取当前登录用户ID
 * @return int|null
 */
function user_id() {
    return session('user_id');
}

/**
 * 获取当前登录用户信息
 * @return array|null
 */
function user() {
    $user_id = user_id();
    if (!$user_id) {
        return null;
    }
    
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    }
    
    return $user ?: null;
}

/**
 * 检查是否登录
 * @return bool
 */
function is_login() {
    return user_id() !== null;
}

/**
 * 检查权限
 * @param string $permission 权限代码
 * @return bool
 */
function has_permission($permission) {
    $user = user();
    if (!$user) {
        return false;
    }
    
    $stmt = db()->prepare('SELECT permissions FROM roles WHERE id = ?');
    $stmt->execute([$user['role_id']]);
    $role = $stmt->fetch();
    
    if (!$role) {
        return false;
    }
    
    $permissions = json_decode($role['permissions'], true);
    return isset($permissions['all']) || isset($permissions[$permission]);
}

/**
 * 记录操作日志
 * @param string $operation_type 操作类型
 * @param string $target_type 目标类型
 * @param int $target_id 目标ID
 * @param string $details 详情
 */
function log_operation($operation_type, $target_type = null, $target_id = null, $details = '') {
    $user = user();
    
    $stmt = db()->prepare('
        INSERT INTO operation_logs 
        (user_id, user_role, operation_type, target_type, target_id, details, ip_address, user_agent, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ');
    
    $stmt->execute([
        $user ? $user['id'] : null,
        $user ? $user['role_id'] : null,
        $operation_type,
        $target_type,
        $target_id,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * 文件上传
 * @param string $field 表单字段名
 * @param string $type 类型 (cert/cover/word/avatar)
 * @return string|false 返回文件路径或false
 */
function upload_file($field, $type = 'cover') {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file = $_FILES[$field];
    $allowed_exts = config('upload.exts', ['jpg', 'png', 'jpeg', 'gif']);
    $max_size = config('upload.max_size', 10485760);
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_exts)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    $upload_dir = ROOT_PATH . 'uploads/' . $type . '/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = date('YmdHis') . '_' . uniqid() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return '/uploads/' . $type . '/' . $filename;
    }
    
    return false;
}

/**
 * HTML转义
 * @param string $string 字符串
 * @return string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * URL生成
 * @param string $path 路径
 * @return string
 */
function url($path) {
    return '/' . ltrim($path, '/');
}

/**
 * 获取系统设置
 * @param string $key 设置键
 * @param mixed $default 默认值
 * @return mixed
 */
function setting($key, $default = null) {
    $stmt = db()->prepare('SELECT setting_value FROM system_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    
    return $result ? $result['setting_value'] : $default;
}

/**
 * 密码哈希
 * @param string $password 密码
 * @return string
 */
function password_hash_custom($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * 密码验证
 * @param string $password 密码
 * @param string $hash 哈希值
 * @return bool
 */
function password_verify_custom($password, $hash) {
    return password_verify($password, $hash);
}
