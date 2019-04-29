var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .autoProvidejQuery()
    .enableVersioning()
    .addEntry('app', './assets/js/app.js')
    .enableSingleRuntimeChunk()
    .addRule({
        test: require.resolve('./assets/js/vendors.bundle.js'),
        use: ['script-loader']
    })
    .addRule({
        test: require.resolve('./assets/js/scripts.bundle.js'),
        use: ['script-loader']
    })
;

module.exports = Encore.getWebpackConfig();
