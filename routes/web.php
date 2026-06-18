<?php

use App\Http\Controllers\AddressSubjController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CityDistrictController;
use App\Http\Controllers\DetailsObjController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\GroupAddressObjController;
use App\Http\Controllers\ImgObjController;
use App\Http\Controllers\ImgSubjController;
use App\Http\Controllers\MapPointController;
use App\Http\Controllers\ObjController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubjController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserVkController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\VerifyEmailController;


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

Auth::routes(['verify' => true]);

Route::get('/group_address/{id}', [GroupAddressObjController::class, 'show'])->name('group.address.show');

Route::get('/get-cities', [CityController::class, 'getCities'])->name('get-cities');
Route::post('/set-city', [CityController::class, 'setCity'])->name('set-city');
Route::get('/api/districts-by-city', [CityController::class, 'getDistrictsByCity'])->name('api.districts.by.city');
Route::get('/city-district', [CityDistrictController::class, 'create'])->name('city-district.create');
Route::post('/city-district', [CityDistrictController::class, 'store'])->name('city-district.store');

Route::get('/map_edit/subj{id}', [MapPointController::class, 'edit'])->name('map.edit')->middleware('auth');
Route::post('/map/points', [MapPointController::class, 'addSubjectToMap'])->name('map_subj.points.store');
Route::get('/map_create/subj{id}', [MapPointController::class, 'create'])->name('map.create')->middleware('auth');
Route::get('/show_map/id{id}', [MapPointController::class, 'show'])->name('show.map');
Route::delete('/destroy_map_address/id{id}', [MapPointController::class, 'destroy'])->name('destroy.map.address')->middleware('auth');

Route::get('/maps', [MapPointController::class, 'index'])->name('map.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/delete_profile', [ProfileController::class, 'deleteProfile'])->name('profile.delete_profile');
});

require __DIR__ . '/auth.php';

Route::get('/', [FrontController::class, 'show'])->name("front");

Route::get('/create_subj', [SubjController::class, 'create'])->name("create.subj")->middleware('auth');
Route::get('/show_subj/id{id}', [SubjController::class, 'show'])->name("show.subj");
Route::get('/edit_subj/id{id}', [SubjController::class, 'edit'])->name("edit.subj")
    ->middleware('auth');
Route::post('/store_subj', [SubjController::class, 'store'])->name("store.subj")->middleware('auth');
Route::post('/update_subj', [SubjController::class, 'update'])
    ->name('update.subj')
    ->middleware('auth');

Route::get('/subj_take_off', [SubjController::class, 'takeOff'])->name("subj.take_off")->middleware('auth');
Route::get('/subj_publish', [SubjController::class, 'published'])->name("subj.publish")->middleware('auth');

Route::get('/api/cities', [AddressSubjController::class, 'search'])->name('api.cities.search');
Route::get('/api/streets', [AddressSubjController::class, 'searchStreets']);
Route::get('/api/districts', [AddressSubjController::class, 'searchDistricts']);

Route::get('/my_obj', [ObjController::class, 'myObj'])->name("my.obj")->middleware('auth');
Route::get('/create_obj', [ObjController::class, 'create'])->name("create.obj")->middleware('auth');
Route::get('/edit_obj/id{id}', [ObjController::class, 'edit'])->name("obj.edit")->middleware('auth');
Route::post('/store_obj', [ObjController::class, 'store'])->name("store.obj")->middleware('auth');
Route::post('/update_obj', [ObjController::class, 'update'])->name("update.obj")->middleware('auth');
Route::get('/show_obj/id{id}', [ObjController::class, 'show'])->name("show.obj");

Route::post('/search', [SearchController::class, 'search'])->name("search.objs");
Route::post('/api/clear-filters', [SearchController::class, 'clearFilters'])->name('clear.filters');
//Route::get('/search', [SearchController::class, 'searchResults'])->name('search.results');

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
Route::post('/img_subj_store', [ImgSubjController::class, 'imgSubjStore'])->name('img_subj.store')->middleware('auth');
Route::delete('/delete_subj_img/{id}', [ImgSubjController::class, 'destroy'])->name('img_subj.destroy')->middleware('auth');
Route::post('/img_subj_order_change', [ImgSubjController::class, 'imgOrderChange'])->name('img_subj.order_change')->middleware('auth');

Route::get('/image_del', [TestController::class, 'delete']);
Route::get('/test', [TestController::class, 'test'])->name("test");
Route::post('/test_upload', [TestController::class, 'upload'])->name("upload.image");
Route::get('/test_cities', [TestController::class, 'testCities'])->name("test.cities");
Route::get('/test_img', [TestController::class, 'show'])->name("test.img");
Route::post('/store_test_img', [TestController::class, 'store'])->name('test_img_obj.store')->middleware('auth');

Route::post('/favorites_store/subj{id}', [FavoriteController::class, 'store'])->name('favorites_subj.store')->middleware('auth.api');
Route::delete('/favorites_destroy/subj{id}', [FavoriteController::class, 'destroy'])->name('favorites_subj.destroy')->middleware('auth.api');
Route::get('/favorites_subjs', [FavoriteController::class, 'index'])->name('favorites.subjs')->middleware('auth');

Route::get('/auth/vk', [UserVkController::class, 'redirectToVk'])->name('vk.auth');
//Route::get('/auth/vk/callback', [UserVkController::class, 'handleVkCallback'])->name('vk.callback');
Route::post('/auth/vk/save', [UserVkController::class, 'saveVkUserData']);


Route::any('/vk-auth', function () {
    return view('auth.vk-auth');
})->name('vk.auth.page');

Route::get('/unauthorized', [ErrorController::class, 'unauthorized'])->name('unauthorized');

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Кэш очищен.";
});




