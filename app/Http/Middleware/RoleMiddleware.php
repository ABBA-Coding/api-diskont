<?php

namespace App\Http\Middleware;

use App\Models\Roles\Permission;
use App\Models\Roles\PermissionGroup;
use App\Models\Roles\Role;
use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
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
//        return $next($request);
        $auth_user = auth()->user();

        if($auth_user->tokenCan('admin') && $auth_user->role_id == null) return $next($request);

        $user_permissions = Permission::whereHas('roles', function ($q) {
                $q->whereHas('admins', function ($qi) {
                    $qi->where('id', auth()->id());
                });
            })
            ->where(function ($q) use ($request) {
                $q->where('methods', 'like', '%'.$request->getMethod().'%')
                    ->orWhere('methods', null);
            })
            ->pluck('url')
            ->toArray();

        $user_permission_groups = PermissionGroup::whereHas('roles', function ($q) {
                $q->whereHas('admins', function ($qi) {
                    $qi->where('id', auth()->id());
                });
            })
            ->get();

        foreach ($user_permission_groups as $user_permission_group) {
            $user_permissions = array_merge($user_permissions, $user_permission_group->permissions->pluck('url')->toArray());
        }

        if(in_array($this->get_url($request), $user_permissions)) return $next($request);

        $counter = 0;
        $url_to_arr = explode('/', $this->get_url($request));
        foreach ($user_permissions as $user_permission) {
            $permission_to_arr = explode('/', $user_permission);

            foreach ($permission_to_arr as $key => $permission_to_arr_item) {
                if(!isset($url_to_arr[$key])) break;
                if($permission_to_arr_item == $url_to_arr[$key]) $counter ++;
            }
            if($counter == count($permission_to_arr)) return $next($request);
        }

        return response([
            'message' => 'Forbidden'
        ], 403);
    }

    public function get_url($request)
    {
        $url = $request->getRequestUri();
        $url = substr($url, 11);

        return $url;
    }
}
