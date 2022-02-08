<?php

namespace App\Http\Models\JobEntities;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Currency;
use CurrencyConverter;
use Money;
use DB;

class JobEntityPrice extends Model
{
	public static $defaultCurrencyCode = 840;

	public static function updatePrices() {
		$jobEntitys = DB::table('job_entities')->get();

		if(!empty($jobEntitys)) {
			$localCurrency = CurrencyConverter::getLocalCurrency();
			$defCurrency = CurrencyConverter::getCurrencyByCode(static::$defaultCurrencyCode);

			foreach($jobEntitys as $item) {
				if($item->currency_code == static::$defaultCurrencyCode) {
					$insert = ['price_local' => CurrencyConverter::convertCurrency($item->price_default, $defCurrency, $localCurrency)];
				} else {
					$insert = ['price_default' => CurrencyConverter::convertCurrency($item->price_local, $localCurrency, $defCurrency)];
				}
				DB::table('job_entities')->where([
					['job_entity_id', '=', $item->job_entity_id],
					['lang_id', '=', $item->lang_id],
				])->update($insert);
			}
		}
		echo 'Prices updated!!';
		exit;
	}

	public static function calculatePrice($data) {
		if(!empty($data['price'])) {
			$data['price'] = (float) $data['price'];
			$isPriceInDefault = $data['currency_code'] == static::$defaultCurrencyCode;
			$localCurrency = CurrencyConverter::getCurrencyByCode($data['currency_code']);
			$defCurrency = CurrencyConverter::getCurrencyByCode(static::$defaultCurrencyCode);

			// if($isPriceInDefault) {
				$data['price_local'] = null;
				$data['price_default'] = $data['price'];
			// } else {
			// 	$data['price_local'] = $data['price'];
			// 	$data['price_default'] = CurrencyConverter::convertCurrency($data['price'], $localCurrency, $defCurrency);
			// }
		}
		return $data;
	}

	public static function preparePriceToView($entity) {
		if(!empty($entity['price_default'])) {
			$default = Currency::USD()->toArray();
			$local = CurrencyConverter::getLocalCurrency();
			$local = !empty($local) ? $local->toArray() : $default;
			$entity['price_view'] = [
				'default' => array_shift($default),
				'local' => array_shift($local),
			];
			$defMoney = Money::USD($entity['price_default'] * 100);

			foreach($entity['price_view'] as $k => $p) {
				if($entity['price_view'][$k]['code'] == static::$defaultCurrencyCode) {
					$entity['price_view'][$k]['price'] = $defMoney->format();
				} else {
					$entity['price_view'][$k]['price'] = CurrencyConverter::convertCurrency(
						$entity['price_default'],
						$defMoney->getCurrency(),
						CurrencyConverter::getCurrencyByCode($entity['price_view'][$k]['code']),
						'format'
					);
				}
			}
		}
		if(!empty($entity['price_second'])) {
			$default = Currency::USD()->toArray();
			$local = CurrencyConverter::getLocalCurrency();
			$local = !empty($local) ? $local->toArray() : $default;
			$entity['price_view_second'] = [
				'default' => array_shift($default),
				'local' => array_shift($local),
			];
			$defMoney = Money::USD($entity['price_second'] * 100);

			foreach($entity['price_view_second'] as $k => $p) {
				if($entity['price_view_second'][$k]['code'] == static::$defaultCurrencyCode) {
					$entity['price_view_second'][$k]['price'] = $defMoney->format();
				} else {
					$entity['price_view_second'][$k]['price'] = CurrencyConverter::convertCurrency(
						$entity['price_second'],
						$defMoney->getCurrency(),
						CurrencyConverter::getCurrencyByCode($entity['price_view_second'][$k]['code']),
						'format'
					);
				}
			}
		}
		return $entity;
	}
}
