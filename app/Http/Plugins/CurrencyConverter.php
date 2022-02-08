<?php
namespace App\Http\Plugins;

use Akaunting\Money\Currency;
use Money;
use Rate;

class CurrencyConverter
{
	private static $checkRate = false;
	public static function convertCurrency($price, $fromCurrency, $toCurrency, $return = '')
	{
		$fromCurrency = is_string($fromCurrency) ? $fromCurrency : $fromCurrency->getCurrency();
		$toCurrency = is_string($toCurrency) ? $toCurrency : $toCurrency->getCurrency();

		$priceToConvert = $price * 100;
		$localMoney = Money::$fromCurrency($priceToConvert);
		$rate = CurrencyConverter::getCurrencyRate($fromCurrency, $toCurrency);
		$result = $localMoney->convert(Currency::$toCurrency(), $rate);

		switch($return) {
			case 'format':
				$result = $result->format();
				break;
			default:
				$result = $result->getValue();
				break;
		}
		return $result;
	}

	public static function getCurrencyRate($fromCurrency, $toCurrency)
	{
		if(static::$checkRate) {
			$rate = Rate::getDateRate($fromCurrency, $toCurrency);
		} else {
			//http://free.currencyconverterapi.com/api/v3/convert?q=EUR_USD&compact=y
			$queryStr = sprintf("%s_%s", $fromCurrency, $toCurrency);
			$url = "http://free.currencyconverterapi.com/api/v3/convert?q={$queryStr}&compact=y&apiKey=" . config('app')['currency_converter_api'];
			$res = function_exists('curl_init') ? self::_fileGetContentsCurl($url) : file_get_contents($url);
			$currencyData = json_decode($res, true);
			$rate = !empty($currencyData[$queryStr]['val']) ? $currencyData[$queryStr]['val'] : 0;
			//$rate = 0;
			if(empty($rate)) {
				$rate = Rate::getDateRate($fromCurrency, $toCurrency);
			} else {
				Rate::setDateRate($fromCurrency, $toCurrency, $rate);
			}
			static::$checkRate = true;
		}
		return $rate;
	}

	public static function getCurrencyByCode($code)
	{
		$currencies = Currency::getCurrencies();

		foreach($currencies as $k => $v) {
			if($v['code'] == $code) {
				return Currency::$k();
			}
		}
		return null;
	}

	public static function getLocalCurrency()
	{
		$currency = Currency::USD();

		if(config('app')['localization_type'] == 1) {
			$currentLocale = CustomLaravelLocalization::getCurrentLocale();
			$currentLocale = $currentLocale == 'en' ? 'com' : $currentLocale;
			$domains = config('domain-zones');

			foreach($domains as $k => $v) {
				if($currentLocale == $v['locale']) {
					$currency = static::getCurrencyByCode(array_shift($v['currency_code']));
				}
			}
		} else {
			$domainData = CustomLaravelLocalization::getDomainData();
			$currency = static::getCurrencyByCode(array_shift($domainData['currency_code']));
		}
		return $currency;
	}

	public static function getCurrenciesForSelect()
	{
		$defaultCurrency = Currency::USD();
		$localCurrency = CurrencyConverter::getLocalCurrency();
		if (is_null($localCurrency)) {
			$localCurrency = $defaultCurrency;
		}
		$list = [
			$defaultCurrency->getCode() => $defaultCurrency->getName(),
			$localCurrency->getCode() => $localCurrency->getName(),
		];
		return $list;
	}

	private static function _fileGetContentsCurl($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

		$data = curl_exec($ch);

		curl_close($ch);

		return $data;
	}
}