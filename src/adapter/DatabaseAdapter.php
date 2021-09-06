<?php

namespace tauthz\adapter;

use tauthz\model\Rule;
use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use Casbin\Persist\UpdatableAdapter;
use Casbin\Persist\BatchAdapter;
use Casbin\Persist\FilteredAdapter;
use Casbin\Persist\Adapters\Filter;
use Casbin\Exceptions\InvalidFilterTypeException;
use think\facade\Db;

/**
 * DatabaseAdapter.
 *
 * @author techlee@qq.com
 */
class DatabaseAdapter implements Adapter, UpdatableAdapter, BatchAdapter, FilteredAdapter
{
    use AdapterHelper;

    /**
     * @var bool
     */
    private $filtered = false;

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
        $this->model->cache('tauthz')->save($col);
    }

    /**
     * loads all policy rules from the storage.
     *
     * @param Model $model
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->model->cache('tauthz')->select()->toArray();
        foreach ($rows as $row) {
            $line = implode(', ', array_filter(array_slice($row, 1), function ($val) {
                return '' != $val && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
    }

    /**
     * saves all policy rules to the storage.
     *
     * @param Model $model
     */
    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }
    }

    /**
     * adds a policy rule to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * Adds a policy rules to the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[][] $rules
     */
    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        $cols = [];
        $i = 0;

        foreach ($rules as $rule) {
            $temp['ptype'] = $ptype;
            foreach ($rule as $key => $value) {
                $temp['v' . strval($key)] = $value;
            }
            $cols[$i++] = $temp;
            $temp = [];
        }
        $this->model->cache('tauthz')->insertAll($cols);
    }

    /**
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param array  $rule
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $count = 0;

        $instance = $this->model->where('ptype', $ptype);

        foreach ($rule as $key => $value) {
            $instance->where('v'.strval($key), $value);
        }

        foreach ($instance->select() as $model) {
            if ($model->cache('tauthz')->delete()) {
                ++$count;
            }
        }
    }

    /**
     * Removes policy rules from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[][] $rules
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        Db::transaction(function () use ($sec, $ptype, $rules) {
            foreach ($rules as $rule) {
                $this->removePolicy($sec, $ptype, $rule);
            }
        });
    }

    /**
     * RemoveFilteredPolicy removes policy rules that match the filter from the storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param int    $fieldIndex
     * @param string ...$fieldValues
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
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
            if ($model->cache('tauthz')->delete()) {
                ++$count;
            }
        }
    }

    /**
     * Updates a policy rule from storage.
     * This is part of the Auto-Save feature.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[] $oldRule
     * @param string[] $newPolicy
     */
    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newPolicy): void
    {
        $instance = $this->model->where('ptype', $ptype);
        foreach ($oldRule as $key => $value) {
            $instance->where('v' . strval($key), $value);
        }
        $instance = $instance->find();

        foreach ($newPolicy as $key => $value) {
            $column = 'v' . strval($key);
            $instance->$column = $value;
        }

        $instance->save();
    }

    /**
     * UpdatePolicies updates some policy rules to storage, like db, redis.
     *
     * @param string $sec
     * @param string $ptype
     * @param string[][] $oldRules
     * @param string[][] $newRules
     * @return void
     */
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        Db::transaction(function () use ($sec, $ptype, $oldRules, $newRules) {
            foreach ($oldRules as $i => $oldRule) {
                $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
            }
        });
    }

    /**
     * UpdateFilteredPolicies deletes old rules and adds new rules.
     *
     * @param string $sec
     * @param string $ptype
     * @param array $newPolicies
     * @param integer $fieldIndex
     * @param string ...$fieldValues
     * @return array
     */
    public function updateFilteredPolicies(string $sec, string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues): array
    {
        $where['ptype'] = $ptype;
        foreach ($fieldValues as $fieldValue) {
            $suffix = $fieldIndex++;
            if (!is_null($fieldValue) && $fieldValue !== '') {
                $where['v'. $suffix] = $fieldValue;
            }
        }

        $newP = [];
        $oldP = [];
        foreach ($newPolicies as $newRule) {
            $col['ptype'] = $ptype;
            foreach ($newRule as $key => $value) {
                $col['v' . strval($key)] = $value;
            }
            $newP[] = $col;
        }

        DB::transaction(function () use ($newP, $where, &$oldP) {
            $oldRules = $this->model->where($where);
            $oldP = $oldRules->select()->hidden(['id'])->toArray();

            foreach ($oldP as &$item) {
                $item = array_filter($item, function ($value) {
                    return !is_null($value) && $value !== '';
                });
                unset($item['ptype']);
            }
            
            $oldRules->delete();
            $this->model->insertAll($newP);
        });

        // return deleted rules
        return $oldP;
    }

    /**
     * Returns true if the loaded policy has been filtered.
     *
     * @return bool
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    /**
     * Sets filtered parameter.
     *
     * @param bool $filtered
     */
    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }

    /**
     * Loads only policy rules that match the filter.
     *
     * @param Model $model
     * @param mixed $filter
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        $instance = $this->model;

        if (is_string($filter)) {
            $instance = $instance->whereRaw($filter);
        } elseif ($filter instanceof Filter) {
            foreach ($filter->p as $k => $v) {
                $where[$v] = $filter->g[$k];
                $instance = $instance->where($v, $filter->g[$k]);
            }
        } elseif ($filter instanceof \Closure) {
            $instance = $instance->where($filter);
        } else {
            throw new InvalidFilterTypeException('invalid filter type');
        }
        $rows = $instance->select()->hidden(['id'])->toArray();
        foreach ($rows as $row) {
            $row = array_filter($row, function ($value) {
                return !is_null($value) && $value !== '';
            });
            $line = implode(', ', array_filter($row, function ($val) {
                return '' != $val && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
        $this->setFiltered(true);
    }
}
