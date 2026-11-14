<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class Controller
{
    /** Chunk sizes for list infinite scroll (and legacy per_page query). */
    public const PAGE_SIZES = [10, 20, 25, 50, 100, 200];

    /**
     * Rows per scroll chunk: the size picked under the list, or the list's own default.
     */
    protected function perPage(Request $request, int $default): int
    {
        $size = $request->integer('per_page');

        return in_array($size, self::PAGE_SIZES, true) ? $size : $default;
    }

    /**
     * Full page view, or JSON HTML rows when the infinite-scroll client asks for the next chunk.
     *
     * @param  array<string, mixed>  $data
     */
    protected function infiniteOrView(
        Request $request,
        string $view,
        string $rowsView,
        array $data,
        string $paginatorKey,
    ): View|JsonResponse {
        /** @var LengthAwarePaginator $paginator */
        $paginator = $data[$paginatorKey];

        if ($request->boolean('infinite')) {
            return response()->json([
                'html' => view($rowsView, $data)->render(),
                'next_url' => $paginator->hasMorePages() ? $paginator->nextPageUrl() : null,
                'has_more' => $paginator->hasMorePages(),
                'shown' => (int) $paginator->lastItem(),
                'total' => (int) $paginator->total(),
            ]);
        }

        return view($view, $data);
    }

    /**
     * Another company's records do not exist as far as this user is concerned.
     */
    protected function ensureSameCompany(Request $request, Model $model): void
    {
        abort_unless((int) $model->company_id === (int) $request->user()->company_id, 404);
    }
}
