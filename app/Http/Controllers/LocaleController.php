<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch current user session locale.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, ['gu', 'en'])) {
            $request->session()->put('locale', $locale);

            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
        }

        $redirect = $request->query('redirect', '/');

        return redirect($redirect);
    }
}
