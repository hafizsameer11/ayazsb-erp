<?php

namespace App\Http\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsWithJsonOrRedirect
{
    protected function jsonOrRedirect(Request $request, RedirectResponse $redirect, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => $redirect->getTargetUrl(),
            ]);
        }

        return $redirect->with('status', $message);
    }
}
