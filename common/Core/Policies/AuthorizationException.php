<?php

namespace Common\Core\Policies;

use Illuminate\Auth\Access\AuthorizationException as LaravelAuthException;

class AuthorizationException extends LaravelAuthException
{
    public $showUpgradeButton = false;
}
