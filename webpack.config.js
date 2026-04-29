const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const { resolve } = require('path');

module.exports = {
    ...defaultConfig,
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-react', '@babel/preset-env'],
                    },
                },
            },
            {
                test: /\.scss$/,
                use: [
                    'style-loader',  // Injects CSS into the DOM
                    'css-loader',    // Resolves CSS imports
                    'sass-loader',   // Compiles SCSS to CSS
                ],
            },
            {
                test: /\.css$/,
                use: [
                    'style-loader',  // Injects CSS into the DOM
                    'css-loader',    // Resolves CSS imports
                ],
            },
        ],
    },
    resolve: {
        extensions: ['.js', '.jsx'], // Ensure Webpack recognizes .jsx files
    },
    entry: {
        admin: './src/js/admin/index.js',
        frontend: './src/js/frontend/index.js',
        dashboard: './src/js/frontend/modules/dashboard/index.js',
        "react-core": './src/js/react-core/index.js',
        wizard: './src/js/react-core/admin/pages/wizard/index.js',
        "lesson-viewer": './src/js/frontend/modules/lesson-viewer/LessonViewer.js',
        "quiz-viewer": './src/js/frontend/modules/quiz-viewer/QuizViewer.js',
        blocks: './src/blocks/index.js',
    },
    output: {
        filename: '[name].js',
        chunkFilename: '[name].js?ver=[chunkhash]',
        path: resolve(process.cwd(), 'assets/js'),
    },
};
