<?php

use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ObjController;
use App\Http\Controllers\ImgObjController;
use App\Http\Controllers\ImgSubjController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\SubjController;
use App\Http\Controllers\DetailsObjController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MapPointController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AddressSubjController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/map', [MapPointController::class, 'showMap'])->name('map.index');
Route::get('/api/map-data', [MapPointController::class, 'getMapData'])->name('map.data');
//Route::get('/map', [MapPointController::class, 'index'])->name('map.index');
Route::get('/map_create/id{id}', [MapPointController::class, 'create'])->name('map.create')->middleware('author');
Route::get('/map_edit/id{id}', [MapPointController::class, 'edit'])->name('map.edit')->middleware('author');
Route::get('/show_map/id{id}', [MapPointController::class, 'show'])->name('show.map');
//Route::post('/map/points', [MapPointController::class, 'store'])->name('map.points.store');
Route::post('/map/points', [MapPointController::class, 'addSubjectToMap'])->name('map_subj.points.store');
Route::delete('/destroy_map_address/id{id}', [MapPointController::class, 'destroy'])->name('destroy.map.address');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/', [FrontController::class, 'show'])->name("front");

Route::get('/create_subj', [SubjController::class, 'create'])->name("create.subj")->middleware('auth');
Route::get('/show_subj/id{id}', [SubjController::class, 'show'])->name("show.subj");
Route::get('/edit_subj/id{id}', [SubjController::class, 'edit'])->name("edit.subj")->middleware('auth');
Route::post('/store_subj', [SubjController::class, 'store'])->name("store.subj")->middleware('auth');
Route::post('/update_subj', [SubjController::class, 'update'])->name("update.subj")->middleware('auth');
Route::get('/my_obj', [SubjController::class, 'myObj'])->name("my.obj")->middleware('auth');
Route::get('/subj_take_off', [SubjController::class, 'takeOff'])->name("subj.take_off")->middleware('auth');
Route::get('/subj_publish', [SubjController::class, 'published'])->name("subj.publish")->middleware('auth');

Route::get('/api/cities', [AddressSubjController::class, 'search'])->name('api.cities.search');
Route::get('/api/streets', [AddressSubjController::class, 'searchStreets']);
Route::post('/api/save-address', [AddressSubjController::class, 'saveAddress']);

Route::get('/create_obj', [ObjController::class, 'create'])->name("create.obj")->middleware('auth');
Route::get('/edit_obj/id{id}', [ObjController::class, 'edit'])->name("obj.edit")->middleware('auth');
Route::post('/store_obj', [ObjController::class, 'store'])->name("store.obj")->middleware('auth');
Route::post('/update_obj', [ObjController::class, 'update'])->name("update.obj")->middleware('auth');
Route::get('/show_obj/id{id}', [ObjController::class, 'show'])->name("show.obj");

Route::post('/search', [SearchController::class, 'search'])->name("search.objs");
Route::post('/api/clear-filters', [SearchController::class, 'clearFilters'])->name('clear.filters');
Route::get('/search', [SearchController::class, 'searchResults'])->name('search.results');

Route::post('/update_details_obj', [DetailsObjController::class, 'update'])->name("update.details_obj");
Route::post('/store_details_obj', [DetailsObjController::class, 'store'])->name("store.details_obj");
Route::get('/create_details_obj', [DetailsObjController::class, 'create'])->name("create.details_obj");
Route::get('/edit_details_obj/id{id}', [DetailsObjController::class, 'edit'])->name("edit.details_obj");

Route::get('/edit_img_obj/id{id}', [ImgObjController::class, 'edit'])->name("edit.img_obj")->middleware('auth');
//Route::post('/img_order_change', [ImgObjController::class, 'imgOrderChange'])->name('img_obj.order_change');
Route::post('/img_obj_store', [ImgObjController::class, 'store'])->name('img_obj.store');
Route::post('/img_obj_update', [ImgObjController::class, 'update'])->name('img_obj.update');
Route::delete('/delete_obj_img/id{id}', [ImgObjController::class, 'destroy'])->name('img_obj.destroy');
//Route::post('/img_obj_order_change', [ImgObjController::class, 'imgOrderChange'])->name('img_obj.order_change');

Route::get('/edit_img_subj/id{id}', [ImgSubjController::class, 'edit'])->name("edit.img_subj")->middleware('auth');
Route::post('/img_subj_store', [ImgSubjController::class, 'imgSubjStore'])->name('img_subj.store');
Route::delete('/delete_subj_img/id{id}', [ImgSubjController::class, 'destroy'])->name('img_subj.destroy');
Route::post('/img_subj_order_change', [ImgSubjController::class, 'imgOrderChange'])->name('img_subj.order_change');

Route::get('/test', [TestController::class, 'test'])->name("test");
Route::get('/test_cities', [TestController::class, 'testCities'])->name("test.cities");
Route::get('/test_img', [TestController::class, 'show'])->name("test.img");
Route::post('/store_test_img', [TestController::class, 'store'])->name('test_img_obj.store')->middleware('auth');

Route::get('/home', 'HomeController@index')->middleware(['auth', 'verified'])->name('home');

Route::get('/test-log', function () {
    // Тестовая запись на русском
    Log::info('Тестовая запись — всё должно быть читаемо!');

    // Проблемная строка с не‑UTF‑8 символами
    $problematic = chr(0xFF) . 'Иероглифы: ㉛㈰';
    Log::error('Проблемная строка: ' . $problematic);

    return 'Проверьте лог — ошибок быть не должно!';
});

Route::get('/test-error-basic', function () {
    Log::error('ТЕСТ: Базовая ошибка для laravel-errors.log');
    return 'Ошибка записана в laravel-errors.log (проверьте файл)';
});


Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Кэш очищен.";
});

Route::get('/create-storage-link', function () {
    if (file_exists(public_path('storage'))) {
        unlink(public_path('storage'));
    }
    symlink(storage_path('app/public'), public_path('storage'));
    return 'Симлинк создан!';
});