<?php
/**
 * This module is used for real time processing of
 * Novalnet payment module of customers.
 * This free contribution made by request.
 * 
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * @author       Novalnet AG
 * @copyright(C) Novalnet
 * All rights reserved. https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 */

namespace Novalnet\Methods;

use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Payment\Method\Services\PaymentMethodBaseService;
use Plenty\Plugin\Application;
use Novalnet\Helper\PaymentHelper;
use Novalnet\Services\PaymentService;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

/**
 * Class NovalnetPaymentMethod
 *
 * @package Novalnet\Methods
 */
class NovalnetInvoicePaymentMethod extends PaymentMethodBaseService
{
    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;
    
    /**
	 * @var PaymentService
	 */
	private $paymentService;
	
		/**
     * @var Basket
     */
    private $basket;

    /**
     * NovalnetPaymentMethod constructor.
     *
     * @param ConfigRepository $configRepository
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     */
    public function __construct(ConfigRepository $config,
                                PaymentHelper $paymentHelper,
                                PaymentService $paymentService,
			       BasketRepositoryContract $basket)
    {
        $this->config = $config;
        $this->paymentHelper = $paymentHelper;
        $this->paymentService  = $paymentService;
	    $this->basket = $basket->load();
    }

    /**
     * Check the configuration if the payment method is active
     * Return true only if the payment method is active
     *
     * @return bool
     */
    public function isActive():bool
    {
       if ($this->config->get('Novalnet.novalnet_invoice_payment_active') == 'true') {
		
		$active_payment_allowed_country = 'true';
		if ($allowed_country = $this->config->get('Novalnet.novalnet_invoice_allowed_country')) {
		$active_payment_allowed_country  = $this->paymentService->allowedCountries($this->basket, $allowed_country);
		}
	    
	    $active_payment_minimum_amount = 'true';
	    $minimum_amount = trim($this->config->get('Novalnet.novalnet_invoice_minimum_order_amount'));
	    if (!empty($minimum_amount) && is_numeric($minimum_amount)) {
		$active_payment_minimum_amount = $this->paymentService->getMinBasketAmount($this->basket, $minimum_amount);
		}
		
		$active_payment_maximum_amount = 'true';
	    $maximum_amount = trim($this->config->get('Novalnet.novalnet_invoice_maximum_order_amount'));
	    if (!empty($maximum_amount) && is_numeric($maximum_amount)) {
		$active_payment_maximum_amount = $this->paymentService->getMaxBasketAmount($this->basket, $maximum_amount);
		}
	    
	    
        return (bool)($this->paymentHelper->paymentActive() && $active_payment_allowed_country && $active_payment_minimum_amount && $active_payment_maximum_amount);
        } 
        return false;
    
    }

    /**
     * Get the name of the payment method. The name can be entered in the configuration.
     *
     * @return string
     */
    public function getName(string $lang = 'de'):string
    {   
		$name = trim($this->config->get('Novalnet.novalnet_invoice_payment_name'));
        return ($name ? $name : $this->paymentHelper->getTranslatedText('novalnet_invoice'));
    }

    /**
     * Retrieves the icon of the payment. The URL can be entered in the configuration.
     *
     * @return string
     */
    public function getIcon(string $lang = 'de'):string
    {
        $logoUrl = $this->config->get('Novalnet.novalnet_invoice_payment_logo');
        if($logoUrl == 'images/nn_invoice.png'){
            /** @var Application $app */
            $app = pluginApp(Application::class);
            $logoUrl = $app->getUrlPath('novalnet') .'/images/nn_invoice.png';
        } 
        return $logoUrl;
    }
    /**
     * Retrieves the description of the payment. The description can be entered in the configuration.
     *
     * @return string
     */
    public function getDescription(string $lang = 'de'):string
    {
		$description = trim($this->config->get('Novalnet.novalnet_invoice_description'));
        	return ($description ? $description : $this->paymentHelper->getTranslatedText('invoice_prepayment_payment_description'));
    }

    /**
     * Check if it is allowed to switch to this payment method
     *
     * @return bool
     */
    public function isSwitchableTo(): bool
    {
        return false;
    }

    /**
     * Check if it is allowed to switch from this payment method
     *
     * @return bool
     */
    public function isSwitchableFrom(): bool
    {
        return false;
    }
    
    public function getBackendIcon():string {
		/** @var Application $app */
		$app = pluginApp(Application::class);
		$logoUrl = $app->getUrlPath('novalnet') .'/images/logos/nn_invoice_backend_icon.svg';
		return $logoUrl;
	}
}
