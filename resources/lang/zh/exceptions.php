<?php

return [
    'daemon_connection_failed' => '尝试与守护进程通信时发生异常，返回 HTTP/:code 响应码。此异常已被记录。',
    'node' => [
        'servers_attached' => '要删除节点，该节点下必须没有关联的服务器。',
        'daemon_off_config_updated' => '守护进程配置 <strong>已更新</strong>，但在尝试自动更新守护进程上的配置文件时遇到错误。您需要手动更新守护进程的配置文件（config.yml）以应用这些更改。',
    ],
    'allocations' => [
        'server_using' => '当前有服务器正在使用此分配。只有没有服务器使用该分配时才能删除。',
        'too_many_ports' => '不支持在单个范围内一次性添加超过 1000 个端口。',
        'invalid_mapping' => '为 :port 提供的映射无效，无法处理。',
        'cidr_out_of_range' => 'CIDR 表示法仅支持 /25 到 /32 之间的掩码。',
        'port_out_of_range' => '分配中的端口必须大于 1024 且小于或等于 65535。',
    ],
    'nest' => [
        'delete_has_servers' => '无法从面板中删除存在活跃服务器的预设组。',
        'egg' => [
            'delete_has_servers' => '无法从面板中删除存在活跃服务器的预设。',
            'invalid_copy_id' => '所选用于复制脚本的预设不存在，或者其本身正在复制脚本。',
            'must_be_child' => '此预设的“从以下位置复制设置”指令必须属于所选预设组的子选项。',
            'has_children' => '此预设是一个或多个其他预设的父级。请先删除这些子预设，再删除此预设。',
        ],
        'variables' => [
            'env_not_unique' => '环境变量 :name 在此预设中必须唯一。',
            'reserved_name' => '环境变量 :name 受保护，不能分配给变量。',
            'bad_validation_rule' => '验证规则 ":rule" 不是此应用程序的有效规则。',
        ],
        'importer' => [
            'json_error' => '解析 JSON 文件时出错：:error。',
            'file_error' => '提供的 JSON 文件无效。',
            'invalid_json_provided' => '提供的 JSON 文件格式无法识别。',
        ],
    ],
    'subusers' => [
        'editing_self' => '不允许编辑您自己的子用户账号。',
        'user_is_owner' => '您不能将服务器所有者添加为此服务器的子用户。',
        'subuser_exists' => '该邮箱地址对应的用户已被分配为此服务器的子用户。',
    ],
    'databases' => [
        'delete_has_databases' => '无法删除存在关联活跃数据库的数据库主机服务器。',
    ],
    'tasks' => [
        'chain_interval_too_long' => '链式任务的最大间隔时间为 15 分钟。',
    ],
    'locations' => [
        'has_nodes' => '无法删除存在关联活跃节点的位置。',
    ],
    'users' => [
        'node_revocation_failed' => '在 <a href=":link">节点 #:node</a> 上撤销密钥失败。:error',
    ],
    'deployment' => [
        'no_viable_nodes' => '未找到满足自动部署要求的节点。',
        'no_viable_allocations' => '未找到满足自动部署要求的分配。',
    ],
    'api' => [
        'resource_not_found' => '请求的资源在此服务器上不存在。',
    ],
];