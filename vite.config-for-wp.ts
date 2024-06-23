import { defineConfig } from 'vite'
import tsconfigPaths from 'vite-tsconfig-paths'
import alias from '@rollup/plugin-alias'
import path from 'path'
import liveReload from 'vite-plugin-live-reload'
import optimizer from 'vite-plugin-optimizer'
import { terser } from 'rollup-plugin-terser'

export default defineConfig({
  build: {
    emptyOutDir: true,
    minify: true,
    outDir: path.resolve(__dirname, 'inc/assets/dist'),
    watch: {
      include: 'inc/**',
      exclude:
        'js/**, modules/**, node_modules/**, release/**, vendor/**, .git/**, .vscode/**',
    },
    rollupOptions: {
      input: 'inc/assets/src/main.ts', // Optional, defaults to 'src/main.js'.
      output: {
        assetFileNames: '[ext]/index.[ext]',
        entryFileNames: 'index.js',
      },
    },
  },
  plugins: [
    alias(),
    tsconfigPaths(),
    liveReload([
      __dirname + '/**/*.php',
    ]),
    optimizer({
      jquery: 'const $ = window.jQuery; export { $ as default }',
    }),
    terser({
      mangle: {
        reserved: ['$'], // 指定 $ 不被改變
      },
    }),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'inc/assets/src'),
    },
  },
})
