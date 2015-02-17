<?php 

class Tealium {
	
	private $account; 
	private $profile; 
	private $target;
	private $udo;
	private $udoElements;
	private $customUdo;
	
	public function __construct( $accountInit = false, 
								 $profileInit = false, 
								 $targetInit = false, 
								 $pageType = "Home", 
								 &$data = array()) {
		
		require_once 'TealiumInit.php';

		$this->udoElements = $udoElements;
		$this->account = $accountInit;
		$this->profile = $profileInit;
		$this->target = $targetInit;
		if( !($this->udo = $this->udoElements[$pageType]) && $pageType != null ){
			$this->udo = array( 'page_type' => $pageType );
		}
	}
	
	public function updateUdo($objectOrKey = "", $value = "") {
		
		$udoObject = $this->udo;
		if ($udoObject instanceof Closure) {
			$udo = $udoObject();
		} elseif (is_array ( $udoObject )) {
			$udo = $udoObject;
		} else {
			$udo = "{}";
		}
		
		if ($objectOrKey instanceof Closure) {
			$updatedUdo = $objectOrKey();
		} elseif (is_array ( $objectOrKey )) {
			$updatedUdo = $objectOrKey();
		} else {
			$updatedUdo = "{}";
		}

		if ( is_array( $updatedUdo ) ) {
			foreach ( $updatedUdo as $objectKey => $objectValue ) {
				$udo[$objectKey] = $objectValue;
			}
		} elseif ($objectOrKey != "") {
			$udo[$objectOrKey] = $value;
		}
		
		$this->udo = $udo;
		
		return $this->udo;
	}
	
	public function setCustomUdo($udo){
		$this->customUdo = $udo;
	}
	
	public function pageType($pageType = "Home") {
		if( !($this->udo = $this->udoElements[$pageType]) && $pageType != null ){
			$this->udo = array( 'page_type' => $pageType );
		}
	}
	public function render($type = null, $external = false, $sync = "sync") {
		
		if ( !($_REQUEST ["tealium_api"] && $_REQUEST ["tealium_api"] == "true") && $external) {
			$type = "udo";
			$is_async = ($sync == "sync") ? "" : "async";
			$udo = "<script type=\"text/javascript\" src=\"";
			$udo .= $_SERVER["REQUEST_URI"];
			if ($_SERVER["QUERY_STRING"]){
				$udo .= "&";
			}
			else {
				$udo .= "?";
			}
			$udo .= "tealium_api=true\" $is_async></script>";
		} 
		else {
			if (isset($this->customUdo)){
				$this->updateUdo($this->customUdo);
			}
			$udoObject = $this->udo;
			if ($udoObject instanceof Closure) {
				$udoJson = json_encode ( $udoObject() );
			} elseif (is_array ( $udoObject )) {
				$udoJson = json_encode ( $udoObject );
			} else {
				$udoJson = "{}";
			}
			$udoJs = "utag_data = $udoJson;";

			$udo = <<<EOD
<!-- Tealium Universal Data Object / Data Layer -->
<script type="text/javascript">
    $udoJs
</script>
<!-- ****************************************** -->
EOD;
		}
		
		// Render Tealium tag in javaScript
		$insert_tag = <<<EOD
    (function(a,b,c,d){
        a='//tags.tiqcdn.com/utag/$this->account/$this->profile/$this->target/utag.js';
        b=document;c='script';d=b.createElement(c);d.src=a;d.type='text/java'+c;d. 
        async=true;
        a=b.getElementsByTagName(c)[0];a.parentNode.insertBefore(d,a);
        })();
EOD;
		$tag = <<<EOD
<!-- Async Load of Tealium utag.js library -->
<script type="text/javascript">
    $insert_tag
</script>
<!-- ************************************* -->
EOD;
		if ($_REQUEST["tealium_api"] && $_REQUEST["tealium_api"] == "true") {
			$tag = $insert_tag . "\n//TEALIUM_END\n";
			$udo = "//TEALIUM_START\n" . $udoJs;
		}
		
		// Determine what code to return
		if ($this->account && $this->profile && $this->target) {
			if ($type == "tag") {
				$renderedCode = $tag;
			} elseif ($type == "udo") {
				$renderedCode = $udo;
			} elseif ($type == "json") {
				$renderedCode = $udoJson;
			} else {
				$renderedCode = $udo . "\n" . $tag;
			}
		} else {
			if ($this->udo != null) {
				$renderedCode = $udo;
			} else {
				// Render instructions if Tealium Object was not used correctly
				$renderedCode = <<<EOD
<!-- Tealium Universal Data Object / Data Layer -->
<!-- Account, profile, or environment was not declared in 
    object Tealium(\$account, \$profile, \$target, \$pageType) -->
EOD;
			}
		}
		
		return $renderedCode;
	}
}

// Open source alternative for json_encode for PHP < 5.4 ***********************************************
if (! function_exists ( 'json_encode' )) {
	function json_encode($a = false) {
		if (is_null ( $a ))
			return 'null';
		if ($a === false)
			return 'false';
		if ($a === true)
			return 'true';
		if (is_scalar ( $a )) {
			if (is_float ( $a )) {
				// Always use "." for floats.
				return floatval ( str_replace ( ",", ".", strval ( $a ) ) );
			}
			if (is_string ( $a )) {
				static $jsonReplaces = array (
						array (
								"\\",
								"/",
								"\n",
								"\t",
								"\r",
								"\b",
								"\f",
								'"' 
						),
						array (
								'\\\\',
								'\\/',
								'\\n',
								'\\t',
								'\\r',
								'\\b',
								'\\f',
								'\"' 
						) 
				);
				return '"' . str_replace ( $jsonReplaces [0], $jsonReplaces [1], $a ) . '"';
			} else
				return $a;
		}
		$isList = true;
		for($i = 0, reset ( $a ); $i < count ( $a ); $i ++, next ( $a )) {
			if (key ( $a ) !== $i) {
				$isList = false;
				break;
			}
		}
		$result = array ();
		if ($isList) {
			foreach ( $a as $v )
				$result [] = json_encode ( $v );
			return '[' . join ( ',', $result ) . ']';
		} else {
			foreach ( $a as $k => $v )
				$result [] = json_encode ( $k ) . ':' . json_encode ( $v );
			return '{' . join ( ',', $result ) . '}';
		}
	}
}
// ***********************************************************************************************************

?>

