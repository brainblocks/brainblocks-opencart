<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ControllerExtensionPaymentBrainblocks extends Controller
{
	public function index()
    {
        if (!isset($this->session->data['order_id']) || !$this->session->data['order_id']) {
            return false;
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if (!$order_info) {
            return false;
        }

        $amount = $this->currency->format(
            $order_info['total'],
            $order_info['currency_code'],
            $order_info['currency_value'],
            false
        );

		return $this->load->view('extension/payment/brainblocks', array(
		    'amount'   => $amount,
            'currency' => strtolower($order_info['currency_code']),
            'address'  => $this->config->get('payment_brainblocks_address')
        ));
	}

	public function confirm()
    {
        if (!isset($this->request->post['token']) || !isset($this->session->data['order_id'])) {
            return false;
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/brainblocks');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if (!$order_info) {
            return false;
        }

        $success = false;

        $client = new Client();

        $url = sprintf('https://brainblocks.io/api/session/%s/verify', $this->request->post['token']);

        try {
            // Allow 10 seconds for a response
            $response = $client->get($url, [
                'timeout' => 10,
                'connect_timeout' => 10
            ]);

            if ($response->getBody()) {
                $jsonResponse = json_decode($response->getBody());

                $validTransaction = $this->validateTransaction(
                    $jsonResponse,
                    $order_info
                );

                if ($validTransaction) {
                    // Transaction was a success, change order status to success
                    $this->model_checkout_order->addOrderHistory(
                        $order_info['order_id'],
                        $this->config->get('payment_brainblocks_success_order_status_id'),
                        'Nano payment was accepted.'
                    );

                    // Add token to DB to prevent re-use
                    $this->model_extension_payment_brainblocks->addToken($order_info['order_id'], $jsonResponse->token);

                    $success = true;
                }
            }
        } catch (RequestException $e) {
            // Brainblocks is unavailable, tell the customer the order has gone through okay but set status to pending
            $success = true;

            // Unable to verify transaction, change order status to pending
            $this->model_checkout_order->addOrderHistory(
                $order_info['order_id'],
                $this->config->get('payment_brainblocks_pending_order_status_id'),
                sprintf(
                    'Unable to verify if transaction was successful. Brainblocks token was %s',
                    $this->request->post['token']
                )
            );

            $this->log($e->getMessage());
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }

        if (!$success) {
            $redirect = $this->url->link('checkout/failure');
        } else {
            $redirect = $this->url->link('checkout/success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(array(
            'success'  => $success,
            'redirect' => $redirect
        )));
    }

    protected function validateTransaction($response, $order_info)
    {
        // Check that the response status isn't an error
        if (isset($response->status) && $response->status === 'error') {
            throw new \Exception('Response status contained an error!');
        }

        // Check if destination is correct
        if ($response->destination !== $this->config->get('payment_brainblocks_address')) {
            throw new \Exception(sprintf(
                'Destination invalid! Expected %s, got %s',
                $this->config->get('payment_brainblocks_address'),
                $response->destination
            ));
        }

        $order_total = $this->currency->format(
            $order_info['total'],
            $order_info['currency_code'],
            $order_info['currency_value'],
            false
        );

        // Check that the transaction amount equals the order total
        if ((float)$response->amount !== (float)$order_total) {
            throw new \Exception(sprintf(
                'Transaction amount does not match order amount. Expected %s, got %s.',
                $order_total,
                $response->amount
            ));
        }

        // Check that we received the full amoutn of nano
        if ($response->received_rai < $response->amount_rai) {
            throw new \Exception(sprintf(
                'Amount received does not reach the full amount required. Expected at least %s, got %s.',
                $response->received_rai,
                $response->amount_rai
            ));
        }

        if (!isset($response->token)) {
            throw new \Exception('Response didnt provide a token.');
        }

        // Check for token re-use
        $this->load->model('extension/payment/brainblocks');

        $tokenHasBeenUsedBefore = $this->model_extension_payment_brainblocks->checkTokenReuse($response->token);

        if ($tokenHasBeenUsedBefore) {
            throw new \Exception(sprintf(
                'Token "%s" has been used before.',
                $response->token
            ));
        }

        // If we reach here, transaction passes validation
        return true;
    }

    protected function log($message)
    {
        $this->log->write('Brainblocks - ' . $message);
    }
}
