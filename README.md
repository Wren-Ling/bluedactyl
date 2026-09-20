# Pyrodactyl

<p align="center">
    <img src="https://img.cdn1.vip/i/6aafad413b953_1789898049.webp" alt="Banner with Bluedactyl Logo">
</p>

基于 [Pterodactyl](https://pterodactyl.io/) 构建的开源游戏服务器管理面板。Pyrodactyl 使用 Laravel 与 React 提供服务器控制台、文件管理、备份、数据库、网络分配、子用户、计划任务和节点管理等功能，并通过 Wings/Elytra 守护进程运行隔离的游戏服务器实例。

> 当前版本为 `canary`，适合开发、测试和参与贡献。生产部署前请自行完成安全审查、备份与升级验证。

## 功能

- 实时服务器控制台与电源管理
- 文件浏览、编辑、上传和 SFTP 访问
- 数据库、备份与网络分配管理
- 启动参数、Docker 镜像与计划任务配置
- 子用户和细粒度权限控制
- 用户、节点、服务器、位置、Egg 与挂载管理
- 应用 API、客户端 API 与账户 API 凭据
- Google 2FA、Sanctum 认证及活动日志
- 中英文界面与响应式布局
- Minecraft Modrinth 集成（开发中）

## 技术栈

| 分类 | 技术 |
| --- | --- |
| 后端 | PHP 8.3/8.4、Laravel 12、Sanctum |
| 前端 | React 19、TypeScript 5、Vite 7 |
| UI | Tailwind CSS 4、shadcn、Base UI、Headless UI、Basecoat CSS |
| 数据 | MySQL、Redis、SWR、easy-peasy |
| 终端 | xterm.js |
| 工具链 | pnpm、Turbo、ESLint、PHP-CS-Fixer、PHPStan、PHPUnit |

## 环境要求

- PHP `8.3` 或 `8.4`
- Composer 2
- Node.js `20` 或更高版本
- pnpm `10.13.1`（推荐通过 Corepack 管理）
- MySQL 8 或兼容版本
- Redis
- Web 服务器（生产环境推荐 Nginx）
- PHP 扩展：`json`、`mbstring`、`pdo`、`pdo_mysql`、`posix`、`zip`

运行游戏服务器还需要单独部署并配置与 Pterodactyl 兼容的 Wings/Elytra 节点守护进程。面板本身不会代替节点守护进程启动容器。

## 快速开始

### 1. 安装依赖

```sh
composer install
corepack enable
pnpm install --frozen-lockfile
```

### 2. 配置环境

```sh
cp .env.example .env
php artisan key:generate
```

编辑 `.env`，至少设置以下内容：

```dotenv
APP_URL=http://localhost:8000
APP_LOCALE=zh

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=panel
DB_USERNAME=pyrodactyl
DB_PASSWORD=your-database-password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

也可以使用交互式命令配置应用、数据库和邮件：

```sh
php artisan p:environment:setup
php artisan p:environment:database
php artisan p:environment:mail
```

请勿将 `.env` 提交到版本控制。生产环境应使用 HTTPS，将 `APP_DEBUG` 保持为 `false`，并为 MySQL 与 Redis 设置独立凭据。

### 3. 初始化数据库

确保数据库已经创建，且配置的数据库用户拥有该数据库的完整权限，然后执行：

```sh
php artisan migrate --seed
```

种子数据会导入项目内置的 Nest 与 Egg。

### 4. 创建管理员

```sh
php artisan p:user:make
```

按提示输入账户信息，并选择创建管理员账户。

### 5. 构建前端

```sh
pnpm build
pnpm build:admin
```

React 客户端会输出到 `public/build/`，管理后台样式会输出到 `public/themes/pterodactyl/css/admin.css`。

### 6. 启动本地服务

终端一：

```sh
pnpm serve
```

终端二：

```sh
pnpm dev
```

默认访问地址为 <http://127.0.0.1:8000>。`php artisan serve` 仅适合本地开发，不应作为生产 Web 服务器。

## 生产部署

生产环境除 Web 服务外，还必须持续运行队列 Worker 与 Laravel 调度器。

队列 Worker 示例：

```sh
php artisan queue:work --queue=high,standard,low --sleep=3 --tries=3
```

Crontab 示例：

```cron
* * * * * cd /path/to/pyrodactyl && php artisan schedule:run >> /dev/null 2>&1
```

建议使用 systemd 或 Supervisor 管理队列进程，并确保 Web 服务用户可写入 `storage/` 与 `bootstrap/cache/`。站点根目录应指向项目的 `public/`，不要暴露项目根目录或 `.env`。

部署或更新后通常需要执行：

```sh
pnpm install --frozen-lockfile
pnpm build
pnpm build:admin
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan queue:restart
```

升级前请备份数据库、`.env` 和持久化文件，并先在测试环境验证迁移。节点安装与反向代理配置可参考 [Pterodactyl 官方文档](https://pterodactyl.io/panel/1.0/getting_started.html)。

## 常用命令

| 命令 | 说明 |
| --- | --- |
| `pnpm dev` | 启动 Vite 开发服务器 |
| `pnpm serve` | 在 `127.0.0.1:8000` 启动 Laravel 开发服务器 |
| `pnpm build` | 构建 React 客户端 |
| `pnpm build:admin` | 构建 Blade 管理后台 CSS |
| `pnpm lint` | 运行 ESLint 并自动修复可修复问题 |
| `pnpm ship` | 依次执行前端检查、构建与管理后台 CSS 构建 |
| `composer cs:check` | 检查 PHP 代码格式 |
| `composer cs:fix` | 修复 PHP 代码格式 |
| `vendor/bin/phpstan analyse` | 运行 PHPStan 静态分析 |
| `vendor/bin/phpunit` | 运行 PHPUnit 测试套件 |
| `php artisan p:user:make` | 创建用户或管理员 |
| `php artisan p:node:make` | 创建节点 |

## 项目结构

```text
app/                    Laravel 应用、服务、模型与控制器
bootstrap/              Laravel 启动与缓存目录
config/                 应用配置
database/               数据库迁移、Seeders 与内置 Eggs
public/                 Web 根目录与构建产物
resources/scripts/      React/TypeScript 客户端源码
resources/views/        Blade 模板与管理后台页面
resources/css/          管理后台样式入口
resources/lang/         Laravel 中英文翻译
routes/                 Web、认证、客户端与应用 API 路由
storage/                日志、缓存和运行时文件
```

用户侧面板由 React/Vite 构建；管理后台主要使用 Blade、Tailwind CSS 与 Basecoat CSS，不经过 React 路由。

## 开发约定

- 前端使用 4 空格缩进、单引号和 120 字符行宽。
- PHP 代码遵循项目根目录的 `.php-cs-fixer.dist.php`。
- 前端路径别名：`@/`、`@definitions/` 和 `@feature/`。
- 不要从前端代码依赖 Node.js 的 `process.env`；浏览器构建使用 Vite 环境变量。
- `@preact/signals-react` 必须保留在应用根入口导入，避免水合不匹配。
- 提交改动前建议运行 `pnpm lint`、`pnpm build`、`composer cs:check` 和相关 PHP 测试。

## 参与贡献

1. 从最新代码创建功能分支。
2. 保持改动聚焦，并为后端行为变更补充相应测试。
3. 运行代码检查与构建，确认没有引入错误。
4. 在 Pull Request 中说明改动目的、验证方式和可能的迁移影响。

## 安全

请不要在公开 Issue 中披露可利用的安全漏洞，也不要提交真实密钥、数据库凭据、节点令牌或用户数据。部署方应定期更新 PHP、Composer、Node.js、数据库、Redis、Wings/Elytra 及其宿主系统。

## 许可证

`composer.json` 将本项目声明为 [Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0)。当前仓库根目录未包含独立许可证文件；分发或部署前请向项目维护者确认完整许可文本及上游 Pterodactyl 代码的适用条款。

Pyrodactyl 是基于 Pterodactyl 的衍生项目，与 Pterodactyl Software 官方无隶属关系。
