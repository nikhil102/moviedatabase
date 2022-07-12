<?php

namespace Common\Tags;

use Common\Billing\BillingPlan;
use Common\Billing\Gateways\GatewayFactory;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Tag $tag
     * @param Request $request
     */
    public function __construct(Tag $tag, Request $request)
    {
        $this->tag = $tag;
        $this->request = $request;
    }

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $this->authorize('index', Tag::class);

        $paginator = (new Paginator($this->tag, $this->request->all()));
        $pagination = $paginator->paginate();

        return $this->success(['pagination' => $pagination]);
    }
}