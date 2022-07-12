<?php

namespace Common\Core\Policies;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function deny($message = 'This action is unauthorized.', $params = [])
    {
        $e = new AuthorizationException($message);
        $e->showUpgradeButton = Arr::get($params, 'showUpgradeButton');
        throw ($e);
    }

    /**
     * @param User $user
     * @param string $namespace
     * @param string $relation
     * @return bool
     */
    protected function storeWithCountRestriction(User $user, $namespace, $relation = null)
    {
        // "App\SomeModel" => "Some_Model"
        $resourceName = snake_case(class_basename($namespace));

        // "Some_Model" => "some_models"
        $pluralName = strtolower(str_plural($resourceName));

        // user can't create resource at all
        if ( ! $user->hasPermission("$pluralName.create")) {
            return false;
        }

        // user is admin, can ignore count restriction
        if ($user->hasPermission('admin')) {
            return true;
        }

        // user does not have any restriction on maximum link count
        $maxCount = $user->getRestrictionValue("$pluralName.create", 'count');

        if ( ! $maxCount) {
            return true;
        }

        // check if user did not go over their max quota
        $relation = $relation ?: $pluralName;
        if ($user->$relation->count() >= $maxCount) {
            $displayPlural = ucwords(str_replace('_', ' ', $pluralName));
            $displaySingular = str_singular(ucwords(str_replace('_', ' ', $pluralName)));
            $message = __('policies.quota_exceeded', ['resources' => $displayPlural, 'resource' => $displaySingular]);
            $this->deny($message, ['showUpgradeButton' => true]);
        }

        return true;
    }
}
