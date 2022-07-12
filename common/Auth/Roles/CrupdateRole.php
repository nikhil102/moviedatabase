<?php

namespace Common\Auth\Roles;

use Common\Auth\Permissions\Traits\SyncsPermissions;
use Illuminate\Support\Arr;

class CrupdateRole
{
    use SyncsPermissions;

    /**
     * @var Role
     */
    private $role;

    /**
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    /**
     * @param array $data
     * @param Role $role
     * @return Role
     */
    public function execute($data, $role = null)
    {
        if ( ! $role) {
            $role = $this->role->newInstance([

            ]);
        }

        $attributes = [
            'name' => Arr::get($data, 'name'),
            'default' => Arr::get($data, 'default', false),
            'guests' => Arr::get($data, 'guests', false)
        ];

        $role->fill($attributes)->save();

        if ($permissions = Arr::get($data, 'permissions')) {
            $this->syncPermissions($role, $permissions);
        }

        return $role;
    }
}