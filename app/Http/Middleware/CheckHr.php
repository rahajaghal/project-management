<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHr
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ((auth()->user()->role->role != 'human resource') && (auth()->user()->role->role != 'admin')){
            return response('you are not allowed to do this,HR Or Admin does');
        }
        return $next($request);
    }
}
