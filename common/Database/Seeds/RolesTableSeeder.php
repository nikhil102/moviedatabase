<?php namespace Common\Database\Seeds;

use Common\Auth\Permissions\Permission;
use Common\Auth\Permissions\Traits\SyncsPermissions;
use DB;
use Carbon\Carbon;
use App\User;
use Common\Auth\Roles\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class RolesTableSeeder extends Seeder
{
    use SyncsPermissions;

    /**
     * @var Role
     */
    private $role;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @param Role $role
     * @param User $user
     * @param Permission $permission
     * @param Filesystem $fs
     */
    public function __construct(Role $role, User $user, Permission $permission, Filesystem $fs)
    {
        $this->user = $user;
        $this->role = $role;
        $this->permission = $permission;
        $this->fs = $fs;
    }

    /**
     * @return void
     */
    public function run()
    {
        $this->createOrUpdateRole('guests', 'guests');
        $usersRole = $this->createOrUpdateRole('default', 'users');

        $this->attachUsersRoleToExistingUsers($usersRole);
    }

    private function createOrUpdateRole($type, $name)
    {
        $defaultPermissions = array_merge_recursive(
            $this->fs->getRequire(app('path.common') . '/resources/defaults/permissions.php'),
            $this->fs->getRequire(resource_path('defaults/permissions.php'))
        )['roles'][$name];

        $defaultPermissions = array_map(function($permission) {
            return is_string($permission) ? ['name' => $permission] : $permission;
        }, $defaultPermissions);

        $dbPermissions = $this->permission->whereIn('name', collect($defaultPermissions)->pluck('name'))->get();
        $dbPermissions->map(function(Permission $permission) use($defaultPermissions) {
            $defaultPermission = collect($defaultPermissions)->where('name', $permission['name'])->first();
            $permission['restrictions'] = Arr::get($defaultPermission, 'restrictions') ?: [];
            return $permission;
        });

        $role = $this->role->firstOrCreate([$type => 1], [$type => 1, 'name' => $name]);
        $this->syncPermissions($role, $role->permissions->concat($dbPermissions));
        $role->save();

        return $role;
    }

    /**
     * Attach default user's role to all existing users.
     *
     * @param Role $role
     */
    private function attachUsersRoleToExistingUsers(Role $role)
    {
        $this->user->with('roles')->orderBy('id', 'desc')->select('id')->chunk(500, function(Collection $users) use($role) {
            $insert = $users->filter(function(User $user) use ($role) {
                return ! $user->roles->contains('id', $role->id);
            })->map(function(User $user) use($role) {
                return ['user_id' => $user->id, 'role_id' => $role->id, 'created_at' => Carbon::now()];
            })->toArray();

            DB::table('user_role')->insert($insert);
        });
    }
}
