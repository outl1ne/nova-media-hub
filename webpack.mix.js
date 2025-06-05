let path = require('path');
let mix = require('laravel-mix');
let postcss = require('postcss-import');
let tailwindcss = require('tailwindcss');
let postcssRtlcss = require('postcss-rtlcss');

mix
  .setPublicPath('dist')
  .js('resources/js/entry.js', 'js')
  .vue({ version: 3 })
  .webpackConfig({
    externals: {
      vue: 'Vue',
      'laravel-nova': 'LaravelNova',
    },
    output: {
      uniqueName: 'outl1ne/nova-media-hub',
    },
    resolve: {
      alias: {
        '@': path.resolve('resources/js'),
      },
    },
  })
  .postCss('resources/css/entry.css', 'dist/css/', [postcss(), tailwindcss('tailwind.config.js'), postcssRtlcss()])
  .alias({
    'laravel-nova': path.join(__dirname, 'vendor/laravel/nova/resources/js/mixins/packages.js'),
  });
