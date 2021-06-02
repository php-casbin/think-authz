<?php

namespace tauthz\facade;

use think\Facade;

/**
 * @see \Casbin\Enforcer
 * @package tauthz\facade
 * @mixin \Casbin\Enforcer
 * @method static bool enforce(mixed ...$rvals) 权限检查，输入参数通常是(sub, obj, act)
 * @method static bool addPolicy(mixed ...$params) 当前策略添加授权规则
 * @method static bool hasPolicy(mixed ...$params) 确定是否存在授权规则
 * @method static bool removePolicy(mixed ...$params) 当前策略移除授权规则
 * @method static array getRolesForUser(string $username, string ...$domain) 获取用户具有的角色
 * @method static array getUsersForRole(string $role, string ...$domain) 获取具有角色的用户
 * @method static bool hasRoleForUser(string $username, string $role, string ...$domain) 确定用户是否具有角色
 * @method static bool addRoleForUser(string $username, string $role, string ...$domain) 为用户添加角色
 * @method static bool deleteRoleForUser(string $username, string $role, string ...$domain) 删除用户的角色
 * @method static bool deleteRolesForUser(string $username, string ...$domain) 删除用户的所有角色
 * @method static bool deleteUser(string $username) 删除一个用户
 * @method static bool deleteRole(string $role) 删除一个角色
 * @method static bool deletePermission(string ...$permission) 删除权限
 * @method static bool addPermissionForUser(string $username, string ...$permission) 为用户或角色添加权限
 * @method static bool deletePermissionForUser(string $username, string ...$permission) 删除用户或角色的权限
 * @method static bool deletePermissionsForUser(string $username) 删除用户或角色的权限
 * @method static array getPermissionsForUser(string $username) 获取用户或角色的权限
 * @method static bool hasPermissionForUser(string $username, string ...$permission) 确定用户是否具有权限
 * @method static array getImplicitRolesForUser(string $username, string ...$domain) 获取用户具有的隐式角色
 * @method static array getImplicitPermissionsForUser(string $username, string ...$domain) 获取用户具有的隐式权限
 */
class Enforcer extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'enforcer';
    }
}
