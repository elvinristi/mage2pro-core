<?php
namespace Df\Payment\R;
use Df\Payment\Method;
use Df\Sales\Model\Order as DfOrder;
use Magento\Sales\Api\Data\OrderInterface as IO;
use Magento\Sales\Api\Data\OrderPaymentInterface as IOP;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment as OP;
use Magento\Sales\Model\Order\Payment\Transaction;
// 2016-07-09
// Портировал из Российской сборки Magento.
abstract class Response extends \Df\Core\O {
	/**
	 * 2016-07-10
	 * @used-by \Df\Payment\R\Response::externalId()
	 * @return string
	 */
	abstract protected function externalIdKey();

	/**
	 * 2016-07-09
	 * @used-by \Df\Payment\R\Response::validate()
	 * @return bool
	 */
	abstract protected function isSuccessful();

	/**
	 * 2016-07-09
	 * @used-by \Df\Payment\R\Response::validate()
	 * @return string
	 */
	abstract protected function messageKey();

	/**
	 * 2016-07-09
	 * @used-by \Df\Payment\R\Response::requestId()
	 * @return string
	 */
	abstract protected function requestIdKey();

	/**
	 * 2016-07-10
	 * @used-by \Df\Payment\R\Response::signatureProvided()
	 * @return string
	 */
	abstract protected function signatureKey();

	/**
	 * 2016-07-10
	 * @return string
	 */
	public function externalId() {return $this[$this->externalIdKey()];}

	/**
	 * 2016-07-10
	 * @return Order|DfOrder
	 */
	public function order() {
		if (!isset($this->_order)) {
			$this->_order = $this->transaction()->getOrder();
			/**
			 * 2016-03-26
			 * Very Important! If not done the order will create a duplicate payment
			 * @used-by \Magento\Sales\Model\Order::getPayment()
			 */
			$this->_order[IO::PAYMENT] = $this->payment();
		}
		return $this->_order;
	}

	/**
	 * 2016-07-10
	 * @return IOP|OP
	 */
	public function payment() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_order_payment_get($this->transaction()->getPaymentId());
			$this->{__METHOD__}[Method::CUSTOM_TRANS_ID] = $this->idL2G($this->externalId());
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-10
	 * @return Report
	 */
	public function report() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = Report::ic($this->reportC(), $this);
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-10
	 * @used-by \Df\Payment\R\Report::asArray()
	 * @return array(string => mixed)
	 */
	public function requestParams() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = $this->requestInfo();
			unset($this->{__METHOD__}[Method::TRANSACTION_PARAM__URL]);
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-10
	 * @return string
	 */
	public function requestUrl() {return dfa($this->requestInfo(), Method::TRANSACTION_PARAM__URL);}

	/**
	 * 2016-07-09
	 * @return void
	 * @throws \Exception
	 */
	public function validate() {
		if (!$this->isSuccessful()) {
			$this->throwException($this->message());
		}
		$this->validateSignature();
	}

	/**
	 * 2016-07-12
	 * @return void
	 */
	protected function addTransaction() {
		df_payment_apply_custom_transaction_id($this->payment());
		df_payment_set_transaction_info($this->payment(), $this->getData());
		/**
		 * 2016-07-12
		 * @used-by \Magento\Sales\Model\Order\Payment\Transaction\Builder::linkWithParentTransaction()
		 */
		$this->payment()->setParentTransactionId($this->transaction()->getTxnId());
		/**
		 * 2016-07-10
		 * @uses TransactionInterface::TYPE_PAYMENT — это единственный транзакции
		 * без специального назначения, и поэтому мы можем безопасно его использовать.
		 */
		$this->payment()->addTransaction(TransactionInterface::TYPE_PAYMENT);
	}

	/**
	 * 2016-07-10
	 * @used-by \Df\Payment\R\Response::throwException()
	 * @return string
	 */
	protected function exceptionC() {return df_convention_same_folder($this, 'Exception', Exception::class);}

	/**
	 * 2016-07-12
	 * @used-by \Df\Payment\R\Response::report()
	 * @return string
	 */
	protected function reportC() {return df_convention_same_folder($this, 'Report', Report::class);}

	/**
	 * 2016-07-12
	 * @return array(string => string)
	 */
	protected function testData() {return [];}

	/**
	 * 2016-07-10
	 * @param Exception|string $message
	 * @return void
	 * @throws Exception
	 */
	protected function throwException($message) {
		/** @var Exception $exception */
		if ($message instanceof Exception) {
			$exception = $message;
		}
		else {
			/** @var mixed[] $arguments */
			$arguments = func_get_args();
			/** @var string $exceptionClass */
			$exceptionClass = $this->exceptionC();
			/** @var Exception $exception */
			$exception = new $exceptionClass(df_format($arguments), $this);
		}
		df_error($exception);
	}

	/**
	 * 2016-07-11
	 * @used-by \Df\Payment\R\Response::payment()
	 * @used-by \Df\Payment\R\Response::requestIdG()
	 * @param string $localId
	 * @return string
	 */
	private function idL2G($localId) {
		/** @uses \Df\Payment\Method::transactionIdL2G() */
		return call_user_func([$this->methodC(), 'transactionIdL2G'], $localId);
	}

	/**
	 * 2016-07-10
	 * @return string
	 */
	private function message() {return $this[$this->messageKey()];}

	/**
	 * 2016-07-10
	 * @return string
	 */
	private function methodC() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_convention($this, 'Method');
			df_assert_is($this->{__METHOD__}, Method::class);
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-09
	 * @return string
	 */
	private function requestId() {return $this[$this->requestIdKey()];}

	/**
	 * 2016-07-10
	 * @return string
	 */
	private function requestIdG() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = $this->idL2G($this->requestId());
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-10
	 * @return array(string => mixed)
	 */
	private function requestInfo() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = $this->transaction()->getAdditionalInformation(
				Transaction::RAW_DETAILS
			);
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-10
	 * @return Signer
	 */
	private function signer() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_create(df_convention($this, 'Signer'), $this->getData());
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-10
	 * @return string
	 */
	private function signatureProvided() {return $this[$this->signatureKey()];}

	/**
	 * 2016-07-10
	 * @return Transaction
	 */
	private function transaction() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_load(Transaction::class, $this->requestIdG(), true, 'txn_id');
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-07-09
	 * @used-by \Df\Payment\R\Response::validate()
	 * @return void
	 * @throws \Exception
	 */
	private function validateSignature() {
		/** @var string $expected */
		$expected = $this->signer()->sign();
		/** @var string $provided */
		$provided = $this->signatureProvided();
		if ($expected !== $provided) {
			$this->throwException(
				"Invalid signature.\nExpected: «%s».\nProvided: «%s».", $expected, $provided
			);
		}
	}

	/**
	 * 2016-07-12
	 * @var Order|DfOrder
	 */
	protected $_order;

	/**
	 * 2016-07-09
	 * http://php.net/manual/en/function.get-called-class.php#115790
	 * @param array(string => mixed)|true $params
	 * @return self
	 */
	public static function i($params) {return self::ic(static::class, $params);}

	/**
	 * 2016-07-12
	 * @param string $class
	 * @param array(string => mixed)|true $params
	 * @return self
	 */
	public static function ic($class, $params) {
		/** @var self $result */
		$result = df_create($class);
		$result->setData(true === $params ? $result->testData() : $params);
		return $result;
	}
}