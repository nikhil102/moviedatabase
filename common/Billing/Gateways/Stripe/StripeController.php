<?php namespace Common\Billing\Gateways\Stripe;

use App\User;
use Common\Billing\BillingPlan;
use Common\Billing\GatewayException;
use Common\Billing\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Common\Core\BaseController;
use Omnipay\Common\Exception\InvalidCreditCardException;

class StripeController extends BaseController
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var BillingPlan
     */
    private $billingPlan;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var StripeGateway
     */
    private $stripe;

    /**
     * @param Request $request
     * @param BillingPlan $billingPlan
     * @param Subscription $subscription
     * @param StripeGateway $stripe
     */
    public function __construct(
        Request $request,
        BillingPlan $billingPlan,
        Subscription $subscription,
        StripeGateway $stripe
    )
    {
        $this->stripe = $stripe;
        $this->request = $request;
        $this->billingPlan = $billingPlan;
        $this->subscription = $subscription;

        $this->middleware('auth');
    }

    /**
     * Create a new subscription on stripe.
     *
     * @return JsonResponse
     */
    public function createSubscription()
    {
        $this->validate($this->request, [
            'plan_id' => 'required|integer|exists:billing_plans,id',
            'start_date' => 'string'
        ]);

        /** @var User $user */
        $user = $this->request->user();
        $plan = $this->billingPlan->findOrFail($this->request->get('plan_id'));

        $sub = $this->stripe->subscriptions()->create($plan, $user, $this->request->get('start_date'));
        $user->subscribe('stripe', $sub['reference'], $plan);

        return $this->success(['user' => $user->load('permissions', 'subscriptions.plan')]);
    }

    /**
     * Add a new bank card to user using stripe token.
     *
     * @return JsonResponse
     * @throws GatewayException
     */
    public function addCard()
    {
        $this->validate($this->request, [
            'token' => 'required|string',
        ]);

        try {
            $user = $this->stripe->addCard($this->request->user(), $this->request->get('token'));
        } catch (InvalidCreditCardException $e) {
            return $this->error(['general' => $e->getMessage()]);
        }

        return $this->success(['user' => $user->load('permissions', 'subscriptions.plan')]);
    }
}
