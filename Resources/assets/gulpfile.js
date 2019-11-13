"use strict";

// Load plugins
const cleanCSS = require("gulp-clean-css");
const del = require("del");
const gulp = require("gulp");
const merge = require("merge-stream");
const rename = require("gulp-rename");
const uglify = require("gulp-uglify");

const outDir = './../public/';

// Clean vendor
function clean() {
  return del([outDir],{force: true});
}

// Bring third party dependencies from node_modules into vendor directory
function modules() {
  return merge(jQuery(), blueimp());
}

function jQuery() {
  var jquery = gulp.src([
      './node_modules/jquery/dist/*.js',
      '!./node_modules/jquery/dist/*.min.*',
      '!./node_modules/jquery/dist/core.js'
    ])
    .pipe(gulp.dest(outDir + 'jquery'))
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(outDir + 'jquery'))
  ;

  return jquery;
}

function blueimp() {
  var blueimpJs = gulp.src([
      './node_modules/blueimp-file-upload/**/*.js',
      '!./node_modules/blueimp-file-upload/**/*.min.js',
    ])
    .pipe(gulp.dest(outDir + 'blueimp'))
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(outDir + 'blueimp'));
  ;

  var blueimpCss = gulp.src([
      './node_modules/blueimp-file-upload/**/*.css',
      '!./node_modules/blueimp-file-upload/**/*.min.css',
    ])
    .pipe(gulp.dest(outDir + 'blueimp'))
    .pipe(rename({
      suffix: ".min"
    }))
    .pipe(cleanCSS())
    .pipe(gulp.dest(outDir + 'blueimp'))
  ;

  var blueimpImg = gulp.src([
      './node_modules/blueimp-file-upload/img/*',
    ])
    .pipe(gulp.dest(outDir + 'blueimp/img'))
  ;

  return merge(blueimpJs, blueimpCss, blueimpImg);
}

// Define complex tasks
const vendor = gulp.series(clean, modules);
exports.vendor = vendor;
