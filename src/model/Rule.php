<?php

namespace tauthz\model;

use tauthz\traits\Configurable;
use think\Model;
use think\contract\Arrayable;

/**
 * Rule Model
 */
class Rule extends Model implements Arrayable
{
    use Configurable;

    protected $name;

    // 设置当前模型对应的完整数据表名称
    protected $table;

    // 设置当前模型的数据库连接
    protected $connection;

    // 默认主键
    protected $pk = 'id';

    /**
     * 设置字段信息
     *
     * @var array
     */
    protected $schema = [
        'id'    => 'int',
        'ptype' => 'string',
        'v0'    => 'string',
        'v1'    => 'string',
        'v2'    => 'string',
        'v3'    => 'string',
        'v4'    => 'string',
        'v5'    => 'string',
    ];
    /**
     * 架构函数
     * 
     * @param array|object $data 数据
     */
    public function __construct(array|object $data = [])
    {
        $this->connection = $this->config('database.connection') ?: '';
        $this->table = $this->config('database.rules_table');
        $this->name = $this->config('database.rules_name');

        parent::__construct($data);
    }
}
