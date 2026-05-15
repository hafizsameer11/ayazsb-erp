<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $user?->load('roles');

        return view('erp.profile', [
            'activeModule' => 'profile',
            'pageTitle' => 'User profile',
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'User profile'],
            ],
            'user' => $user,
        ]);
    }
}
