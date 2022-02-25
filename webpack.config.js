const path = require("path");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {

    mode: "production",
  //define entry point
  entry: {
    admin: './uncompiled_assets/js/admin/main.js',
    public: './uncompiled_assets/js/public/main.js',
    ajax: './uncompiled_assets/js/public/cruise_ajax.js',
  },
  output: {
      path: path.resolve(__dirname, "./assets/js"),
      filename: "[name]-cruise-harvest.bundle.min.js"
  },

  plugins:
  [
    new MiniCssExtractPlugin(
      {
        filename:"../css/[name]-cruise-harvest.min.css"
      }
    ),
    new CleanWebpackPlugin(),
  ],
  module:{
      rules:[
        {
        test: /\.scss$/,
        use: [MiniCssExtractPlugin.loader,"css-loader","sass-loader"],
        },
        {
          test: /\.(jpg|jpeg|png|woff|woff2|eot|ttf|svg)$/,
          use: [{loader: 'url-loader?limit=100000'}],
        }
    ]
  }

}
