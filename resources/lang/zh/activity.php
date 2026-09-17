<?php

/**
 * 包含所有活动日志事件的语言翻译字符串。
 * 这些字符串应按事件名称中冒号（:）前的值进行键名组织。
 * 如果没有冒号，则应位于顶级。
 */
return [
    'auth' => [
        'fail' => '登录失败',
        'success' => '已登录',
        'password-reset' => '已重置密码',
        'reset-password' => '已请求重置密码',
        'checkpoint' => '已请求双因素认证',
        'recovery-token' => '使用了双因素恢复令牌',
        'token' => '已通过双因素认证挑战',
        'ip-blocked' => '已阻止来自未列入白名单的IP地址 :identifier 的请求',
        'sftp' => [
            'fail' => 'SFTP登录失败',
        ],
    ],
    'user' => [
        'account' => [
            'email-changed' => '已将邮箱从 :old 更改为 :new',
            'password-changed' => '已更改密码',
        ],
        'api-key' => [
            'create' => '已创建新的API密钥 :identifier',
            'delete' => '已删除API密钥 :identifier',
        ],
        'ssh-key' => [
            'create' => '已向账号添加SSH密钥 :fingerprint',
            'delete' => '已从账号移除SSH密钥 :fingerprint',
        ],
        'two-factor' => [
            'create' => '已启用双因素认证',
            'delete' => '已禁用双因素认证',
        ],
    ],
    'server' => [
        'reinstall' => '已重新安装服务器',
        'console' => [
            'command' => '已在服务器上执行 ":command"',
        ],
        'power' => [
            'start' => '已启动服务器',
            'stop' => '已停止服务器',
            'restart' => '已重启服务器',
            'kill' => '已强制终止服务器进程',
        ],
        'backup' => [
            'download' => '已下载备份 :name',
            'delete' => '已删除备份 :name',
            'restore' => '已恢复备份 :name（已删除文件：:truncate）',
            'restore-complete' => '已完成备份 :name 的恢复',
            'restore-failed' => '备份 :name 的恢复失败',
            'start' => '已开始创建新备份 :name',
            'complete' => '已将备份 :name 标记为完成',
            'fail' => '已将备份 :name 标记为失败',
            'lock' => '已锁定备份 :name',
            'unlock' => '已解锁备份 :name',
        ],
        'database' => [
            'create' => '已创建新数据库 :name',
            'rotate-password' => '已轮换数据库 :name 的密码',
            'delete' => '已删除数据库 :name',
        ],
        'file' => [
            'compress_one' => '已压缩 :directory:file',
            'compress_other' => '已在 :directory 中压缩 :count 个文件',
            'read' => '已查看 :file 的内容',
            'copy' => '已创建 :file 的副本',
            'create-directory' => '已创建目录 :directory:name',
            'decompress' => '已在 :directory 中解压 :files',
            'delete_one' => '已删除 :directory:files.0',
            'delete_other' => '已在 :directory 中删除 :count 个文件',
            'download' => '已下载 :file',
            'pull' => '已从 :url 下载远程文件到 :directory',
            'rename_one' => '已将 :directory:files.0.from 重命名为 :directory:files.0.to',
            'rename_other' => '已在 :directory 中重命名 :count 个文件',
            'write' => '已向 :file 写入新内容',
            'upload' => '已开始上传文件',
            'uploaded' => '已上传 :directory:file',
        ],
        'sftp' => [
            'denied' => '因权限不足已阻止SFTP访问',
            'create_one' => '已创建 :files.0',
            'create_other' => '已创建 :count 个新文件',
            'write_one' => '已修改 :files.0 的内容',
            'write_other' => '已修改 :count 个文件的内容',
            'delete_one' => '已删除 :files.0',
            'delete_other' => '已删除 :count 个文件',
            'create-directory_one' => '已创建目录 :files.0',
            'create-directory_other' => '已创建 :count 个目录',
            'rename_one' => '已将 :files.0.from 重命名为 :files.0.to',
            'rename_other' => '已重命名或移动 :count 个文件',
        ],
        'allocation' => [
            'create' => '已向服务器添加分配 :allocation',
            'notes' => '已将 :allocation 的备注从 ":old" 更新为 ":new"',
            'primary' => '已将 :allocation 设为主分配',
            'delete' => '已删除分配 :allocation',
        ],
        'schedule' => [
            'create' => '已创建计划任务 :name',
            'update' => '已更新计划任务 :name',
            'execute' => '已手动执行计划任务 :name',
            'delete' => '已删除计划任务 :name',
        ],
        'task' => [
            'create' => '已为计划任务 :name 创建新的 ":action" 任务',
            'update' => '已为计划任务 :name 更新 ":action" 任务',
            'delete' => '已为计划任务 :name 删除一个任务',
        ],
        'settings' => [
            'rename' => '已将服务器名称从 :old 重命名为 :new',
            'description' => '已将服务器描述从 :old 更改为 :new',
        ],
        'startup' => [
            'edit' => '已将变量 :variable 从 ":old" 更改为 ":new"',
            'image' => '已将服务器的Docker镜像从 :old 更新为 :new',
        ],
        'subuser' => [
            'create' => '已添加 :email 为子用户',
            'update' => '已更新 :email 的子用户权限',
            'delete' => '已移除子用户 :email',
        ],
    ],
];