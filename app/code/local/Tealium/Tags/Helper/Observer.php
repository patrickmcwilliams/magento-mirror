<?php
class Tealium_Tags_Helper_Observer extends Mage_Core_Helper_Abstract
{
	public function __construct()
	{
	}
	
	public function apiHandler($observer)
	{
		if (isset($_REQUEST["tealium_api"]) && $_REQUEST["tealium_api"] == "true"){
			$response = $observer->getEvent()->getFront()->getResponse();
			$html = $response->getBody();
			preg_match('/\/\/TEALIUM_START(.*)\/\/TEALIUM_END/is', $html, $matches);
			$javaScript = "// Tealium Magento Callback API";
			$javaScript .= $matches[1];
			$response->setBody($javaScript);
		}

		return $this;
	}
}
?>