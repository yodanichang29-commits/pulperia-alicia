// vite.config.js
import { defineConfig, loadEnv } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const host = env.VITE_HOST || '127.0.0.1'
  const port = Number(env.VITE_PORT || 5173)

  return {
    server: {
      host,              // <— servidor en 127.0.0.1
      port,              // <— 5173
      strictPort: true,
      hmr: {
        host,            // <— HMR también en 127.0.0.1
        port,
        protocol: 'ws',
      },
      cors: true,
    },
    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
      }),
    ],
  }
})
