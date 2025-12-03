<?php
/**
 * 应用配置
 */
return [
    // 应用调试模式
    'app_debug' => true,
    
    // 应用Trace
    'app_trace' => false,
    
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    
    // 默认语言
    'default_lang' => 'zh-cn',
    
    // Session配置
    'session' => [
        'type' => 'file',
        'expire' => 1800,
        'prefix' => 'campus_',
    ],
    
    // 上传配置
    'upload' => [
        'max_size' => 10485760, // 10MB
        'exts' => ['jpg', 'png', 'jpeg', 'gif', 'doc', 'docx'],
        'save_path' => __DIR__ . '/../uploads/',
    ],
    
    // 稿件配置
    'article' => [
        'daily_limit' => 5,
        'weekly_video_limit' => 2,
    ],
    
    // 密码配置
    'password' => [
        'min_length' => 6,
        'max_length' => 20,
    ],
];
