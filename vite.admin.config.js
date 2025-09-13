import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// Admin Vue Configuration  
export default defineConfig({
  plugins: [vue()],
  
  build: {
    outDir: 'dist/admin',
    rollupOptions: {
      input: resolve(__dirname, 'src/admin/main.js'),
      output: {
        entryFileNames: 'ze-funnel-admin.js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'style.css') return 'ze-funnel-admin.css'
          return assetInfo.name
        },
        format: 'iife',
        name: 'ZeFunnelAdminBundle'
      }
    },
    sourcemap: true,
    minify: 'terser'
  },
  
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
      '@admin': resolve(__dirname, 'src/admin'),
      '@shared': resolve(__dirname, 'src/shared')
    }
  },
  
  
  define: {
    // WordPress admin globals
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
    '__VUE_OPTIONS_API__': true,
    '__VUE_PROD_DEVTOOLS__': false
  }
})