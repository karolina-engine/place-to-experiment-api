var utils = require('./utils')
var webpack = require('webpack')
var config = require('../config')
var merge = require('webpack-merge')
var baseWebpackConfig = require('./webpack.base.conf')
var FriendlyErrorsPlugin = require('friendly-errors-webpack-plugin')

module.exports = merge(baseWebpackConfig, {
    module: {
        rules: utils.styleLoaders({
            sourceMap: config.dev.cssSourceMap
        })
    },
    output: {
        path: config.dev.assetsRoot
    },
    // cheap-module-eval-source-map is faster for development
    devtool: '#source-map', // buggy on chrome..
    plugins: [
        new webpack.DefinePlugin({
            'process.env': config.dev.env
        }),
        new FriendlyErrorsPlugin()
    ]
})
