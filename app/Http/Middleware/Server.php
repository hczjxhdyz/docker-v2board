<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Server
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $params = $request->validate([
            'token' => 'required|string',
            'node_type' => [
                'required',
                'string',
                'regex:/^(?i)(hysteria|vless|trojan|vmess|v2ray|tuic)$/'
            ],
            'node_id' => 'required'
        ]);

        if ($params['token'] !== Setting('server_token')) {
            throw new \Exception('token is error', 500);
        }

        return $next($request);
    }
}
