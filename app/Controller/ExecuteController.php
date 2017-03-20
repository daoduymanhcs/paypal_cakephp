<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ExecutePayment;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class ExecuteController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Order');

	public function index() {
		$apiContext = $this->apiContext();
		// ### Approval Status
		// Determine if the user approved the payment or not
		if (isset($_GET['success']) && $_GET['success'] == 'true') {

		    // Get the payment Object by passing paymentId
		    // payment id was previously stored in session in
		    // CreatePaymentUsingPayPal.php
		    $paymentId = $_GET['paymentId'];
		    $payment = Payment::get($paymentId, $apiContext);
		    // echo $payment->transactions[0]->invoice_number; die;
		    // ### Payment Execute
		    // PaymentExecution object includes information necessary
		    // to execute a PayPal account payment.
		    // The payer_id is added to the request query parameters
		    // when the user is redirected from paypal back to your site
		    $execution = new PaymentExecution();
		    $execution->setPayerId($_GET['PayerID']);

		    // ### Optional Changes to Amount
		    // If you wish to update the amount that you wish to charge the customer,
		    // based on the shipping address or any other reason, you could
		    // do that by passing the transaction object with just `amount` field in it.
		    // Here is the example on how we changed the shipping to $1 more than before.
		    $transaction = new Transaction();
		    $amount = new Amount();
		    $details = new Details();
		    $invoice_number = $payment->transactions[0]->invoice_number;
		    $dataOrder = $this->Order->find('all', array(
		    		'conditions' => array(
		    			'invoice_number' => $invoice_number,
		    			'state' => 'created',
		    			'delet_flg' => 0
		    			)
		    		)

		    	);
		    $setSubtotal = 0;
		    if(isset($dataOrder) && !empty($dataOrder)) {
		    	foreach ($dataOrder as $key => $order) {
		    		$setSubtotal += ($order['Order']['item_price'] * $order['Order']['item_quantity']);
		    	}
		    }	
		    $setTotal = $setSubtotal + 1.2 + 1.3;
		    $details->setShipping(1.2)
		        ->setTax(1.3)
		        ->setSubtotal($setSubtotal);

		    $amount->setCurrency('USD');
		    $amount->setTotal($setTotal);
		    $amount->setDetails($details);
		    $transaction->setAmount($amount);

		    // Add the above transaction object inside our Execution object.
		    $execution->addTransaction($transaction);

		    try {
		        // Execute the payment
		        // (See bootstrap.php for more on `ApiContext`)
		        $result = $payment->execute($execution, $apiContext);

		        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
		        // ResultPrinter::printResult("Executed Payment", "Payment", $payment->getId(), $execution, $result);

		        try {
		            $payment = Payment::get($paymentId, $apiContext);
		        } catch (Exception $ex) {
		            // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
		            // ResultPrinter::printError("Get Payment", "Payment", null, null, $ex);
		            exit(1);
		        }
		    } catch (Exception $ex) {
		        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
		        // ResultPrinter::printError("Executed Payment", "Payment", null, null, $ex);
		        exit(1);
		    }

		    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
		    // ResultPrinter::printResult("Get Payment", "Payment", $payment->getId(), null, $payment);
		    if($payment->state == 'approved') {
		    	foreach ($dataOrder as $key => $data) {
		    		$this->Order->id = $order['Order']['id'];
		    		$this->Order->save(array(
		    			'state' => 'approved',
		    			)
		    		);
		    	}
		    }
		    return $this->redirect('/success');
		    // return $payment;
		} else {
		    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
		    // ResultPrinter::printResult("User Cancelled the Approval", null);
		    exit;
		}
		
	}
}
