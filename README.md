<h1 align="center">
    ThinkPHP 6.0 Authorization
</h1>

<p align="center">
	<strong>Think-authz 是一个专为 ThinkPHP6.0 打造的授权（角色和权限控制）工具</strong>    
</p>

<p align="center">
	<a href="https://travis-ci.org/php-casbin/think-authz">
		<img src="https://travis-ci.org/php-casbin/think-authz.svg?branch=master" alt="Build Status">
  	</a>
	<a href="https://coveralls.io/github/php-casbin/think-authz">
		<img src="https://coveralls.io/repos/github/php-casbin/think-authz/badge.svg" alt="Coverage Status">
  	</a>
    <a href="https://packagist.org/packages/casbin/think-authz">
		<img src="https://poser.pugx.org/casbin/think-authz/v/stable" alt="Latest Stable Version">
  	</a>
     <a href="https://packagist.org/packages/casbin/think-authz">
		<img src="https://poser.pugx.org/casbin/think-authz/downloads" alt="Total Downloads">
  	</a>
    <a href="https://packagist.org/packages/casbin/think-authz">
		<img src="https://poser.pugx.org/casbin/think-authz/license" alt="License">
  	</a>
</p>

它基于 [PHP-Casbin](https://github.com/php-casbin/php-casbin), 一个强大的、高效的开源访问控制框架，支持基于`ACL`, `RBAC`, `ABAC`等访问控制模型。

在这之前，你需要了解 [Casbin](https://github.com/php-casbin/php-casbin) 的相关知识。

* [安装](#安装)
* [用法](#用法)
  * [快速开始](#快速开始)
  * [使用 Enforcer Api](#使用-enforcer-api)
  * [使用中间件](#使用中间件)
* [感谢](#thinks)
* [License](#license)

## 安装

> 该扩展需要 PHP 7.1+ 和 ThinkPHP 6.0+，针对 TP 5.1 请使用 [Think-Casbin](https://github.com/php-casbin/think-casbin) .

使用`composer`安装：

```
composer require casbin/think-authz
```

注册服务，在应用的全局公共文件`service.php`中加入：

```php
return [
    // ...

    tauthz\TauthzService::class,
];
```

发布配置文件和数据库迁移文件：

```
php think tauthz:publish
```

这将自动生成 `config/tauthz-rbac-model.conf` 和 `config/tauthz.php` 文件。


执行迁移工具（**确保数据库配置信息正确**）：

```
php think migrate:run
```

这将创建名为 `rules` 的表。


## 用法

### 快速开始

安装成功后，可以这样使用:

```php

use tauthz\facade\Enforcer;

// adds permissions to a user
Enforcer::addPermissionForUser('eve', 'articles', 'read');
// adds a role for a user.
Enforcer::addRoleForUser('eve', 'writer');
// adds permissions to a rule
Enforcer::addPolicy('writer', 'articles','edit');

```

你可以检查一个用户是否拥有某个权限:

```php
// to check if a user has permission
if (Enforcer::enforce("eve", "articles", "edit")) {
    // permit eve to edit articles
} else {
    // deny the request, show an error
}

```

### 使用 Enforcer Api

它提供了非常丰富的 `API`，以促进对 `Policy` 的各种操作：

获取所有角色:

```php
Enforcer::getAllRoles(); // ['writer', 'reader']
```

获取所有的角色的授权规则：

```php
Enforcer::getPolicy();
```

获取某个用户的所有角色：

```php
Enforcer::getRolesForUser('eve'); // ['writer']
```

获取某个角色的所有用户：

```php
Enforcer::getUsersForRole('writer'); // ['eve']
```

决定用户是否拥有某个角色：

```php
Enforcer::hasRoleForUser('eve', 'writer'); // true or false
```

给用户添加角色：

```php
Enforcer::addRoleForUser('eve', 'writer');
```

赋予权限给某个用户或角色：

```php
// to user
Enforcer::addPermissionForUser('eve', 'articles', 'read');
// to role
Enforcer::addPermissionForUser('writer', 'articles','edit');
```

删除用户的角色：

```php
Enforcer::deleteRoleForUser('eve', 'writer');
```

删除某个用户的所有角色：

```php
Enforcer::deleteRolesForUser('eve');
```

删除单个角色：

```php
Enforcer::deleteRole('writer');
```

删除某个权限：

```php
Enforcer::deletePermission('articles', 'read'); // returns false if the permission does not exist (aka not affected).
```

删除某个用户或角色的权限：

```php
Enforcer::deletePermissionForUser('eve', 'articles', 'read');
```

删除某个用户或角色的所有权限：

```php
// to user
Enforcer::deletePermissionsForUser('eve');
// to role
Enforcer::deletePermissionsForUser('writer');
```

获取用户或角色的所有权限：

```php
Enforcer::getPermissionsForUser('eve'); // return array
```

决定某个用户是否拥有某个权限

```php
Enforcer::hasPermissionForUser('eve', 'articles', 'read');  // true or false
```

更多 `API` 参考 [Casbin API](https://casbin.org/docs/en/management-api) 。

### 使用中间件


该扩展包带有一个 `\tauthz\middleware\Basic::class` 中间件:

```php
Route::get('news/:id','News/Show')
	->middleware(\tauthz\middleware\Basic::class, ['news', 'read']);
```

## 感谢

[Casbin](https://github.com/php-casbin/php-casbin)，你可以查看全部文档在其 [官网](https://casbin.org/) 上。

## License

This project is licensed under the [Apache 2.0 license](LICENSE).
