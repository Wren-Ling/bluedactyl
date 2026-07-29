<?php

return [
    'sign_in' => '登录',
    'go_to_login' => '前往登录',
    'failed' => '未找到匹配该凭据的账号。',

    'forgot_password' => [
        'label' => '忘记密码？',
        'label_help' => '输入您的账号邮箱地址，以接收重置密码的说明。',
        'button' => '恢复账号',
    ],

    'reset_password' => [
        'button' => '重置并登录',
    ],

    'two_factor' => [
        'label' => '双因素令牌',
        'label_help' => '此账号需要双重身份验证才能继续。请输入您的设备生成的代码以完成登录。',
        'checkpoint_failed' => '双因素认证令牌无效。',
    ],

    'throttle' => '登录尝试次数过多。请 :seconds 秒后重试。',
    'password_requirements' => '密码长度至少为8个字符，且应为此站点唯一。',
    '2fa_must_be_enabled' => '管理员要求您的账号必须启用双因素认证才能使用此面板。',
];