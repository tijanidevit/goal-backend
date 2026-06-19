<?php

use App\Http\Controllers\Api\SelectionController;
use Illuminate\Support\Facades\Route;

Route::get('/goal-categories', [SelectionController::class, 'goalCategories']);
