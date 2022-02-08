<?php
namespace App\Http\Plugins;

use LaravelLocalization;
use PropertyMeasures;

class CustomLaravelLocalization
{
	private static $cookieLang = null;
	private static $defaultLang = 37;

	public static function setLocaleLL($locale = null) {
		if(!empty($locale)) {
			return LaravelLocalization::setLocale($locale);
		} elseif(!is_null(static::$cookieLang)) {
			return LaravelLocalization::setLocale(static::$cookieLang);
		} else {
			return LaravelLocalization::setLocale();
		}
	}

	public static function getLocalizedURL($locale = null, $url = null, $attributes = [], $forceDefaultLocation = false) {
		return LaravelLocalization::getLocalizedURL($locale, $url, $attributes, $forceDefaultLocation);
	}

	public static function getSupportedLocales() {
		return LaravelLocalization::getSupportedLocales();
	}
	public static function isSupportedLocale($locale)
	{
		$locales = static::getSupportedLocales();
		if(isset($locales[$locale])) {
			if(config('app')['localization_type'] == 1) {
				return LaravelLocalization::getCurrentLocale() == $locale;
			} else {
				$domain = static::getDomainData();
				if(!is_null($domain)) {
					return in_array($locales[$locale]['code'], $domain['lang_code']);
				}
			}
		}
		return false;
	}

	public static function getCurrentLocale($request = null)
	{
		$lang = $request ? $request->cookie('lang') : static::$cookieLang;
        if(isset($lang) && !empty($lang) && CustomLaravelLocalization::isSupportedLocale($lang)) {
        	static::$cookieLang = $lang;
            return $lang;
        } else {
            return (config('app')['localization_type'] == 1 ? LaravelLocalization::getCurrentLocale() : static::getLocaleFromDomain());
        }
	}

	public static function getLocaleFromDomain($domain = null)
	{
		$locale = 'en';
		$domainData = static::getDomainData($domain);
		if(!is_null($domainData)) {
			$langCodes = $domainData['lang_code'];

			foreach($langCodes as $code) {
				$localeName = static::getLocaleByCode($code);

				if(!empty($localeName)) {
					$locale = $localeName;
					break;
				}
			}
		}
		return $locale;
	}

	public static function getLocalesForDomain($domain = null)
	{
		$supportedLocales = static::getSupportedLocales();
		$current = static::getCurrentLocale();

		if(is_null($current)) return [];
		$locales = [$current => $supportedLocales[$current]['native']];

		if(config('app')['localization_type'] == 1) return $locales;
		
		$domainData = static::getDomainData($domain);
		if(!is_null($domainData)) {
			$langCodes = $domainData['lang_code'];

			foreach($langCodes as $code) {
				foreach($supportedLocales as $k => $v) {
					if($v['code'] == $code) {
						$locales[$k] = $v['native'];
					}
				}
			}
		}
		return $locales;
	}

	public static function getDomainData($domain = null)
	{
		$domain = !empty($domain) ? $domain : (!empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
		$domains = config('domain-zones');
		$domainLocale = 'com';

		if(!empty($domain)) {
			preg_match('/\.([a-z]+)$/', $domain, $matches);
			$domainLocale = !empty($matches[1]) ? $matches[1] : $domainLocale;
		}
		foreach($domains as $k => $v) {
			if($domainLocale == $v['locale']) {
				if(!in_array(static::$defaultLang, $v['lang_code'])) {
					$v['lang_code'][] = static::$defaultLang;
				}
				return $v;
			}
		}
		return null;
	}

	public static function getDomainLocale($domain = null)
	{
		$domain = !empty($domain) ? $domain : (!empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '');
		$domains = config('domain-zones');
		$domainLocale = 'com';

		if(!empty($domain)) {
			preg_match('/\.([a-z]+)$/', $domain, $matches);
			$domainLocale = !empty($matches[1]) ? $matches[1] : $domainLocale;
		}
		return $domainLocale;
	}

	public static function getLocaleByCode($code)
	{
		$supportedLocales = static::getSupportedLocales();

		foreach($supportedLocales as $k => $v) {
			if($supportedLocales[$k]['code'] == $code) {
				return $k;
			}
		}
		return null;
	}

    public static function getLocaleCode($locale = '')
    {
		$locale = empty($locale) ? CustomLaravelLocalization::getCurrentLocale() : $locale;
		$supportedLocales = static::getSupportedLocales();

        return $supportedLocales[$locale]['code'];
    }

	public static function getDefaultAreaMeasureCode()
	{
		$measureCode = PropertyMeasures::$defaultMeasureCode;

		if(config('app')['localization_type'] == 1) {
			$currentLocale = CustomLaravelLocalization::getCurrentLocale();
			$currentLocale = $currentLocale == 'en' ? 'com' : $currentLocale;
			$domains = config('domain-zones');

			foreach($domains as $k => $v) {
				if($currentLocale == $v['locale']) {
					$measureCode = $v['area_measure'];
					break;
				}
			}
		} else {
			$domainData = CustomLaravelLocalization::getDomainData();
			$measureCode = $domainData['area_measure'];
		}
		return $measureCode;
	}
}