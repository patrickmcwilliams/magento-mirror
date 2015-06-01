<?php
// TealiumInit.php var definition file
// Replace $STRING or $ARRAY with your server side variable reference unique to that key 

class TealiumData {
	private static $store;
	private static $page;
	
	public static function setStore($store){
		TealiumData::$store = $store;
	}
	
	public static function setPage($page){
		TealiumData::$page = $page;
	}
	
	public function getHome(){
		$store = TealiumData::$store;
		$page = TealiumData::$page;
		
		$outputArray = array();
		$outputArray['site_region'] = Mage::app()->getLocale()->getLocaleCode() ?: "";
		$outputArray['site_currency'] = $store->getCurrentCurrencyCode() ?: "";
		$outputArray['page_name'] = $page->getLayout()->getBlock('head')->getTitle() ?: "";
		$outputArray['page_type'] = $page->getTealiumType() ?: "";
		
		return $outputArray;
	}
	
	public function getSearch(){
		$store = TealiumData::$store;
		$page = TealiumData::$page;
		
		$outputArray = array();
	    $outputArray['site_region'] = Mage::app()->getLocale()->getLocaleCode() ?: "";
	    $outputArray['site_currency'] = $store->getCurrentCurrencyCode() ?: ""; 
	    $outputArray['page_name'] = "search results";
	    $outputArray['page_type'] = "search";
	    $outputArray['search_results'] = $page->getResultCount() . "" ?: "";
	    $outputArray['search_keyword'] = $page->helper('catalogsearch')->getEscapedQueryText() ?: "";

		return $outputArray;
	}
	
	public function getCategory(){
		$store = TealiumData::$store;
		$page = TealiumData::$page;
		
		if ($page->getCurrentCategory()) {
			$_category   = $page->getCurrentCategory();
			$parent      = false;
			$grandparent = false;
		
			// check for parent and grandparent
			if ($_category->getParentId()) {
				$parent = Mage::getModel('catalog/category')->load($_category->getParentId());
		
				if ($parent->getParentId()) {
					$grandparent = Mage::getModel('catalog/category')->load($parent->getParentId());
				}
			}
		
			// Set the section and subcategory with parent and grandparent
			if ($grandparent) {
				$section     = $grandparent->getName();
				$category    = $parent->getName();
				$subcategory = $_category->getName();
			} elseif ($parent) {
				$section  = $parent->getName();
				$category = $_category->getName();
			} else {
				$category = $_category->getName();
			}
		}
		
		$outputArray = array();
		$outputArray['site_region'] = Mage::app()->getLocale()->getLocaleCode() ?: "";
		$outputArray['site_currency'] = $store->getCurrentCurrencyCode() ?: "";
		$outputArray['page_name'] = isset($_category) ? ($_category->getName() ?: "") : "";
		$outputArray['page_type'] = "category";
		$outputArray['page_section_name'] = isset($section) ?: "";
		$outputArray['page_category_name'] = isset($category) ?: "";
		$outputArray['page_subcategory_name'] = isset($subcategory) ?: "";
		
		return $outputArray;
	}
	
	public function getProductPage(){
		$store = TealiumData::$store;
		$page = TealiumData::$page;
		
		$outputArray = array();
		$outputArray['site_region'] = Mage::app()->getLocale()->getLocaleCode() ?: "";
		$outputArray['site_currency'] = $store->getCurrentCurrencyCode() ?: "";
		$outputArray['page_name'] = $page->getProduct() ? ($page->getProduct()->getName() ?: "") : "";
		$outputArray['page_type'] = "product";
		
		// THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
		if ( $page->getProduct() ) {
			if ( !($outputArray['product_id'] = array( $page->getProduct()->getId() )) ){
				$outputArray['product_id'] = array();
			}
			if ( !($outputArray['product_sku'] = array( $page->getProduct()->getSku() )) ){
				$outputArray['product_sku'] = array();
			}
			if ( !($outputArray['product_name'] = array( $page->getProduct()->getName() )) ){
				$outputArray['product_name'] = array();
			}
			if ( !($outputArray['product_brand'] = array( $page->getProduct()->getBrand() )) ){
				$outputArray['product_brand'] = array();
			}
			if ( !($outputArray['product_unit_price'] = array( number_format($page->getProduct()->getFinalPrice(), 2) )) ){
				$outputArray['product_unit_price'] = array();
			}
			if ( !($outputArray['product_list_price'] = array( number_format($page->getProduct()->getPrice(), 2) )) ){
				$outputArray['product_list_price'] = array();
			}
		}
		else {
			$outputArray['product_id'] = array();
			$outputArray['product_sku'] = array();
			$outputArray['product_name'] = array();
			$outputArray['product_brand'] = array();
			$outputArray['product_unit_price'] = array();
			$outputArray['product_list_price'] = array();
		}

		$outputArray['product_price'] = $outputArray['product_unit_price'];
		$outputArray['product_original_price'] = $outputArray['product_list_price'];

		if ( Mage::registry('current_category') ){
			if ( Mage::registry('current_category')->getName() ){
				$outputArray['product_category'] = array(Mage::registry('current_category')->getName());
			}
			else {
				$outputArray['product_category'] = array();
			}
		}
		else {
			$outputArray['product_category'] = array();
		}
		
		return $outputArray;
	}
	
	public function getCartPage() {
		$store = TealiumData::$store;
		$page = TealiumData::$page;
		
		if (Mage::helper('checkout')) {
			$quote = Mage::helper('checkout')->getQuote();
			foreach ($quote->getAllVisibleItems() as $item) {
				$checkout_ids[]    = $item->getProductId();
				$checkout_skus[]   = $item->getSku();
				$checkout_names[]  = $item->getName();
				$checkout_qtys[]   = number_format($item->getQty(), 0, ".", "");
				$checkout_prices[] = number_format($item->getPrice(), 2, ".", "");
				$checkout_original_prices[] = number_format($item->getProduct()->getPrice(), 2, ".", "");
				$checkout_brands[] = $item->getProduct()->getBrand();
			}
		}
		
		$outputArray = array();
		$outputArray['site_region'] = Mage::app()->getLocale()->getLocaleCode() ?: "";
		$outputArray['site_currency'] = $store->getCurrentCurrencyCode() ?: "";
		$outputArray['page_name'] = $page->getLayout()->getBlock('head')->getTitle() ?: "";
		$outputArray['page_type'] = "checkout";

		// THE FOLLOWING NEEDS TO BE MATCHED ARRAYS (SAME NUMBER OF ELEMENTS)
		$outputArray['product_id'] = isset($checkout_ids) ?: array();
		$outputArray['product_sku'] = isset($checkout_skus) ?: array();
		$outputArray['product_name'] = isset($checkout_names) ?: array();
		$outputArray['product_brand'] = isset($checkout_brands) ?: array();
		$outputArray['product_category'] = array();
		$outputArray['product_quantity'] = isset($checkout_qtys) ?: array();
		$outputArray['product_unit_price'] = isset($checkout_prices) ?: array();
		$outputArray['product_list_price'] = isset($checkout_original_prices) ?: array();

		$outputArray['product_price'] = $outputArray['product_unit_price'];
		$outputArray['product_original_price'] = $outputArray['product_list_price'];
		
		return $outputArray;
	}
	
	public function getOrderConfirmation(){
		$store = TealiumData::$store;
		$page = TealiumData::$page;

		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer       = Mage::getSingleton('customer/session')->getCustomer();
			$customer_id    = $customer->getEntityId();
			$customer_email = $customer->getEmail();
			$groupId        = $customer->getGroupId();
			$customer_type  = Mage::getModel('customer/group')->load($groupId)->getCode();
		}
		
		if (Mage::getModel('sales/order')) {
			$order = Mage::getModel('sales/order')->loadByIncrementId($page->getOrderId());
            $order_id = $order->getIncrementId();
            $order_discount = number_format($order->getDiscountAmount(), 2, ".", "");
            $order_subtotal = number_format($order->getSubtotal(), 2, ".", "");
            $order_shipping = number_format($order->getShippingAmount(), 2, ".", "");
            $order_tax = number_format($order->getTaxAmount(), 2, ".", "");
            $order_payment_type = $order->getPayment() ? $order->getPayment()->getMethodInstance()->getTitle() : 'unknown';
            $order_currency = $order->getOrderCurrencyCode();
            $order_total = number_format($order->getGrandTotal(), 2, ".", "") ?: "";

			foreach ($order->getAllVisibleItems() as $item) {
		
				$ids[]           = $item->getProductId();
				$skus[]          = $item->getSku();
				$names[]         = $item->getName();
				$qtys[]          = number_format($item->getQtyOrdered(), 0, ".", "");
				$prices[]        = number_format($item->getPrice(), 2, ".", "");
				$original_prices[] = number_format($item->getProduct()->getPrice(), 2, ".", "");
				$discount        = number_format($item->getDiscountAmount(), 2, ".", "");
				$discounts[]     = $discount;
				$applied_rules   = explode(",", $item->getAppliedRuleIds());
				$discount_object = array();
				$brands[]	 = $item->getProduct()->getBrand();
				foreach ($applied_rules as $rule) {
					$quantity          = number_format(Mage::getModel('salesrule/rule')->load($rule)->getDiscountQty(), 0, ".", "");
					$amount            = number_format(Mage::getModel('salesrule/rule')->load($rule)->getDiscountAmount(), 2, ".", "");
					$type              = Mage::getModel('salesrule/rule')->load($rule)->getSimpleAction();
					$discount_object[] = array("rule"		=>$rule,
							"quantity"	=>$quantity,
							"amount"	=>$amount,
							"type"		=>$type);
				}
				$discount_quantity[] = array("product_id" => $item->getProductId(),
						"total_discount"	=> $discount,
						"discounts"		=> $discount_object);
		
			}
		}
		
		$outputArray = array();

		$outputArray['site_region'] =  Mage::app()->getLocale()->getLocaleCode() ?: "";
        $outputArray['site_currency'] =  $store->getCurrentCurrencyCode() ?: "";
        $outputArray['page_name'] =  "cart success";   
        $outputArray['page_type'] =  "cart";
		$outputArray['order_id'] = isset($order_id) ? $order_id : "";
		$outputArray['order_discount'] = isset($order_discount) ? $order_discount : "";
		$outputArray['order_subtotal'] = isset($order_subtotal) ? $order_subtotal : "";
		$outputArray['order_shipping'] = isset($order_shipping) ? $order_shipping : "";
		$outputArray['order_tax'] = isset($order_tax) ? $order_tax : "";
		$outputArray['order_payment_type'] = isset($order_payment_type) ? $order_payment_type : 'unknown';
		$outputArray['order_total'] = isset($order_total) ? $order_total : "";
		$outputArray['order_currency'] = isset($order_currency) ? $order_currency : "";
		$outputArray['customer_id'] = isset($customer_id) ? $customer_id : "";
		$outputArray['customer_email'] = isset($customer_email) ? $customer_email : "";
		$outputArray['product_id'] = isset($ids) ? $ids : array();
		$outputArray['product_sku'] = isset($skus) ? $skus : array();
		$outputArray['product_name'] = isset($names) ? $names : array();
		$outputArray['product_brand'] = isset($brands) ? $brands : array();
		$outputArray['product_category'] = array();
		$outputArray['product_unit_price'] = isset($prices) ? $prices : array();
		$outputArray['product_list_price'] = isset($original_prices) ? $original_prices : array();
		$outputArray['product_price'] = $outputArray['product_unit_price'];
		$outputArray['product_original_price'] = $outputArray['product_list_price'];
		$outputArray['product_quantity'] = isset($qtys) ? $qtys : array();
		$outputArray['product_discount'] = isset($discounts) ? $discounts: array();
		$outputArray['product_discounts'] = isset($discount_quantity) ? $discount_quantity : array();
		
		return  $outputArray;
	}
	
	public function getCustomerData(){
		$store = TealiumData::$store;
		$page = TealiumData::$page;
		
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer       = Mage::getSingleton('customer/session')->getCustomer();
			$customer_id    = $customer->getEntityId();
			$customer_email = $customer->getEmail();
			$groupId        = $customer->getGroupId();
			$customer_type  = Mage::getModel('customer/group')->load($groupId)->getCode();
		}
		
		$outputArray = array();

		$outputArray['site_region'] = Mage::app()->getLocale()->getLocaleCode() ?: "";
		$outputArray['site_currency'] = $store->getCurrentCurrencyCode() ?: "";
		$outputArray['page_name'] = $page->getLayout()->getBlock('head')->getTitle() ?: "";
		$outputArray['page_type'] = $page->getTealiumType() ?: "";
		$outputArray['customer_id'] = isset($customer_id) ? $customer_id : "";
		$outputArray['customer_email'] = isset($customer_email) ? $customer_email : "";
		$outputArray['customer_type'] = isset($customer_type) ? $customer_type : "";
		
		return $outputArray;
	}
}


TealiumData::setStore($data["store"]);
TealiumData::setPage($data["page"]);


$udoElements = array(
    'Home' => function(){
    	$tealiumData = new TealiumData();
    	return $tealiumData->getHome();
    },
    'Search' => function(){
    	$tealiumData = new TealiumData();
    	return $tealiumData->getSearch();
    },
    'Category' => function(){
    	$tealiumData = new TealiumData();
    	return $tealiumData->getCategory();
    },
    'ProductPage' => function(){
    	$tealiumData = new TealiumData();
    	return $tealiumData->getProductPage();
    },
    'Cart' => function(){
    	$tealiumData = new TealiumData();
    	return $tealiumData->getCartPage();
    },
    'Confirmation' => function(){
    	$tealiumData = new TealiumData();
    	return $tealiumData->getOrderConfirmation();
    },
    'Customer' => function(){
    	$tealiumData = new TealiumData();
    	return $tealiumData->getCustomerData();
    }
);


?>