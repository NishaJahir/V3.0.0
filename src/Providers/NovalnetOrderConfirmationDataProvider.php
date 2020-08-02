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

namespace Novalnet\Providers;

use Plenty\Plugin\Templates\Twig;

use Novalnet\Helper\PaymentHelper;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Comment\Contracts\CommentRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use \Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Novalnet\Services\PaymentService;
use Novalnet\Services\TransactionService;

/**
 * Class NovalnetOrderConfirmationDataProvider
 *
 * @package Novalnet\Providers
 */
class NovalnetOrderConfirmationDataProvider
{
    /**
     * Setup the Novalnet transaction comments for the requested order
     *
     * @param Twig $twig
     * @param PaymentRepositoryContract $paymentRepositoryContract
     * @param Arguments $arg
     * @return string
     */
    public function call(Twig $twig, PaymentRepositoryContract $paymentRepositoryContract, $arg)
    {
        $paymentHelper = pluginApp(PaymentHelper::class);
        $paymentService = pluginApp(PaymentService::class);
        $transactionLog  = pluginApp(TransactionService::class); 
        $sessionStorage = pluginApp(FrontendSessionStorageFactoryContract::class);
        $order = $arg[0];
        $barzhlentoken = '';
        $barzahlenurl = '';
        $payments = $paymentRepositoryContract->getPaymentsByOrderId($order['id']);
        if (!empty ($order['id'])) {
            foreach($payments as $payment)
            {
                $properties = $payment->properties;
                foreach($properties as $property)
                {
                    if ($property->typeId == 30)
                    {
                    $tid_status = $property->value;
                    }
                }
                if($paymentHelper->getPaymentKeyByMop($payment->mopId))
                {
                    $orderId = (int) $payment->order['orderId'];
                    $comment = '';
                    $db_details = $paymentService->getDatabaseValues($orderId);
                    $paymentHelper->logger('db1', $db_details);
                    $comments = '';
                    $comments .= PHP_EOL . $paymentHelper->getTranslatedText('nn_tid') . $db_details['tid'];
                    if(!empty($db_details['test_mode'])) {
                        $comments .= PHP_EOL . $paymentHelper->getTranslatedText('test_order');
                    }
                    
                    $get_transaction_details = $transactionLog->getTransactionData('orderNo', $orderId);
                    $totalCallbackAmount = 0;
                    foreach ($get_transaction_details as $transaction_details) {
                       $totalCallbackAmount += $transaction_details->callbackAmount;
                    }
                    
                    if(in_array($tid_status, ['91', '100']) && ($db_details['payment_id'] == '27' && ($transaction_details->amount > $totalCallbackAmount) ) ) {
                        //$bankDetails .= PHP_EOL . $paymentService->getInvoicePrepaymentComments($db_details);
                        $bankDetails = $db_details;
                    }
                }
            }
                    $comment .= (string) $comments;
                    $comment .= PHP_EOL;
        }   
                  return $twig->render('Novalnet::NovalnetOrderHistory', ['bankDetails' => $bankDetails, 'paymentDetails' => $db_details]);
    }
}

    

