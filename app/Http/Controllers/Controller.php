<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /** Page sizes offered by the "Show ... per page" box under every list. */
    public const PAGE_SIZES = [10, 20, 25, 50, 100, 200];

    /**
     * Rows per page for a list: the size picked under the list, or the list's own default.
     */
    protected function perPage(Request $request, int $default): int
    {
        $size = $request->integer('per_page');

        return in_array($size, self::PAGE_SIZES, true) ? $size : $default;
    }
}
