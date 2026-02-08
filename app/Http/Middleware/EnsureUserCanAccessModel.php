<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessModel
{
    public function handle(Request $request, Closure $next, string ...$params): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        foreach ($params as $param) {
            $model = $request->route($param);
            if ($model && method_exists($model, 'isVisibleTo') && !$model->isVisibleTo($user)) {
                abort(403, 'شما به این مورد دسترسی ندارید.');
            }
        }

        return $next($request);
    }
}
