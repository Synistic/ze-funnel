module.exports = {
  root: true,
  env: {
    browser: true,
    es2022: true,
    node: true
  },
  extends: [
    'eslint:recommended',
    '@typescript-eslint/recommended',
    'plugin:svelte/recommended',
    'plugin:vue/vue3-recommended',
    'prettier'
  ],
  parser: '@typescript-eslint/parser',
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
    extraFileExtensions: ['.svelte']
  },
  plugins: ['@typescript-eslint'],
  overrides: [
    {
      files: ['*.svelte'],
      parser: 'svelte-eslint-parser',
      parserOptions: {
        parser: '@typescript-eslint/parser'
      },
      rules: {
        // Svelte-specific rules
        'svelte/valid-compile': 'error',
        'svelte/no-at-debug-tags': 'warn',
        'svelte/no-unused-svelte-ignore': 'warn'
      }
    },
    {
      files: ['*.vue'],
      parser: 'vue-eslint-parser',
      parserOptions: {
        parser: '@typescript-eslint/parser',
        sourceType: 'module'
      },
      rules: {
        // Vue-specific rules
        'vue/multi-word-component-names': 'off',
        'vue/no-reserved-component-names': 'warn'
      }
    }
  ],
  globals: {
    // WordPress globals
    wp: 'readonly',
    jQuery: 'readonly',
    $: 'readonly',
    ajaxurl: 'readonly',
    
    // Ze-funnel globals
    zeFunnelWP: 'readonly'
  },
  rules: {
    // General rules
    'no-console': 'warn',
    'no-debugger': 'warn',
    'no-unused-vars': 'off',
    '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
    '@typescript-eslint/no-explicit-any': 'warn',
    
    // Code style
    'prefer-const': 'error',
    'no-var': 'error',
    'object-shorthand': 'error',
    'prefer-arrow-callback': 'error',
    
    // WordPress specific
    'camelcase': 'off', // WordPress uses snake_case
    'no-undef': 'error'
  },
  ignorePatterns: [
    'dist/',
    'node_modules/',
    '*.min.js',
    'vendor/'
  ]
}