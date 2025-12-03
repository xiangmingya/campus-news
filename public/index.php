<?php
/**
 * 校园新闻投稿系统 - 入口文件
 * Version: 1.0.0
 */

// 定义项目根目录
define('ROOT_PATH', dirname(__DIR__) . '/');

// 加载配置文件
require ROOT_PATH . 'app/common/functions.php';

// 开启Session
session_start();

// 错误报告设置
if (config('app.app_debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 设置时区
date_default_timezone_set(config('app.default_timezone', 'Asia/Shanghai'));

// 路由处理
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = str_replace(dirname($script_name), '', $request_uri);
$path = str_replace('//', '/', $path);
$path = trim($path, '/');

// 解析路由
if (empty($path) || $path == 'index.php') {
    $controller = 'Index';
    $action = 'index';
} else {
    // 去除查询字符串
    if (strpos($path, '?') !== false) {
        $path = substr($path, 0, strpos($path, '?'));
    }
    
    $parts = explode('/', $path);
    $controller = isset($parts[0]) && !empty($parts[0]) ? ucfirst($parts[0]) : 'Index';
    $action = isset($parts[1]) && !empty($parts[1]) ? $parts[1] : 'index';
}

// 控制器文件路径
$controller_file = ROOT_PATH . 'app/controller/' . $controller . 'Controller.php';

if (!file_exists($controller_file)) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

// 加载控制器
require $controller_file;

$controller_class = 'App\\Controller\\' . $controller . 'Controller';

if (!class_exists($controller_class)) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

$controller_instance = new $controller_class();

if (!method_exists($controller_instance, $action)) {
    http_response_code(404);
    echo '404 Not Found';
    exit;
}

// 执行方法
call_user_func([$controller_instance, $action]);
