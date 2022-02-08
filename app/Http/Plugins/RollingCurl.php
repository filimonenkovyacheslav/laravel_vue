<?php

namespace App\Http\Plugins;
use \Exception;
use BaseParser;

class RollingCurl {

	private $windowSize = 30;
	private $timeout = 10;
	private $callback;
	private $master = null;
	protected $options = array(
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		//CURLOPT_VERBOSE => 1,
		//CURLOPT_HEADER => 1,
		CURLOPT_CONNECTTIMEOUT => 30,
		CURLOPT_TIMEOUT => 30
	);
	private $headers = array();
	private $requests = array();
	private $requestMap = array();

	private static $aliveProxy = [];
	private static $maxAlive = null;
	private $badProxies = [];
	private $proxies = [];
	private $useragents = [];
	
	private $nProxies = 0;
	private $nUseragents = 0;
	private $lastProxy = -1;
    private static $hostIp = null;
	
	private $proxyTestUrl = 'https://www.google.com/';
	private static $proxyValidRegexp = 'title>G[o]{2}gle';
	//private $proxyTestUrl = 'https://www.yelp.com/';
	//private static $proxyValidRegexp = 'title>Restaurants';
	
	private $useProxyList = false;
	private $useUseragentList = false;

	function __construct($callback = null) {
		$this->callback = $callback;
	}

	public function getAliveProxies()
    {
        return $this->proxies;
    }

    public function setAliveProxies($proxies)
    {
		$this->proxies = $proxies;
		$this->nProxies = count($this->proxies);
		$this->useProxyList = true;
    }

	public function setCookie($cookie)
    {
        $this->options[CURLOPT_COOKIE] = $cookie;
    }
    
    public function setAuth($auth)
    {
        $this->options[CURLOPT_PROXYUSERPWD] = $auth;
    }
    
    public function setReferer($url)
    {
        $this->options[CURLOPT_REFERER] = $url;
    }
    
    public function setHostIp($ip)
    {
        static::$hostIp = $ip;
    }

	public function setUserAgents($userAgents) {
		$this->useragents = $userAgents;
		$this->nUseragents = count($this->useragents);

		if($this->nUseragents > 0) {
			$this->useUseragentList = true;
		}
	}

	public function setProxies($proxies, $proxyType = 'http', $proxyTestUrl = null, $proxyValidRegexp = null, $maxAlive = null) {

		//$proxyType = 'socks5';
		$this->proxies = $proxies;
		if($proxyType == 'socks5') {
			$this->__set('options', [CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5]);
		}

		$this->nProxies = count($this->proxies);
		if($this->nProxies > 0)	{
			$this->proxies = array_values(array_unique($this->proxies));

			$this->nProxies = count($this->proxies);

			if(!is_null($proxyTestUrl)) {
				$this->proxyTestUrl = $proxyTestUrl;
			}
			if(!is_null($proxyValidRegexp)) {
				static::$proxyValidRegexp = $proxyValidRegexp;
			}

			if($this->windowSize >= 1)
			{
				echo 'check proxies: '.$this->nProxies.PHP_EOL;
				static::$aliveProxy = [];
				static::$maxAlive = $maxAlive;
				$buffCallbackFunc = $this->__get('callback');

				$this->__set('callback', ['RollingCurl', 'callbackProxyCheck']);

				foreach($this->proxies as $id => $proxy) {
					if(strlen($proxy) > 4) {
						$this->request($this->proxyTestUrl, "GET", null, null, [CURLOPT_PROXY => $proxy, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => 15, CURLOPT_RETURNTRANSFER => 1]);
					}
				}
				$this->execute($this->windowSize);

				$this->__set('requests', []);
				$this->__set('requestMap', []);

				$this->nProxies = count(static::$aliveProxy);
				$this->proxies = static::$aliveProxy;
				$this->__set('callback', $buffCallbackFunc);
			}
			echo 'alive proxies: '.$this->nProxies.PHP_EOL;
			BaseParser::saveAliveProxies($this->proxies);

			if($this->nProxies > 0) {
				$this->useProxyList = true;
			}
		}
	}
    
    public static function callbackProxyCheck($response, $info, $request) {
        echo $request->options[CURLOPT_PROXY].'|'.$info['http_code'].PHP_EOL;
        if($info['http_code'] !== 200) return true;
        if(!empty(static::$proxyValidRegexp) && !@preg_match('#'.static::$proxyValidRegexp.'#', $response)) return true;
        echo 'proxyOK'.PHP_EOL;
        
        $ch = curl_init();
        $options = $request->options;
        $options[CURLOPT_URL] = 'http://httpbin.org/ip';
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        echo $output;
        
        if(!is_null(static::$hostIp) && strpos($output, static::$hostIp) === false) {
            static::$aliveProxy[] = $request->options[CURLOPT_PROXY];
            $cnt = sizeof(static::$aliveProxy);
            echo 'ipOK '.$cnt.PHP_EOL;
            
            if(!is_null(static::$maxAlive) && $cnt >= static::$maxAlive) return false;
        }
        return true;
    }

	public function setBadProxy($proxy, $delete = false) {
		if(isset($this->badProxies[$proxy])) {
			$this->badProxies[$proxy]++;
		} else {
			$this->badProxies[$proxy] = 1;
		}

		if($this->badProxies[$proxy] > 2 || $delete) {
			$proxies = $this->proxies;
			$index = array_search($proxy, $proxies);
			echo ' badProxy!';
			if($index !== false) {
				unset($proxies[$index]);
				$this->proxies = array_values($proxies);
				$this->nProxies = count($this->proxies);
				BaseParser::saveAliveProxies($this->proxies);

				if($this->useUseragentList && ($this->windowSize * 2) > $this->nProxies) {
					BaseParser::setError('Too few good proxies. Count: '.$this->nProxies);
				}
			}
		}
	}

	public function resetBadProxy($proxy) {
		if(isset($this->badProxies[$proxy])) {
			echo ' RESETProxy!';
			unset($this->badProxies[$proxy]);
		}
	}

	public function __get($name) {
		return (isset($this->{$name})) ? $this->{$name} : null;
	}

	public function __set($name, $value) {
		if($name == "options" || $name == "headers") {
			$this->{$name} = $value + $this->{$name};
		} else {
			$this->{$name} = $value;
		}
		return true;
	}

	public function add($request) {
		$this->requests[] = $request;
		return true;
	}

	public function request($url, $method = "GET", $postData = null, $headers = null, $options = null, $params = null) {
		$this->requests[] = new RollingCurlRequest($url, $method, $postData, $headers, $options, $params);
		return true;
	}

	public function get($url, $headers = null, $options = null, $params = null) {
		return $this->request($url, "GET", null, $headers, $options, $params);
	}

	public function post($url, $postData = null, $headers = null, $options = null, $params = null) {
		return $this->request($url, "POST", $postData, $headers, $options, $params);
	}

	public function execute($windowSize = null, $checkProxies = true) {
		if (is_null($windowSize) && sizeof($this->requests) == 1) {
			return $this->singleCurl();
		} else {
			if($this->nProxies < 5 && ($windowSize * 2) > $this->nProxies && $checkProxies) {
				BaseParser::saveAliveProxies($this->proxies);
				BaseParser::setError('Too few good proxies. Size: '.$windowSize.' Count: '.$this->nProxies);
				return false;
			}
			$this->windowSize = $windowSize;
			return $this->rollingCurl($windowSize);
		}
	}

	private function singleCurl() {
		$ch = curl_init();
		$request = array_shift($this->requests);
		$options = $this->get_options($request);
		curl_setopt_array($ch, $options);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);

		// it's not neccesary to set a callback for one-off requests
		if ($this->callback) {
			$callback = $this->callback;
			if (is_callable($this->callback)) {
				call_user_func($callback, $output, $info, $request);
			}
		}
		else
			return $output;
		return true;
	}

	private function rollingCurl($windowSize = null) {
		if($windowSize)	$this->windowSize = $windowSize;

		// make sure the rolling window isn't greater than the # of urls
		//if (sizeof($this->requests) < $this->window_size)
		//	$this->window_size = sizeof($this->requests);

		//if ($this->window_size < 2) {
		//	throw new RollingCurlException("Window size must be greater than 1");
		//}

		$result = true;
		$this->requestMap = [];
		$this->master = curl_multi_init();

		for($i = 0; $i < $this->windowSize; $i++) {
			if(!isset($this->requests[$i])) break;

			$ch = curl_init();

			$options = $this->getOptions($this->requests[$i]);

			curl_setopt_array($ch, $options);
			curl_multi_add_handle($this->master, $ch);

			// Add to our request Maps
			$key = (string) $ch;
			$this->requestMap[$key] = $i;
		}

		do {
			while(($execrun = curl_multi_exec($this->master, $running)) == CURLM_CALL_MULTI_PERFORM);
			if($execrun != CURLM_OK) break;
			//sleep(1);

			// a request was just completed -- find out which one
			while($done = curl_multi_info_read($this->master)) {
				// get the info and content returned on the request
				$info = curl_getinfo($done['handle']);
				$output = curl_multi_getcontent($done['handle']);

				// send the return values to the callback function.
				$callback = $this->callback;

				if(is_callable($callback)) {
					$key = (string) $done['handle'];
					$request = $this->requests[$this->requestMap[$key]];
					unset($this->requestMap[$key]);
					$result = call_user_func($callback, $output, $info, $request);
					if(!$result) {
						$running = false;
						break;
					}
				}
				
				// start a new request (it's important to do this before removing the old one)
				if($i < sizeof($this->requests) && count($this->requestMap) < $this->windowSize) {
					do {
						if(!isset($this->requests[$i])) break;
						$ch = curl_init();
						$options = $this->getOptions($this->requests[$i]);
						curl_setopt_array($ch, $options);
						curl_multi_add_handle($this->master, $ch);

						// Add to our request Maps
						$key = (string) $ch;
						$this->requestMap[$key] = $i;
						$i++;
					} while (count($this->requestMap) < $this->windowSize && $i < sizeof($this->requests));
					$running = true;
				}
				// remove the curl handle that just completed
				curl_multi_remove_handle($this->master, $done['handle']);

			}

			// Block for data in / output; error handling is done by curl_multi_exec
			if($running) curl_multi_select($this->master, $this->timeout);

		} while ($running);

		$this->closeMaster();
		return $result;
	}

	public function closeMaster() {
		if(!is_null($this->master)) curl_multi_close($this->master);
		$this->master = null;
	}


	private function getOptions($request) {
		// options for this entire curl object
		$options = $request->options;
		$default = $this->__get('options');
		foreach($default as $key => $value) {
			if(!isset($options[$key])) {
				$options[$key] = $value;
			}
		}

		if (ini_get('safe_mode') == 'Off' || !ini_get('safe_mode')) {
			$options[CURLOPT_FOLLOWLOCATION] = 1;
			$options[CURLOPT_MAXREDIRS] = 5;
		}
		
		if($this->useProxyList)
		{
			$this->lastProxy++;
			if($this->lastProxy >= $this->nProxies) {
				$this->lastProxy = 0;
			}
			$options[CURLOPT_PROXY] = $this->proxies[$this->lastProxy];
			//$options[CURLOPT_PROXY] = $this->proxies[mt_rand(0, $this->nProxies - 1)];
		}
		
		if($this->useUseragentList)
		{
			$options[CURLOPT_USERAGENT] = $this->useragents[mt_rand(0, $this->nUseragents - 1)];
		}

		$request->options = $options;

		$headers = $this->__get('headers');

		// set the request URL
		$options[CURLOPT_URL] = $request->url;

		// posting data w/ this request?
		if ($request->postData) {
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_POSTFIELDS] = $request->postData;
		}
		if ($headers) {
			$options[CURLOPT_HEADER] = 0;
			$options[CURLOPT_HTTPHEADER] = $headers;
		}

		return $options;
	}

	public function __destruct() {
		$this->closeMaster();
		unset($this->windowSize, $this->callback, $this->options, $this->headers, $this->requests);
	}
}

class RollingCurlRequest {
	public $url = false;
	public $method = 'GET';
	public $postData = null;
	public $headers = null;
	public $options = null;
	public $params = null;
	public $repeat = 0;

	function __construct($url, $method = "GET", $postData = null, $headers = null, $options = null, $params = null) {
		$this->url = $url;
		$this->method = $method;
		$this->postData = $postData;
		$this->headers = $headers;
		$this->options = $options;
		$this->params = $params;
	}

	/**
	 * @return void
	 */
	public function __destruct() {
		unset($this->url, $this->method, $this->postData, $this->headers, $this->options, $this->params, $this->repeat);
	}
}

/**
 * RollingCurl custom exception
 */
class RollingCurlException extends Exception {
}

