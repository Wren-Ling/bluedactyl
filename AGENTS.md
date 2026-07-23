# Pyrodactyl

Pyrodactyl 是基于 Pterodactyl 的游戏服务器管理面板（PHP + React）。

## 技术栈

- **后端**: Laravel 12, PHP 8.3/8.4, MySQL, Redis
- **前端**: React 19, TypeScript, Vite 7, styled-components, Tailwind CSS 4
- **状态管理**: easy-peasy (global), SWR (API 数据获取)
- **表单**: Formik + Yup
- **路由**: react-router-dom v7, 懒加载路由模块
- **UI**: Radix UI, Headless UI, sonner (通知), motion (动画)
- **终端**: @xterm/xterm

## 常用命令

```sh
pnpm dev              # 启动 Vite 开发服务器
pnpm dev:docker       # 使用 Docker 开发 (cross-env APP_URL="http://localhost:3000")
pnpm build            # Vite 生产构建 → public/build/manifest.json
pnpm build:admin      # 构建 admin CSS (Tailwind + Basecoat) → public/themes/pterodactyl/css/admin.css
pnpm lint             # ESLint (前端)
pnpm ship             # turbo lint build && build:admin
composer cs:fix       # PHP-CS-Fixer (后端)
composer cs:check     # 检查 PHP 格式
vendor/bin/phpunit    # 运行 PHP 测试 (Unit + Integration)
vendor/bin/phpstan analyse  # PHPStan level 4
```

## 智能体工作区配置
- `agent/` - 智能体工作区，分为`docs`文档等，结构为`agent/docs/文档名称/*`

## 项目结构
- `app/` - Laravel 后端 (命名空间 `Pterodactyl\` 和 `Pyrodactyl\`)
- `routes/` - 按域划分: `admin.php`, `api-client.php`, `api-application.php`, `auth.php`, `servers/`
- `config/` - Laravel 配置
- `tests/` - PHPUnit 测试 (`Unit/`, `Integration/`)
- 前端源码在 `example/pyrodactyl/resources/scripts/`，入口 `index.tsx`

## 构建/前端注意

- 前端路径别名: `@/` → `resources/scripts/`, `@definitions/` → `api/definitions/`, `@feature/` → `components/server/features/`
- SWC 编译 (非 Babel)，styled-components 插件命名空间 `pyrodactyl`
- million.js 编译器做 React 优化 (阈值 0.01)
- 构建输出 SRI manifest
- 导入排序规则: `@/routers/` → `@/components/` → `@/hoc/` → `@/lib/` → `@/api/` → `@/state` → `@/plugins/` → `@feature/` → `./`

## Admin 面板

- 管理后台是纯 Blade 模板（不走 Vite/React）
- CSS 框架: Tailwind CSS 4 + Basecoat CSS
- 图标: Lucide（通过 `<x-icon name="..." />` Blade 组件）
- CSS 构建: `pnpm build:admin`（PostCSS + @tailwindcss/postcss）
- 输入文件: `resources/css/admin.css`
- 输出文件: `public/themes/pterodactyl/css/admin.css`
- 加载方式: `{!! Theme::css('css/admin.css?t={cache-version}') !!}`
- JS 组件: Basecoat sidebar/select/popover/dropdown-menu/toast（CDN）
- 遗留 JS: jQuery、select2、SweetAlert、lodash、Ace Editor

## 后端注意

- `.env` 需从 `.env.example` 复制，运行 `php artisan key:generate`
- 数据库 MySQL + Redis (队列)
- Sanctum 认证, HashIDs 混淆 ID, Google 2FA
- hCaptcha / Cloudflare Turnstile 验证码支持
- `legacy-peer-deps=true` (`.npmrc`)

## 测试

- PHPUnit: `vendor/bin/phpunit` 或 `vendor/bin/phpunit --testsuite=Unit`
- 无前端测试框架配置

## 代码风格

- PHP: `.php-cs-fixer.dist.php` (Symfony 风格)
- JS/TS: ESLint + Prettier (通过 eslint-plugin-prettier)
- Prettier: 120 列宽, 4 空格缩进, 单引号, JSX 单引号

## 避免的坑

- `@preact/signals-react` 必须在 App 根级别 import，否则会报水合不匹配错误
- 用户数据通过 `window.PterodactylUser` 和 `window.SiteConfiguration` 注入（服务端渲染），不是 API 获取
- Vite 配置中 `process.env` 被置为 `{}`，`process.platform/version/versions` 被置为 `null`，不要依赖它们
- `resources/scripts/` 目录仅存在于 `example/pyrodactyl/`，根级别缺失；开发时需要确认前端源码位置
