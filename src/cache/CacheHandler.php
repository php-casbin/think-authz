<?php

namespace tauthz\cache;

use tauthz\model\Rule;
use tauthz\traits\Configurable;
use think\db\Query;

class CacheHandler implements CacheHandlerContract
{
    use Configurable;

    /**
     * Cache policies for the given model.
     *
     * @param Rule $model The model to cache policies for.
     * @return Query|Rule The cached query if caching is disabled, or origin Rule.
     */
    public function cachePolicies(Rule $model): Query|Rule
    {
        if ($this->config('cache.enabled', false)) {
            $key = $this->config('cache.key', 'tauthz');
            $expire = $this->config('cache.expire', 0);
            return $model->cache($key, $expire);
        } else {
            return $model;
        }
    }
}
