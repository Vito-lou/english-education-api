# 前端项目配置指南

> 英语教育管理系统前端项目完整配置文档
> 技术栈：React 18 + TypeScript + Vite + shadcn/ui
> 包管理器：pnpm (强制使用)

## 前置要求

### 环境要求
- Node.js >= 18.0.0
- pnpm >= 8.0.0 (强制使用)

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

# 安装核心库
pnpm add axios @tanstack/react-query zustand

# 安装 shadcn/ui 相关依赖
pnpm add @radix-ui/react-slot class-variance-authority clsx tailwind-merge lucide-react

# 安装额外图标库
pnpm add react-icons

# 安装开发依赖
pnpm add -D @types/node
```

### 3. 初始化 shadcn/ui
```bash
# 初始化 shadcn/ui (选择默认配置)
pnpm dlx shadcn-ui@latest init

# 添加常用组件
pnpm dlx shadcn-ui@latest add button
pnpm dlx shadcn-ui@latest add input
pnpm dlx shadcn-ui@latest add card
pnpm dlx shadcn-ui@latest add table
pnpm dlx shadcn-ui@latest add form
pnpm dlx shadcn-ui@latest add dialog
pnpm dlx shadcn-ui@latest add dropdown-menu
pnpm dlx shadcn-ui@latest add toast
pnpm dlx shadcn-ui@latest add tabs
pnpm dlx shadcn-ui@latest add badge
```

### 4. 环境配置
```bash
# 创建环境变量文件
echo "VITE_API_BASE_URL=http://localhost:8000/api" > .env.local
echo "VITE_APP_NAME=英语教育管理系统" >> .env.local
```

## 核心配置文件

### 1. Vite 配置 (vite.config.ts)
```typescript
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
})
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
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// 请求拦截器
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// 响应拦截器
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
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