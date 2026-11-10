<?php

namespace App\Http\Plugins;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Renderable;
use JsonSerializable;
use OutOfBoundsException;

/**
 * Class Measure.
 *
 * @method static Measure SQMETER()
 * @method static Measure SQFEET()
 */
class Measure implements Arrayable, Jsonable, JsonSerializable, Renderable
{
	/**
	 * @var string
	 */
	protected $measure;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var int
	 */
	protected $code;

	/**
	 * @var string
	 */
	protected $symbol;
	protected $abbreviation;

	/**
	 * @var string
	 */
	protected $convert_rate;

	/**
	 * @var array
	 */
	protected static $measures;

	/**
	 * Create a new instance.
	 *
	 * @param string $measure
	 *
	 * @throws \OutOfBoundsException
	 */
	public function __construct($measure)
	{
		$measure = strtoupper(trim($measure));
		$measures = static::getMeasures();

		if (!array_key_exists($measure, $measures)) {
			throw new OutOfBoundsException('Invalid measure "' . $measure . '"');
		}

		$attributes = $measures[$measure];
		$this->measure = $measure;
		$this->name = (string) $attributes['name'];
		$this->code = (int) $attributes['code'];
		$this->symbol = (string) $attributes['symbol'];
		$this->abbreviation = (string) $attributes['abbreviation'];
		$this->convert_rate = (float) $attributes['convert_rate'];
	}

	/**
	 * __callStatic.
	 *
	 * @param string $method
	 * @param array  $arguments
	 *
	 * @return \App\Http\Plugins\Measure
	 */
	public static function __callStatic($method, array $arguments)
	{
		return new static($method, $arguments);
	}

	/**
	 * setMeasures.
	 *
	 * @param array $measures
	 *
	 * @return void
	 */
	public static function setMeasures(array $measures)
	{
		static::$measures = $measures;
	}

	/**
	 * getMeasures.
	 *
	 * @return array
	 */
	public static function getMeasures()
	{
		if (!isset(static::$measures)) {
			static::$measures = config('measures');
		}

		return (array) static::$measures;
	}

	public static function getMeasuresForSelect()
	{
		$measures = static::getMeasures();
		$list = [];

		foreach($measures as $m) {
			$list[$m['code']] = $m['symbol'];
		}

		return $list;
	}

	public static function getMeasureByCode($code, $arr = false)
	{
		$measures = Measure::getMeasures();

		foreach($measures as $k => $m) {
			if($m['code'] == $code) {
				return $arr ? $m : Measure::$k();
			}
		}
		return null;
	}

	public static function convert($value, $from, $to)
	{
		$valueToConvert = $from->getConvertRate() == 1 ? $value : ($value / $from->getConvertRate());

		return round($valueToConvert * $to->getConvertRate(), 2, PHP_ROUND_HALF_UP);
	}

	/**
	 * equals.
	 *
	 * @param \App\Custom\Measure $measure
	 *
	 * @return bool
	 */
	public function equals(self $measure)
	{
		return $this->getMeasure() === $measure->getMeasure();
	}

	/**
	 * getMeasure.
	 *
	 * @return string
	 */
	public function getMeasure()
	{
		return $this->measure;
	}

	/**
	 * getName.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * getCode.
	 *
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * getSymbol.
	 *
	 * @return string
	 */
	public function getSymbol()
	{
		return $this->symbol;
	}
	public function getAbbreviation()
	{
		return $this->abbreviation;
	}

	/**
	 * isSymbolFirst.
	 *
	 * @return bool
	 */
	public function isSymbolFirst()
	{
		return $this->symbolFirst;
	}

	/**
	 * getSymbol.
	 *
	 * @return string
	 */
	public function getConvertRate()
	{
		return $this->convert_rate;
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [$this->measure => [
			'name'		=> $this->name,
			'code'		=> $this->code,
			'symbol'	=> $this->symbol,
			'abbreviation'	=> $this->abbreviation,
			'convert_rate'	=> $this->convert_rate,
		]];
	}

	/**
	 * Convert the object to its JSON representation.
	 *
	 * @param int $options
	 *
	 * @return string
	 */
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * jsonSerialize.
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Get the evaluated contents of the object.
	 *
	 * @return string
	 */
	public function render()
	{
		return $this->name . ' (' . $this->symbol . ')';
	}

	/**
	 * __toString.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}
}
