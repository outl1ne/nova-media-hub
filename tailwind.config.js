const path = require('path');

module.exports = {
  content: [path.resolve(__dirname, 'resources/**/*.{vue,js,ts,jsx,tsx,scss}')],
  prefix: 'o1-',
  darkMode: 'class',
  safelist: [
    'text-red-500',
    'text-slate-600',
    'o1-h-24',
    'o1-h-32',
    'o1-h-36',
    'o1-h-40',
    'o1-h-48',
    'o1-w-24',
    'o1-w-32',
    'o1-w-36',
    'o1-w-40',
    'o1-w-48',
  ],
};
