<?php

namespace App\Http\Plugins;

use DB;
use BaseModel;
use ElasticSearchHelper;
use Country;
use PropertyMeasures;
use Measure;
use Profession;
use JobCategory;

class SearchHelper {
	public static function applyCommonSearchParams($query, $params) {
		$from = $query->getQuery()->from;
		$isProperty = ($from == 'properties' || $from == 'franchises' || $from == 'designs' || $from == 'arts' || $from == 'products' || $from == 'wines' || $from == 'furnitures' || $from == 'goods' || $from == 'news');
		
        //$isArt = ($from == 'arts');
        $type = isset($params['search_type']) ? $params['search_type'] : '';

		$prefix = $from . '.';
		$relation = !empty($type) ? str_plural($type) : '';
		$relationPrefix = $relation . '.';

		$keyLocationOnly = ($type == 'design' || $type == 'art' || $type == 'product' || $type == 'wine' || $type == 'furniture' || $type == 'good' || $type == 'brand');
		$keyLocation = $keyLocationOnly || $type == 'property';
		if ($keyLocation && !empty($params['key'])) {
			//unset($params['search_location']);
			unset($params['ao']);
		}

		$isBrand = $type == 'brand';
		//$isProfessional = $type == 'professional';
		//dd($type, $keyLocationOnly, $keyLocation, $params);
		/*if (!empty($params['fcategory']) && empty($params['category'])) {
			$params['category'] = $params['fcategory'];
		}*/

		foreach($params as $k => $v) {
			switch($k) {
				case 'not_in':
					$query = $query->whereNotIn($prefix . 'id', $v);
					break;
				case 'key':
					if ($keyLocationOnly) {
						if ($isBrand) {
							//$query->whereRaw("{$prefix}id IN (SELECT user_id FROM user_address_keywords WHERE key_id = {$v})");
							$query->whereRaw("{$prefix}id IN (SELECT user_id FROM user_address_keywords WHERE key_id = ?)", [$v]);
						} else {
							//$query->whereRaw("{$prefix}id IN (SELECT {$type}_id FROM {$type}_address_keywords WHERE key_id = {$v})");
							$query->join($type . '_address_keywords as addkey', 'addkey.' . $type . '_id', '=', $prefix . 'id');
                    		$query->where('addkey.key_id', $v);
						}
					}
					break;
				case 'search_location':
					if(strlen($v) < 2 || $keyLocationOnly) break;

					if(isset($params['ao']) && $params['ao'] == 1) {
						if(isset($params['radius']) && !empty($params['radius'])) {
							$ids = static::getItemsIdsInRadius($params, $from);
                            $query->whereIn($from.'.id', $ids);
						} else {
							if(isset($params['lat']) && !empty($params['lat'])) {
								$query->where($prefix.'lat', $params['lat']);
							}
							if(isset($params['lng']) && !empty($params['lng'])) {
								$query->where($prefix.'lng', $params['lng']);
							}
						}
					} else {
						$geo = '';
						$parts = explode(',', $v);
						$cnt = sizeof($parts);
						$existCity = false;
						$existCountry = false;
						$states = [];
						$bindings = [];

						if(isset($params['ac']) && !empty($params['ac'])) {
                            $params['ac'] = trim(preg_replace('/city/i', '', $params['ac']));
							//$geo .= $prefix.'"city" ILIKE '.DB::getPdo()->quote($params['ac']).' AND ';
							$geo .= $prefix."city ILIKE ? AND ";
							$bindings[] = $params['ac'];
							$existCity = true;
						}
						if(isset($params['ai']) && !empty($params['ai'])) {
                            if(strlen($params['ai']) == 3) {
                                $country = Country::where('iso3', $params['ai'])->first();
                            } else {
                                $country = Country::where('iso2', $params['ai'])->first();
                            }
							if($country) {
								//$geo .= $prefix.'"country"='.DB::getPdo()->quote($isProperty ? $country->id : $country->name).' AND ';
								$geo .= $prefix."country= ? AND ";
								$bindings[] = ($isProperty ? $country->id : $country->name);
								$existCountry = true;
                                $states = config('country-states.'.$country->iso2);
							}
						}

						if(!$existCity || ($existCountry && $country->iso2 == 'US')) {
							if($isProperty && isset($params['as']) && !empty($params['as'])) {
								if (!empty($states) && isset($states[$params['as']])) {
                                    //$geo .= '('.$prefix.'"state"='.DB::getPdo()->quote($params['as']).' OR '.$prefix.'"state"='.DB::getPdo()->quote($states[$params['as']]['code']).') AND ';
                                    $geo .= '('.$prefix."state= ? OR ".$prefix."state= ?) AND ";
                                    $bindings[] = $params['as'];
                                    $bindings[] = $states[$params['as']]['code'];
                                } else {
                                    //$geo .= $prefix.'"state"='.DB::getPdo()->quote($params['as']).' AND ';
                                    $geo .= $prefix."state= ? AND ";
                                    $bindings[] = $params['as'];
                                }
							}
						}
						if(empty($geo)) {
							if($cnt > 2) {
								if(isset($params['lat']) && !empty($params['lat'])) {
									//$geo .= $prefix.'"lat"='.DB::getPdo()->quote($params['lat']).' AND ';
									$geo .= $prefix."lat= ? AND ";
									$bindings[] = $params['lat'];
								}
								if(isset($params['lng']) && !empty($params['lng'])) {
									//$geo .= $prefix.'"lng"='.DB::getPdo()->quote($params['lng']).' AND ';
									$geo .= $prefix."lng= ? AND ";
									$bindings[] = $params['lng'];
								}
								if(empty($geo)) {
									$queted = '%'.$v.'%';
									//$geo = '('.$prefix.'address ilike '.$queted.($isProperty ? ' OR '.$prefix.'"map_address" ilike '.$queted.') AND ' : ') AND ');
									$geo = '('.$prefix."address ilike ?";
									$bindings[] = $queted;
									if ($isProperty) {
										$geo .= ' OR '.$prefix."map_address ilike ?";
										$bindings[] = $queted;
									}
									$geo .= ') AND ';
								}
                                $geo = substr($geo, 0, -5);
							} else {
								$country = trim($parts[$cnt - 1]);
                                $country = strtoupper($country);
								$len = strlen($country);
								if($len == 2) {
									$result = Country::where('iso2', $country)->get();
								} else if($len == 3) {
									$result = Country::where('iso3', $country)->get();
								} else {
									//$result = Country::whereRaw('("name" ILIKE '.DB::getPdo()->quote('%'.$country.'%').')')->get();
									$result = Country::where('name', 'ilike', '%'.$country.'%')->get();
								}
								$list = '';
								if($result) {
									foreach($result as $r) {
										$list .= ($isProperty ? $r->id : "'".$r->name."'").',';
									}
									if (empty($states) && isset($result[0])) {
                                        $states = config('country-states.'.$result[0]->iso2);
                                    }
								}
								if(!empty($list)) {
									$geo = $prefix.'"country" IN ('.substr($list, 0, -1).')'.($cnt == 1 ? ' OR ' : ' AND ');
								} elseif ($isProperty) {
                                    /*$queted = DB::getPdo()->quote('%'.trim($parts[$cnt - 1]).'%');
                                    //$geo = $prefix.'"state" ILIKE '.$queted.($cnt == 1 ? ' OR ' : ' AND ');
                                    $geo = $prefix."state ILIKE ?".($cnt == 1 ? ' OR ' : ' AND ');
                                    $bindings[] = $queted;*/
                                /*}
                                if ($isArt) {*/
                                    $queted = '%'.trim($parts[$cnt - 1]).'%';
                                    //$geo = '('.$prefix.'"city" ILIKE '.$queted.' OR '.$prefix.'"state" ILIKE '.$queted.' OR '.$prefix.'"address" ILIKE '.$queted.') OR ';
                                    $geo = '('.$prefix."city ILIKE ? OR ".$prefix."state ILIKE ? OR ".$prefix."address ILIKE ?) OR ";
                                    $bindings[] = $queted;
                                    $bindings[] = $queted;
                                    $bindings[] = $queted;
                                }
								$queted = ucwords(trim($parts[0]));
								//$geo .= '('.$prefix.'"city" = '.$queted.($isProperty ? ' OR '.$prefix.'"state" = '.$queted : '').')';
								$geo .= '('.$prefix."city = ?";
								$bindings[] = $queted;
								if ($isProperty) {
									$geo .= ' OR '.$prefix."state = ?";
									$bindings[] = $queted;
								}
								$geo .= ')';
                                if (!empty($states) && isset($states[ucfirst(trim($parts[0]))])) {
                                    //$geo .= ' OR ('.$prefix.'"state"='.DB::getPdo()->quote($states[ucfirst(trim($parts[0]))]['code']).')';
                                    $geo .= ' OR ('.$prefix."state= ?)";
                                    $bindings[] = $states[ucfirst(trim($parts[0]))]['code'];
                                }
							}
						} else {
							$geo = substr($geo, 0, -5);
						}

						$geo = '('.$geo.')';

						if ($keyLocation) {
							if(!empty($params['key'])) {
								$key = (int) $params['key'];
								//$geo = '('.$geo.' OR ' . "{$prefix}id IN (SELECT {$type}_id FROM {$type}_address_keywords WHERE key_id = {$key}))";
								$geo = '('.$geo.' OR ' . "{$prefix}id IN (SELECT {$type}_id FROM {$type}_address_keywords WHERE key_id = ?))";
								$bindings[] = $key;
							}
						} else if(isset($params['radius']) && !empty($params['radius']) && !empty($params['lat']) && !empty($params['lng'])) {
							$idsQuery = static::getItemsIdsInRadius($params, $from, true);
                            $geo = '('.$geo.' OR '.$from.'."id" IN ('.$idsQuery.'))';
						}
						
						$query->whereRaw($geo, $bindings);
					}
					break;
                case 'artist':
                    //$query = $query->whereRaw("{$prefix}author IN (SELECT id FROM users au WHERE (au.name ILIKE '%{$v}%' OR au.first_name ILIKE '%{$v}%' OR au.last_name ILIKE '%{$v}%' OR CONCAT(au.first_name,' ',au.last_name) ILIKE '%{$v}%'))");
                	$like = '%'.$v.'%';
                    $query = $query->whereRaw("{$prefix}author IN (SELECT id FROM users au WHERE (au.name ILIKE ? OR au.first_name ILIKE ? OR au.last_name ILIKE ? OR CONCAT(au.first_name,' ',au.last_name) ILIKE ?))", [$like, $like, $like, $like]);
                    break;
                case 'seller_type':
                    //$query = $query->whereRaw("{$prefix}author IN (SELECT user_id FROM sellers WHERE seller_type IN ({$v}))");
                    $query = $query->whereRaw("{$prefix}author IN (SELECT user_id FROM sellers WHERE seller_type IN (?))", [$v]);
                    break;
                case 'wineseller_type':
                    $query = $query->whereRaw("{$prefix}author IN (SELECT user_id FROM winesellers WHERE wineseller_type IN (?))", [$v]);
                    break;
                case 'furnitureseller_type':
                    $query = $query->whereRaw("{$prefix}author IN (SELECT user_id FROM furnituresellers WHERE furnitureseller_type IN (?))", [$v]);
                    break;
                case 'product_labels':
                case 'wine_labels':
                case 'furniture_labels':
                case 'good_labels':
                case 'news_labels':
                    //$query = $query->whereRaw("{$prefix}label IN ({$v})");
                    $query = $query->whereRaw("{$prefix}label IN (?)", [$v]);
                    break;
                case 'category':
                	if ($from == 'properties' || $from == 'products' || $from == 'wines' || $from == 'furnitures' || $from == 'goods' || $from == 'designs' || $from == 'arts') {
                		$item = ($from == 'properties' ? 'property' : substr($from, 0, -1));
                    	//$query->whereRaw("{$prefix}id IN (SELECT {$item}_id FROM {$item}_category_relation WHERE {$item}_category_id = {$v})");
                    	$query->join($item . '_category_relation as catrel', 'catrel.' . $item . '_id', '=', $prefix . 'id');
                    	$query->where('catrel.' . $item . '_category_id', $v);
                    }
                    break;
				case 'keyword':
					$useElasticSearch = ElasticSearchHelper::isUseElasticSearch();

					if($useElasticSearch) {
						$output = ElasticSearchHelper::searchElastic($v, $isProperty ? 'properties' : $relation);
						$ids = [];

						if(!empty($output->hits->hits)) {
							foreach($output->hits->hits as $hit) {
								$ids[] = $hit->_id;
							}
							$query = $query->whereIn($prefix . 'id', $ids);
						}
					} else {
						$isID = false;
						if(substr(strtoupper($v), 0, 3) == 'ID:') {
							$id = trim(substr($v, 3));
							if(ctype_digit($id)) {
								//$query = $query->whereRaw(($isProperty ? $from : 'users').'.id='.$id);
								$query = $query->whereRaw(($isProperty ? $from : 'users').".id= ?", [$id]);
								$isID = true;
							}
						}
						if(!$isID) {
                            $v = '%'.trim($v).'%';
							if($isProperty) {
								//$query = $query->whereRaw('("title" ILIKE '. $v. ' OR '.  '"description" ILIKE ' . $v . ')');
								$query = $query->whereRaw("(title ILIKE ? OR description ILIKE ?)", [$v, $v]);
							//} else if ($isBrand || $isProfessional) {
							} else if ($isBrand) {
								//$query = $query->whereRaw('(CONCAT(users.first_name,\' \',users.last_name) ILIKE ' . $v . ' OR '.  '"description" ILIKE ' . $v . ')');
								$query = $query->whereRaw("(CONCAT(users.first_name,' ',users.last_name) ILIKE ? OR description ILIKE ?)", [$v, $v]);
							} else {
								//$query = $query->whereRaw('("company_name" ILIKE '. $v. ' OR CONCAT(users.first_name,\' \',users.last_name) ILIKE ' . $v . ' OR '.  '"description" ILIKE ' . $v . ')');
								$query = $query->whereRaw("(company_name ILIKE ? OR CONCAT(users.first_name,' ',users.last_name) ILIKE ? OR description ILIKE ?)", [$v, $v, $v]);
							}
						}
					}
					break;
				case 'news_keyword':
                    $v = '%'.trim($v).'%';
					if($isProperty) {
						$query = $query->whereRaw("(title ILIKE ? OR description ILIKE ?)", [$v, $v]);
					} 
					break;
				case 'profession':
					$query = $query
						->join('professions_users as pu', 'pu.user_id', '=', 'users.id')
						->join('professions as p', 'p.profession_id', '=', 'pu.profession_id')
						->where('p.slug', '=', $v);
						//->groupBy('users.id');
					break;

				default:
					break;
			}
		}
		//dd($query->toSql(), $bindings, $geo);
		return $query;
	}
    
    public static function applyAdsCommonSearchParams($query, $params) {
        
        foreach ($params as $k => $v) {
            switch ($k) {
                case 'title':
                    if (!empty($v)) {
                        $v = '%'.trim(strtolower($v)).'%';
                        //$query = $query->whereRaw("(ads.title ILIKE '%{$v}%') OR (ads.address ILIKE '%{$v}%')");
                        $query = $query->whereRaw("(ads.title ILIKE ?) OR (ads.address ILIKE ?)", [$v, $v]);
                    }
                    break;
                case 'keyword':
                    if (!empty($v)) {
                        $v = trim(strtolower($v));
                        $query = $query->select('ads.*');
                        
                        $searchIds = DB::table('ads_keywords')->where('key_hash', '=', md5($v))->get(['ads_id'])->toArray();
                        $searchIds = array_map(function ($item) {
                                return $item->ads_id;
                            }, $searchIds);
                    }
                    break;
                default:
                    break;
            }
        }
        if (!empty($searchIds)) {
            $query = $query->whereRaw('(ads.ads_id IN (' . implode(',', $searchIds) . '))');
        }
        
        return $query;
    }

	public static function getItemsIdsInRadius($params, $from, $returnQuery = false) {
		if(empty($params['radius']) || empty($params['lat']) || empty($params['lng'])) return [];

		$earthRadius = 6371;	//in km
		$subQuery = sprintf(
			"SELECT DISTINCT id
FROM {$from}
WHERE ( 2 * %d * asin(sqrt((sin(radians((CAST ( NULLIF ( lat, '' ) AS numeric ) - %f) / 2))) ^ 2 + cos(radians(%f)) * cos(radians(CAST ( NULLIF ( lat, '' ) AS numeric))) * (sin(radians((CAST ( NULLIF ( lng, '' ) AS numeric) - %f) / 2))) ^ 2)) ) < %d",
            $earthRadius, $params['lat'], $params['lat'], $params['lng'], $params['radius']);
		if ($returnQuery) {
            return $subQuery;
        }
		$result = DB::select($subQuery);
		$ids = [];

		if(!empty($result)) {
			foreach($result as $r) {
				$ids[] = $r->id;
			}
		}
		return $ids;
	}

	public static function applyWhere($query, $key, $value) {
		return $query->where($key, $value);
	}

	public static function applyWhereBetween($query, $key, $value) {
		return $query->whereBetween($key, $value);
	}

	public static function applyPropertyPriceParam($query, $key, $value, $currency_code) {
		$currency = CurrencyConverter::getCurrencyByCode($currency_code);

		foreach($value as $index => $price) {
			$value[$index] = CurrencyConverter::convertCurrency(is_numeric($value[$index]) ? $value[$index] : null, $currency, 'USD');
		}
		if(!empty($value['min']) && !empty($value['max'])) {
			$query = $query->whereBetween($key, $value);
		} else {
			if(!empty($value['min'])) {
				$query = $query->where($key, '>=', $value['min']);
			} else {
				$query = $query->where($key, '<=', $value['max']);
			}
		}
		return $query;
	}

	public static function applyPropertyAreaParam($query, $key, $value, $measure_code) {
		$def_measure = PropertyMeasures::$defaultMeasureCode;

		if($measure_code != $def_measure) {
			$from = Measure::getMeasureByCode($measure_code);
			$to = Measure::getMeasureByCode($def_measure);
			foreach($value as $index => $area) {
				$value[$index] = Measure::convert(is_numeric($area) ? $area : null, $from, $to);
			}
		}

		if(!empty($value['min']) && !empty($value['max'])) {
			$query = $query->whereBetween($key, $value);
		} else {
			if(!empty($value['min'])) {
				$query = $query->where($key, '>=', $value['min']);
			} else {
				$query = $query->where($key, '<=', $value['max']);
			}
		}
		return $query;
	}

	public static function applyPropertyFeaturesParam($query, $key, $value) {
		$subQuery = sprintf('' .
			'select distinct "property_id"
from (
select "property_id", "feature_id", count("property_id") over(partition by "property_id") as cnt
from "features_properties"
where "feature_id" in (%s)
) t
where t.cnt = %s', implode(',', $value), count($value));
		$result = DB::select($subQuery);
		$ids = [];

		if(!empty($result)) {
			foreach($result as $r) {
				$ids[] = $r->property_id;
			}
		}
		return $query->whereIn($key, $ids);
	}
}
