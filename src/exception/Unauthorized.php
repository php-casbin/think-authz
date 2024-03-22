<?php

namespace tauthz\exception;

use think\exception\HttpException;

class Unauthorized extends HttpException
{
    /**
     * Create a new exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct(403, '此操作未经授权');
    }
}
