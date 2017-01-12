<?php
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel as M;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\Config\Compiled;

/**
 * 2016-05-06
 * By analogy with https://github.com/magento/magento2/blob/135f967/lib/internal/Magento/Framework/ObjectManager/TMap.php#L97-L99
 * @param string $type
 * @return bool
 */
function df_class_exists($type) {
	/**
	 * 2016-05-23
	 * Намеренно не объединяем строки в единное выражение,
	 * чтобы собака @ не подавляла сбои первой строки.
	 * Такие сбои могут произойти при синтаксических ошибках в проверяемом классе
	 * (похоже, getInstanceType как-то загружает код класса).
	 */
	/** @var string $type */
	$type = df_om_config()->getInstanceType(df_om_config()->getPreference($type));
	return @class_exists($type);
}

/**
 * 2016-01-06
 * 2017-01-12
 * Если Вам не нужен Object Manager, то используйте более простую функцию @see df_new()
 * @param string $resultClass
 * @param array(string => mixed) $params [optional]
 * @return \Magento\Framework\DataObject|object
 */
function df_create($resultClass, array $params = []) {return
	df_om()->create($resultClass, ['data' => $params])
;}

/**
 * @see df_sc()
 * @param string $resultClass
 * @param string|null|array(string => mixed) $a2 [optional]
 * @param array(string => mixed) $a3 [optional]
 * @return DataObject|object
 */
function df_ic($resultClass, $a2 = null, array $a3 = []) {
	/** @var string|null $expectedClass */
	/** @var array(string => mixed) $params */
	list($expectedClass, $params) = is_array($a2) ? [null, $a2] : [$a2, $a3];
	return df_ar(df_create($resultClass, $params), $expectedClass);
}

/**
 * 2016-08-24
 * 2016-09-04
 * Метод getId присутствует не только у потомков @see \Magento\Framework\Model\AbstractModel,
 * но и у классов сторонних библиотек, например:
 * https://github.com/CKOTech/checkout-php-library/blob/v1.2.4/com/checkout/ApiServices/Charges/ResponseModels/Charge.php?ts=4#L170-L173
 * По возможности, задействуем и сторонние реализации.
 *
 * К сожалению, нельзя здесь для проверки публичности метода использовать @see is_callable(),
 * потому что наличие @see \Magento\Framework\DataObject::__call()
 * приводит к тому, что @see is_callable всегда возвращает true.
 * Обратите внимание, что @uses method_exists(), в отличие от @see is_callable(),
 * не гарантирует публичную доступность метода:
 * т.е. метод может у класса быть, но вызывать его всё равно извне класса нельзя,
 * потому что он имеет доступность private или protected.
 * Пока эта проблема никак не решена.
 *
 * 2016-09-05
 * Этот код прекрасно работает с объектами классов типа @see \Magento\Directory\Model\Currency
 * благодаря тому, что @uses \Magento\Framework\Model\AbstractModel::getId()
 * не просто тупо считывает значение поля id, а вызывает метод
 * @see \Magento\Framework\Model\AbstractModel::getIdFieldName()
 * который, в ссвою очередь, узнаёт имя идентифицирующего поля из своего ресурса:
 * @see \Magento\Framework\Model\AbstractModel::_init()
 * @see \Magento\Directory\Model\ResourceModel\Currency::_construct()
 *
 * @see dfo_hash() использует тот же алгоритм, но не вызывает @see df_id() ради ускорения.
 *
 * @param object|int|string $o
 * @param bool $allowNull [optional]
 * @return int|string|null
 */
function df_id($o, $allowNull = false) {
	/** @var int|string|null $result */
	$result = !is_object($o) ? $o : (
		$o instanceof M || method_exists($o, 'getId') ? $o->getId() : null
	);
	df_assert($allowNull || $result);
	return $result;
}

/**
 * 2016-09-05
 * @param object|int|string $o
 * @param bool $allowNull [optional]
 * @return int
 */
function df_idn($o, $allowNull = false) {return df_nat(df_id($o, $allowNull), $allowNull);}

/**
 * 2017-01-12
 * 1) PHP, к сожалению, не разрешает в выражении с new делать выражением имя класса:
 * https://3v4l.org/U6TJR
 * Поэтому и создал эту небольшую функцию.
 * В отличие от @see df_create(), она не использует Object Manager.
 * 2) Впервые использую в своём коде возможность argument unpacking, появившуюся в PHP 5.6:
 * https://3v4l.org/eI2vf
 * http://stackoverflow.com/a/25781989
 * http://php.net/manual/en/functions.arguments.php#example-145
 * @param string $c
 * @param array ...$args
 * @return object
 */
function df_new($c, ...$args) {return new $c(...$args);}

/**
 * 2017-01-12
 * PHP, к сожалению, не разрешает в выражении с new делать выражением имя класса.
 * Поэтому и создал эту небольшую функцию.
 * В отличие от @see df_create(), она не использует Object Manager.
 * @param string $c
 * @param string $expected
 * @param array ...$args
 * @return object
 */
function df_newa($c, $expected, ...$args) {return df_ar(df_new($c, $args), $expected);}

/**
 * @param string $type
 * @return mixed
 */
function df_o($type) {return dfcf(function($type) {return df_om()->get($type);}, func_get_args());}

/**
 * 2015-08-13
 * @used-by df_o()
 * @used-by df_ic()
 * @return \Magento\Framework\ObjectManagerInterface|\Magento\Framework\App\ObjectManager
 */
function df_om() {return \Magento\Framework\App\ObjectManager::getInstance();}

/**
 * 2016-05-06
 * @return ConfigInterface|Config|Compiled
 */
function df_om_config() {return df_o(ConfigInterface::class);}

/**
 * 2015-03-23
 * @see df_ic()
 * @param string $resultClass
 * @param string $expectedClass
 * @param array(string => mixed) $params [optional]
 * @param string $cacheKeySuffix [optional]
 * @return DataObject|object
 */
function df_sc($resultClass, $expectedClass, array $params = [], $cacheKeySuffix = '') {
	/** @var array(string => object) $cache */
	static $cache;
	/** @var string $key */
	$key = $resultClass . $cacheKeySuffix;
	if (!isset($cache[$key])) {
		$cache[$key] = df_ic($resultClass, $expectedClass, $params);
	}
	return $cache[$key];
}

/**
 * 2016-08-23
 * @see dfa()
 * @param object $object
 * @param string|int $key
 * @param mixed|callable $default
 * @return mixed|null
 */
function dfo($object, $key, $default = null) {
	return isset($object->{$key}) ? $object->{$key} : df_call_if($default, $key);
}