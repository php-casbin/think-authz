<?php

namespace tauthz\middleware;

use tauthz\exception\Unauthorized;
use tauthz\facade\Enforcer;
use think\Request;

class Basic
{
    /**
     * Undocumented function.
     *
     * @param Request  $request
     * @param \Closure $next
     * @param mixed    ...$args
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $args)
    {
        $authzIdentifier = $this->getAuthzIdentifier($request);
        if (!$authzIdentifier) {
            throw new Unauthorized();
        }

        if (!Enforcer::enforce($authzIdentifier, ...$args)) {
            throw new Unauthorized();
        }

        return $next($request);
    }

    public function getAuthzIdentifier(Request $request)
    {
        return $request->middleware('auth_id');
    }
}
