<?php

use App\Http\Controllers\BookController;
use App\Http\Middleware\APIKEY;
use Illuminate\Support\Facades\Route;

Route::middleware(APIKEY::class)->group(function (){
   Route::prefix('books')->group(function(){
       Route::get('',[BookController::class,'index']);
       Route::get('spcific',[BookController::class,'specific']);
       Route::get('{id}',[BookController::class,'show']);
       Route::post('',[BookController::class,'store']);
       Route::put('{id}',[BookController::class,'update']);
       Route::patch('{id}',[BookController::class,'edit']);
       Route::delete('{id}',[BookController::class,'destroy']);
   });
});
