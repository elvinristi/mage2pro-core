<?php
namespace Df\StripeClone\W\Strategy;
use Df\Payment\Source\AC;
use Df\StripeClone\W\Event as Ev;
use Magento\Sales\Model\Order\Payment as OP;
// 2017-01-15
/** @used-by \Dfe\Omise\W\Handler\Charge\Complete::strategyC() */
final class Authorize extends \Df\StripeClone\W\Strategy {
	/**
	 * 2017-01-15
	 * @override
	 * @see \Df\StripeClone\W\Strategy::_handle()
	 * @used-by \Df\StripeClone\W\Strategy::::handle()
	 * @return void
	 */
	protected function _handle() {
		$this->action();
		df_order_send_email($this->o());
		$this->resultSet($this->op()->getId());
	}

	/**
	 * 2017-01-15
	 * @used-by _handle()
	 * @return void
	 */
	private function action() {
		/**
		 * 2016-03-15
		 * Если оставить открытой транзакцию «capture»,
		 * то операция «void» (отмена авторизации платежа) будет недоступна:
		 * https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Sales/Model/Order/Payment.php#L540-L555
		 * @used-by \Magento\Sales\Model\Order\Payment::canVoid()
		 * Транзакция считается закрытой, если явно не указать «false».
		 *
		 * 2017-01-16
		 * Наоборот: если закрыть транзакцию типа «authorize»,
		 * то операция «Capture Online» из административного интерфейса будет недоступна:
		 * @see \Magento\Sales\Model\Order\Payment::canCapture()
		 *		if ($authTransaction && $authTransaction->getIsClosed()) {
		 *			$orderTransaction = $this->transactionRepository->getByTransactionType(
		 *				Transaction::TYPE_ORDER,
		 *				$this->getId(),
		 *				$this->getOrder()->getId()
		 *			);
		 *			if (!$orderTransaction) {
		 *				return false;
		 *			}
		 *		}
		 * https://github.com/magento/magento2/blob/2.1.3/app/code/Magento/Sales/Model/Order/Payment.php#L263-L281
		 * «How is \Magento\Sales\Model\Order\Payment::canCapture() implemented and used?»
		 * https://mage2.pro/t/650
		 * «How does Magento 2 decide whether to show the «Capture Online» dropdown
		 * on a backend's invoice screen?»: https://mage2.pro/t/2475
		 */
		/** @var OP $op */
		$op = $this->op();
		/** @var string $action */
		$action = dftr($this->e()->ttCurrent(), [Ev::T_AUTHORIZE => AC::A, Ev::T_CAPTURE => AC::C]);
		$op->setIsTransactionClosed(AC::C === $action);
		/**
		 * 2017-01-15
		 * $this->m()->setStore($o->getStoreId()); здесь не нужно,
		 * потому что это делается автоматически в ядре:
		 * @see \Magento\Sales\Model\Order\Payment\Operations\AuthorizeOperation::authorize():
		 * 		$method->setStore($order->getStoreId());
		 * https://github.com/magento/magento2/blob/2.1.3/app/code/Magento/Sales/Model/Order/Payment/Operations/AuthorizeOperation.php#L44
		 */
		dfp_action($op, $action);
		$this->o()->save();
	}
}