<?php

namespace Pterodactyl\Models;

use Illuminate\Support\Collection;

class Permission extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'subuser_permission';

    /**
     * Constants defining different permissions available.
     */
    public const ACTION_WEBSOCKET_CONNECT = 'websocket.connect';
    public const ACTION_CONTROL_CONSOLE = 'control.console';
    public const ACTION_CONTROL_START = 'control.start';
    public const ACTION_CONTROL_STOP = 'control.stop';
    public const ACTION_CONTROL_RESTART = 'control.restart';

    public const ACTION_DATABASE_READ = 'database.read';
    public const ACTION_DATABASE_CREATE = 'database.create';
    public const ACTION_DATABASE_UPDATE = 'database.update';
    public const ACTION_DATABASE_DELETE = 'database.delete';
    public const ACTION_DATABASE_VIEW_PASSWORD = 'database.view_password';

    public const ACTION_SCHEDULE_READ = 'schedule.read';
    public const ACTION_SCHEDULE_CREATE = 'schedule.create';
    public const ACTION_SCHEDULE_UPDATE = 'schedule.update';
    public const ACTION_SCHEDULE_DELETE = 'schedule.delete';

    public const ACTION_USER_READ = 'user.read';
    public const ACTION_USER_CREATE = 'user.create';
    public const ACTION_USER_UPDATE = 'user.update';
    public const ACTION_USER_DELETE = 'user.delete';

    public const ACTION_BACKUP_READ = 'backup.read';
    public const ACTION_BACKUP_CREATE = 'backup.create';
    public const ACTION_BACKUP_DELETE = 'backup.delete';
    public const ACTION_BACKUP_DOWNLOAD = 'backup.download';
    public const ACTION_BACKUP_RESTORE = 'backup.restore';

    public const ACTION_ALLOCATION_READ = 'allocation.read';
    public const ACTION_ALLOCATION_CREATE = 'allocation.create';
    public const ACTION_ALLOCATION_UPDATE = 'allocation.update';
    public const ACTION_ALLOCATION_DELETE = 'allocation.delete';

    public const ACTION_FILE_READ = 'file.read';
    public const ACTION_FILE_READ_CONTENT = 'file.read-content';
    public const ACTION_FILE_CREATE = 'file.create';
    public const ACTION_FILE_UPDATE = 'file.update';
    public const ACTION_FILE_DELETE = 'file.delete';
    public const ACTION_FILE_ARCHIVE = 'file.archive';
    public const ACTION_FILE_SFTP = 'file.sftp';

    public const ACTION_STARTUP_READ = 'startup.read';
    public const ACTION_STARTUP_UPDATE = 'startup.update';
    public const ACTION_STARTUP_COMMAND = 'startup.command';
    public const ACTION_STARTUP_DOCKER_IMAGE = 'startup.docker-image';

    public const ACTION_STARTUP_SOFTWARE = 'startup.software';

    public const ACTION_SETTINGS_RENAME = 'settings.rename';
    public const ACTION_SETTINGS_MODR = 'settings.mod';
    public const ACTION_SETTINGS_REINSTALL = 'settings.reinstall';

    public const ACTION_ACTIVITY_READ = 'activity.read';

    public const ACTION_MOD_DOWNLOAD = 'mod.download';

    /**
     * Should timestamps be used on this model.
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     */
    protected $table = 'permissions';

    /**
     * Fields that are not mass assignable.
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Cast values to correct type.
     */
    protected $casts = [
        'subuser_id' => 'integer',
    ];

    public static array $validationRules = [
        'subuser_id' => 'required|numeric|min:1',
        'permission' => 'required|string',
    ];

    /**
     * All the permissions available on the system. You should use self::permissions()
     * to retrieve them, and not directly access this array as it is subject to change.
     *
     * @see \Pterodactyl\Models\Permission::permissions()
     */
    protected static array $permissions = [
        'websocket' => [
            'description' => '允许用户连接到服务器 WebSocket，查看控制台输出和实时服务器统计信息。',
            'keys' => [
                'connect' => '允许用户连接到服务器的 WebSocket 实例以流式查看控制台。',
            ],
        ],

        'control' => [
            'description' => '控制用户对服务器电源状态和发送命令的权限。',
            'keys' => [
                'console' => '允许用户通过控制台向服务器发送命令。',
                'start' => '允许用户在服务器已停止时启动它。',
                'stop' => '允许用户在服务器正在运行时停止它。',
                'restart' => '允许用户执行服务器重启。这将允许他们在服务器离线时启动它，但不会使其完全停止。',
            ],
        ],

        'user' => [
            'description' => '允许用户管理服务器上的其他子用户。他们无法编辑自己的账户或分配自己没有的权限。',
            'keys' => [
                'create' => '允许用户为服务器创建新的子用户。',
                'read' => '允许用户查看子用户及其对服务器的权限。',
                'update' => '允许用户修改其他子用户。',
                'delete' => '允许用户从服务器删除子用户。',
            ],
        ],

        'file' => [
            'description' => '控制用户修改此服务器文件系统的权限。',
            'keys' => [
                'create' => '允许用户通过面板或直接上传创建额外的文件和文件夹。',
                'read' => '允许用户查看目录内容，但不能查看文件内容或下载文件。',
                'read-content' => '允许用户查看给定文件的内容。这也将允许用户下载文件。',
                'update' => '允许用户更新现有文件或目录的内容。',
                'delete' => '允许用户删除文件或目录。',
                'archive' => '允许用户压缩目录内容以及在系统上解压现有压缩包。',
                'sftp' => '允许用户通过 SFTP 连接并使用分配的其他文件权限管理服务器文件。',
            ],
        ],

        'backup' => [
            'description' => '控制用户生成和管理服务器备份的权限。',
            'keys' => [
                'create' => '允许用户为此服务器创建新备份。',
                'read' => '允许用户查看此服务器的所有备份。',
                'delete' => '允许用户从系统中删除备份。',
                'download' => '允许用户下载服务器备份。注意：这将允许用户访问备份中的所有服务器文件。',
                'restore' => '允许用户恢复服务器备份。注意：这将允许用户在过程中删除所有服务器文件。',
            ],
        ],

        // 控制编辑或查看服务器分配的权限
        'allocation' => [
            'description' => '控制用户修改此服务器端口分配的权限。',
            'keys' => [
                'read' => '允许用户查看当前分配给此服务器的所有分配。具有任何访问级别的用户始终可以查看主要分配。',
                'create' => '允许用户为服务器分配额外分配。',
                'update' => '允许用户更改主要服务器分配并附加备注到每个分配。',
                'delete' => '允许用户从服务器删除分配。',
            ],
        ],

        // 控制编辑或查看服务器启动参数的权限
        'startup' => [
            'description' => '控制用户查看此服务器启动参数的权限。',
            'keys' => [
                'read' => '允许用户查看服务器的启动变量。',
                'update' => '允许用户修改服务器的启动变量。',
                'command' => '允许用户修改服务器的启动命令。',
                'docker-image' => '允许用户修改运行服务器时使用的 Docker 镜像。',
                'software' => '允许用户修改服务器使用的游戏或软件。',
            ],
        ],

        'database' => [
            'description' => '控制用户对此服务器数据库管理的访问权限。',
            'keys' => [
                'create' => '允许用户为此服务器创建新数据库。',
                'read' => '允许用户查看与此服务器关联的数据库。',
                'update' => '允许用户轮换数据库实例的密码。如果用户没有 view_password 权限，他们将看不到更新后的密码。',
                'delete' => '允许用户从此服务器移除数据库实例。',
                'view_password' => '允许用户查看与此服务器关联的数据库实例的密码。',
            ],
        ],

        'schedule' => [
            'description' => '控制用户对此服务器计划任务管理的访问权限。',
            'keys' => [
                'create' => '允许用户为此服务器创建新计划任务。',
                'read' => '允许用户查看计划任务及其关联的任务。',
                'update' => '允许用户更新计划任务和计划任务项。',
                'delete' => '允许用户删除此服务器的计划任务。',
            ],
        ],

        'settings' => [
            'description' => '控制用户对此服务器设置的访问权限。',
            'keys' => [
                'rename' => '允许用户重命名此服务器并更改其描述。',
                'reinstall' => '允许用户触发此服务器的重装。',
            ],
        ],

        'activity' => [
            'description' => '控制用户访问服务器活动日志的权限。',
            'keys' => [
                'read' => '允许用户查看服务器的活动日志。',
            ],
        ],

        'mod' => [
            'description' => '控制用户下载和更新模组的权限。',
            'keys' => [
                'version' => '允许用户更改下载的版本',
                'loader' => '允许用户更改下载的加载器',
                'download' => '允许用户下载模组到服务器',
                'resolver' => '允许用户访问依赖解析器',
                'update' => '允许用户更新已安装的模组',
            ],
        ],
    ];

    /**
     * Returns all the permissions available on the system for a user to
     * have when controlling a server.
     */
    public static function permissions(): Collection
    {
        return Collection::make(self::$permissions);
    }
}
