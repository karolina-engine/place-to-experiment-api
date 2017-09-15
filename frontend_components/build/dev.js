// https://github.com/shelljs/shelljs
require('./check-versions')()

process.env.NODE_ENV = 'development'

var ora = require('ora')
var path = require('path')
var chalk = require('chalk')
var shell = require('shelljs')
var webpack = require('webpack')
var config = require('../config')
var webpackConfig = require('./webpack.dev.conf')

var spinner = ora('building for development...')
spinner.start()

// returns a Compiler instance
var compiler = webpack(webpackConfig);

compiler.watch({}, function (err, stats) {
    spinner.stop()
    if (err) throw err
    process.stdout.write(stats.toString({
        colors: true,
        modules: false,
        children: false,
        chunks: false,
        chunkModules: false
    }) + '\n\n')
})
