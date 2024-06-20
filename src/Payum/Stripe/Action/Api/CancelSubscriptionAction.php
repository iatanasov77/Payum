<?php

namespace Payum\Stripe\Action\Api;

use Composer\InstalledVersions;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Stripe\Constants;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CancelSubscription;
use Stripe\Exception;
use Stripe\Stripe;
use Stripe\Subscription;

class CancelSubscriptionAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }
    use GatewayAwareTrait;

    /**
     * @deprecated BC will be removed in 2.x. Use $this->api
     *
     * @var Keys
     */
    protected $keys;

    public function __construct()
    {
        $this->apiClass = Keys::class;
    }

    public function setApi($api)
    {
        $this->_setApi();
        
        // BC. will be removed in 2.x
        $this->keys = $this->api;
    }

    public function execute($request): void
    {
        /** @var $request CancelSubscription */
        RequestNotSupportedException::assertSupports($this, $request);
        
        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        try {
            Stripe::setApiKey($this->keys->getSecretKey());
            
            if (class_exists(InstalledVersions::class)) {
                Stripe::setAppInfo(
                    Constants::PAYUM_STRIPE_APP_NAME,
                    InstalledVersions::getVersion('stripe/stripe-php'),
                    Constants::PAYUM_URL
                );
            }
            
            $subscription   = Subscription::retrieve($model['id']);
            if ( $subscription ) {
                $deletedSubscription = $subscription->cancel();
            }
            
            $model->replace($deletedSubscription->toArray());
        } catch (Exception\ApiErrorException $e) {
            $model->replace($e->getJsonBody());
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof CancelSubscription &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}