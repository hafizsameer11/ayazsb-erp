<?php

namespace App\Http\Concerns;

use App\Support\AdminGate;
use App\Support\RecordHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait AuthorizesAdminDelete
{
    protected function ensureAdminCanDeleteRecords(): void
    {
        abort_unless(AdminGate::canDeleteRecords(), 403);
    }

    protected function erpDeleteResponse(
        Request $request,
        string $routeName,
        array $routeParameters = [],
        string $message = 'Record deleted.',
        ?string $historyDateDisplay = null,
    ): RedirectResponse|JsonResponse {
        $params = $historyDateDisplay !== null
            ? ['history_date' => $historyDateDisplay]
            : RecordHistory::historyQuery($request);

        $redirect = route($routeName, array_merge($routeParameters, $params));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => $redirect,
            ]);
        }

        return redirect()->to($redirect)->with('status', $message);
    }
}
