<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public_Group\PublicController;
use App\Http\Controllers\Groups\GroupController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware'=>['Aspect']],function (){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/activate_email', [AuthController::class, 'activate_email']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/list_file_public', [PublicController::class, 'list_file_public']);
    Route::middleware(['active_acount','auth:sanctum'])->group(function () {
        Route::post('/add_public_file', [PublicController::class, 'add_public_file']);
        Route::get('/my_public_files', [PublicController::class, 'my_public_files']);
        Route::get('/check_in_public_file/{id}', [PublicController::class, 'check_in_public_file']);
        Route::post('/check_in_list_public_files', [PublicController::class, 'check_in_list_public_files']);
        Route::post('/check_out_public_file', [PublicController::class, 'check_out_public_file']);
      //  ----------------------------------------------------------------------------------------------
      //groups
      Route::post('/create_group', [GroupController::class, 'create_group'])->middleware('verified');
      Route::post('/add_file_to_group', [GroupController::class, 'add_file_to_group']);
      Route::post('/delete_file_from_group', [GroupController::class, 'delete_file_from_group']);
      Route::post('/add_user_to_group', [GroupController::class, 'add_user_to_group']);
      Route::post('/delete_user_from_group', [GroupController::class, 'delete_user_from_group']);
      Route::get('/list_user_in_my_group/{group_id}', [GroupController::class, 'list_user_in_my_group']);
      Route::get('/group_details/{group_id}', [GroupController::class, 'group_details']);
      Route::get('/list_created_groups', [GroupController::class, 'list_created_groups']);
      Route::get('/list_joined_groups', [GroupController::class, 'list_joined_groups']);
      Route::get('/list_users', [GroupController::class, 'list_users']);
      Route::get('/read_file_from_group/{file_id}', [GroupController::class, 'read_file_from_group']);
      Route::get('/check_in_group_file/{file_id}', [GroupController::class, 'check_in_group_file']);
      Route::post('/check_out_group_file', [GroupController::class, 'check_out_group_file']);
    });
});
