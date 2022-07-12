<?php namespace Common\Auth\Controllers;

use Common\Core\Bootstrap\BootstrapData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Common\Settings\Settings;
use Common\Core\BaseController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Response;

class LoginController extends BaseController
{
    use AuthenticatesUsers;

    /**
     * @var BootstrapData
     */
    private $bootstrapData;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param BootstrapData $bootstrapData
     * @param Settings $settings
     */
    public function __construct(BootstrapData $bootstrapData, Settings $settings)
    {
        $this->middleware('guest', ['except' => 'logout']);

        $this->bootstrapData = $bootstrapData;
        $this->settings = $settings;
    }

    /**
     * Validate the user login request.
     *
     * @param Request $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string|email_confirmed',
            'password' => 'required|string',
        ]);
    }

    /**
     * @return mixed
     */
    protected function authenticated()
    {
        $data = $this->bootstrapData->init()->getEncoded();
        return $this->success(['data' => $data]);
    }

    /**
     * Get the failed login response instance.
     *
     * @return JsonResponse
     */
    protected function sendFailedLoginResponse()
    {
        return $this->error(['general' => __('auth.failed')]);
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return $this->success();
    }
}