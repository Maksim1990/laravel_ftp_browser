let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.styles([
    'resources/assets/lib_noty/noty.css',
    'resources/assets/css/style.css'
], 'public/css/app_custom.css')
    .js('resources/assets/js/app.js', 'public/js/app.js')
    .js('resources/assets/js/custom/*.js', 'public/js/app_custom.js');
