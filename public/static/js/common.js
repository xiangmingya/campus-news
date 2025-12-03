/**
 * 校园新闻投稿系统 - 公共JavaScript
 * Version: 1.0.0
 */

(function() {
    'use strict';
    
    /**
     * 通用工具函数
     */
    window.Utils = {
        /**
         * Ajax请求封装
         */
        ajax: function(options) {
            const defaults = {
                method: 'GET',
                url: '',
                data: {},
                success: function() {},
                error: function() {},
                complete: function() {}
            };
            
            options = Object.assign(defaults, options);
            
            $.ajax({
                type: options.method,
                url: options.url,
                data: options.data,
                dataType: 'json',
                success: function(res) {
                    if (res.code === 200) {
                        options.success(res);
                    } else {
                        Utils.showAlert(res.msg || '操作失败', 'danger');
                        options.error(res);
                    }
                },
                error: function(xhr) {
                    Utils.showAlert('网络错误，请稍后重试', 'danger');
                    options.error(xhr);
                },
                complete: function() {
                    options.complete();
                }
            });
        },
        
        /**
         * GET请求
         */
        get: function(url, data, success) {
            this.ajax({
                method: 'GET',
                url: url,
                data: data,
                success: success
            });
        },
        
        /**
         * POST请求
         */
        post: function(url, data, success) {
            this.ajax({
                method: 'POST',
                url: url,
                data: data,
                success: success
            });
        },
        
        /**
         * 显示提示信息
         */
        showAlert: function(message, type) {
            type = type || 'info';
            const alertClass = 'alert-' + type;
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
                     role="alert" style="z-index: 9999; min-width: 300px;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            $('body').append(alertHtml);
            
            setTimeout(function() {
                $('.alert').alert('close');
            }, 3000);
        },
        
        /**
         * 确认对话框
         */
        confirm: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },
        
        /**
         * 加载动画
         */
        loading: {
            show: function() {
                const loadingHtml = `
                    <div class="loading-overlay position-fixed top-0 start-0 w-100 h-100 
                         d-flex align-items-center justify-content-center" 
                         style="background: rgba(0,0,0,0.5); z-index: 9998;">
                        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">加载中...</span>
                        </div>
                    </div>
                `;
                $('body').append(loadingHtml);
            },
            hide: function() {
                $('.loading-overlay').remove();
            }
        },
        
        /**
         * 格式化日期
         */
        formatDate: function(timestamp, format) {
            const date = new Date(timestamp * 1000);
            format = format || 'Y-m-d H:i:s';
            
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hour = String(date.getHours()).padStart(2, '0');
            const minute = String(date.getMinutes()).padStart(2, '0');
            const second = String(date.getSeconds()).padStart(2, '0');
            
            return format
                .replace('Y', year)
                .replace('m', month)
                .replace('d', day)
                .replace('H', hour)
                .replace('i', minute)
                .replace('s', second);
        },
        
        /**
         * 文件大小格式化
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        },
        
        /**
         * 防抖函数
         */
        debounce: function(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        },
        
        /**
         * 节流函数
         */
        throttle: function(func, wait) {
            let previous = 0;
            return function() {
                const now = Date.now();
                const context = this;
                const args = arguments;
                if (now - previous > wait) {
                    func.apply(context, args);
                    previous = now;
                }
            };
        }
    };
    
    /**
     * 表单验证
     */
    window.FormValidator = {
        /**
         * 验证邮箱
         */
        email: function(email) {
            const reg = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            return reg.test(email);
        },
        
        /**
         * 验证手机号
         */
        phone: function(phone) {
            const reg = /^1[3-9]\d{9}$/;
            return reg.test(phone);
        },
        
        /**
         * 验证密码强度
         */
        password: function(password) {
            // 至少6位，包含字母和数字
            const reg = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/;
            return reg.test(password);
        },
        
        /**
         * 验证必填
         */
        required: function(value) {
            return value !== '' && value !== null && value !== undefined;
        }
    };
    
    /**
     * 页面加载完成后执行
     */
    $(document).ready(function() {
        // Bootstrap工具提示初始化
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // 自动隐藏提示信息
        setTimeout(function() {
            $('.alert-dismissible').fadeOut();
        }, 3000);
        
        // 确认操作
        $('[data-confirm]').on('click', function(e) {
            const message = $(this).data('confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
        
        // 表单提交防重复点击
        $('form').on('submit', function() {
            const $btn = $(this).find('[type="submit"]');
            $btn.prop('disabled', true);
            setTimeout(function() {
                $btn.prop('disabled', false);
            }, 3000);
        });
    });
    
})();
