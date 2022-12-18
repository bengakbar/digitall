<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Administrasi\KewenanganController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Administrasi\MenuController;
use App\Http\Controllers\Administrasi\SubMenuController;
use App\Http\Controllers\Administrasi\KewenanganMenuController;
use App\Http\Controllers\Administrasi\KewenanganUserController;
use App\Http\Controllers\Administrasi\AdministrasiUserController;
use App\Http\Controllers\ReferensiUnit\DeputiController;
use App\Http\Controllers\ReferensiUnit\BiroController;
use App\Http\Controllers\ReferensiUnit\BagianController;
use App\Http\Controllers\PIPK\TemuanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::match(["GET", "POST"], "/register", function(){
    return redirect("/login");
})->name("register");


Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::resource('kewenangan',KewenanganController::class);
Route::any('/tampillistmenu',[MenuController::class,'tampillistmenu'])->name('tampillistmenu');
Route::resource('menu',MenuController::class);
Route::resource('submenu',SubMenuController::class);
Route::resource('kewenanganmenu',KewenanganMenuController::class);
Route::resource('kewenanganuser',KewenanganUserController::class);
Route::any('/editpassword/{id}',[AdministrasiUserController::class,'editpassword']);
Route::resource('kelolauser',AdministrasiUserController::class);
Route::resource('deputi',DeputiController::class);
Route::resource('biro',BiroController::class);
Route::post('/ambildatabiro',[BagianController::class,'dapatkandatabiro'])->name('ambildatabiro');
Route::post('/ambildatabagian',[BagianController::class,'dapatkandatabagian'])->name('ambildatabagian');
Route::get('/kirimtemuankeunit/{id}',[TemuanController::class,'kirimtemuankeunit'])->name('kirimtemuankeunit')->middleware(['auth']);
Route::resource('bagian',BagianController::class)->middleware(['auth','aksesmenu']);
Route::resource('temuan',TemuanController::class)->middleware(['auth','aksesmenu']);




