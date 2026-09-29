<?php

use App\Http\Controllers\Admin\AlbumAdminController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeAdminController;
use App\Http\Controllers\Admin\MenuAdminController;
use App\Http\Controllers\Admin\NewsAdminController;
use App\Http\Controllers\Admin\NoticeAdminController;
use App\Http\Controllers\Admin\PageAdminController;
use App\Http\Controllers\Admin\RankingAdminController;
use App\Http\Controllers\Admin\SettingsAdminController;
use App\Http\Controllers\Admin\TeacherAdminController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\VideoAdminController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TeacherApiController;
use App\Http\Controllers\TeacherProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/login', [AuthController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:admin-login')->name('admin.login.submit');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/home-banners', [HomeAdminController::class, 'edit'])->name('home.edit');
    Route::put('/home-banners', [HomeAdminController::class, 'update'])->name('home.update');

    Route::get('/settings', [SettingsAdminController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsAdminController::class, 'update'])->name('settings.update');

    Route::get('/menus', [MenuAdminController::class, 'edit'])->name('menus.edit');
    Route::put('/menus', [MenuAdminController::class, 'update'])->name('menus.update');

    Route::get('/rankings', [RankingAdminController::class, 'index'])->name('rankings.index');
    Route::post('/rankings', [RankingAdminController::class, 'store'])->name('rankings.store');
    Route::delete('/rankings/{id}', [RankingAdminController::class, 'destroy'])->name('rankings.destroy');

    Route::get('/videos', [VideoAdminController::class, 'index'])->name('videos.index');
    Route::post('/videos', [VideoAdminController::class, 'store'])->name('videos.store');
    Route::delete('/videos/{id}', [VideoAdminController::class, 'destroy'])->name('videos.destroy');

    Route::get('/albums', [AlbumAdminController::class, 'index'])->name('albums.index');
    Route::get('/albums/create', [AlbumAdminController::class, 'create'])->name('albums.create');
    Route::post('/albums', [AlbumAdminController::class, 'store'])->name('albums.store');
    Route::get('/albums/{id}/edit', [AlbumAdminController::class, 'edit'])->name('albums.edit');
    Route::put('/albums/{id}', [AlbumAdminController::class, 'update'])->name('albums.update');
    Route::delete('/albums/{id}', [AlbumAdminController::class, 'destroy'])->name('albums.destroy');

    Route::get('/news', [NewsAdminController::class, 'index'])->name('news.index');
    Route::get('/news/create', [NewsAdminController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsAdminController::class, 'store'])->name('news.store');
    Route::get('/news/{id}/edit', [NewsAdminController::class, 'edit'])->name('news.edit')->whereNumber('id');
    Route::put('/news/{id}', [NewsAdminController::class, 'update'])->name('news.update')->whereNumber('id');
    Route::delete('/news/{id}', [NewsAdminController::class, 'destroy'])->name('news.destroy')->whereNumber('id');

    Route::get('/notices', [NoticeAdminController::class, 'index'])->name('notices.index');
    Route::get('/notices/create', [NoticeAdminController::class, 'create'])->name('notices.create');
    Route::post('/notices', [NoticeAdminController::class, 'store'])->name('notices.store');
    Route::get('/notices/{id}/edit', [NoticeAdminController::class, 'edit'])->name('notices.edit')->whereNumber('id');
    Route::put('/notices/{id}', [NoticeAdminController::class, 'update'])->name('notices.update')->whereNumber('id');
    Route::delete('/notices/{id}', [NoticeAdminController::class, 'destroy'])->name('notices.destroy')->whereNumber('id');

    Route::get('/pages', [PageAdminController::class, 'index'])->name('pages.index');
    Route::get('/pages/create', [PageAdminController::class, 'create'])->name('pages.create');
    Route::post('/pages', [PageAdminController::class, 'store'])->name('pages.store');
    Route::get('/pages/{slug}/edit', [PageAdminController::class, 'edit'])->name('pages.edit')->where('slug', '.+');
    Route::put('/pages/{slug}', [PageAdminController::class, 'update'])->name('pages.update')->where('slug', '.+');
    Route::post('/pages/{slug}/approve', [PageAdminController::class, 'approve'])->name('pages.approve')->where('slug', '.+');
    Route::post('/pages/{slug}/unpublish', [PageAdminController::class, 'unpublish'])->name('pages.unpublish')->where('slug', '.+');
    Route::delete('/pages/{slug}/content', [PageAdminController::class, 'destroy'])->name('pages.destroy')->where('slug', '.+');
    Route::delete('/pages/{slug}', [PageAdminController::class, 'destroyPage'])->name('pages.destroyPage')->where('slug', '.+');

    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');

    Route::get('/teachers', [TeacherAdminController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/create', [TeacherAdminController::class, 'create'])->name('teachers.create');
    Route::post('/teachers', [TeacherAdminController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{id}/edit', [TeacherAdminController::class, 'edit'])->name('teachers.edit');
    Route::put('/teachers/{id}', [TeacherAdminController::class, 'update'])->name('teachers.update');
    Route::delete('/teachers/{id}', [TeacherAdminController::class, 'destroy'])->name('teachers.destroy');
});

Route::get('/api/teachers/{department}', [TeacherApiController::class, 'index'])->name('api.teachers');
Route::get('/api/office-staff/{code}', [TeacherApiController::class, 'office'])->name('api.office-staff')->whereNumber('code');
Route::get('/faculty/{id}', [TeacherProfileController::class, 'show'])->name('faculty.show')->whereNumber('id');
Route::get('/faculty/e/{key}', [TeacherProfileController::class, 'showByKey'])->name('faculty.by-key')->where('key', '[^/]+');

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/search', [SiteController::class, 'search'])->name('search');
Route::get('/{slug}', [SiteController::class, 'show'])->name('page.show')->where('slug', '.+');
