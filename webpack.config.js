const Encore = require("@symfony/webpack-encore");

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore.setOutputPath("public/build/")
    .setPublicPath("/build")
    .addEntry("app", "./assets/app.js")
    .splitEntryChunks()
    .enableStimulusBridge("./assets/controllers.json")
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabelPresetEnv((config) => {
        config.targets = "> 0.5%, not dead, not IE 11";
        config.useBuiltIns = false;
    })
    .enablePostCssLoader()
    .configureTerserPlugin((options) => {
        options.extractComments = false;
        options.terserOptions = {
            format: { comments: false },
            compress: {
                drop_console: true,
                passes: 2,
            },
        };
    })
    .copyFiles([
        {
            from: "./assets/images",
            to: "images/[path][name].[hash:8].[ext]",
            pattern: /\.(png|jpg|jpeg|gif|svg|webp)$/,
        },
    ]);

module.exports = Encore.getWebpackConfig();
