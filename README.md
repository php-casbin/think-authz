<h1 align="center">
    ThinkPHP 6.0 Authorization
</h1>

<p align="center">
	<strong>Think-authz 是一个专为ThinkPHP6.0打造的授权（角色和权限控制）工具</strong>    
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

它基于 [Casbin](https://github.com/php-casbin/php-casbin), 一个强大的、高效的开源访问控制框架，它支持基于各种访问控制模型的权限管理。

在这之前，你需要了解 `Casbin` 的相关知识.

* [安装](#安装)
* [用法](#用法)
  * [快速开始](#快速开始)
  * [使用 Enforcer Api](#使用-enforcer-api)
  * [Using a middleware](#using-a-middleware)
    * [basic Enforcer Middleware](#basic-enforcer-middleware)
    * [HTTP Request Middleware ( RESTful is also supported )](#http-request-middleware--restful-is-also-supported-)
  * [Using commands](#using-commands)
  * [Cache](#using-cache)
* [感谢](#thinks)
* [License](#license)

## 安装

使用`composer`安装：

```
composer require casbin/think-authz
```

注册服务，在应用的全局公共文件service.php中加入：

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


执行迁移工具（确保数据库配置信息正确）：

```
php think migrate:run
```

这将创将创建名为 `rules` 的表。


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

You can check if a user has a permission like this:

```php
// to check if a user has permission
if (Enforcer::enforce("eve", "articles", "edit")) {
    // permit eve to edit articles
} else {
    // deny the request, show an error
}

```

### 使用 Enforcer Api

It provides a very rich api to facilitate various operations on the Policy:

Gets all roles:

```php
Enforcer::getAllRoles(); // ['writer', 'reader']
```

Gets all the authorization rules in the policy.:

```php
Enforcer::getPolicy();
```

Gets the roles that a user has.

```php
Enforcer::getRolesForUser('eve'); // ['writer']
```

Gets the users that has a role.

```php
Enforcer::getUsersForRole('writer'); // ['eve']
```

Determines whether a user has a role.

```php
Enforcer::hasRoleForUser('eve', 'writer'); // true or false
```

Adds a role for a user.

```php
Enforcer::addRoleForUser('eve', 'writer');
```

Adds a permission for a user or role.

```php
// to user
Enforcer::addPermissionForUser('eve', 'articles', 'read');
// to role
Enforcer::addPermissionForUser('writer', 'articles','edit');
```

Deletes a role for a user.

```php
Enforcer::deleteRoleForUser('eve', 'writer');
```

Deletes all roles for a user.

```php
Enforcer::deleteRolesForUser('eve');
```

Deletes a role.

```php
Enforcer::deleteRole('writer');
```

Deletes a permission.

```php
Enforcer::deletePermission('articles', 'read'); // returns false if the permission does not exist (aka not affected).
```

Deletes a permission for a user or role.

```php
Enforcer::deletePermissionForUser('eve', 'articles', 'read');
```

Deletes permissions for a user or role.

```php
// to user
Enforcer::deletePermissionsForUser('eve');
// to role
Enforcer::deletePermissionsForUser('writer');
```

Gets permissions for a user or role.

```php
Enforcer::getPermissionsForUser('eve'); // return array
```

Determines whether a user has a permission.

```php
Enforcer::hasPermissionForUser('eve', 'articles', 'read');  // true or false
```

### Using a middleware

敬请期待...

#### basic Enforcer Middleware



#### HTTP Request Middleware ( RESTful is also supported )


```
```

### Using artisan commands

敬请期待...

### Using cache

敬请期待...

## 感谢

[Casbin](https://github.com/php-casbin/php-casbin) . You can find the full documentation of Casbin [on the website](https://casbin.org/).

## License

This project is licensed under the [Apache 2.0 license](LICENSE).