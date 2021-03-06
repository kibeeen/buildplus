<?php
class ControllerModuleQuickCheckout extends Controller {
	private $version = '9.3.2';
	private $code = 'quickcheckout';
	private $extension = 'Quick Checkout';
	private $extension_id = '7382';
	private $purchase_url = 'quick-checkout';
	private $error = array();
	public function index() {
		$this->language->load('module/quickcheckout');
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$this->load->model('setting/setting');
		$domain = isset($this->request->server['HTTP_HOST']) ? $this->request->server['HTTP_HOST'] : (isset($this->request->server['SERVER_NAME']) ? $this->request->server['SERVER_NAME'] : 'example.com');
		if (utf8_strtolower($this->config->get($this->code . '_domain')) != utf8_strtolower($domain)) {
			$setting = $this->model_setting_setting->getSetting($this->code);
			$data = array(
				$this->code . '_order_id' => '',
				$this->code . '_email' => '',
				$this->code . '_domain' => '',
				$this->code . '_activated_date' => ''
			);
			$this->model_setting_setting->editSetting($this->code, array_merge($setting, $data));
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->request->post[$this->code . '_order_id'] = $this->config->get($this->code . '_order_id');
			$this->request->post[$this->code . '_email'] = $this->config->get($this->code . '_email');
			$this->request->post[$this->code . '_domain'] = $this->config->get($this->code . '_domain');
			$this->request->post[$this->code . '_activated_date'] = $this->config->get($this->code . '_activated_date');
			$this->model_setting_setting->editSetting('quickcheckout', $this->request->post, $store_id);
			$this->session->data['success'] = $this->language->get('text_success');
			if (!isset($this->request->get['continue'])) {
				$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->response->redirect($this->url->link('module/quickcheckout', 'token=' . $this->session->data['token'] . '&store_id=' . $store_id, 'SSL'));
			}
		}

		$fields = array(
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'customer_group',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country',
			'zone',
			'newsletter',
			'register',
			'comment'
		);
		$data['fields'] = $fields;
		$data['heading_title'] = $this->language->get('heading_title');
		$data['tab_home'] = $this->language->get('tab_home');
		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_technical'] = $this->language->get('tab_technical');
		$data['tab_field'] = $this->language->get('tab_field');
		$data['tab_module'] = $this->language->get('tab_module');
		$data['tab_survey'] = $this->language->get('tab_survey');
		$data['tab_delivery'] = $this->language->get('tab_delivery');
		$data['tab_countdown'] = $this->language->get('tab_countdown');
		$data['help_status'] = $this->language->get('help_status');
		$data['help_load_screen'] = $this->language->get('help_load_screen');
		$data['help_payment_logo'] = $this->language->get('help_payment_logo');
		$data['help_payment'] = $this->language->get('help_payment');
		$data['help_shipping'] = $this->language->get('help_shipping');
		$data['help_edit_cart'] = $this->language->get('help_edit_cart');
		$data['help_highlight_error'] = $this->language->get('help_highlight_error');
		$data['help_text_error'] = $this->language->get('help_text_error');
		$data['help_layout'] = $this->language->get('help_layout');
		$data['help_slide_effect'] = $this->language->get('help_slide_effect');
		$data['help_minimum_order'] = $this->language->get('help_minimum_order');
		$data['help_debug'] = $this->language->get('help_debug');
		$data['help_auto_submit'] = $this->language->get('help_auto_submit');
		$data['help_responsive'] = $this->language->get('help_responsive');
		$data['help_country_reload'] = $this->language->get('help_country_reload');
		$data['help_payment_reload'] = $this->language->get('help_payment_reload');
		$data['help_shipping_reload'] = $this->language->get('help_shipping_reload');
		$data['help_voucher'] = $this->language->get('help_voucher');
		$data['help_coupon'] = $this->language->get('help_coupon');
		$data['help_reward'] = $this->language->get('help_reward');
		$data['help_cart'] = $this->language->get('help_cart');
		$data['help_shipping_module'] = $this->language->get('help_shipping_module');
		$data['help_payment_module'] = $this->language->get('help_payment_module');
		$data['help_login_module'] = $this->language->get('help_login_module');
		$data['help_html_header'] = $this->language->get('help_html_header');
		$data['help_html_footer'] = $this->language->get('help_html_footer');
		$data['help_survey_required'] = $this->language->get('help_survey_required');
		$data['help_survey_text'] = $this->language->get('help_survey_text');
		$data['help_survey_type'] = $this->language->get('help_survey_type');
		$data['help_survey_answer'] = $this->language->get('help_survey_answer');
		$data['help_delivery'] = $this->language->get('help_delivery');
		$data['help_delivery_time'] = $this->language->get('help_delivery_time');
		$data['help_delivery_required'] = $this->language->get('help_delivery_required');
		$data['help_delivery_unavailable'] = $this->language->get('help_delivery_unavailable');
		$data['help_delivery_min'] = $this->language->get('help_delivery_min');
		$data['help_delivery_max'] = $this->language->get('help_delivery_max');
		$data['help_delivery_days_of_week'] = $this->language->get('help_delivery_days_of_week');
		$data['help_delivery_times'] = $this->language->get('help_delivery_times');
		$data['help_countdown'] = $this->language->get('help_countdown');
		$data['help_countdown_start'] = $this->language->get('help_countdown_start');
		$data['help_countdown_date_start'] = $this->language->get('help_countdown_date_start');
		$data['help_countdown_date_end'] = $this->language->get('help_countdown_date_end');
		$data['help_countdown_time'] = $this->language->get('help_countdown_time');
		$data['help_countdown_text'] = $this->language->get('help_countdown_text');
		$data['text_default_store'] = $this->language->get('text_default_store');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['text_general'] = $this->language->get('text_general');
		$data['text_technical'] = $this->language->get('text_technical');
		$data['text_field'] = $this->language->get('text_field');
		$data['text_module_home'] = $this->language->get('text_module_home');
		$data['text_survey'] = $this->language->get('text_survey');
		$data['text_delivery'] = $this->language->get('text_delivery');
		$data['text_countdown'] = $this->language->get('text_countdown');
		$data['text_support_home'] = $this->language->get('text_support_home');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_radio_type'] = $this->language->get('text_radio_type');
		$data['text_select_type'] = $this->language->get('text_select_type');
		$data['text_text_type'] = $this->language->get('text_text_type');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_one_column'] = $this->language->get('text_one_column');
		$data['text_two_column'] = $this->language->get('text_two_column');
		$data['text_three_column'] = $this->language->get('text_three_column');
		$data['text_estimate'] = $this->language->get('text_estimate');
		$data['text_choose'] = $this->language->get('text_choose');
		$data['text_day'] = $this->language->get('text_day');
		$data['text_specific'] = $this->language->get('text_specific');
		$data['text_display'] = $this->language->get('text_display');
		$data['text_required'] = $this->language->get('text_required');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_sort_order'] = $this->language->get('text_sort_order');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_load_screen'] = $this->language->get('entry_load_screen');
		$data['entry_payment_logo'] = $this->language->get('entry_payment_logo');
		$data['entry_payment'] = $this->language->get('entry_payment');
		$data['entry_shipping'] = $this->language->get('entry_shipping');
		$data['entry_edit_cart'] = $this->language->get('entry_edit_cart');
		$data['entry_highlight_error'] = $this->language->get('entry_highlight_error');
		$data['entry_text_error'] = $this->language->get('entry_text_error');
		$data['entry_layout'] = $this->language->get('entry_layout');
		$data['entry_slide_effect'] = $this->language->get('entry_slide_effect');
		$data['entry_minimum_order'] = $this->language->get('entry_minimum_order');
		$data['entry_debug'] = $this->language->get('entry_debug');
		$data['entry_auto_submit'] = $this->language->get('entry_auto_submit');
		$data['entry_responsive'] = $this->language->get('entry_responsive');
		$data['entry_country_reload'] = $this->language->get('entry_country_reload');
		$data['entry_payment_reload'] = $this->language->get('entry_payment_reload');
		$data['entry_shipping_reload'] = $this->language->get('entry_shipping_reload');

		foreach($fields as $field) {
			$data['entry_field_' . $field] = $this->language->get('entry_field_' . $field);
		}

		$data['entry_voucher'] = $this->language->get('entry_voucher');
		$data['entry_coupon'] = $this->language->get('entry_coupon');
		$data['entry_reward'] = $this->language->get('entry_reward');
		$data['entry_cart'] = $this->language->get('entry_cart');
		$data['entry_shipping_module'] = $this->language->get('entry_shipping_module');
		$data['entry_payment_module'] = $this->language->get('entry_payment_module');
		$data['entry_login_module'] = $this->language->get('entry_login_module');
		$data['entry_html_header'] = $this->language->get('entry_html_header');
		$data['entry_html_footer'] = $this->language->get('entry_html_footer');
		$data['entry_survey'] = $this->language->get('entry_survey');
		$data['entry_survey_required'] = $this->language->get('entry_survey_required');
		$data['entry_survey_text'] = $this->language->get('entry_survey_text');
		$data['entry_survey_type'] = $this->language->get('entry_survey_type');
		$data['entry_survey_answer'] = $this->language->get('entry_survey_answer');
		$data['entry_delivery'] = $this->language->get('entry_delivery');
		$data['entry_delivery_time'] = $this->language->get('entry_delivery_time');
		$data['entry_delivery_required'] = $this->language->get('entry_delivery_required');
		$data['entry_delivery_unavailable'] = $this->language->get('entry_delivery_unavailable');
		$data['entry_delivery_min'] = $this->language->get('entry_delivery_min');
		$data['entry_delivery_max'] = $this->language->get('entry_delivery_max');
		$data['entry_delivery_days_of_week'] = $this->language->get('entry_delivery_days_of_week');
		$data['entry_delivery_times'] = $this->language->get('entry_delivery_times');
		$data['entry_countdown'] = $this->language->get('entry_countdown');
		$data['entry_countdown_start'] = $this->language->get('entry_countdown_start');
		$data['entry_countdown_date_start'] = $this->language->get('entry_countdown_date_start');
		$data['entry_countdown_date_end'] = $this->language->get('entry_countdown_date_end');
		$data['entry_countdown_time'] = $this->language->get('entry_countdown_time');
		$data['entry_countdown_text'] = $this->language->get('entry_countdown_text');
		$data['text_support'] = $this->language->get('text_support');
		$data['text_need_support'] = $this->language->get('text_need_support');
		$data['text_follow'] = $this->language->get('text_follow');
		$data['entry_mail_name'] = $this->language->get('entry_mail_name');
		$data['entry_mail_order_id'] = $this->language->get('entry_mail_order_id');
		$data['entry_mail_message'] = $this->language->get('entry_mail_message');
		$data['entry_mail_email'] = $this->language->get('entry_mail_email');
		$data['button_mail'] = $this->language->get('button_mail');
		$data['button_review'] = $this->language->get('button_review');
		$data['button_purchase'] = $this->language->get('button_purchase');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_continue'] = $this->language->get('button_continue');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_add'] = $this->language->get('button_add');
		$data['button_remove'] = $this->language->get('button_remove');
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$setting = $this->model_setting_setting->getSetting('quickcheckout', $store_id);
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home') ,
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module') ,
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title') ,
			'href' => $this->url->link('module/quickcheckout', 'token=' . $this->session->data['token'], 'SSL')
		);
		$data['action'] = $this->url->link('module/quickcheckout', 'token=' . $this->session->data['token'] . '&store_id=' . $store_id, 'SSL');
		$data['continue'] = $this->url->link('module/quickcheckout', 'token=' . $this->session->data['token'] . '&continue=1&store_id=' . $store_id, 'SSL');
		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		$data['token'] = $this->session->data['token'];
		if (isset($this->request->post['quickcheckout_status'])) {
			$data['quickcheckout_status'] = $this->request->post['quickcheckout_status'];
		} elseif (isset($setting['quickcheckout_status'])) {
			$data['quickcheckout_status'] = $setting['quickcheckout_status'];
		} else {
			$data['quickcheckout_status'] = 0;
		}

		if (isset($this->request->post['quickcheckout_load_screen'])) {
			$data['quickcheckout_load_screen'] = $this->request->post['quickcheckout_load_screen'];
		} elseif (isset($setting['quickcheckout_load_screen'])) {
			$data['quickcheckout_load_screen'] = $setting['quickcheckout_load_screen'];
		} else {
			$data['quickcheckout_load_screen'] = 0;
		}

		if (isset($this->request->post['quickcheckout_payment_logo'])) {
			$data['quickcheckout_payment_logo'] = $this->request->post['quickcheckout_payment_logo'];
		} elseif (isset($setting['quickcheckout_payment_logo'])) {
			$data['quickcheckout_payment_logo'] = $setting['quickcheckout_payment_logo'];
		} else {
			$data['quickcheckout_payment_logo'] = 0;
		}

		if (isset($this->request->post['quickcheckout_payment'])) {
			$data['quickcheckout_payment'] = $this->request->post['quickcheckout_payment'];
		} elseif (isset($setting['quickcheckout_payment'])) {
			$data['quickcheckout_payment'] = $setting['quickcheckout_payment'];
		} else {
			$data['quickcheckout_payment'] = 0;
		}

		if (isset($this->request->post['quickcheckout_shipping'])) {
			$data['quickcheckout_shipping'] = $this->request->post['quickcheckout_shipping'];
		} elseif (isset($setting['quickcheckout_shipping'])) {
			$data['quickcheckout_shipping'] = $setting['quickcheckout_shipping'];
		} else {
			$data['quickcheckout_shipping'] = 0;
		}

		if (isset($this->request->post['quickcheckout_edit_cart'])) {
			$data['quickcheckout_edit_cart'] = $this->request->post['quickcheckout_edit_cart'];
		} elseif (isset($setting['quickcheckout_edit_cart'])) {
			$data['quickcheckout_edit_cart'] = $setting['quickcheckout_edit_cart'];
		} else {
			$data['quickcheckout_edit_cart'] = 0;
		}

		if (isset($this->request->post['quickcheckout_highlight_error'])) {
			$data['quickcheckout_highlight_error'] = $this->request->post['quickcheckout_highlight_error'];
		} elseif (isset($setting['quickcheckout_highlight_error'])) {
			$data['quickcheckout_highlight_error'] = $setting['quickcheckout_highlight_error'];
		} else {
			$data['quickcheckout_highlight_error'] = 0;
		}

		if (isset($this->request->post['quickcheckout_text_error'])) {
			$data['quickcheckout_text_error'] = $this->request->post['quickcheckout_text_error'];
		} elseif (isset($setting['quickcheckout_text_error'])) {
			$data['quickcheckout_text_error'] = $setting['quickcheckout_text_error'];
		} else {
			$data['quickcheckout_text_error'] = 0;
		}

		if (isset($this->request->post['quickcheckout_layout'])) {
			$data['quickcheckout_layout'] = $this->request->post['quickcheckout_layout'];
		} elseif (isset($setting['quickcheckout_layout'])) {
			$data['quickcheckout_layout'] = $setting['quickcheckout_layout'];
		} else {
			$data['quickcheckout_layout'] = '2';
		}

		if (isset($this->request->post['quickcheckout_slide_effect'])) {
			$data['quickcheckout_slide_effect'] = $this->request->post['quickcheckout_slide_effect'];
		} elseif (isset($setting['quickcheckout_slide_effect'])) {
			$data['quickcheckout_slide_effect'] = $setting['quickcheckout_slide_effect'];
		} else {
			$data['quickcheckout_slide_effect'] = 0;
		}

		if (isset($this->request->post['quickcheckout_minimum_order'])) {
			$data['quickcheckout_minimum_order'] = $this->request->post['quickcheckout_minimum_order'];
		} elseif (isset($setting['quickcheckout_minimum_order'])) {
			$data['quickcheckout_minimum_order'] = $setting['quickcheckout_minimum_order'];
		} else {
			$data['quickcheckout_minimum_order'] = 0;
		}

		if (isset($this->request->post['quickcheckout_debug'])) {
			$data['quickcheckout_debug'] = $this->request->post['quickcheckout_debug'];
		} elseif (isset($setting['quickcheckout_debug'])) {
			$data['quickcheckout_debug'] = $setting['quickcheckout_debug'];
		} else {
			$data['quickcheckout_debug'] = 0;
		}

		if (isset($this->request->post['quickcheckout_auto_submit'])) {
			$data['quickcheckout_auto_submit'] = $this->request->post['quickcheckout_auto_submit'];
		} elseif (isset($setting['quickcheckout_auto_submit'])) {
			$data['quickcheckout_auto_submit'] = $setting['quickcheckout_auto_submit'];
		} else {
			$data['quickcheckout_auto_submit'] = 0;
		}

		if (isset($this->request->post['quickcheckout_responsive'])) {
			$data['quickcheckout_responsive'] = $this->request->post['quickcheckout_responsive'];
		} elseif (isset($setting['quickcheckout_responsive'])) {
			$data['quickcheckout_responsive'] = $setting['quickcheckout_responsive'];
		} else {
			$data['quickcheckout_responsive'] = 0;
		}

		if (isset($this->request->post['quickcheckout_country_reload'])) {
			$data['quickcheckout_country_reload'] = $this->request->post['quickcheckout_country_reload'];
		} elseif (isset($setting['quickcheckout_country_reload'])) {
			$data['quickcheckout_country_reload'] = $setting['quickcheckout_country_reload'];
		} else {
			$data['quickcheckout_country_reload'] = 0;
		}

		if (isset($this->request->post['quickcheckout_payment_reload'])) {
			$data['quickcheckout_payment_reload'] = $this->request->post['quickcheckout_payment_reload'];
		} elseif (isset($setting['quickcheckout_payment_reload'])) {
			$data['quickcheckout_payment_reload'] = $setting['quickcheckout_payment_reload'];
		} else {
			$data['quickcheckout_payment_reload'] = 0;
		}

		if (isset($this->request->post['quickcheckout_shipping_reload'])) {
			$data['quickcheckout_shipping_reload'] = $this->request->post['quickcheckout_shipping_reload'];
		} elseif (isset($setting['quickcheckout_shipping_reload'])) {
			$data['quickcheckout_shipping_reload'] = $setting['quickcheckout_shipping_reload'];
		} else {
			$data['quickcheckout_shipping_reload'] = 0;
		}

		foreach($fields as $field) {
			if (isset($this->request->post['quickcheckout_field_' . $field])) {
				$data['quickcheckout_field_' . $field] = $this->request->post['quickcheckout_field_' . $field];
			}
			elseif (isset($setting['quickcheckout_field_' . $field]) && is_array($setting['quickcheckout_field_' . $field])) {
				$data['quickcheckout_field_' . $field] = $setting['quickcheckout_field_' . $field];
			}
			else {
				$data['quickcheckout_field_' . $field] = array();
			}
		}

		if (isset($this->request->post['quickcheckout_coupon'])) {
			$data['quickcheckout_coupon'] = $this->request->post['quickcheckout_coupon'];
		} elseif (isset($setting['quickcheckout_coupon'])) {
			$data['quickcheckout_coupon'] = $setting['quickcheckout_coupon'];
		} else {
			$data['quickcheckout_coupon'] = 0;
		}

		if (isset($this->request->post['quickcheckout_voucher'])) {
			$data['quickcheckout_voucher'] = $this->request->post['quickcheckout_voucher'];
		} elseif (isset($setting['quickcheckout_voucher'])) {
			$data['quickcheckout_voucher'] = $setting['quickcheckout_voucher'];
		} else {
			$data['quickcheckout_voucher'] = 0;
		}

		if (isset($this->request->post['quickcheckout_reward'])) {
			$data['quickcheckout_reward'] = $this->request->post['quickcheckout_reward'];
		} elseif (isset($setting['quickcheckout_reward'])) {
			$data['quickcheckout_reward'] = $setting['quickcheckout_reward'];
		} else {
			$data['quickcheckout_reward'] = 0;
		}

		if (isset($this->request->post['quickcheckout_cart'])) {
			$data['quickcheckout_cart'] = $this->request->post['quickcheckout_cart'];
		} elseif (isset($setting['quickcheckout_cart'])) {
			$data['quickcheckout_cart'] = $setting['quickcheckout_cart'];
		} else {
			$data['quickcheckout_cart'] = 0;
		}

		if (isset($this->request->post['quickcheckout_shipping_module'])) {
			$data['quickcheckout_shipping_module'] = $this->request->post['quickcheckout_shipping_module'];
		} elseif (isset($setting['quickcheckout_shipping_module'])) {
			$data['quickcheckout_shipping_module'] = $setting['quickcheckout_shipping_module'];
		} else {
			$data['quickcheckout_shipping_module'] = 0;
		}

		if (isset($this->request->post['quickcheckout_payment_module'])) {
			$data['quickcheckout_payment_module'] = $this->request->post['quickcheckout_payment_module'];
		} elseif (isset($setting['quickcheckout_payment_module'])) {
			$data['quickcheckout_payment_module'] = $setting['quickcheckout_payment_module'];
		} else {
			$data['quickcheckout_payment_module'] = 0;
		}

		if (isset($this->request->post['quickcheckout_login_module'])) {
			$data['quickcheckout_login_module'] = $this->request->post['quickcheckout_login_module'];
		} elseif (isset($setting['quickcheckout_login_module'])) {
			$data['quickcheckout_login_module'] = $setting['quickcheckout_login_module'];
		} else {
			$data['quickcheckout_login_module'] = 0;
		}

		if (isset($this->request->post['quickcheckout_html_header'])) {
			$data['quickcheckout_html_header'] = $this->request->post['quickcheckout_html_header'];
		} elseif (isset($setting['quickcheckout_html_header']) && is_array($setting['quickcheckout_html_header'])) {
			$data['quickcheckout_html_header'] = $setting['quickcheckout_html_header'];
		} else {
			$data['quickcheckout_html_header'] = array();
		}

		if (isset($this->request->post['quickcheckout_html_footer'])) {
			$data['quickcheckout_html_footer'] = $this->request->post['quickcheckout_html_footer'];
		} elseif (isset($setting['quickcheckout_html_footer']) && is_array($setting['quickcheckout_html_footer'])) {
			$data['quickcheckout_html_footer'] = $setting['quickcheckout_html_footer'];
		} else {
			$data['quickcheckout_html_footer'] = array();
		}

		if (isset($this->request->post['quickcheckout_survey'])) {
			$data['quickcheckout_survey'] = $this->request->post['quickcheckout_survey'];
		} elseif (isset($setting['quickcheckout_survey'])) {
			$data['quickcheckout_survey'] = $setting['quickcheckout_survey'];
		} else {
			$data['quickcheckout_survey'] = 0;
		}

		if (isset($this->request->post['quickcheckout_survey_required'])) {
			$data['quickcheckout_survey_required'] = $this->request->post['quickcheckout_survey_required'];
		} elseif (isset($setting['quickcheckout_survey_required'])) {
			$data['quickcheckout_survey_required'] = $setting['quickcheckout_survey_required'];
		} else {
			$data['quickcheckout_survey_required'] = 0;
		}

		if (isset($this->request->post['quickcheckout_survey_text'])) {
			$data['quickcheckout_survey_text'] = $this->request->post['quickcheckout_survey_text'];
		} elseif (isset($setting['quickcheckout_survey_text']) && is_array($setting['quickcheckout_survey_text'])) {
			$data['quickcheckout_survey_text'] = $setting['quickcheckout_survey_text'];
		} else {
			$data['quickcheckout_survey_text'] = array();
		}

		if (isset($this->request->post['quickcheckout_survey_type'])) {
			$data['quickcheckout_survey_type'] = $this->request->post['quickcheckout_survey_type'];
		} elseif (isset($setting['quickcheckout_survey_type'])) {
			$data['quickcheckout_survey_type'] = $setting['quickcheckout_survey_type'];
		} else {
			$data['quickcheckout_survey_type'] = 0;
		}

		if (isset($this->request->post['quickcheckout_survey_answers'])) {
			$data['quickcheckout_survey_answers'] = $this->request->post['quickcheckout_survey_answers'];
		} elseif (isset($setting['quickcheckout_survey_answers']) && is_array($setting['quickcheckout_survey_answers'])) {
			$data['quickcheckout_survey_answers'] = $setting['quickcheckout_survey_answers'];
		} else {
			$data['quickcheckout_survey_answers'] = array();
		}

		if (isset($this->request->post['quickcheckout_delivery'])) {
			$data['quickcheckout_delivery'] = $this->request->post['quickcheckout_delivery'];
		} elseif (isset($setting['quickcheckout_delivery'])) {
			$data['quickcheckout_delivery'] = $setting['quickcheckout_delivery'];
		} else {
			$data['quickcheckout_delivery'] = 0;
		}

		if (isset($this->request->post['quickcheckout_delivery_time'])) {
			$data['quickcheckout_delivery_time'] = $this->request->post['quickcheckout_delivery_time'];
		} elseif (isset($setting['quickcheckout_delivery_time'])) {
			$data['quickcheckout_delivery_time'] = $setting['quickcheckout_delivery_time'];
		} else {
			$data['quickcheckout_delivery_time'] = 0;
		}

		if (isset($this->request->post['quickcheckout_delivery_required'])) {
			$data['quickcheckout_delivery_required'] = $this->request->post['quickcheckout_delivery_required'];
		} elseif (isset($setting['quickcheckout_delivery_required'])) {
			$data['quickcheckout_delivery_required'] = $setting['quickcheckout_delivery_required'];
		} else {
			$data['quickcheckout_delivery_required'] = 0;
		}

		if (isset($this->request->post['quickcheckout_delivery_unavailable'])) {
			$data['quickcheckout_delivery_unavailable'] = $this->request->post['quickcheckout_delivery_unavailable'];
		} elseif (isset($setting['quickcheckout_delivery_unavailable'])) {
			$data['quickcheckout_delivery_unavailable'] = $setting['quickcheckout_delivery_unavailable'];
		} else {
			$data['quickcheckout_delivery_unavailable'] = '"6-3-2013", "7-3-2013", "8-3-2013"';
		}

		if (isset($this->request->post['quickcheckout_delivery_min'])) {
			$data['quickcheckout_delivery_min'] = $this->request->post['quickcheckout_delivery_min'];
		} elseif (isset($setting['quickcheckout_delivery_min'])) {
			$data['quickcheckout_delivery_min'] = $setting['quickcheckout_delivery_min'];
		} else {
			$data['quickcheckout_delivery_min'] = 1;
		}

		if (isset($this->request->post['quickcheckout_delivery_max'])) {
			$data['quickcheckout_delivery_max'] = $this->request->post['quickcheckout_delivery_max'];
		} elseif (isset($setting['quickcheckout_delivery_max'])) {
			$data['quickcheckout_delivery_max'] = $setting['quickcheckout_delivery_max'];
		} else {
			$data['quickcheckout_delivery_max'] = 30;
		}

		if (isset($this->request->post['quickcheckout_delivery_days_of_week'])) {
			$data['quickcheckout_delivery_days_of_week'] = $this->request->post['quickcheckout_delivery_days_of_week'];
		} elseif (isset($setting['quickcheckout_delivery_days_of_week'])) {
			$data['quickcheckout_delivery_days_of_week'] = $setting['quickcheckout_delivery_days_of_week'];
		} else {
			$data['quickcheckout_delivery_days_of_week'] = '';
		}

		if (isset($this->request->post['quickcheckout_delivery_times'])) {
			$data['quickcheckout_delivery_times'] = $this->request->post['quickcheckout_delivery_times'];
		} elseif (isset($setting['quickcheckout_delivery_times'])) {
			$data['quickcheckout_delivery_times'] = $setting['quickcheckout_delivery_times'];
		} else {
			$data['quickcheckout_delivery_times'] = array();
		}

		if (isset($this->request->post['quickcheckout_countdown'])) {
			$data['quickcheckout_countdown'] = $this->request->post['quickcheckout_countdown'];
		} elseif (isset($setting['quickcheckout_countdown'])) {
			$data['quickcheckout_countdown'] = $setting['quickcheckout_countdown'];
		} else {
			$data['quickcheckout_countdown'] = 0;
		}

		if (isset($this->request->post['quickcheckout_countdown_start'])) {
			$data['quickcheckout_countdown_start'] = $this->request->post['quickcheckout_countdown_start'];
		} elseif (isset($setting['quickcheckout_countdown_start'])) {
			$data['quickcheckout_countdown_start'] = $setting['quickcheckout_countdown_start'];
		} else {
			$data['quickcheckout_countdown_start'] = 0;
		}

		if (isset($this->request->post['quickcheckout_countdown_date_start'])) {
			$data['quickcheckout_countdown_date_start'] = $this->request->post['quickcheckout_countdown_date_start'];
		} elseif (isset($setting['quickcheckout_countdown_date_start'])) {
			$data['quickcheckout_countdown_date_start'] = $setting['quickcheckout_countdown_date_start'];
		} else {
			$data['quickcheckout_countdown_date_start'] = 0;
		}

		if (isset($this->request->post['quickcheckout_countdown_date_end'])) {
			$data['quickcheckout_countdown_date_end'] = $this->request->post['quickcheckout_countdown_date_end'];
		} elseif (isset($setting['quickcheckout_countdown_date_end'])) {
			$data['quickcheckout_countdown_date_end'] = $setting['quickcheckout_countdown_date_end'];
		} else {
			$data['quickcheckout_countdown_date_end'] = 0;
		}

		if (isset($this->request->post['quickcheckout_countdown_time'])) {
			$data['quickcheckout_countdown_time'] = $this->request->post['quickcheckout_countdown_time'];
		} elseif (isset($setting['quickcheckout_countdown_time'])) {
			$data['quickcheckout_countdown_time'] = $setting['quickcheckout_countdown_time'];
		} else {
			$data['quickcheckout_countdown_time'] = 0;
		}

		if (isset($this->request->post['quickcheckout_countdown_text'])) {
			$data['quickcheckout_countdown_text'] = $this->request->post['quickcheckout_countdown_text'];
		} elseif (isset($setting['quickcheckout_countdown_text'])) {
			$data['quickcheckout_countdown_text'] = $setting['quickcheckout_countdown_text'];
		} else {
			$data['quickcheckout_countdown_text'] = 0;
		}

		$data['store_id'] = $store_id;
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();
		$data['version'] = $this->version;
		$data['code'] = $this->code;
		$data['extension'] = $this->extension;
		$data['extension_id'] = $this->extension_id;
		$data['purchase_url'] = $this->purchase_url;
		$data['order_id'] = utf8_strtolower($this->config->get($this->code . '_domain')) == utf8_strtolower($domain) ? $this->config->get($this->code . '_order_id') : '';
		$data['email'] = $this->config->get($this->code . '_email');
		$data['domain'] = $this->config->get($this->code . '_domain');
		$data['activated_date'] = $this->config->get($this->code . '_activated_date');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view('module/quickcheckout.tpl', $data));
	}

	public function country() {
		$json = array();
		$this->load->model('localisation/country');
		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);
		if ($country_info) {
			$this->load->model('localisation/zone');
			$json = array(
				'country_id' => $country_info['country_id'],
				'name' => $country_info['name'],
				'iso_code_2' => $country_info['iso_code_2'],
				'iso_code_3' => $country_info['iso_code_3'],
				'address_format' => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone' => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']) ,
				'status' => $country_info['status']
			);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function install() {
		$this->language->load('module/quickcheckout');
		$this->load->model('setting/setting');
		$data = array(
			'quickcheckout_status' => '0',
			'quickcheckout_load_screen' => '1',
			'quickcheckout_payment_logo' => '1',
			'quickcheckout_payment' => '1',
			'quickcheckout_shipping' => '1',
			'quickcheckout_edit_cart' => '1',
			'quickcheckout_highlight_error' => '1',
			'quickcheckout_text_error' => '1',
			'quickcheckout_layout' => '2',
			'quickcheckout_slide_effect' => '0',
			'quickcheckout_debug' => '0',
			'quickcheckout_auto_submit' => '0',
			'quickcheckout_responsive' => '0',
			'quickcheckout_country_reload' => '0',
			'quickcheckout_payment_reload' => '0',
			'quickcheckout_shipping_reload' => '0',
			'quickcheckout_field_firstname' => array(
				'display' => '1',
				'required' => '1',
				'default' => '',
				'sort_order' => '1'
			) ,
			'quickcheckout_field_lastname' => array(
				'display' => '1',
				'required' => '1',
				'default' => '',
				'sort_order' => '2'
			) ,
			'quickcheckout_field_email' => array(
				'display' => '1',
				'required' => '1',
				'default' => '',
				'sort_order' => '3'
			) ,
			'quickcheckout_field_telephone' => array(
				'display' => '1',
				'required' => '1',
				'default' => '',
				'sort_order' => '4'
			) ,
			'quickcheckout_field_fax' => array(
				'display' => '0',
				'required' => '0',
				'default' => '',
				'sort_order' => '5'
			) ,
			'quickcheckout_field_company' => array(
				'display' => '1',
				'required' => '0',
				'default' => '',
				'sort_order' => '6'
			) ,
			'quickcheckout_field_customer_group' => array(
				'display' => '1',
				'required' => '',
				'default' => '',
				'sort_order' => '7'
			) ,
			'quickcheckout_field_address_1' => array(
				'display' => '1',
				'required' => '1',
				'default' => '',
				'sort_order' => '9'
			) ,
			'quickcheckout_field_address_2' => array(
				'display' => '0',
				'required' => '0',
				'default' => '',
				'sort_order' => '10'
			) ,
			'quickcheckout_field_city' => array(
				'display' => '1',
				'required' => '1',
				'default' => '',
				'sort_order' => '11'
			) ,
			'quickcheckout_field_postcode' => array(
				'display' => '1',
				'required' => '0',
				'default' => '',
				'sort_order' => '12'
			) ,
			'quickcheckout_field_country' => array(
				'display' => '1',
				'required' => '1',
				'default' => '1',
				'sort_order' => '13'
			) ,
			'quickcheckout_field_zone' => array(
				'display' => '1',
				'required' => '0',
				'default' => '1',
				'sort_order' => '14'
			) ,
			'quickcheckout_field_newsletter' => array(
				'display' => '1',
				'required' => '0',
				'default' => '1',
				'sort_order' => ''
			) ,
			'quickcheckout_field_register' => array(
				'display' => '1',
				'required' => '0',
				'default' => '',
				'sort_order' => ''
			) ,
			'quickcheckout_field_comment' => array(
				'display' => '1',
				'required' => '0',
				'default' => '',
				'sort_order' => ''
			) ,
			'quickcheckout_coupon' => '1',
			'quickcheckout_voucher' => '1',
			'quickcheckout_reward' => '1',
			'quickcheckout_cart' => '1',
			'quickcheckout_shipping_module' => '1',
			'quickcheckout_payment_module' => '1',
			'quickcheckout_login_module' => '1',
			'quickcheckout_html_header' => array() ,
			'quickcheckout_html_footer' => array() ,
			'quickcheckout_survey' => '0',
			'quickcheckout_survey_required' => '0',
			'quickcheckout_survey_text' => array() ,
			'quickcheckout_delivery' => '0',
			'quickcheckout_delivery_time' => '0',
			'quickcheckout_delivery_required' => '0',
			'quickcheckout_delivery_unavailable' => '"31-10-2013", "08-11-2013", "25-12-2013"',
			'quickcheckout_delivery_min' => '1',
			'quickcheckout_delivery_max' => '30',
			'quickcheckout_delivery_days_of_week' => ''
		);
		$this->model_setting_setting->editSetting('quickcheckout', $data);
		$this->load->model('setting/store');
		$stores = $this->model_setting_store->getStores();
		foreach($stores as $store) {
			$this->model_setting_setting->editSetting('quickcheckout', $data, $store['store_id']);
		}
	}

	protected function validate() {
		// if (!$this->user->hasPermission('modify', 'module/' . $this->code) || !$this->config->get($this->code . base64_decode('X29yZGVyX2lk'))) {
		if (!$this->user->hasPermission('modify', 'module/' . $this->code)) {
			$this->error['warning'] = $this->language->get('error_permission');
			//$this->language->get(base64_decode('ZXJyb3JfcGVybWlzc2lvbg=='));
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function license() {
		$json = array();
		if (isset($this->request->post['license_order_id']) && isset($this->request->post['license_email'])) {
			$domain = isset($this->request->server['HTTP_HOST']) ? $this->request->server['HTTP_HOST'] : (isset($this->request->server['SERVER_NAME']) ? $this->request->server['SERVER_NAME'] : 'example.com');
			$post_data = array(
				'order_id' => $this->request->post['license_order_id'],
				'email' => $this->request->post['license_email'],
				'domain' => $domain
			);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, 'http://license.marketinsg.com/index.php?load=common/home/activatelicense');
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
			$response = curl_exec($curl);
			$data = json_decode($response, true);
			if (isset($data['error'])) {
				$json['error'] = $data['error'];
			} elseif (isset($data['success'])) {
				$json['success'] = true;
				$this->load->model('setting/setting');
				$setting = $this->model_setting_setting->getSetting($this->code);
				$data = array(
					$this->code . '_order_id' => $post_data['order_id'],
					$this->code . '_email' => $post_data['email'],
					$this->code . '_domain' => $post_data['domain'],
					$this->code . '_activated_date' => date('d M Y H:i:s')
				);
				$this->model_setting_setting->editSetting($this->code, array_merge($setting, $data));
			} else {
				$json['error'] = base64_decode('V2UgYXJlIHVuYWJsZSB0byByZWFjaCB0aGUgbGljZW5zaW5nIHNlcnZlci4gRW5zdXJlIHlvdSBoYXZlIGFuIGludGVybmV0IGNvbm5lY3Rpb24u');
			}

			curl_close($curl);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function revoke() {
		$json = array();
		if ($this->user->hasPermission('modify', 'module/' . $this->code)) {
			$domain = isset($this->request->server['HTTP_HOST']) ? $this->request->server['HTTP_HOST'] : (isset($this->request->server['SERVER_NAME']) ? $this->request->server['SERVER_NAME'] : 'example.com');
			$post_data = array(
				'order_id' => $this->config->get($this->code . '_order_id') ,
				'email' => $this->config->get($this->code . '_email') ,
				'domain' => $this->config->get($this->code . '_domain')
			);
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, 'http://license.marketinsg.com/index.php?load=common/home/revokelicense');
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
			$response = curl_exec($curl);
			$data = json_decode($response, true);
			if (isset($data['error'])) {
				$json['error'] = $data['error'];
			} elseif (isset($data['success'])) {
				$json['success'] = true;
				$this->load->model('setting/setting');
				$setting = $this->model_setting_setting->getSetting($this->code);
				$data = array(
					$this->code . '_order_id' => '',
					$this->code . '_email' => '',
					$this->code . '_domain' => '',
					$this->code . '_activated_date' => ''
				);
				$this->model_setting_setting->editSetting($this->code, array_merge($setting, $data));
			} else {
				$json['error'] = base64_decode('V2UgYXJlIHVuYWJsZSB0byByZWFjaCB0aGUgbGljZW5zaW5nIHNlcnZlci4gRW5zdXJlIHlvdSBoYXZlIGFuIGludGVybmV0IGNvbm5lY3Rpb24u');
			}

			curl_close($curl);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function mail() {
		$json = array();
		if ($this->user->hasPermission('modify', 'module/' . $this->code)) {
			if (strlen($this->request->post['mail_name']) < 3 || strlen($this->request->post['mail_name']) > 16) {
				$json['error']['name'] = 'Name must be between 3 and 16 characters';
			}

			if ((strlen($this->request->post['mail_email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,15}$/i', $this->request->post['mail_email'])) {
				$json['error']['email'] = 'Email address must be valid!';
			}

			if (strlen($this->request->post['mail_order_id']) < 3 || (int)$this->request->post['mail_order_id'] == 0) {
				$json['error']['order_id'] = 'Order ID must be valid!';
			}

			if (strlen($this->request->post['mail_message']) < 20 || strlen($this->request->post['mail_message']) > 2400) {
				$json['error']['message'] = 'Message must be between 20 and 2400 characters!';
			}

			if (!$json) {
				$subject = '[' . $this->extension . '] Support ' . $this->request->post['mail_name'];
				$message = 'Order ID: ' . $this->request->post['mail_order_id'] . "\n\n";
				$message.= $this->request->post['mail_message'];
				if (version_compare(VERSION, '1.5.6.5', '<')) {
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->hostname = $this->config->get('config_smtp_host');
					$mail->username = $this->config->get('config_smtp_username');
					$mail->password = $this->config->get('config_smtp_password');
					$mail->port = $this->config->get('config_smtp_port');
					$mail->timeout = $this->config->get('config_smtp_timeout');
				} elseif (version_compare(VERSION, '2.0.2.0', '<')) {
					$mail = new Mail($this->config->get('config_mail'));
				} else {
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password') , ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
				}

				$mail->setTo('support@marketinsg.com');
				$mail->setFrom($this->request->post['mail_email']);
				$mail->setSender($this->request->post['mail_name']);
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
				$json['success'] = 'You have successfully contacted MarketInSG\'s Support';
			}
		} else {
			$json['error']['warning'] = $this->error['warning'];
		}

		$this->response->setOutput(json_encode($json));
	}
}