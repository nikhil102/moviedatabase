<?php namespace Common\Core\Controllers;

use Common\Core\Bootstrap\BootstrapData;
use Common\Core\BaseController;
use Illuminate\Http\JsonResponse;

class BootstrapController extends BaseController
{
    /**
     * Get data needed to bootstrap the application.
     *
     * @param BootstrapData $bootstrapData
     * @return JsonResponse
     */
    public function getBootstrapData(BootstrapData $bootstrapData)
    {
        return response(['data' => $bootstrapData->init()->getEncoded()]);
    }
}
