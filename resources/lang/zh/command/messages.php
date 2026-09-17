<?php

return [
    'location' => [
        'no_location_found' => '找不到与提供的短代码匹配的记录。',
        'ask_short' => '位置短代码',
        'ask_long' => '位置描述',
        'created' => '已成功创建新位置 (:name)，ID 为 :id。',
        'deleted' => '已成功删除请求的位置。',
    ],
    'user' => [
        'search_users' => '输入用户名、用户ID或邮箱地址',
        'select_search_user' => '要删除的用户ID（输入“0”重新搜索）',
        'deleted' => '用户已成功从面板中删除。',
        'confirm_delete' => '您确定要从面板中删除此用户吗？',
        'no_users_found' => '未找到与搜索词匹配的用户。',
        'multiple_found' => '找到多个与提供的用户匹配的账号，由于使用了 --no-interaction 标志，无法删除用户。',
        'ask_admin' => '此用户是管理员吗？',
        'ask_email' => '邮箱地址',
        'ask_username' => '用户名',
        'ask_name_first' => '名字',
        'ask_name_last' => '姓氏',
        'ask_password' => '密码',
        'ask_password_tip' => '如果您想创建一个带有随机密码的账号并通过邮件发送给用户，请重新运行此命令（CTRL+C）并传入 `--no-password` 标志。',
        'ask_password_help' => '密码长度至少为8个字符，并且至少包含一个大写字母和一个数字。',
        '2fa_help_text' => [
            '如果用户账号启用了双因素认证，此命令将禁用它。这仅应在用户无法登录其账号时作为账号恢复命令使用。',
            '如果这不是您想要执行的操作，请按 CTRL+C 退出此过程。',
        ],
        '2fa_disabled' => '已禁用 :email 的双因素认证。',
    ],
    'schedule' => [
        'output_line' => '正在为 `:schedule` (:hash) 中的第一个任务派发作业。',
    ],
    'maintenance' => [
        'deleting_service_backup' => '正在删除服务备份文件 :file。',
    ],
    'server' => [
        'rebuild_failed' => '节点 ":node" 上 ":name" (#:id) 的重建请求失败，错误：:message',
        'reinstall' => [
            'failed' => '节点 ":node" 上 ":name" (#:id) 的重新安装请求失败，错误：:message',
            'confirm' => '您即将对一组服务器执行重新安装。是否继续？',
        ],
        'power' => [
            'confirm' => '您即将对 :count 台服务器执行 :action 操作。是否继续？',
            'action_failed' => '节点 ":node" 上 ":name" (#:id) 的电源操作请求失败，错误：:message',
        ],
    ],
    'environment' => [
        'mail' => [
            'ask_smtp_host' => 'SMTP 主机（例如 smtp.gmail.com）',
            'ask_smtp_port' => 'SMTP 端口',
            'ask_smtp_username' => 'SMTP 用户名',
            'ask_smtp_password' => 'SMTP 密码',
            'ask_mailgun_domain' => 'Mailgun 域名',
            'ask_mailgun_endpoint' => 'Mailgun 端点',
            'ask_mailgun_secret' => 'Mailgun 密钥',
            'ask_mandrill_secret' => 'Mandrill 密钥',
            'ask_postmark_username' => 'Postmark API 密钥',
            'ask_driver' => '应使用哪个驱动来发送邮件？',
            'ask_mail_from' => '邮件应来自的邮箱地址',
            'ask_mail_name' => '邮件应显示的发件人名称',
            'ask_encryption' => '要使用的加密方式',
        ],
    ],
];