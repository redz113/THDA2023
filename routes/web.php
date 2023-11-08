<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ExaminerController;
use App\Http\Controllers\MedalController;
use App\Http\Controllers\MedalAwardController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\ConfigurationsController;


//Android
Route::get('list-user', [UserController::class, 'showListUser'])->name('list-user');

Auth::routes();

Route::get('/changePassword', [HomeController::class, 'showChangePasswordForm']);
Route::post('/changePassword', [HomeController::class, 'changePassword'])->name('changePassword');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {
    // Route::get('roles/roleAdmin', [RoleController::class, 'roleAdmin']);
    Route::get('researchs/export', [ResearchController::class, 'export']);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('topics', TopicController::class);
    Route::get('topics-register-list/{sort}', [TopicController::class, 'topicRegisterList']);
    Route::post('topics-register', [TopicController::class, 'topicRegister']);
    Route::get('topic-register-details', [TopicController::class, 'topicRegisterDetails']);
    Route::post('topic-register-cancel', [TopicController::class, 'topicRegisterCancel']);
    Route::get('topic-register-confirm/{topic_id}', [TopicController::class, 'showTopicConfirm'])->name('topic-register-confirm');
    Route::get('time', [TopicController::class, 'testTime']);
    Route::get('topic-register-result', [TopicController::class, 'showRegisterResult'])->name('topic-register-result');
    //Instructor
    Route::get('instructor-topics-list', [TopicController::class, 'instructorTopicList'])->name('instructor-topics-list');
    Route::get('instructor-topics-show/{topic}', [TopicController::class, 'instructorShow'])->name('instructor-topics-show');
    Route::get('instructor-topics-edit/{topic}', [TopicController::class, 'instructorEdit'])->name('instructor-topics-edit');
    Route::get('instructor-topics-create', [TopicController::class, 'instructorCreate'])->name('instructor-topics-create');


    // Route::resource('configurations', ConfigurationsController::class);
    // Route::get('configurations/edit', [ConfigurationsController::class,'edit'])->name('configurations.edit');
});

Route::prefix('configurations')->as('configurations.')->group(function () {
    Route::get('/', [ConfigurationsController::class,'index'])->name('index');
    Route::get('edit', [ConfigurationsController::class,'edit'])->name('edit');
    Route::patch('/', [ConfigurationsController::class,'add'])->name('add');
});

