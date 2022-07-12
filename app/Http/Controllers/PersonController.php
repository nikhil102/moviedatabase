<?php

namespace App\Http\Controllers;

use App\Jobs\IncrementModelViews;
use App\Person;
use App\Services\People\Retrieve\GetPersonCredits;
use App\Services\People\Store\StorePersonData;
use App\Services\Titles\Retrieve\FindOrCreateMediaItem;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Common\Settings\Settings;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PersonController extends BaseController
{
    /**
     * @var Person
     */
    private $person;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Person $person
     * @param Request $request
     */
    public function __construct(Person $person, Request $request)
    {
        $this->person = $person;
        $this->request = $request;
    }

    public function index()
    {
        $this->authorize('index', Person::class);

        $paginator = new Paginator($this->person, $this->request->all());

        if ( ! app(Settings::class)->get('tmdb.includeAdult')) {
            $paginator->where('adult', false);
        }

        $paginator->setDefaultOrderColumns('popularity', 'desc');
        $paginator->with('popularCredits');
        $paginator->searchCallback = function(Builder $builder, $query) {
            $builder->whereRaw("MATCH(name) AGAINST('$query')");
        };

        if ($this->request->get('mostPopular') && $min = app(Settings::class)->get('content.people_index_min_popularity')) {
            $paginator->where('popularity', '>', $min);
        }

        $pagination = $paginator->paginate();

        $pagination->map(function(Person $person) {
            $person->description = str_limit($person->description, 500);
            $person->setRelation('popular_credits', $person->popularCredits->slice(0, 1));
            return $person;
        });

        return $this->success(['pagination' => $pagination]);
    }

    public function show($id, $name = null)
    {
        $this->authorize('show', Person::class);

        $person = app(FindOrCreateMediaItem::class)->execute($id, Person::PERSON_TYPE);

        if ($person->needsUpdating()) {
            $data = Person::dataProvider()->getPerson($person);
            $person = app(StorePersonData::class)->execute($person, $data);
        }

        $response = array_merge(
            ['person' => $person],
            app(GetPersonCredits::class)->execute($person)
        );

        $this->dispatch(new IncrementModelViews(Person::PERSON_TYPE, $person->id));

        return $this->success($response);
    }

    public function store()
    {
        $this->authorize('store', Person::class);

        $data = $this->request->all();
        $data['popularity'] = Arr::get($data, 'popularity') ?: 50;
        $person = $this->person->create($data);

        return $this->success(['person' => $person]);
    }

    public function update($id)
    {
        $this->authorize('update', Person::class);

        $person = $this->person->findOrFail($id);

        $data = $this->request->all();
        $data['popularity'] = Arr::get($data, 'popularity') ?: 50;
        $person->fill($data)->save();

        return $this->success(['person' => $person]);
    }

    public function destroy()
    {
        $this->authorize('destroy', Person::class);

        $ids = $this->request->get('ids');

        $this->person->whereIn('id', $ids)->delete();
        DB::table('creditables')->whereIn('person_id', $ids)->delete();

        return $this->success();
    }
}
