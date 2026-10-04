<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Общий барьер для административных маршрутов, в том числе смены статуса. */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Гостю предлагаем войти, а авторизованному пользователю без роли admin отдаём 403.
        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }
        abort_unless($request->user()->isAdmin(), 403);

        return $next($request);
    }
}
