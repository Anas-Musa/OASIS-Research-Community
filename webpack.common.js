import HtmlWebpackPlugin from 'html-webpack-plugin';
import { resolve } from 'path';

import { dirname } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

export default {
    entry: {
        app: './src/index.js',

    },
    plugins: [
        new HtmlWebpackPlugin({
            title: 'Production',
        }),
    ],
    output: {
        filename: '[name].bundle.js',
        path: resolve(__dirname, 'dist'),
        clean: true,
    },
    module: {
        rules: [
          {
            test: /\.css$/,
            use: [
                {
                    // inject CSS to page
                    loader: 'style-loader'
                },
                {
                    // translates CSS into CommonJS modules
                    loader: 'css-loader'
                },
                {
                    // Run postcss actions
                    loader: 'postcss-loader',
                    options: {
                    // `postcssOptions` is needed for postcss 8.x;
                    // if you use postcss 7.x skip the key
                    postcssOptions: {
                        // postcss plugins, can be exported to postcss.config.js
                        plugins: function () {
                        return [
                            require('autoprefixer')
                        ];
                        }
                    }
                    }
                }
            ]
          }
        ]
    }
}