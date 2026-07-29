const path = require('path');

// Mirror Nova's dynamic "gray" palette. Nova remaps Tailwind's `gray` onto
// theme-driven CSS variables (its gray used to be a static "slate"), so we
// resolve `o1-*-gray-*` to the same `--colors-gray-*` values Nova exposes at
// runtime. This keeps the tool in sync with the active theme instead of
// hardcoding a static slate/gray. The `<alpha-value>` placeholder keeps
// opacity utilities such as `o1-bg-opacity-90` working.
const grayShades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
const gray = Object.fromEntries(grayShades.map(shade => [shade, `rgba(var(--colors-gray-${shade}))`]));

module.exports = {
  content: [path.resolve(__dirname, 'resources/**/*.{vue,js,ts,jsx,tsx,scss}')],
  prefix: 'o1-',
  darkMode: 'class',
  theme: {
    extend: {
      colors: { gray },
    },
  },
  // MediaItem builds its size classes dynamically, so they can't be found by
  // Tailwind's content scanner.
  safelist: [
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
