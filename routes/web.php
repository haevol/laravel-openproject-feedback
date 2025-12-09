<?php

use Illuminate\Support\Facades\Route;
use Haevol\OpenProjectFeedback\Http\Controllers\FeedbackController;

Route::post('/feedback', [FeedbackController::class, 'store'])
    ->name('openproject-feedback.store');

