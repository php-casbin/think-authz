<?php

namespace tauthz\adapter;

use tauthz\model\Rule;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;

/**
 * DatabaseAdapter.
 *
 * @author techlee@qq.com
 */
class DatabaseAdapter implements Adapter
{
    use AdapterHelper;

    /**
     * Rules model.
     *
     * @var Rule
     */
    protected $model;

    /**
     * the DatabaseAdapter constructor.
     *
     * @param Rule $model
     */
    public function __construct(Rule $model)
    {
        $this->model = $model;
    }

    /**
     * savePolicyLine function.
     *
     * @param string $ptype
     * @param array  $rule
     *
     * @return void
     */
    public function savePolicyLine($ptype, array $rule)
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v'.strval($key).''] = $value;
        }
        $this->model->create($col);
    }

    /**
     * loads all policy rules from the storage.
     *
     * @param Model $model
     *
     * @return mixed
     */
    public function loadPolicy($model)
    {
        $rows = $this->model->select()->toArray();
        foreach ($rows as $row) {
            $line = implode(', ', array_filter(array_slice($row, 1),function($val){
                return $val != "" && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
    }

    /**
     * saves all policy rules to the storage.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function savePolicy($model)
    {
        foreach ($model->model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model->model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        return true;
    }

    /**
     * Adds a policy rule to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     *
     * @return mixed
     */
    public function addPolicy($sec, $ptype, $rule)
    {
        return $this->savePolicyLine($ptype, $rule);
    }

    /**
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     *
     * @return mixed
     */
    public function removePolicy($sec, $ptype, $rule)
    {
        $count = 0;

        $instance = $this->model->where('ptype', $ptype);

        foreach ($rule as $key => $value) {
            $instance->where('v'.strval($key), $value);
        }

        foreach ($instance->select() as $model) {
            if ($model->delete()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * RemoveFilteredPolicy removes policy rules that match the filter from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param int    $fieldIndex
     * @param mixed  ...$fieldValues
     *
     * @return mixed
     */
    public function removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues)
    {
        $count = 0;

        $instance = $this->model->where('ptype', $ptype);
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $instance->where('v'.strval($value), $fieldValues[$value - $fieldIndex]);
                }
            }
        }

        foreach ($instance->select() as $model) {
            if ($model->delete()) {
                ++$count;
            }
        }
        
        return $count;
    }
}
