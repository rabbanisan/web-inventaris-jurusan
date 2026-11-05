<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Show the email verification notice screen.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Auth/VerifyEmail');
    }
}
