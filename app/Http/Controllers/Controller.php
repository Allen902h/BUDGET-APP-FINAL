<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function dashboardRedirect(?string $returnTo, string $fallback)
    {
        $dashboardUrl = route('dashboard');

        if (is_string($returnTo) && ($returnTo === $dashboardUrl || str_starts_with($returnTo, $dashboardUrl.'#') || str_starts_with($returnTo, $dashboardUrl.'?') || str_starts_with($returnTo, $dashboardUrl.'/?'))) {
            return redirect()->to($returnTo);
        }

        return redirect()->to($fallback);
    }
}
