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
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\FundingInstrument;
use PayPal\Api\PaymentCard;

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class TestsController extends AppController {

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array('Order');

/**
 * Displays a view
 *
 * @return \Cake\Network\Response|null
 * @throws ForbiddenException When a directory traversal attempt.
 * @throws NotFoundException When the view file could not be found
 *   or MissingViewException in debug mode.
 */
	public function index() {
		$this->autoRender = false;
		$this->layout = false;
		$apiContext = new \PayPal\Rest\ApiContext(
		    new \PayPal\Auth\OAuthTokenCredential(
		        'Ac7PY0OeAj10GxvsE1LYprqMwNeinKulWpUmHS0NPwItaghBF6bANyxuCJWflEsmnB0ubLP3CK1KaAF-',     // ClientID
		        'EIPjdmhU5c700OlQ8C-JFEtpCs2VrHPkWo5Euim9xy7P7reftZwdK6DTBxwpP_tE7uQNsZBYd203uJQE'      // ClientSecret
		    )
		);
		// 3. Lets try to save a credit card to Vault using Vault API mentioned here
		// https://developer.paypal.com/webapps/developer/docs/api/#store-a-credit-card
		$creditCard = new \PayPal\Api\CreditCard();
		$creditCard->setType("visa")
		->setNumber("4417119669820331")
		->setExpireMonth("11")
		->setExpireYear("2019")
		->setCvv2("012")
		->setFirstName("Joe")
		->setLastName("Shopper");
		// 4. Make a Create Call and Print the Card
		try {
		$creditCard->create($apiContext);
		echo $creditCard;
		}
		catch (\PayPal\Exception\PayPalConnectionException $ex) {
		// This will print the detailed information on the exception. 
		//REALLY HELPFUL FOR DEBUGGING
		echo $ex->getData();
		}
	}

	public function fail() {

	}
	public function success() {

	}
	public function product() {

	}
	public function process() {
		if($this->request->is('post')) {
			$dataRequest = $this->request->data;
			if($dataRequest['payment_method'] == 'paypal') {
				$this->CreatePayment($dataRequest);
			}
			if($dataRequest['payment_method'] == 'credit') {
				$this->creditCard($dataRequest);
			}
		}
	}
	public function creditCard($data) {
		$this->autoRender = false;
		$this->layout = false;
		$apiContext = $this->apiContext();

		$requestData = array();
		// $data = $this->request->data;
		$requestData['description'] = isset($data['description']) ? $data['description'] : '';
		$requestData['price'] = isset($data['price']) ? floatval($data['price']) : 0;
		$requestData['quantity'] = isset($data['quantity']) ? floatval($data['quantity']) : 0;
			$setSubtotal = $requestData['price'] * $requestData['quantity'];
			$setTotal = $setSubtotal + 0.3 + 1.2;
		$requestData['setNumber'] = isset($data['card_number']) ? $data['card_number'] : 0;
		$requestData['ExpireMonth'] = isset($data['ExpireMonth']) ? $data['ExpireMonth'] : 0;
		$requestData['ExpireYear'] = isset($data['ExpireYear']) ? $data['ExpireYear'] : 0;
		$requestData['Cvv2'] = isset($data['Cvv2']) ? $data['Cvv2'] : 0;
		$requestData['FirstName'] = isset($data['FirstName']) ? $data['FirstName'] : '';
		$requestData['BillingCountry'] = isset($data['BillingCountry']) ? $data['BillingCountry'] : '';
		$requestData['LastName'] = isset($data['LastName']) ? $data['LastName'] : '';
		// debug($requestData); die;
		$card = new PaymentCard();
		$card->setType("visa")
		    ->setNumber("4669424246660779")
		    ->setExpireMonth("11")
		    ->setExpireYear("2019")
		    ->setCvv2("012")
		    ->setFirstName("Joe")
		    ->setBillingCountry("US")
		    ->setLastName("Shopper");

		// ### FundingInstrument
		// A resource representing a Payer's funding instrument.
		// For direct credit card payments, set the CreditCard
		// field on this object.
		$fi = new FundingInstrument();
		$fi->setPaymentCard($card);

		// ### Payer
		// A resource representing a Payer that funds a payment
		// For direct credit card payments, set payment method
		// to 'credit_card' and add an array of funding instruments.
		$payer = new Payer();
		$payer->setPaymentMethod("credit_card")
		    ->setFundingInstruments(array($fi));

		// ### Itemized information
		// (Optional) Lets you specify item wise
		// information
		$item1 = new Item();
		$item1->setName($requestData['description'])
		    ->setDescription($requestData['description'])
		    ->setCurrency('USD')
		    ->setQuantity($requestData['quantity'])
		    ->setTax(0.3)
		    ->setPrice($requestData['price']);
		$item2 = new Item();
		$itemList = new ItemList();
		$itemList->setItems(array($item1));

		// ### Additional payment details
		// Use this optional field to set additional
		// payment information such as tax, shipping
		// charges etc.
		$details = new Details();
		$details->setShipping(1.2)
		    ->setTax(0.3)
		    ->setSubtotal($setSubtotal);

		// ### Amount
		// Lets you specify a payment amount.
		// You can also specify additional details
		// such as shipping, tax.
		$amount = new Amount();
		$amount->setCurrency("USD")
		    ->setTotal($setTotal)
		    ->setDetails($details);

		// ### Transaction
		// A transaction defines the contract of a
		// payment - what is the payment for and who
		// is fulfilling it.
		$transaction = new Transaction();
		$transaction->setAmount($amount)
		    ->setItemList($itemList)
		    ->setDescription("Payment description")
		    ->setInvoiceNumber(uniqid());

		// ### Payment
		// A Payment Resource; create one using
		// the above types and intent set to sale 'sale'
		$payment = new Payment();
		$payment->setIntent("sale")
		    ->setPayer($payer)
		    ->setTransactions(array($transaction));

		// For Sample Purposes Only.
		$request = clone $payment;
		// echo $payment->transactions[0]->invoice_number; die;
		try {
		    $payment->create($apiContext);
		} catch (Exception $ex) {
			echo '2222';
		    exit(1);
		}
		$this->Order->create();
		$this->Order->save(array(
			'item_name' => $requestData['description'],
			'item_quantity' => $requestData['quantity'],
			'item_price' => $requestData['price'],
			'payment_method' => 'credit_card',
			'invoice_number' => $payment->transactions[0]->invoice_number,
			'state' => $payment->state,
			'wdate' => date("Y-m-d H:i:s")
			)
		);
		return $this->redirect('/success');
		// return $payment;

	}

	public function CreatePayment($data) {
		$this->autoRender = false;
		$this->layout = false;
		$apiContext = $this->apiContext();
		// if($this->request->is('post')) {
			$requestData = array();
			// $data = $this->request->data;
			$requestData['description'] = isset($data['description']) ? $data['description'] : '';
			$requestData['price'] = isset($data['price']) ? $data['price'] : 0;
			$requestData['quantity'] = isset($data['quantity']) ? $data['quantity'] : 0;
			$requestData['method'] = 'paypal';	
			$setSubtotal = $requestData['price'] * $requestData['quantity'];
			$setTotal = $setSubtotal + 1.2 + 1.3;
			// debug($this->request->data); die;

			$payer = new Payer();
			$payer->setPaymentMethod($requestData['method']);

			$item1 = new Item();
			$item1->setName($requestData['description'])
			    ->setCurrency('USD')
			    ->setQuantity($requestData['quantity'])
			    // ->setSku("123123") // Similar to `item_number` in Classic API
			    ->setPrice($requestData['price']);
			$itemList = new ItemList();
			$itemList->setItems(array($item1));

			$details = new Details();
			$details->setShipping(1.2)
			    ->setTax(1.3)
			    ->setSubtotal($setSubtotal);

			$amount = new Amount();
			$amount->setCurrency("USD")
			    ->setTotal($setTotal)
			    ->setDetails($details);

			$transaction = new Transaction();
			$transaction->setAmount($amount)
			    ->setItemList($itemList)
			    ->setDescription("Payment description")
			    ->setInvoiceNumber(uniqid());

			$baseUrl = 'http://localhost/cakephp/';
			$redirectUrls = new RedirectUrls();
			$redirectUrls->setReturnUrl("$baseUrl/Execute?success=true")
			    ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

			$payment = new Payment();
			$payment->setIntent("sale")
			    ->setPayer($payer)
			    ->setRedirectUrls($redirectUrls)
			    ->setTransactions(array($transaction));


			// For Sample Purposes Only.
			$request = clone $payment;


			try {
			    $payment->create($apiContext);
			} catch (Exception $ex) {

			    exit(1);
			}


			$approvalUrl = $payment->getApprovalLink();

			$this->Order->create();
			$this->Order->save(array(
				'item_name' => $requestData['description'],
				'item_quantity' => $requestData['quantity'],
				'item_price' => $requestData['price'],
				'payment_method' => $requestData['method'],
				'invoice_number' => $payment->transactions[0]->invoice_number,
				'state' => $payment->state,
				'wdate' => date("Y-m-d H:i:s")
				)
			);
			return $this->redirect($approvalUrl);
			// return $payment;
		// }

	}
}
