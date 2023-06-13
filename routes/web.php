<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
	return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\KriteriaController;
use App\Http\Controllers\PaketDataController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;


Route::get('/', function () {
	return redirect('/dashboard');
});
Route::get('/home', [HomeController::class, 'home'])->name('home');
Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware('auth');
Route::group(['middleware' => 'auth'], function () {

	Route::post('/register-user', [RegisterController::class, 'regisNew'])->name('register.new');
	Route::get('/setting', [HomeController::class, 'formLanding'])->name('landing');
	Route::post('/setting', [HomeController::class, 'updateLanding'])->name('landing.update');

	Route::get('/add-user', [RegisterController::class, 'formRegis'])->name('user.form');
	Route::get('/user', [UserProfileController::class, 'showUser'])->name('user.all');
	Route::get('/user/hapus/{id}', [UserProfileController::class, 'deleteUser'])->name('user.delete');
	Route::get('/user/edit/{id}', [UserProfileController::class, 'editUser'])->name('user.edit');
	Route::post('/user/update/{id}', [UserProfileController::class, 'updateUser'])->name('user.update');

	Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/change-pass', [UserProfileController::class, 'changePassword'])->name('profile.pass');

	Route::get('/paket-data', [PaketDataController::class, 'show'])->name('paket-data');
	Route::post('/paket-data', [PaketDataController::class, 'create'])->name('paket.insert');
	Route::get('/paket-data/hapus/{id}', [PaketDataController::class, 'delete'])->name('paket.delete');
	Route::get('/paket-data/edit/{id}/', [PaketDataController::class, 'edit'])->name('paket.edit');
	Route::post('/paket-data/edit/{id}/', [PaketDataController::class, 'update'])->name('paket.update');

	Route::get('/plan', [PlanController::class, 'show'])->name('plan');
	Route::get('/create-plan', [PlanController::class, 'createForm'])->name('plan.form');
	Route::post('/create-plan', [PlanController::class, 'create'])->name('plan.create');
	Route::get('/plan/hapus/{id}', [PlanController::class, 'delete'])->name('plan.delete');
	Route::get('/plan/edit/{id}', [PlanController::class, 'edit'])->name('plan.edit');
	Route::post('/plan/edit/{id}', [PlanController::class, 'update'])->name('plan.update');

	Route::get('/nilai-ternormalisasi', [PlanController::class, 'nilaiTernormalisasi'])->name('plan.normal');
	Route::get('/nilai-ternormalisasi-terbobot', [PlanController::class, 'nilaiTernormalisasiBobot'])->name('plan.bobot');
	Route::get('/perankingan', [PlanController::class, 'perankingan'])->name('plan.rank');

	Route::get('/kriteria', [KriteriaController::class, 'show'])->name('kriteria');
	Route::get('/kriteria/add-kriteria', [KriteriaController::class, 'formCreate'])->name('kriteria.form');
	Route::post('/kriteria', [KriteriaController::class, 'create'])->name('kriteria.create');
	Route::get('/kriteria/hapus/{id}', [KriteriaController::class, 'delete'])->name('kriteria.delete');
	Route::get('/kriteria/edit/{id}', [KriteriaController::class, 'edit'])->name('kriteria.edit');
	Route::post('/kriteria/edit/{id}', [KriteriaController::class, 'update'])->name('kriteria.update');
	Route::get('/kriteria/detail/{id}', [KriteriaController::class, 'detail'])->name('kriteria.detail');
	Route::post('/kriteria/detail', [KriteriaController::class, 'addDetail'])->name('kriteria.addDetail');
	Route::get('/kriteria/edit/detail/{id}', [KriteriaController::class, 'editDetail'])->name('detail.edit');
	Route::post('/kriteria/edit/detail/{id}', [KriteriaController::class, 'updateDetail'])->name('detail.update');
	Route::get('/kriteria/hapus/detail/{id}', [KriteriaController::class, 'deleteDetail'])->name('detail.delete');

	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');

});
