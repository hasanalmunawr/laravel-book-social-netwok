<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth'])->group(function () {
//    Book
    Route::post('books', [BookController::class, 'store']);
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{id}', [BookController::class, 'searchById']);
    Route::get('books/{title}', [BookController::class, 'searchByTitle']);
    Route::get('books/own', [BookController::class, 'findByOwn']);
    Route::post('books/borrow/{id}', [BookController::class, 'borrow']);

//    Feedback
    Route::post('feedback', [\App\Http\Controllers\api\FeedbackController::class, 'create']);
    Route::get('books/{bookId}/feedbacks', [\App\Http\Controllers\api\FeedbackController::class, 'findAllFeedbacksByBook']);



    Route::post('logout', [AuthController::class, 'logout']);
});
