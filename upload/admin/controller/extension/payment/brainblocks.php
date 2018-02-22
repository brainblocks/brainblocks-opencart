<?php
class ControllerExtensionPaymentBrainblocks extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/brainblocks');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_brainblocks', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/brainblocks', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/brainblocks', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_brainblocks_total'])) {
			$data['payment_brainblocks_total'] = $this->request->post['payment_brainblocks_total'];
		} else {
			$data['payment_brainblocks_total'] = $this->config->get('payment_brainblocks_total');
		}

        if (isset($this->request->post['payment_brainblocks_address'])) {
            $data['payment_brainblocks_address'] = $this->request->post['payment_brainblocks_address'];
        } else {
            $data['payment_brainblocks_address'] = $this->config->get('payment_brainblocks_address');
        }

		if (isset($this->request->post['payment_brainblocks_failed_order_status_id'])) {
			$data['payment_brainblocks_failed_order_status_id'] = $this->request->post['payment_brainblocks_failed_order_status_id'];
		} else {
			$data['payment_brainblocks_failed_order_status_id'] = $this->config->get('payment_brainblocks_failed_order_status_id');
		}

		if (isset($this->request->post['payment_brainblocks_pending_order_status_id'])) {
			$data['payment_brainblocks_pending_order_status_id'] = $this->request->post['payment_brainblocks_pending_order_status_id'];
		} else {
			$data['payment_brainblocks_pending_order_status_id'] = $this->config->get('payment_brainblocks_pending_order_status_id');
		}

		if (isset($this->request->post['payment_brainblocks_success_order_status_id'])) {
			$data['payment_brainblocks_success_order_status_id'] = $this->request->post['payment_brainblocks_success_order_status_id'];
		} else {
			$data['payment_brainblocks_success_order_status_id'] = $this->config->get('payment_brainblocks_success_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_brainblocks_geo_zone_id'])) {
			$data['payment_brainblocks_geo_zone_id'] = $this->request->post['payment_brainblocks_geo_zone_id'];
		} else {
			$data['payment_brainblocks_geo_zone_id'] = $this->config->get('payment_brainblocks_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_brainblocks_status'])) {
			$data['payment_brainblocks_status'] = $this->request->post['payment_brainblocks_status'];
		} else {
			$data['payment_brainblocks_status'] = $this->config->get('payment_brainblocks_status');
		}

		if (isset($this->request->post['payment_brainblocks_sort_order'])) {
			$data['payment_brainblocks_sort_order'] = $this->request->post['payment_brainblocks_sort_order'];
		} else {
			$data['payment_brainblocks_sort_order'] = $this->config->get('payment_brainblocks_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/brainblocks', $data));
	}

    public function install() {
        if ($this->user->hasPermission('modify', 'marketplace/extension')) {
            $this->load->model('extension/payment/brainblocks');

            $this->model_extension_payment_brainblocks->install();
        }
    }

    public function uninstall() {
        if ($this->user->hasPermission('modify', 'marketplace/extension')) {
            $this->load->model('extension/payment/brainblocks');

            $this->model_extension_payment_brainblocks->uninstall();
        }
    }

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/brainblocks')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}