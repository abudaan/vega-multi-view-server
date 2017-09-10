import gulp from 'gulp';
import sourcemaps from 'gulp-sourcemaps';
import gutil from 'gulp-util';
import rename from 'gulp-rename';
import minify from 'gulp-babel-minify';
import sass from 'gulp-sass';
import concat from 'gulp-concat';
import filter from 'gulp-filter';
import autoprefixer from 'gulp-autoprefixer';
import buffer from 'vinyl-buffer';
import source from 'vinyl-source-stream';
import browserify from 'browserify';
import watchify from 'watchify';
import babelify from 'babelify';
import es from 'event-stream';
import glob from 'glob';
import path from 'path';

const logBrowserifyError = (e) => {
    gutil.log(gutil.colors.red(e.message));
    // if(e.codeFrame){
    //   if(_.startsWith(e.codeFrame, 'false')){
    //     console.log(e.codeFrame.substr(5))
    //   }else{
    //     console.log(e.codeFrame)
    //   }
    // }
};

const rebundle = (b, target) => b.bundle()
    .on('error', logBrowserifyError)
    .pipe(source('app.bundle.js'))
    .pipe(buffer())
    .pipe(gulp.dest('./assets'));


gulp.task('watch_js', () => {
    const opts = {
        debug: true,
    };

    opts.cache = {};
    opts.packageCache = {};

    const b = watchify(browserify(opts));
    b.add('./assets/src/js/app.js');
    b.transform(babelify.configure({
        compact: true,
    }));

    b.on('update', () => {
        gutil.log('update js bundle');
        rebundle(b);
    });

    return rebundle(b);
});


gulp.task('build_js', () => {
    const opts = {
        debug: true,
    };
    const b = browserify(opts);
    b.add(`./assets/src/js/app.js`);
    b.transform(babelify.configure({
        compact: true,
    }));

    return b.bundle()
        .on('error', logBrowserifyError)
        .pipe(source('app.bundle.js'))
        .pipe(buffer())
        .pipe(sourcemaps.init({
            loadMaps: false,
        }))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(`./assets`))
        .pipe(filter('**/*.js'))
        .pipe(minify({
            mangle: {
                keepClassName: true,
            },
        }))
        .pipe(gulp.dest('./assets'));
});


gulp.task('build_css', () => gulp.src('./asset/src/css/main.sass')
    .pipe(sass().on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(concat('app.css'))
    .pipe(gulp.dest('./assets')));

