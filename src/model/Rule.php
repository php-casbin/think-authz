<?php

namespace tauthz\model;

use think\Model;
use think\contract\Arrayable;

/**
 * Rule Model
 */
class Rule extends Model implements Arrayable
{
    /**
     * 设置字段信息
     *
     * @var array
     */
    protected $schema = [
        'id' => 'int',
        'ptype' => 'string',
        'v0' => 'string',
        'v1' => 'string',
        'v2' => 'string',
        'v3' => 'string',
        'v4' => 'string',
        'v5' => 'string',
    ];

    /** 缓存是否开启 @var string */
    protected $cacheEnabled;

    /** 设置缓存key @var string */
    protected $cacheKey;

    /** 设置缓存key过期时间 @var int */
    protected $cacheExpire;

    /**
     * 架构函数
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->connection = $this->config('database.connection') ?: '';
        $this->table = $this->config('database.rules_table');
        $this->name = $this->config('database.rules_name');

        $this->cacheEnabled = $this->config('cache.enabled', false);
        $this->cacheKey = $this->config('cache.key');
        $this->cacheExpire = $this->config('cache.expire');
        parent::__construct($data);
    }

    /**
     * Gets config value by key.
     *
     * @param string|null $key
     * @param null $default
     *
     * @return mixed
     */
    protected function config(string $key = null, $default = null)
    {
        $driver = config('tauthz.default');
        return config('tauthz.enforcers.' . $driver . '.' . $key, $default);
    }
}
