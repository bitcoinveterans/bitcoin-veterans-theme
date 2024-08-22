const gulp = require("gulp"),
	sass = require("gulp-dart-sass"),
	uglify = require("gulp-uglify"),
	postcss = require("gulp-postcss"),
	cssnano = require("cssnano"),
	rename = require("gulp-rename"),
	concat = require("gulp-concat"),
	notify = require("gulp-notify"),
	sourcemaps = require("gulp-sourcemaps"),
	info = require("./package.json"),
	zip = require("gulp-zip"),
	del = require("del"),
	newer = require("gulp-newer"),
	imagemin = require("gulp-imagemin"),
	plumber = require("gulp-plumber");

const paths = {
	styleSRC: "src/sass/main.scss",
	styleDest: "assets/css/",
	imagesSRC: "src/img/**/*.{png,jpg,gif,svg}",
	imagesDest: "assets/img/",
	jsVendorsSRC: "src/js/lib/*.js",
	jsVendorsDest: "assets/js/",
	jsVendorsFile: "lib",
	jsCustomSRC: "src/js/custom/*.js",
	jsCustomDest: "assets/js/",
	jsCustomFile: "theme",
	stylesWatchFiles: "src/sass/**/*.scss",
	vendorsJSWatchFiles: "src/js/lib/*.js",
	customJSWatchFiles: "src/js/custom/*.js",
	customImagesWatchFiles: "src/img/**/*",
	projectPHPWatchFiles: "**/*.php",
    fontsSRC: "src/fonts/**/*",
    fontsDest: "assets/fonts/",
	node: "node_modules/",
	bower: "bower_components/",
	dev: "src",
	dist: "dist",
	distprod: "dist-product",
};

// Error handling
const errorHandler = function(err) {
	notify.onError({
		title: "Gulp error in " + err.plugin,
		message: err.toString()
	})(err);
	this.emit("end");
};

// Styles task
gulp.task("styles", function() {
    return gulp
        .src(paths.styleSRC)
        .pipe(plumber({ errorHandler }))
        .pipe(sourcemaps.init())
        .pipe(sass().on("error", sass.logError))
        .pipe(postcss([cssnano()]))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.styleDest));
});

// Images task
gulp.task("images", function() {
    return gulp
        .src(paths.imagesSRC)
        .pipe(plumber({ errorHandler }))
        .pipe(newer(paths.imagesDest))
        .pipe(imagemin([
            imagemin.gifsicle({interlaced: true}),
            imagemin.mozjpeg({quality: 75, progressive: true}),
            imagemin.optipng({optimizationLevel: 5}),
            imagemin.svgo({
                plugins: [
                    {removeViewBox: true},
                    {cleanupIDs: false}
                ]
            })
        ]))
        .pipe(gulp.dest(paths.imagesDest));
});

// Vendor JS task
gulp.task("vendorsJS", function() {
    return gulp
        .src(paths.jsVendorsSRC)
        .pipe(plumber({ errorHandler }))
        .pipe(newer(paths.jsVendorsDest))
        .pipe(uglify())
        .pipe(rename(function(path) {
            path.extname = ".min.js";
        }))
        .pipe(gulp.dest(paths.jsVendorsDest));
});

// Custom JS task
gulp.task("customJS", function() {
    return gulp
        .src(paths.jsCustomSRC)
        .pipe(plumber({ errorHandler }))
        .pipe(concat(paths.jsCustomFile + ".js"))
        .pipe(rename({ suffix: ".min" }))
        .pipe(uglify())
        .pipe(gulp.dest(paths.jsCustomDest));
});

// Fonts task
gulp.task("fonts", function() {
    return gulp
        .src(paths.fontsSRC)
        .pipe(plumber({ errorHandler }))
        .pipe(newer(paths.fontsDest))
        .pipe(gulp.dest(paths.fontsDest));
});

// Clean dist
gulp.task("cleanDist", function() {
    return del([paths.dist + "/**"]);
});

// Copy files to dist
gulp.task("copy", function() {
    return gulp
        .src([
            "**/*",
            "!node_modules{,/**}",
            "!src{,/**}",
            "!.babelrc",
            "!.gitignore",
            "!gulpfile.js",
            "!package.json",
            "!package-lock.json",
			"!untitled.php",
			"!sandbox.php",
			"!sandbox.scss",
            "!*.html"
        ])
        .pipe(gulp.dest(paths.dist));
});

// Zip task
gulp.task("zip", function() {
    return gulp
        .src(paths.dist + "/**/*")
        .pipe(zip(`${info.name}.zip`))
        .pipe(gulp.dest("bundled"));
});

// Build task
gulp.task(
    "build",
    gulp.series(
        "cleanDist",
        gulp.parallel("styles", "images", "vendorsJS", "customJS", "fonts"),
        "copy",
        "zip"
    )
);

// Watch task
gulp.task("watch", function() {
    gulp.watch(paths.stylesWatchFiles, gulp.series("styles"));
    gulp.watch(paths.vendorsJSWatchFiles, gulp.series("vendorsJS"));
    gulp.watch(paths.customJSWatchFiles, gulp.series("customJS"));
    gulp.watch(paths.customImagesWatchFiles, gulp.series("images"));
    gulp.watch(paths.fontsSRC, gulp.series("fonts"));
});

// Default task
gulp.task("default", gulp.parallel("styles", "images", "vendorsJS", "customJS", "watch"));