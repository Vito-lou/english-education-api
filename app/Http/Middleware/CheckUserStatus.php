<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 如果用户已认证，检查状态
        if ($user && $user->status !== 'active') {
            // 删除当前token
            $user->currentAccessToken()->delete();
            
            return response()->json([
                'success' => false,
                'message' => '账户已被禁用，请联系管理员',
                'code' => 403
            ], 403);
        }

        return $next($request);
    }
}
