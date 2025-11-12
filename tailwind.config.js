import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js', // por si mueves lógica a JS
  ],

  // Para que las utilidades ganen a estilos globales raros
  // (si no lo necesitas, puedes quitarlo)
  // important: true,

  darkMode: 'class',

  safelist: [
    // Estados inactivos (siempre los usamos)
    'bg-gray-100', 'text-gray-800', 'border', 'border-gray-300', 'hover:bg-gray-200',

    // Utilidades comunes cuando el botón está activo
    'text-white', 'shadow-lg',

    // Colores dinámicos de los botones (ACTIVOS):
    // cash, card, transfer, credit
    // bg-*-600 + border-*-700
    { pattern: /^(bg|border)-(emerald|indigo|amber|sky)-(500|600|700)$/ },

    // Nota: La línea de hover se quitó porque no se usaba y causaba advertencias

    // Gradientes/barras azules que quizá se arman dinámicamente
    'from-blue-700', 'to-indigo-700', 'bg-blue-600', 'bg-blue-700', 'hover:bg-blue-800',

    // Cosas del grid/targetas que sueles tener dinámicas
    'hover:border-blue-400', 'bg-blue-100', 'text-blue-800',

    'text-emerald-700','text-red-600',
  'bg-blue-50','bg-emerald-50',
  'rounded-2xl','shadow-2xl','ring-1','ring-black/5',
  'backdrop-blur-sm'
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
    },
  },

  plugins: [forms],
}