<?php namespace Common\Auth\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Storage;
use App\User;
use Illuminate\Http\Request;
use Common\Core\BaseController;

class UserAvatarController extends BaseController {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * @var FilesystemAdapter
     */
    private $storage;

    /**
     * @param Request $request
     * @param User $user
     */
    public function __construct(Request $request, User $user)
    {
        $this->request = $request;
        $this->storage = Storage::disk('public');
        $this->user = $user;
    }

    /**
     * @param int $userId
     * @return JsonResponse
     */
    public function store($userId) {

        $user = $this->user->findOrFail($userId);

        $this->authorize('update', $user);

        $this->validate($this->request, [
            'file' => 'required|image|max:1500',
        ]);

        // delete old user avatar
        $this->storage->delete($user->getOriginal('avatar'));

        // store new avatar on public disk
        $path = $this->request->file('file')->storePublicly('avatars', ['disk' => 'public']);

        // attach avatar to user model
        $user->fill(['avatar' => $path])->save();

        return $this->success([
            'user' => $user,
            'fileEntry' => ['url' => "storage/$path"]
        ]);
    }

    /**
     * @param int $userId
     * @return User
     */
    public function destroy($userId)
    {
        $user = $this->user->findOrFail($userId);

        $this->authorize('update', $user);

        $this->storage->delete($user->getOriginal('avatar'));

        $user->fill(['avatar' => null])->save();

        return $user;
    }
}
