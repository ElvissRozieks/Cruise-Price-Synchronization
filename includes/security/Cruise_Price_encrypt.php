<?php

namespace cruise\includes\security;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 *
 * @package    Cruise_Price
 * @subpackage Cruise_Price/includes
 */

class Cruise_Price_encrypt {


	/**
	 * Cipher method 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $chiper  The cipher method
	 */
	private static $cipher_algo = 'AES-256-CTR';

	/**
	 * Passphrase
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $encryption_key  AUTH_KEY from wp-config
	 */

	private static $key = AUTH_KEY;

	/**
	 * A non-NULL Initialization Vector.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $encryption_iv  AUTH_SALT from wp-config
	 */

	private static $iv = AUTH_SALT;

	/**
	 * Defines bitwise disjunction of the flags
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $options  bitwise disjunction of the flags
	 */
	
	private static $options = 0;

	/**
	 * Private Data
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $api_data  encryption and decryption
	 */

	private $api_data;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($encrypt_data) {
		$this->api_data = $encrypt_data;
	}

	private function iv_truncating() : string {
		return substr(self::$iv, 0, 16);
	}

	private function iv_length() : string{ 
		return openssl_cipher_iv_length(self::$cipher_algo); 
	}

	public function encryption() : string {
		return openssl_encrypt(
			$this->api_data,
			self::$cipher_algo, 
        	self::$key,
			self::$options,
			$this->iv_truncating()
		); 
	}

	public function decryption() : string {
		return openssl_decrypt (
			$this->api_data,
			self::$cipher_algo, 
        	self::$key,
			self::$options,
			$this->iv_truncating()
		); 
	}

}
