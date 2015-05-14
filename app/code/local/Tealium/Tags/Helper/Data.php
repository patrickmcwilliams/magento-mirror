<?php
require_once (Mage::getBaseDir ( 'lib' ) . '/Tealium/Tealium.php');

class Tealium_Tags_Helper_Data extends Mage_Core_Helper_Abstract {
	protected $tealium;
	protected $store;
	protected $page;
	public function init(&$store, &$page = array(), $pageType) {

		$account = $this->getAccount ( $store );
		$profile = $this->getProfile ( $store );
		$env = $this->getEnv ( $store );
		
		$data = array (
				"store" => $store,
				"page" => $page 
		);
		$this->store = $store;
		$this->page = $page;
		$this->tealium = new Tealium( $account, $profile, $env, $pageType, $data );
		
		return $this;
	}
	
	public function addCustomDataFromSetup(&$store, $pageType){
		$data = array (
				"store" => $this->store,
				"page" => $this->page
		);
		if (Mage::getStoreConfig ( 'tealium_tags/general/udo_enable', $store )) {
			include_once (Mage::getStoreConfig ( 'tealium_tags/general/udo', $store ));

			if ( method_exists($this, "getCustomUdo") ){
				$customUdoElements = getCustomUdo();
				if ( is_array($customUdoElements) && self::isAssocArray($customUdoElements) ){
					$udoElements = $customUdoElements;
				}
			}
			elseif (!isset($udoElements) || ( isset($udoElements) && !self::isAssocArray($udoElements) )){
				$udoElements = array();
			}
			
			if ( isset($udoElements[$pageType]) ){
				$this->tealium->setCustomUdo($udoElements[$pageType]);
			}
			
		}
		
		return $this;
	}
	
	public function addCustomDataFromObject($udoObject){
		if ( is_array($udoObject) && self::isAssocArray($udoObject) ){
			$this->tealium->updateUdo($udoObject);
		}
		return $this;
	}
	
	protected static function isAssocArray( $array) {
		$keys = array_keys($array);
		return array_keys($keys) !== $keys;
	}
	
	public function isEnabled($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/enable', $store );
	}
	public function enableOnePageCheckout($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/onepage', $store );
	}
	public function externalUdoEnabled($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/udo_enable', $store );
	}
	public function getTealiumBaseUrl($store) {
		$account = $this->getAccount ( $store );
		$profile = $this->getProfile ( $store );
		$env = $this->getEnv ( $store );
		return "//tags.tiqcdn.com/utag/$account/$profile/$env/utag.js";
	}
	public function getTealiumObject() {
		return $this->tealium;
	}
	public function getAccount($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/account', $store );
	}
	public function getProfile($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/profile', $store );
	}
	public function getEnv($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/env', $store );
	}
	public function getUDOPath($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/udo', $store );
	}
	public function getAPIEnabled($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/api_enable', $store );
	}
	public function getIsExternalScript($store) {
		return Mage::getStoreConfig ( 'tealium_tags/general/external_script', $store );
	}
	public function getExternalScriptType($store) {
		$async = Mage::getStoreConfig ( 'tealium_tags/general/external_script_type', $store );
		$scriptType = $async ? "async" : "sync";
		return $scriptType;
	}
	public function getDiagnosticTag($store) {
		if (Mage::getStoreConfig ( 'tealium_tags/general/diagnostic_enable', $store )) {
			$utag_data = urlencode ( $this->tealium->render ( "json" ) );
			$url = Mage::getStoreConfig ( 'tealium_tags/general/diagnostic_tag', $store ) . '?origin=server&user_agent=' . $_SERVER ['HTTP_USER_AGENT'] . '&data=' . $utag_data;
			return '<img src="' . $url . '" style="display:none"/>';
		}
		return "";
	}
}
	 