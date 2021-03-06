<?php
//==============================================================================
// MailChimp Integration v201.2
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

class Mailchimp_Integration {
	private $type = 'module';
	private $name = 'mailchimp_integration';
	
	public function __construct($config, $db, $log, $session) {
		$this->config = $config;
		$this->db = $db;
		$this->log = $log;
		$this->session = $session;
	}
	
	public function determineList($customer) {
		$settings = $this->getSettings();
		
		if (!empty($settings['mapping'])) {
			foreach ($settings['mapping'] as $mapping) {
				if (empty($mapping['list']) || empty($mapping['rule'])) continue;
				
				$rules = array('list' => $mapping['list']);
				foreach ($mapping['rule'] as $rule) {
					if (empty($rule['type'])) continue;
					$rules[$rule['type']][$rule['comparison']][] = $rule['value'];
				}
				
				if ($this->ruleViolation($rules, 'currency', $this->config->get('config_currency')) ||
					$this->ruleViolation($rules, 'customer_group', $customer['customer_group_id']) ||
					$this->ruleViolation($rules, 'language', !empty($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language')) ||
					$this->ruleViolation($rules, 'store', $this->config->get('config_store_id'))
				) {
					continue;
				}
				
				return $mapping['list'];
			}
		}
		
		return $settings['listid'];
	}
	
	public function getLists() {
		$response = $this->curlRequest(array('method' => 'lists/list', 'apikey' => $this->config->get($this->name . '_apikey')));
		return $response['data'];
	}
	
	public function getMergeTags($listid) {
		$response = $this->curlRequest(array('method' => 'lists/merge-vars', 'apikey' => $this->config->get($this->name . '_apikey'), 'id' => array($listid)));
		return $response['data'][0]['merge_vars'];
	}
	
	public function getInterestGroups($listid) {
		if (!file_exists(DIR_SYSTEM . 'library/mailchimp_integration_pro.php')) return array();
		$response = $this->curlRequest(array('method' => 'lists/interest-groupings', 'apikey' => $this->config->get($this->name . '_apikey'), 'id' => $listid));
		return (empty($response['error'])) ? $response : array();
	}
	
	public function getMemberInfo($listid, $email) {
		$response = $this->curlRequest(array('method' => 'lists/member-info', 'apikey' => $this->config->get($this->name . '_apikey'), 'id' => $listid, 'emails' => array(array('email' => $email))));
		return (empty($response['error'])) ? $response['data'][0] : array();
	}
	
	public function addWebhooks() {
		$settings = $this->getSettings();
		
		if (empty($settings['apikey']) || empty($settings['listid']) || empty($settings['webhooks'])) return;
		
		$catalog_url = ($this->config->get('config_ssl') || $this->config->get('config_secure')) ? str_replace('http:', 'https:', HTTP_CATALOG) : HTTP_CATALOG;
		$url = $catalog_url . 'index.php?route=' . $this->type . '/' . $this->name . '/webhook&key=' . $this->config->get('config_encryption');
		
		$webhooks = explode(';', $settings['webhooks']);
		
		foreach ($this->getLists() as $list) {
			$curl_data = array(
				'method'	=> 'lists/webhooks',
				'apikey'	=> $settings['apikey'],
				'id'		=> $list['id'],
			);
			$response = $this->curlRequest($curl_data);
			
			$mc_webhooks = array();
			if (empty($response['error'])) {
				foreach ($response as $mc_webhook) {
					$mc_webhooks[] = $mc_webhook['url'];
				}
			}
			
			if (!in_array($url, $mc_webhooks)) {
				$curl_data = array(
					'method'	=> 'lists/webhook-add',
					'apikey'	=> $settings['apikey'],
					'id'		=> $list['id'],
					'url'		=> $url,
					'actions'	=> array(
						'subscribe'		=> in_array('subscribe', $webhooks),
						'unsubscribe'	=> in_array('unsubscribe', $webhooks),
						'profile'		=> in_array('profile', $webhooks),
						'upemail'		=> in_array('profile', $webhooks),
						'cleaned'		=> in_array('cleaned', $webhooks),
						'campaign'		=> false,
					)
				);
				$response = $this->curlRequest($curl_data);
			}
		}
	}
	
	public function send($data) {
		$settings = $this->getSettings();
		
		if (empty($settings['status'])) {
			if ($settings['testing_mode']) $this->log->write(strtoupper($this->name) . ' ERROR: Extension is disabled');
			return;
		}
		if (empty($settings['apikey'])) {
			if ($settings['testing_mode']) $this->log->write(strtoupper($this->name) . ' ERROR: API Key is not filled');
			return;
		}
		if (empty($settings['status'])) {
			if ($settings['testing_mode']) $this->log->write(strtoupper($this->name) . ' ERROR: Default list is not set');
			return;
		}
		
		// Get customer information
		if (!empty($data['customer_id'])) {
			if (!empty($data['newsletter']) && !empty($settings['subscribed_group'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "customer SET customer_group_id = " . (int)$settings['subscribed_group'] . " WHERE customer_id = " . (int)$data['customer_id']);
			} elseif (empty($data['newsletter']) && !empty($settings['unsubscribed_group'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "customer SET customer_group_id = " . (int)$settings['unsubscribed_group'] . " WHERE customer_id = " . (int)$data['customer_id']);
			}
			$customer = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = " . (int)$data['customer_id'])->row;
		} else {
			$customer = array(
				'customer_id'		=> 0,
				'customer_group_id'	=> (isset($data['customer_group_id'])) ? $data['customer_group_id'] : $this->config->get('config_customer_group_id'),
				'email'				=> (isset($data['email'])) ? $data['email'] : '',
				'firstname'			=> '',
				'lastname'			=> '',
				'address_id'		=> '',
				'telephone'			=> '',
			);
		}
		
		// Get address information
		if (!empty($data['addresses'])) {
			$data['address'] = $data['addresses'];
		}
		if (!empty($data['address'])) {
			foreach ($data['address'] as $address_data) {
				$address = $address_data;
				if (!empty($address['default'])) break;
			}
		} else {
			$address = $data;
		}
		unset($data['address']);
		
		if (!empty($address['country_id'])) {
			$country_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = " . (int)$address['country_id']);
			$address['iso_code_2'] = (!empty($country_query->row['iso_code_2'])) ? $country_query->row['iso_code_2'] : '';
		}
		if (!empty($address['zone_id'])) {
			$zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = " . (int)$address['zone_id']);
			$address['zone'] = (!empty($zone_query->row['name'])) ? html_entity_decode($zone_query->row['name'], ENT_QUOTES, 'UTF-8') : '(none)';
		}
		
		$address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = " . (int)$customer['address_id']);
		$default_address = ($address_query->num_rows) ? $address_query->row : array(
			'address_1'		=> '',
			'address_2'		=> '',
			'city'			=> '',
			'postcode'		=> '',
			'zone_id'		=> '',
			'country_id'	=> '',
		);
		
		$country_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = " . (int)$default_address['country_id']);
		$default_address['iso_code_2'] = (!empty($country_query->row['iso_code_2'])) ? $country_query->row['iso_code_2'] : '';
		
		$zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = " . (int)$default_address['zone_id']);
		$default_address['zone'] = (!empty($zone_query->row['name'])) ? html_entity_decode($zone_query->row['name'], ENT_QUOTES, 'UTF-8') : '(none)';
		
		$customer['address'] = array(
			'addr1'		=> (isset($address['address_1']))	? $address['address_1']		: $default_address['address_1'],
			'addr2'		=> (isset($address['address_2']))	? $address['address_2']		: $default_address['address_2'],
			'city'		=> (isset($address['city']))		? $address['city']			: $default_address['city'],
			'state'		=> (isset($address['zone']))		? $address['zone']			: $default_address['zone'],
			'zip'		=> (isset($address['postcode']))	? $address['postcode']		: $default_address['postcode'],
			'country'	=> (isset($address['iso_code_2']))	? $address['iso_code_2']	: $default_address['iso_code_2'],
		);
		if (empty($customer['address']['zip'])) $customer['address']['zip'] = '00000';
		
		$listid = $this->determineList($customer);
		
		// Subscribe or Unsubscribe
		if (!empty($data['newsletter'])) {
			$merge_array = array();
			
			// E-mail merge tag
			$merge_array['EMAIL'] = (isset($data['email'])) ? $data['email'] : $customer['email'];
			
			// Language merge tag
			if (!empty($this->session->data['language'])) {
				$merge_array['MC_LANGUAGE'] = $this->session->data['language'];
			} elseif (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$language_region = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5));
				if ($language_region == 'fr-ca' || $language_region == 'pt-pt' || $language_region == 'es-es') {
					$merge_array['MC_LANGUAGE']	= substr($language_region, 0, 2) . '_' . strtoupper(substr($language_region, -2));
				} else {
					$merge_array['MC_LANGUAGE']	= substr($language_region, 0, 2);
				}
			} else {
				$merge_array['MC_LANGUAGE']	= $this->config->get('config_language');
			}
			
			// Other merge tags
			foreach ($this->getMergeTags($listid) as $merge) {
				if ($merge['tag'] == 'EMAIL') continue;
				
				$merge_setting_value = (!empty($settings[$listid . '_' . $merge['tag']])) ? $settings[$listid . '_' . $merge['tag']] : '';
				
				if (empty($merge_setting_value)) {
					if (!$merge['req']) continue;
					$merge_array[$merge['tag']] = ($merge['field_type'] == 'zip') ? '00000' : '(none)';
				} else {
					$merge_setting_split = explode(':', $merge_setting_value);
					$column = ($merge_setting_split[1] == 'address_id') ? 'address' : $merge_setting_split[1];
					
					if (isset($data[$column])) {
						$merge_array[$merge['tag']] = $data[$column];
					} elseif (isset($customer[$column])) {
						$merge_array[$merge['tag']] = $customer[$column];
					} else {
						$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . $merge_setting_split[0] . " WHERE customer_id = " . (int)$customer['customer_id']);
						if (isset($customer_query->row[$column])) {
							$merge_array[$merge['tag']] = $customer_query->row[$column];
						} elseif ($merge['req']) {
							$merge_array[$merge['tag']] = ($merge['field_type'] == 'zip') ? '00000' : '(none)';
						}
					}
					if ($merge['field_type'] == 'phone') {
						$telephone = preg_replace('/[^0-9]/', '', $merge_array[$merge['tag']]);
						if ($telephone || $merge['req']) {
							$merge_array[$merge['tag']] = substr($telephone, 0, 3) . '-' . substr($telephone, 3, 3) . '-' . substr($telephone, 6);
						} else {
							unset($merge_array[$merge['tag']]);
						}
					}
				}
			}
			
			// Interest Groups
			if (!empty($settings['interest_groups']) && !empty($data['interest_groups']) && file_exists(DIR_SYSTEM . 'library/mailchimp_integration_pro.php')) {
				$merge_array['GROUPINGS'] = array();
				foreach ($data['interest_groups'] as $id => $groups) {
					$merge_array['GROUPINGS'][] = array('id' => $id, 'groups' => $groups);
				}
				unset($this->session->data['mailchimp_interest_groups']);
				unset($this->session->data['mailchimp_interests']);
			}
			
			// Subscribe
			$curl_data = array(
				'method'			=> 'lists/subscribe',
				'apikey'			=> $settings['apikey'],
				'id'				=> $listid,
				'email'				=> array('email' => $customer['email']),
				'merge_vars'		=> $merge_array,
				'email_type'		=> 'html',
				'double_optin'		=> (isset($data['double_optin']) ? $data['double_optin'] : $settings['double_optin']),
				'update_existing'	=> (isset($data['update_existing']) ? $data['update_existing'] : true),
				'send_welcome'		=> (isset($data['send_welcome']) ? $data['send_welcome'] : true),
			);
		} else {
			// Unsubscribe
			$curl_data = array(
				'method'			=> 'lists/unsubscribe',
				'apikey'			=> $settings['apikey'],
				'id'				=> $listid,
				'email'				=> array('email' => $customer['email']),
				'delete_member'		=> false,
				'send_goodbye'		=> true,
				'send_notify'		=> true,
			);
		}
		
		$response = $this->curlRequest($curl_data);
		
		return (empty($response['error'])) ? array('code' => 0) : $response;
	}
	
	public function sync() {
		$settings = $this->getSettings();
		
		if (empty($settings['apikey'])) {
			return 'Error: No API Key is filled in';
		} elseif (empty($settings['listid'])) {
			return 'Error: No List ID is set';
		}
		
		$output = "Completed!\n\n";
		
		// Get OpenCart customers, and change customer groups
		$customers = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer ORDER BY customer_group_id, store_id ASC");
		$opencart_emails = array();
		foreach ($customers->rows as $customer) {
			$opencart_emails[] = $customer['email'];
		}
		
		if (!empty($settings['subscribed_group'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET customer_group_id = " . (int)$settings['subscribed_group'] . " WHERE newsletter = 1");
		}
		if (!empty($settings['unsubscribed_group'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET customer_group_id = " . (int)$settings['unsubscribed_group'] . " WHERE newsletter = 0");
		}
		
		// MailChimp to OpenCart
		if ($settings['autocreate']) {
			$created = 0;
			$data_center = explode('-', $settings['apikey']);
			
			foreach ($this->getLists() as $list) {
				$context = stream_context_create(array('http' => array('ignore_errors' => '1')));
				$response = @file_get_contents('https://' . $data_center[1] . '.api.mailchimp.com/export/1.0/list/?apikey=' . $settings['apikey'] . '&id=' . $list['id'], false, $context);
				
				$mailchimp_emails = array();
				foreach (explode("\n", $response) as $line) {
					$subscriber = json_decode($line);
					if (strpos($subscriber[0], '@') === false) continue;
					$mailchimp_emails[] = $subscriber[0];
				}
				$diff_emails = array_diff($mailchimp_emails, $opencart_emails);
				
				for ($i = 0; $i < count($diff_emails); $i += 50) {
					$subscribers = array();
					foreach (array_slice($diff_emails, $i, 50) as $email) {
						$subscribers[] = array('email' => $email);
					}
					
					$curl_data = array(
						'method'	=> 'lists/member-info',
						'apikey'	=> $settings['apikey'],
						'id'		=> $list['id'],
						'emails'	=> $subscribers,
					);
					
					$response = $this->curlRequest($curl_data);
					if (!empty($response['error'])) {
						return $response['error'];
					}
					
					foreach ($response['data'] as $data) {
						$this->createCustomer($data);
						$created++;
					}
				}
			}
			
			$output .= $created . " customer(s) created in OpenCart\n";
		}
		
		// Get merge tags
		$merge_array = array();
		foreach ($this->getLists() as $list) {
			foreach ($this->getMergeTags($list['id']) as $merge) {
				if ($merge['tag'] == 'EMAIL') {
					$merge_array[$list['id']]['EMAIL'] = 'email';
					continue;
				}
				
				$merge_setting_value = (!empty($settings[$list['id'] . '_' . $merge['tag']])) ? $settings[$list['id'] . '_' . $merge['tag']] : '';
				
				if (empty($merge_setting_value)) {
					if (!$merge['req']) continue;
					$merge_array[$list['id']][$merge['tag']] = ($merge['field_type'] == 'zip') ? '00000' : '(none)';
				} else {
					$merge_setting_split = explode(':', $merge_setting_value);
					if ($merge_setting_split[0] == 'customer') {
						$merge_array[$list['id']][$merge['tag']] = ($merge_setting_split[1] == 'address_id') ? 'address' : $merge_setting_split[1];
					} else {
						$merge_array[$list['id']][$merge['tag']] = $merge_setting_value;
					}
				}
			}
		}
			
		// OpenCart to MailChimp
		$batches = array();
		
		$add_count = 0;
		$update_count = 0;
		$error_count = 0;
		$errors = '';
		
		foreach ($customers->rows as $customer) {
			if (!$customer['newsletter']) continue;
			
			$this->config->set($this->name . '_testing_mode', 0);
			$listid = $this->determineList($customer);
			$this->config->set($this->name . '_testing_mode', $settings['testing_mode']);
			
			$formatted_customer = array('email' => array('email' => $customer['email']));
			
			foreach ($merge_array as $merge_listid => $merges) {
				if ($merge_listid != $listid) continue;
				foreach ($merges as $merge_tag => $opencart_field) {
					if ($opencart_field == 'address') {
						$address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = " . (int)$customer['address_id']);
						if ($address_query->num_rows) {
							$address = $address_query->row;
							if (!empty($address['country_id'])) {
								$country_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id = " . (int)$address['country_id']);
								$address['iso_code_2'] = (!empty($country_query->row['iso_code_2'])) ? $country_query->row['iso_code_2'] : '';
							}
							if (!empty($address['zone_id'])) {
								$zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = " . (int)$address['zone_id']);
								$address['zone'] = (!empty($zone_query->row['name'])) ? html_entity_decode($zone_query->row['name'], ENT_QUOTES, 'UTF-8') : '(none)';
							}
							$formatted_customer['merge_vars'][$merge_tag] = array(
								'addr1'		=> $address['address_1'],
								'addr2'		=> $address['address_2'],
								'city'		=> $address['city'],
								'state'		=> (isset($address['zone'])) ? $address['zone'] : '(none)',
								'zip'		=> $address['postcode'],
								'country'	=> (isset($address['iso_code_2'])) ? $address['iso_code_2'] : ''
							);
						} else {
							$formatted_customer['merge_vars'][$merge_tag] = array(
								'addr1'		=> '',
								'addr2'		=> '',
								'city'		=> '',
								'state'		=> '',
								'zip'		=> '',
								'country'	=> '',
							);
						}
					} elseif ($opencart_field == 'telephone') {
						$telephone = preg_replace('/[^0-9]/', '', $customer[$opencart_field]);
						$formatted_customer['merge_vars'][$merge_tag] = substr($telephone, 0, 3) . '-' . substr($telephone, 3, 3) . '-' . substr($telephone, 6);
					} elseif ($opencart_field == '00000' || $opencart_field == '(none)') {
						$formatted_customer['merge_vars'][$merge_tag] = $opencart_field;
					} elseif (isset($customer[$opencart_field])) {
						$formatted_customer['merge_vars'][$merge_tag] = $customer[$opencart_field];
					} else {
						$field_split = explode(':', $opencart_field);
						if ($field_split[0] == 'address') {
							$database_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = " . (int)$customer['address_id']);
						} else {
							$database_query = $this->db->query("SELECT * FROM " . DB_PREFIX . $field_split[0] . " WHERE customer_id = " . (int)$customer['customer_id']);
						}
						$formatted_customer['merge_vars'][$merge_tag] = $database_query->row[$field_split[1]];
					}
				}
			}
			
			$batches[$listid][] = $formatted_customer;
		}
		
		foreach ($batches as $listid => $batch) {
			$i = 0;
			while ($i < count($batch)) {
				$sliced_batch = array_slice($batch, $i, 5000);
				$i += 5000;
				
				$curl_data = array(
					'method'			=> 'lists/batch-subscribe',
					'apikey'			=> $settings['apikey'],
					'id'				=> $listid,
					'batch'				=> $sliced_batch,
					'double_optin'		=> false,
					'update_existing'	=> true,
				);
				
				$response = $this->curlRequest($curl_data);
				
				if (!empty($response['error'])) {
					$error_count += 1;
					$errors .= $response['error'] . "\n";
				} else {
					$add_count += $response['add_count'];
					$update_count += $response['update_count'];
					$error_count += $response['error_count'];
					if (!empty($response['errors'])) {
						foreach ($response['errors'] as $error) {
							$errors .= $error['error'] . "\n";
						}
					}
				}
			}
		}
		
		$output .= $add_count . " customer(s) added to MailChimp\n";
		$output .= $update_count . " customer(s) updated in MailChimp\n";
		$output .= $error_count . " customer(s) failed sending to MailChimp\n\n";
		
		if ($settings['testing_mode']) $this->log->write(strtoupper($this->name) . ' SYNC SUCCESS: ' .str_replace("\n", ', ', $output));
		if (!empty($errors)) {
			if ($settings['testing_mode']) $this->log->write(strtoupper($this->name) . ' SYNC ERRORS: ' . $errors);
			$output .= "Error(s):\n" . $errors;
		}
		
		return $output;
	}
	
	public function webhook($type, $data) {
		$settings = $this->getSettings();
		
		if (empty($settings['status'])) {
			if ($settings['testing_mode']) {
				$this->log->write(strtoupper($this->name) . ' ERROR: Extension is disabled in the admin panel');
			}
			return;
		}
		if (empty($settings['webhooks'])) {
			if ($settings['testing_mode']) {
				$this->log->write(strtoupper($this->name) . ' ERROR: No webhooks are enabled in the admin panel');
			}
			return;
		}
		
		$webhooks = explode(';', $settings['webhooks']);
		
		$listid = $settings['listid'];
		$customer_group_id = $this->config->get('config_customer_group_id');
		/*
		foreach ($settings as $key => $value) {
			if (strpos($value, '_list') && $value == $data['list_id']) {
				if (customer group rule exists) {
					$listid = $data['list_id'];
					$customer_group_id = customer group value
					break;
				}
			}
		}
		*/
		$data['customer_group_id'] = $customer_group_id;
		
		if ($data['list_id'] != $listid) {
			if ($settings['testing_mode']) {
				$this->log->write(strtoupper($this->name) . ' WEBHOOK ERROR: webhook List ID ' . $data['list_id'] . ' does not match the List ID ' . $listid . ' for action "' . $type . '" for e-mail address ' . $data['email']);
			}
			return;
		}
		
		$success = false;
		
		if ($type == 'subscribe' && in_array('subscribe', $webhooks)) {
			
			if ($settings['autocreate']) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE email = '" . $this->db->escape($data['email']) . "'");
				if (!$query->num_rows) {
					$this->createCustomer($data);
				}
			}
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = 1 WHERE email = '" . $this->db->escape($data['email']) . "'");
			if (!empty($settings['subscribed_group'])) $this->db->query("UPDATE " . DB_PREFIX . "customer SET customer_group_id = " . (int)$settings['subscribed_group'] . " WHERE email = '" . $this->db->escape($data['email']) . "'");
			$success = true;
			
		} elseif (($type == 'unsubscribe' && in_array('unsubscribe', $webhooks)) || ($type == 'cleaned' && in_array('cleaned', $webhooks))) {
			
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = 0 WHERE email = '" . $this->db->escape($data['email']) . "'");
			if (!empty($settings['unsubscribed_group'])) $this->db->query("UPDATE " . DB_PREFIX . "customer SET customer_group_id = " . (int)$settings['unsubscribed_group'] . " WHERE email = '" . $this->db->escape($data['email']) . "'");
			$success = true;
			
		} elseif ($type == 'profile' && in_array('profile', $webhooks)) {
			
			$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE email = '" . $this->db->escape($data['email']) . "'");
			if (empty($customer_query->row['address_id'])) {
				$this->log->write(strtoupper($this->name) . ' WEBHOOK ERROR: customer ' . $data['email'] . ' does not have a valid address_id');
				return;
			}
			
			foreach ($data['merges'] as $merge_tag => $merge_value) {
				$merge_mapping = $settings[$data['list_id'] . '_' . $merge_tag];
				if (strpos($merge_mapping, ':firstname')) {
					$this->db->query("UPDATE " . DB_PREFIX . "customer SET firstname = '" . $this->db->escape($merge_value) . "' WHERE email = '" . $this->db->escape($data['email']) . "'");
					$this->db->query("UPDATE " . DB_PREFIX . "address SET firstname = '" . $this->db->escape($merge_value) . "' WHERE address_id = " . (int)$customer_query->row['address_id']);
				} elseif (strpos($merge_mapping, ':lastname')) {
					$this->db->query("UPDATE " . DB_PREFIX . "customer SET lastname = '" . $this->db->escape($merge_value) . "' WHERE email = '" . $this->db->escape($data['email']) . "'");
					$this->db->query("UPDATE " . DB_PREFIX . "address SET lastname = '" . $this->db->escape($merge_value) . "' WHERE address_id = " . (int)$customer_query->row['address_id']);
				} elseif (strpos($merge_mapping, ':telephone')) {
					$this->db->query("UPDATE " . DB_PREFIX . "customer SET telephone = '" . $this->db->escape($merge_value) . "' WHERE email = '" . $this->db->escape($data['email']) . "'");
				} elseif (strpos($merge_mapping, ':address_id')) {
					$country_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE iso_code_2 = '" . $this->db->escape($merge_value['country']) . "'");
					$zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE (`name` = '" . $this->db->escape($merge_value['state']) . "' OR `code` = '" . $this->db->escape($merge_value['state']) . "') AND country_id = '" . $this->db->escape($country->row['country_id']) . "'");
					$this->db->query("
						UPDATE " . DB_PREFIX . "address SET
						address_1 = '" . $this->db->escape($merge_value['addr1']) . "',
						address_2 = '" . $this->db->escape($merge_value['addr2']) . "',
						city = '" . $this->db->escape($merge_value['city']) . "',
						zone_id = " . ($zone_query->num_rows ? (int)$zone->row['zone_id'] : 0) . ",
						postcode = '" . $this->db->escape($merge_value['zip']) . "',
						country_id = " . ($country_query->num_rows ? (int)$country->row['country_id'] : 0) . "
						WHERE address_id = " . (int)$customer_query->row['address_id'] . "
					");
				} else {
					$merge_mapping_split = explode(':', $merge_mapping);
					$this->db->query("UPDATE " . DB_PREFIX . $merge_mapping_split[0] . "customer SET `" . $merge_mapping_split[1] . "` = '" . $this->db->escape($merge_value) . "' WHERE email = '" . $this->db->escape($data['email']) . "'");
				}
			}
			
			$success = true;
			
		} elseif ($type == 'upemail' && in_array('profile', $webhooks)) {
			
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET email = '" . $this->db->escape($data['new_email']) . "' WHERE email = '" . $this->db->escape($data['old_email']) . "'");
			$success = true;
			
		}
		
		if ($settings['testing_mode'] && $success) {
			$this->log->write(strtoupper($this->name) . ' WEBHOOK SUCCESS: ' . $type . ' ' . $data['email'] . ' (List ID ' . $data['list_id'] . ')');
		}
	}
	
	private function createCustomer($data) {
		$settings = $this->getSettings();
		
		$merge_mapping = array();
		foreach ($settings as $key => $value) {
			if (strpos($key, $data['list_id']) !== 0) continue;
			if (strpos($value, ':firstname')) {
				$merge_mapping['firstname'] = str_replace($data['list_id'] . '_', '', $key);
			} elseif (strpos($value, ':lastname')) {
				$merge_mapping['lastname'] = str_replace($data['list_id'] . '_', '', $key);
			} elseif (strpos($value, ':telephone')) {
				$merge_mapping['telephone'] = str_replace($data['list_id'] . '_', '', $key);
			} elseif (strpos($value, ':address_id')) {
				$merge_mapping['address'] = str_replace($data['list_id'] . '_', '', $key);
			}
		}
		
		$customer = array(
			'status'			=> (int)($settings['autocreate'] == 2),
			'customer_group_id'	=> (!empty($data['customer_group_id']) ? $data['customer_group_id'] : $this->config->get('config_customer_group_id')),
			'email'				=> $data['email'],
			'firstname'			=> (!empty($data['merges'][$merge_mapping['firstname']]) ? $data['merges'][$merge_mapping['firstname']] : ''),
			'lastname'			=> (!empty($data['merges'][$merge_mapping['lastname']]) ? $data['merges'][$merge_mapping['lastname']] : ''),
			'telephone'			=> (!empty($data['merges'][$merge_mapping['telephone']]) ? $data['merges'][$merge_mapping['telephone']] : ''),
			'address'			=> (!empty($data['merges'][$merge_mapping['address']]) ? $data['merges'][$merge_mapping['address']] : array()),
			'ip'				=> $data['ip_opt'],
			'password'			=> rand(),
		);
		
		$this->db->query("
			INSERT INTO " . DB_PREFIX . "customer SET
			status = " . (int)$customer['status'] . ",
			approved = 1,
			newsletter = 1,
			customer_group_id = " . (int)$customer['customer_group_id'] . ",
			email = '" . $this->db->escape($customer['email']) . "',
			firstname = '" . $this->db->escape($customer['firstname']) . "',
			lastname = '" . $this->db->escape($customer['lastname']) . "',
			telephone = '" . $this->db->escape($customer['telephone']) . "',
			ip = '" . $this->db->escape($customer['ip']) . "',
			password = '" . $this->db->escape(md5($customer['password'])) . "',
			date_added = NOW()
		");
		
		if (!isset($customer['address']['addr1']))		$customer['address']['addr1']	= '';
		if (!isset($customer['address']['addr2']))		$customer['address']['addr2']	= '';
		if (!isset($customer['address']['city']))		$customer['address']['city']	= '';
		if (!isset($customer['address']['zip']))		$customer['address']['zip']	= '';
		if (!isset($customer['address']['country']))	$customer['address']['country']	= '';
		if (!isset($customer['address']['state']))		$customer['address']['state'] = '';
		
		$customer_id = $this->db->getLastId();
		$country_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE iso_code_2 = '" . $this->db->escape($customer['address']['country']) . "'");
		$country_id = ($country_query->num_rows) ? $country_query->row['country_id'] : 0;
		$zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE (`name` = '" . $this->db->escape($customer['address']['state']) . "' OR `code` = '" . $this->db->escape($customer['address']['state']) . "') AND country_id = " . (int)$country_id);
		$zone_id = ($zone_query->num_rows) ? $zone_query->row['zone_id'] : 0;
		
		$this->db->query("
			INSERT INTO " . DB_PREFIX . "address SET
			customer_id = " . (int)$customer_id . ",
			firstname = '" . $this->db->escape($customer['firstname']) . "',
			lastname = '" . $this->db->escape($customer['lastname']) . "',
			address_1 = '" . $this->db->escape($customer['address']['addr1']) . "',
			address_2 = '" . $this->db->escape($customer['address']['addr2']) . "',
			city = '" . $this->db->escape($customer['address']['city']) . "',
			zone_id = " . (int)$zone_id . ",
			postcode = '" . $this->db->escape($customer['address']['zip']) . "',
			country_id = " . (int)$country_id . "
		");
		
		$address_id = $this->db->getLastId();
		$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = " . (int)$address_id . " WHERE customer_id = " . (int)$customer_id);
		
		$language = (!empty($this->session->data['language'])) ? $this->session->data['language'] : $this->config->get('config_language');
		$email_subject = str_replace('[store]', $this->config->get('config_name'), $settings['emailtext_subject_' . $language]);
		$email_body = html_entity_decode(str_replace(array('[store]', '[password]'), array($this->config->get('config_name'), $customer['password']), $settings['emailtext_body_' . $language]), ENT_QUOTES, 'UTF-8');
		
		if ($settings['email_password'] && $customer['status']) {
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			
			$mail->setSubject($email_subject);
			$mail->setHtml($email_body);
			$mail->setSender(str_replace(array(',', '&'), array('', 'and'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setTo($customer['email']);
			$mail->send();
		}
		
		if ($settings['testing_mode']) {
			$this->log->write(strtoupper($this->name) . ' CUSTOMER CREATED: ' . $customer['firstname'] . ' ' . $customer['lastname'] . ' (' . $customer['email'] . ')');
		}
	}
	
	//==============================================================================
	// Private functions
	//==============================================================================
	private function getSettings() {
		$settings = array();
		$settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `" . (version_compare(VERSION, '2.0.1') < 0 ? 'group' : 'code') . "` = '" . $this->db->escape($this->name) . "' ORDER BY `key` ASC");
		
		foreach ($settings_query->rows as $setting) {
			$value = (is_string($setting['value']) && strpos($setting['value'], 'a:') === 0) ? unserialize($setting['value']) : $setting['value'];
			$split_key = preg_split('/_(\d+)_?/', str_replace($this->name . '_', '', $setting['key']), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			
			if (count($split_key) == 1) {
				$settings[$split_key[0]] = $value;
			} elseif (count($split_key) == 2) {
				$settings[$split_key[0]][$split_key[1]] = $value;
			} elseif (count($split_key) == 3) {
				$settings[$split_key[0]][$split_key[1]][$split_key[2]] = $value;
			} elseif (count($split_key) == 4) {
				$settings[$split_key[0]][$split_key[1]][$split_key[2]][$split_key[3]] = $value;
			} else {
				$settings[$split_key[0]][$split_key[1]][$split_key[2]][$split_key[3]][$split_key[4]] = $value;
			}
		}
		
		return $settings;
	}
	
	private function ruleViolation($rules, $rule, $value) {
		$violation = false;
		$function = (is_array($value)) ? 'array_intersect' : 'in_array';
		
		if (isset($rules[$rule]['after']) && strtotime($value) < min(array_map('strtotime', $rules[$rule]['after']))) {
			$violation = true;
			$comparison = 'after';
		}
		if (isset($rules[$rule]['before']) && strtotime($value) > max(array_map('strtotime', $rules[$rule]['before']))) {
			$violation = true;
			$comparison = 'before';
		}
		if (isset($rules[$rule]['is']) && !$function($value, $rules[$rule]['is'])) {
			$violation = true;
			$comparison = 'is';
		}
		if (isset($rules[$rule]['not']) && $function($value, $rules[$rule]['not'])) {
			$violation = true;
			$comparison = 'not';
		}
		
		if ($this->config->get($this->name . '_testing_mode') && $violation) {
			$this->log->write(strtoupper($this->name) . ': Mapping for list ID ' . $rules['list'] . ' ignored due to violating rule "' . $rule . ' ' . $comparison . ' ' . implode(', ', $rules[$rule][$comparison]) . '" with value "' . (is_array($value) ? implode(',', $value) : $value) . '"');
		}
		
		return $violation;
	}
	
	private function curlRequest($data = array()) {
		$data_center = explode('-', $data['apikey']);
		$url = 'https://' . (isset($data_center[1]) ? $data_center[1] : 'us1') . '.api.mailchimp.com/2.0/' . $data['method'];
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 300);
		$response = json_decode(curl_exec($curl), true);
		
		if (curl_error($curl)) {
			$response = array('code' => 'CURL (' . curl_errno($curl) . ')', 'error' => curl_error($curl), 'data' => array());
		} elseif (empty($response)) {
			$response = array('code' => 'CURL', 'error' => 'Empty gateway response', 'data' => array());
		} elseif (!empty($response['errors'])) {
			$response = $response['errors'][0];
			$response['data'] = array();
		} elseif (!empty($response['error'])) {
			$response['data'] = array();
		}
		curl_close($curl);
		
		if (!empty($response['error']) && $this->config->get($this->name . '_testing_mode')) {
			$this->log->write(strtoupper($this->name) . ' ERROR ' . $response['code'] . ': ' . $response['error']);
		}
		return $response;
	}
}
?>