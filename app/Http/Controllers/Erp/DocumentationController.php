<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        abort_unless($user instanceof \App\Models\User && $user->hasPermission('accounts.dashboard.view'), 403);

        return view('erp.docs.index', [
            'activeModule' => 'docs',
            'pageTitle' => 'ERP Documentation',
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'ERP Documentation'],
            ],
        ]);
    }
}

