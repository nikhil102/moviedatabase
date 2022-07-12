<?php

namespace App\Http\Controllers;

use App\Listable;
use App\ListModel;
use App\Services\Lists\DeleteLists;
use App\Services\Lists\UpdateListsContent;
use Auth;
use Common\Core\BaseController;
use Common\Database\Paginator;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ListController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ListModel
     */
    private $list;

    /**
     * @param Request $request
     * @param ListModel $list
     */
    public function __construct(Request $request, ListModel $list)
    {
        $this->request = $request;
        $this->list = $list;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', [ListModel::class, Auth::id()]);

        $paginator = (new Paginator($this->list, $this->request->all()));

        if ($userId = $this->request->get('userId')) {
            $paginator->where('user_id', $userId);
        }

        if ($listIds = $this->request->get('listIds')) {
            $paginator->query()->whereIn('id', explode(',', $listIds));
        }

        if ($excludeSystem = $this->request->get('excludeSystem')) {
            $paginator->where('system', false);
        }

        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }

    /**
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        /** @var ListModel $list */
        $list = $this->list->findOrFail($id);

        $this->authorize('show', $list);

        $items = $list->getItems();
        $items = $items->sortBy(
            $this->request->get('sortBy', 'pivot.order'),
            SORT_REGULAR,
            $this->request->get('sortDir') === 'desc'
        )->values();

        $paginator = new LengthAwarePaginator($items, $items->count(), $items->count() ?: 1);

        return $this->success([
            'list' => $list,
            'items' => $paginator,
        ]);
    }

    /**
     * @return Response
     */
    public function store()
    {
        $this->authorize('store', ListModel::class);

        $this->validate($this->request, [
            'details.name' => 'required|string|max:100',
            'details.description' => 'nullable|string|max:500',
            'details.public' => 'boolean',
            'details.auto_update' => 'nullable|string',
            'items' => 'array'
        ]);

        $details = $this->request->get('details');
        $autoUpdate = Arr::get($details, 'auto_update');

        $list = $this->list->create([
            'name' => $details['name'],
            'description' => $details['description'],
            'auto_update' => $autoUpdate,
            'public' => $details['public'],
            'user_id' => Auth::id()
        ]);

       if ($items = $this->request->get('items')) {
           $list->attachItems($items);
       }

       if ($autoUpdate) {
           app(UpdateListsContent::class)
               ->execute([$list]);
       }

        return $this->success(['list' => $list]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function update($id)
    {
        $list = $this->list->findOrFail($id);

        $this->authorize('store', $list);

        $this->validate($this->request, [
            'details.name' => 'required|string|max:100',
            'details.description' => 'nullable|string|max:500',
        ]);

        $originalAutoUpdate = $list->auto_update;
        $list->fill($this->request->get('details'))->save();

        if ($originalAutoUpdate !== $list->auto_update) {
            app(UpdateListsContent::class)
                ->execute([$list]);
        }

        return $this->success(['list' => $list]);
    }

    /**
     * @return Response
     */
    public function destroy()
    {
        $listIds = $this->request->get('listIds');

        // make sure system lists can't be deleted
        $lists = $this->list->whereIn('id', $listIds)
            ->where('system', false)
            ->get();

        $this->authorize('destroy', [ListModel::class, $lists]);

        app(DeleteLists::class)->execute($lists->pluck('id'));

        return $this->success();
    }
}
