<?php
namespace App\Controller;

/**
 * 基础控制器
 */
class BaseController {
    
    /**
     * 构造函数
     */
    public function __construct() {
        // 可以在这里添加全局逻辑
    }
    
    /**
     * 检查登录
     */
    protected function checkLogin() {
        if (!is_login()) {
            if ($this->isAjax()) {
                json_response(401, '请先登录');
            } else {
                redirect('/auth/login');
            }
        }
    }
    
    /**
     * 检查权限
     */
    protected function checkPermission($permission) {
        if (!has_permission($permission)) {
            if ($this->isAjax()) {
                json_response(403, '权限不足');
            } else {
                echo '权限不足';
                exit;
            }
        }
    }
    
    /**
     * 检查是否为Ajax请求
     */
    protected function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * 成功响应
     */
    protected function success($msg = '操作成功', $data = null) {
        json_response(200, $msg, $data);
    }
    
    /**
     * 失败响应
     */
    protected function error($msg = '操作失败', $code = 400) {
        json_response($code, $msg);
    }
}
