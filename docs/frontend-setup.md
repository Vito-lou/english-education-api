# 前端项目配置指南

> 英语教育管理系统前端项目完整配置文档
> 技术栈：React 18 + TypeScript + Vite + shadcn/ui
> 包管理器：pnpm (强制使用)

## 前置要求

### 环境要求

-   Node.js >= 18.0.0
-   pnpm >= 8.0.0 (强制使用)

### 安装 pnpm

```bash
# 如果没有安装 pnpm
npm install -g pnpm

# 验证安装
pnpm --version
```

## 项目创建步骤

### 1. 创建项目

```bash
# 在后端项目的同级目录创建前端项目
cd ..
pnpm create vite english-education-frontend --template react-ts
cd english-education-frontend
```

### 2. 安装基础依赖

```bash
# 安装基础依赖
pnpm install

# 安装开发依赖（必须在 Tailwind CSS 之前安装）
pnpm add -D @types/node

# 安装 Tailwind CSS v3（重要：必须使用 v3，v4 与 shadcn/ui 不兼容）
pnpm add -D tailwindcss@^3.4.0 postcss autoprefixer tailwindcss-animate

# 安装核心库
pnpm add axios @tanstack/react-query zustand

# 安装 shadcn/ui 相关依赖
pnpm add @radix-ui/react-slot class-variance-authority clsx tailwind-merge lucide-react
```

### 3. 配置 Tailwind CSS

```bash
# 创建 Tailwind CSS 配置文件
npx tailwindcss init -p
```

### 4. 初始化 shadcn/ui

```bash
# 初始化 shadcn/ui
# 选择：New York (Recommended) -> Slate
pnpm dlx shadcn@latest init

# 添加常用组件
pnpm dlx shadcn@latest add button
pnpm dlx shadcn@latest add input card table form dialog
pnpm dlx shadcn@latest add dropdown-menu
pnpm dlx shadcn@latest add toast
pnpm dlx shadcn@latest add tabs
pnpm dlx shadcn@latest add badge
```

### 5. VSCode 配置（可选但推荐）

```bash
# 创建 VSCode 配置目录
mkdir -p .vscode
```

创建 `.vscode/settings.json` 文件：

```json
{
    "css.validate": false,
    "less.validate": false,
    "scss.validate": false,
    "tailwindCSS.includeLanguages": {
        "typescript": "typescript",
        "javascript": "javascript",
        "typescriptreact": "typescriptreact",
        "javascriptreact": "javascriptreact"
    },
    "tailwindCSS.experimental.classRegex": [
        ["cva\\(([^)]*)\\)", "[\"'`]([^\"'`]*).*?[\"'`]"],
        ["cx\\(([^)]*)\\)", "(?:'|\"|`)([^']*)(?:'|\"|`)"]
    ],
    "editor.quickSuggestions": {
        "strings": true
    }
}
```

### 6. 环境配置

```bash
# 创建环境变量文件
echo "VITE_API_BASE_URL=http://localhost:8000/api" > .env.local
echo "VITE_APP_NAME=英语教育管理系统" >> .env.local
```

## 故障排除

### 常见问题及解决方案

#### 1. Node.js 版本问题

```bash
# 错误：This version of pnpm requires at least Node.js v18.12
# 解决：升级 Node.js 到 18+ 版本
nvm install 18
nvm use 18
# 或使用 20+ 版本
nvm install 20
nvm use 20
```

#### 2. Tailwind CSS v4 兼容性问题

```bash
# 错误：@tailwind components' is no longer available in v4
# 解决：确保使用 Tailwind CSS v3.4.x
pnpm remove tailwindcss @tailwindcss/postcss
pnpm add -D tailwindcss@^3.4.0
```

#### 3. shadcn/ui 初始化失败

```bash
# 错误：No import alias found in your tsconfig.json file
# 解决：确保 tsconfig.json 中有正确的路径配置
# 参考下面的 TypeScript 配置部分
```

#### 4. ESLint 警告

```bash
# 警告：Fast refresh only works when a file only exports components
# 解决：已在 eslint.config.js 中配置 allowConstantExport: true
```

#### 5. CSS 文件 Tailwind 指令不识别

```bash
# 错误：Unknown at rule @tailwind
# 解决：安装 Tailwind CSS IntelliSense 扩展
# 并使用上面的 VSCode 配置
```

## 核心配置文件

### 1. Vite 配置 (vite.config.ts)

```typescript
import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import path from "path";

export default defineConfig({
    plugins: [react()],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./src"),
        },
    },
    server: {
        port: 3000,
        proxy: {
            "/api": {
                target: "http://localhost:8000",
                changeOrigin: true,
            },
        },
    },
});
```

### 2. TypeScript 配置 (tsconfig.json)

```json
{
    "compilerOptions": {
        "target": "ES2020",
        "useDefineForClassFields": true,
        "lib": ["ES2020", "DOM", "DOM.Iterable"],
        "module": "ESNext",
        "skipLibCheck": true,
        "moduleResolution": "bundler",
        "allowImportingTsExtensions": true,
        "resolveJsonModule": true,
        "isolatedModules": true,
        "noEmit": true,
        "jsx": "react-jsx",
        "strict": true,
        "noUnusedLocals": true,
        "noUnusedParameters": true,
        "noFallthroughCasesInSwitch": true,
        "baseUrl": ".",
        "paths": {
            "@/*": ["./src/*"]
        }
    },
    "include": ["src"],
    "references": [{ "path": "./tsconfig.node.json" }]
}
```

### 3. API 客户端 (src/lib/api.ts)

```typescript
import axios from "axios";

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    withCredentials: true,
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

// 请求拦截器
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("auth_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// 响应拦截器
api.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem("auth_token");
            window.location.href = "/login";
        }
        return Promise.reject(error);
    }
);

export default api;
```

### 4. 认证状态管理 (src/stores/auth.ts)

```typescript
import { create } from 'zustand'
import { persist } from 'zustand/middleware'

interface User {
  id: number
  name: string
  email: string
  role: string
  system_access: {
    offline: boolean
    online: boolean
  }
}

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
  login: (user: User, token: string) => void
  logout: () => void
  updateUser: (user: Partial<User>) => void
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      isAuthenticated: false,

      login: (user, token) => {
        localStorage.setItem('auth_token', token)
        set({ user, token, isAuthenticated: true })
      },

      logout: () => {
        localStorage.removeItem('auth_token')
        set({ user: null, token: null, isAuthenticated: false })
      },

      updateUser: (userData) => {
        const currentUser = get().user
        if (currentUser) {
          set({ user: { ...currentUser, ...userData } })
        }
      },
    }),
    {
      name: 'auth-storage',
      partialize: (state) => ({
        user: state.user,
        token: state.token,
        isAuthenticated: state.isAuthenticated
      }),
    }
  )
)
EOF

# 更新 main.tsx
echo "⚙️  配置 React Query..."
cat > src/main.tsx << 'EOF'
import React from 'react'
import ReactDOM from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { ReactQueryDevtools } from '@tanstack/react-query-devtools'
import App from './App.tsx'
import './index.css'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
      staleTime: 5 * 60 * 1000, // 5 minutes
    },
  },
})

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <QueryClientProvider client={queryClient}>
      <App />
      <ReactQueryDevtools initialIsOpen={false} />
    </QueryClientProvider>
  </React.StrictMode>,
)
EOF

# 创建工具函数
echo "⚙️  创建工具函数..."
cat > src/lib/utils.ts << 'EOF'
import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

// API 响应类型
export interface ApiResponse<T = any> {
  success: boolean
  message: string
  data: T
}

export interface PaginatedResponse<T = any> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// 格式化日期
export function formatDate(date: string | Date) {
  return new Intl.DateTimeFormat('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(new Date(date))
}

// 格式化时间
export function formatDateTime(date: string | Date) {
  return new Intl.DateTimeFormat('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(date))
}
EOF

# 更新 package.json 脚本
echo "⚙️  更新 package.json 脚本..."
pnpm pkg set scripts.dev="vite --port 3000"
pnpm pkg set scripts.build="tsc && vite build"
pnpm pkg set scripts.preview="vite preview"
pnpm pkg set scripts.lint="eslint . --ext ts,tsx --report-unused-disable-directives --max-warnings 0"
pnpm pkg set scripts.type-check="tsc --noEmit"

echo ""
echo "🎉 前端项目创建完成！"
echo ""
echo "📁 项目位置: $(pwd)"
echo "🌐 开发服务器: http://localhost:3000"
echo "🔗 API 地址: http://localhost:8000/api"
echo ""
echo "🚀 启动开发服务器:"
echo "   cd english-education-frontend"
echo "   pnpm dev"
echo ""
echo "📚 下一步:"
echo "   1. 启动后端服务器: php artisan serve"
echo "   2. 启动前端开发服务器: pnpm dev"
echo "   3. 开始开发认证系统"
echo ""
echo "⚠️  重要提示:"
echo "   • 必须使用 Node.js 18+ 版本"
echo "   • 必须使用 Tailwind CSS v3.4.x（不要使用 v4）"
echo "   • 安装顺序很重要：@types/node → Tailwind CSS → shadcn/ui"
echo "   • 如遇问题请参考文档中的故障排除部分"
echo ""
```

## 总结

本文档提供了英语教育管理系统前端项目的完整配置指南。关键要点：

### ✅ 正确的安装顺序

1. **Node.js 环境** - 确保使用 18+ 版本
2. **基础依赖** - 安装 @types/node
3. **Tailwind CSS v3** - 必须使用 v3.4.x 版本
4. **shadcn/ui 初始化** - 在 Tailwind 配置完成后进行

### ⚠️ 常见陷阱

-   **不要使用 Tailwind CSS v4** - 与 shadcn/ui 不兼容
-   **确保路径别名配置** - tsconfig.json 和 vite.config.ts 都需要配置
-   **VSCode 配置** - 禁用 CSS 验证，启用 Tailwind IntelliSense

### 🔧 故障排除

如果遇到问题，请参考文档中的故障排除部分，包含了所有常见问题的解决方案。
