<?php

namespace tauthz\cache;

use tauthz\model\Rule;
use think\db\Query;

interface CacheHandlerContract
{
    public function cachePolicies(Rule $model): Query;
}