<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ( (auth()->user()->role->role != 'admin')&& (auth()->user()->role->role != 'technical manager')&& (auth()->user()->role->role != 'project manager') && (auth()->user()->role->role != 'frontend developer')&& (auth()->user()->role->role != 'backend developer')&& (auth()->user()->role->role != 'full stack developer')&& (auth()->user()->role->role != 'software developer')&& (auth()->user()->role->role != 'system analyst') && (auth()->user()->role->role != 'QA engineer') && (auth()->user()->role->role != 'AI developer') && (auth()->user()->role->role != 'flutter developer') ){
            return response('you are not allowed to do this,admin,technical manager Or Project manager,front ,back ,full,QA tester,software developer,system analyst does');
        }
        return $next($request);
    }
}
