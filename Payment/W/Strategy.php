<?php
namespace Df\Payment\W;
use Df\Payment\Method as M;
use Df\Sales\Model\Order as DFO;
use Magento\Framework\Controller\AbstractResult as Result;
use Magento\Framework\Phrase;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Payment as OP;
/**
 * 2017-01-06
 * @see \Df\Payment\W\Strategy\CapturePreauthorized
 * @see \Df\Payment\W\Strategy\ConfirmPending
 * @see \Df\Payment\W\Strategy\Refund
 */
abstract class Strategy {
	/**
	 * 2017-01-06
	 * @used-by handle()
	 * @see \Df\Payment\W\Strategy\CapturePreauthorized::_handle()
	 * @see \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @see \Df\Payment\W\Strategy\Refund::_handle()
	 */
	abstract protected function _handle();

	/**
	 * 2017-03-18
	 * @used-by ro()
	 * @used-by ttCurrent()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::action()
	 * @return Event
	 */
	final protected function e() {return $this->_h->e();}

	/**
	 * 2017-01-17
	 * @used-by \Df\Payment\W\Strategy\Refund::_handle()
	 * @return Handler
	 */
	final protected function h() {return $this->_h;}

	/**
	 * 2017-01-15
	 * @return M
	 */
	final protected function m() {return dfc($this, function() {return df_ar($this->_h->m(), M::class);});}

	/**
	 * 2017-01-06
	 * @used-by \Df\Payment\W\Strategy\CapturePreauthorized::_handle()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @return O|DFO
	 */
	final protected function o() {return $this->_h->o();}

	/**
	 * 2017-01-07
	 * @used-by \Df\Payment\W\Strategy\CapturePreauthorized::_handle()
	 * @used-by \Df\Payment\W\Strategy\CapturePreauthorized::invoice()
	 * @used-by \Df\Payment\W\Strategy\ConfirmPending::_handle()
	 * @used-by \Df\Payment\W\Strategy\Refund::_handle()
	 * @return OP
	 */
	final protected function op() {return $this->_h->op();}

	/**
	 * 2017-01-07
	 * @used-by \Df\Payment\W\Strategy\CapturePreauthorized::_handle()
	 * @param Result|Phrase|string $v
	 */
	final protected function resultSet($v) {$this->_h->resultSet($v);}

	/**
	 * 2017-01-06
	 * @used-by \Df\StripeClone\W\Handler::_handle()
	 * @param Handler $h
	 */
	private function __construct(Handler $h) {$this->_h = $h;}

	/**
	 * 2017-01-06
	 * @used-by __construct()
	 * @used-by m()
	 * @used-by o()
	 * @used-by resultSet()
	 * @var Handler
	 */
	private $_h;

	/**
	 * 2017-03-18
	 * @used-by \Df\StripeClone\W\Handler::_handle()
	 * @param string $class
	 * @param Handler $h
	 */
	final static function handle($class, Handler $h) {
		$i = df_ar(new $class($h), __CLASS__); /** @var self $i */
		$i->_handle();
	}
}