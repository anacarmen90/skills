"use strict";

var webpack = require("webpack");
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');

module.exports = {
    entry: {
        main: __dirname + "/web/main.js"
    },
    output: {
        filename: "[name].js",
        path: __dirname + "/web/assets/"
    },
    module: {
        loaders: [
            {
                test: /\.vue$/,
                loader: vue.withLoaders({
                    // apply babel transform to all javascript
                    // inside *.vue files.
                    js: 'babel?optional[]=runtime'
                })
            },
            {
                test:/\.css$/,
                use:['style-loader','css-loader']
            },
            {
                test: /\.(png|jpg|jpeg|gif)/,
                loader: "base64-image-loader"
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $:"jquery",
            jQuery:"jquery",
            _:"underscore"
        })
    ]
};
