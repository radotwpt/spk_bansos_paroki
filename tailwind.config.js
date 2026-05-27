/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.{js,jsx,ts,tsx,blade.php}',
    './app/Http/Controllers/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9f8',
          100: '#dff2f0',
          200: '#bce4e0',
          300: '#99d7d1',
          400: '#76c9c2',
          500: '#174e4a',
          600: '#0d3734',
          700: '#0a2a27',
          800: '#081f1d',
          900: '#051514',
        },
        accent: {
          50: '#fef5f0',
          100: '#fde6da',
          200: '#fbc9b0',
          300: '#f8ac85',
          400: '#b86f22',
          500: '#b86f22',
          600: '#963e0d',
          700: '#6b2b08',
          800: '#4a1c05',
          900: '#2e1203',
        },
        success: {
          50: '#f1f8f4',
          100: '#d4ede4',
          200: '#a8dcc9',
          300: '#7ccaaf',
          400: '#28704a',
          500: '#28704a',
          600: '#1f5538',
          700: '#163f2a',
          800: '#0f2a1d',
          900: '#081b12',
        },
        warning: {
          50: '#fef8f0',
          100: '#fce8d4',
          200: '#f8cfa0',
          300: '#f4b66c',
          400: '#8a641b',
          500: '#8a641b',
          600: '#6b4d14',
          700: '#4d360e',
          800: '#332408',
          900: '#1f1605',
        },
        danger: {
          50: '#fdf3f3',
          100: '#fae0e0',
          200: '#f4bfbf',
          300: '#ed9d9d',
          400: '#a83434',
          500: '#a83434',
          600: '#882a2a',
          700: '#641f1f',
          800: '#451414',
          900: '#2d0d0d',
        },
        neutral: {
          50: '#fafaf8',
          100: '#f6f7f2',
          200: '#e8ebe5',
          300: '#d8dfdc',
          400: '#c4cac7',
          500: '#a0a69f',
          600: '#63706c',
          700: '#455150',
          800: '#2c3532',
          900: '#18211f',
          950: '#0a0d0c',
        },
      },
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        mono: ['Fira Code', 'Monaco', 'Courier New', 'monospace'],
      },
      fontSize: {
        xs: ['0.75rem', { lineHeight: '1rem' }],
        sm: ['0.875rem', { lineHeight: '1.25rem' }],
        base: ['1rem', { lineHeight: '1.5rem' }],
        lg: ['1.125rem', { lineHeight: '1.75rem' }],
        xl: ['1.25rem', { lineHeight: '1.75rem' }],
        '2xl': ['1.5rem', { lineHeight: '2rem' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
        '5xl': ['3rem', { lineHeight: '1' }],
      },
      spacing: {
        0: '0',
        1: '0.25rem',
        2: '0.5rem',
        3: '0.75rem',
        4: '1rem',
        5: '1.25rem',
        6: '1.5rem',
        8: '2rem',
        10: '2.5rem',
        12: '3rem',
        14: '3.5rem',
        16: '4rem',
        20: '5rem',
        24: '6rem',
        28: '7rem',
        32: '8rem',
        40: '10rem',
        48: '12rem',
        56: '14rem',
        64: '16rem',
        80: '20rem',
        96: '24rem',
      },
      borderRadius: {
        none: '0',
        xs: '0.25rem',
        sm: '0.375rem',
        md: '0.5rem',
        lg: '0.75rem',
        xl: '1rem',
        '2xl': '1.25rem',
        '3xl': '1.5rem',
        full: '9999px',
      },
      boxShadow: {
        xs: '0 1px 2px 0 rgba(24, 33, 31, 0.05)',
        sm: '0 1px 3px 0 rgba(24, 33, 31, 0.1), 0 1px 2px 0 rgba(24, 33, 31, 0.06)',
        md: '0 4px 6px -1px rgba(24, 33, 31, 0.1), 0 2px 4px -1px rgba(24, 33, 31, 0.06)',
        lg: '0 10px 15px -3px rgba(24, 33, 31, 0.1), 0 4px 6px -2px rgba(24, 33, 31, 0.05)',
        xl: '0 20px 25px -5px rgba(24, 33, 31, 0.1), 0 10px 10px -5px rgba(24, 33, 31, 0.04)',
        '2xl': '0 25px 50px -12px rgba(24, 33, 31, 0.12)',
        inner: 'inset 0 2px 4px 0 rgba(24, 33, 31, 0.06)',
        none: 'none',
      },
      animation: {
        'fade-in': 'fadeIn 0.3s ease-in-out',
        'slide-in-down': 'slideInDown 0.3s ease-out',
        'slide-in-up': 'slideInUp 0.3s ease-out',
        'slide-in-left': 'slideInLeft 0.3s ease-out',
        'slide-in-right': 'slideInRight 0.3s ease-out',
        'pulse-subtle': 'pulseSubtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'bounce-subtle': 'bounceSubtle 1s cubic-bezier(0.4, 0, 0.6, 1) infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideInDown: {
          '0%': { transform: 'translateY(-10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        slideInUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        slideInLeft: {
          '0%': { transform: 'translateX(-10px)', opacity: '0' },
          '100%': { transform: 'translateX(0)', opacity: '1' },
        },
        slideInRight: {
          '0%': { transform: 'translateX(10px)', opacity: '0' },
          '100%': { transform: 'translateX(0)', opacity: '1' },
        },
        pulseSubtle: {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0.7' },
        },
        bounceSubtle: {
          '0%, 100%': { transform: 'translateY(0)', opacity: '1' },
          '50%': { transform: 'translateY(-4px)', opacity: '1' },
        },
      },
      transitionDuration: {
        250: '250ms',
        350: '350ms',
      },
      backdropBlur: {
        xs: '2px',
        sm: '4px',
        md: '8px',
        lg: '12px',
      },
    },
  },
  plugins: [
    function ({ addComponents, theme }) {
      const buttons = {
        '.btn': {
          '@apply px-4 py-2.5 rounded-md font-medium text-sm transition-all duration-250 flex items-center justify-center gap-2 cursor-pointer': {},
        },
        '.btn-primary': {
          '@apply btn bg-primary-600 text-white hover:bg-primary-700 active:bg-primary-800 disabled:opacity-50 disabled:cursor-not-allowed': {},
        },
        '.btn-secondary': {
          '@apply btn bg-neutral-100 text-neutral-900 hover:bg-neutral-200 active:bg-neutral-300 disabled:opacity-50 disabled:cursor-not-allowed': {},
        },
        '.btn-ghost': {
          '@apply btn bg-transparent text-neutral-600 hover:bg-neutral-50 active:bg-neutral-100 disabled:opacity-50 disabled:cursor-not-allowed': {},
        },
        '.btn-danger': {
          '@apply btn bg-danger-600 text-white hover:bg-danger-700 active:bg-danger-800 disabled:opacity-50 disabled:cursor-not-allowed': {},
        },
        '.btn-success': {
          '@apply btn bg-success-600 text-white hover:bg-success-700 active:bg-success-800 disabled:opacity-50 disabled:cursor-not-allowed': {},
        },
        '.btn-warning': {
          '@apply btn bg-warning-600 text-white hover:bg-warning-700 active:bg-warning-800 disabled:opacity-50 disabled:cursor-not-allowed': {},
        },
        '.btn-sm': {
          '@apply px-3 py-1.5 text-xs': {},
        },
        '.btn-lg': {
          '@apply px-6 py-3 text-base': {},
        },
      };
      const inputs = {
        '.input': {
          '@apply w-full px-3 py-2.5 rounded-md border border-neutral-300 bg-white text-neutral-900 placeholder-neutral-500 transition-all duration-250 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-0 focus:border-transparent': {},
        },
        '.input-sm': {
          '@apply px-2.5 py-2 text-sm': {},
        },
        '.input-lg': {
          '@apply px-4 py-3 text-base': {},
        },
      };
      const cards = {
        '.card': {
          '@apply bg-white rounded-lg border border-neutral-200 shadow-sm p-6': {},
        },
        '.card-header': {
          '@apply pb-4 border-b border-neutral-200 mb-4': {},
        },
        '.card-body': {
          '@apply flex-1': {},
        },
        '.card-footer': {
          '@apply pt-4 border-t border-neutral-200 mt-4': {},
        },
      };
      const badges = {
        '.badge': {
          '@apply inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium': {},
        },
        '.badge-primary': {
          '@apply badge bg-primary-100 text-primary-700': {},
        },
        '.badge-success': {
          '@apply badge bg-success-100 text-success-700': {},
        },
        '.badge-warning': {
          '@apply badge bg-warning-100 text-warning-700': {},
        },
        '.badge-danger': {
          '@apply badge bg-danger-100 text-danger-700': {},
        },
      };

      addComponents({ ...buttons, ...inputs, ...cards, ...badges });
    },
  ],
};
