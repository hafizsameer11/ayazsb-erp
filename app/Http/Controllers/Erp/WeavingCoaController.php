<?php

namespace App\Http\Controllers\Erp;

use App\Http\Concerns\AuthorizesWeaving;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\ChartOfAccountsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeavingCoaController extends Controller
{
    use AuthorizesWeaving;

    public function show(): View
    {
        abort_unless($this->weavingAllowed('coa', 'view'), 403);

        $service = ChartOfAccountsService::weaving();

        return view('erp.weaving.coa', [
            'activeModule' => 'weaving',
            'moduleKey' => 'weaving',
            'moduleLabel' => 'Weaving',
            'permissionPrefix' => 'weaving.coa',
            'pageTitle' => 'Weaving Chart of Accounts',
            'screen' => ['slug' => 'coa', 'label' => 'Weaving Chart of Accounts', 'code' => 'WEAVSP_0006'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Weaving', 'route' => 'erp.weaving.dashboard'],
                ['label' => 'Weaving Chart of Accounts'],
            ],
            'accounts' => $service->accountsForListing(),
            'coaParentsJson' => $service->parentsJson(),
            'coaTitle' => 'WEAVSP_0006 — Weaving Chart of Accounts',
            'coaStoreRoute' => route('erp.weaving.coa.store'),
            'coaUpdateBase' => url('/erp/weaving/coa'),
            'coaPermissionPrefix' => 'weaving.coa',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->weavingAllowed('coa', 'create'), 403);

        $service = ChartOfAccountsService::weaving();
        $validated = $request->validate($service->storeValidationRules());
        $service->create($validated);

        return back()->with('status', 'Weaving account created.');
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        abort_unless($this->weavingAllowed('coa', 'edit'), 403);

        $service = ChartOfAccountsService::weaving();
        $validated = $request->validate($service->updateValidationRules());
        $account = $service->update($account, $validated, $request);

        return back()->with('status', "Weaving account {$account->code} updated.");
    }
}
