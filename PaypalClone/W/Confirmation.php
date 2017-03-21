<?php
namespace Df\PaypalClone\W;
use Df\Sales\Model\Order\Payment as DfOP;
use Magento\Payment\Model\Method\AbstractMethod as M;
use Magento\Sales\Model\Order as O;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment as OP;
use Magento\Sales\Model\Order\Payment\Transaction as T;
/**
 * 2016-07-12
 * @see \Df\GingerPaymentsBase\W\Handler
 * @see \Dfe\AllPay\W\Handler
 * 2017-03-20
 * The class is not abstract anymore: you can use it as a base for a virtual type:
 * 1) SecurePay: https://github.com/mage2pro/securepay/blob/1.4.2/etc/di.xml#L8
 * @method Event e()
 */
class Confirmation extends Handler {
	/**
	 * 2017-01-01
	 * @override
	 * @see \Df\Payment\W\Handler::_handle()
	 * @used-by \Df\Payment\W\Handler::handle()
	 * @return void
	 */
	final protected function _handle() {
		/**
		 * 2016-07-10
		 * @uses \Magento\Sales\Model\Order\Payment\Transaction::TYPE_PAYMENT —
		 * это единственная транзакции без специального назначения,
		 * и поэтому мы можем безопасно его использовать.
		 * 2017-01-16
		 * Идентификатор и данные транзакции мы уже установили в методе @see \Df\Payment\W\Hav::op()
		 */
		$this->op()->addTransaction(T::TYPE_PAYMENT);
		/** @var Event $e */
		$e = $this->e();
		// 2016-07-14
		// Если покупатель не смог или не захотел оплатить заказ, то мы заказ отменяем,
		// а затем, когда платёжная система возвратит покупателя в магазин,
		// то мы проверим, не отменён ли последний заказ,
		// и если он отменён — то восстановим корзину покупателя.
		/** @var bool $succ */
		if (!($succ = $e->isSuccessful())) {
			$this->o()->cancel();
		}
		else if ($e->needCapture()) {
			$this->capture();
		}
		$this->o()->save();
		// 2016-08-17
		// https://code.dmitry-fedyuk.com/m2e/allpay/issues/17
		// Письмо отсылаем только если isSuccessful() вернуло true
		// (при этом не факт, что оплата уже прошла: при оффлайновом способе оплаты
		// isSuccessful() говорит лишь о том, что покупатель успешно выбрал оффлайновый способ оплаты,
		// а подтверждение платежа придёт лишь потом, через несколько дней).
		if ($succ) {
			$this->sendEmailIfNeeded();
		}
	}

	/**
	 * 2016-07-12
	 * @return void
	 */
	private function capture() {
		/** @var O $o */
		/** @var OP $op */
		DfOP::processActionS($op = $this->op(), M::ACTION_AUTHORIZE_CAPTURE, $o = $this->o());
		DfOP::updateOrderS($op, $o, O::STATE_PROCESSING, df_order_ds(O::STATE_PROCESSING), true);
	}

	/**
	 * 2016-08-17
	 * 2016-07-15
	 * Send email confirmation to the customer.
	 * https://code.dmitry-fedyuk.com/m2e/allpay/issues/6
	 * It is implemented by analogy with https://github.com/magento/magento2/blob/2.1.0/app/code/Magento/Paypal/Model/Ipn.php#L312-L321
	 *
	 * 2016-07-15
	 * What is the difference between InvoiceSender and OrderSender?
	 * https://mage2.pro/t/1872
	 *
	 * 2016-07-18
	 * Раньше тут был код:
	 *		$payment = $this->o()->getPayment();
	 *		if ($payment && $payment->getCreatedInvoice()) {
	 *			df_order_send_email($this->o());
	 *		}
	 *
	 * 2016-08-17
	 * https://code.dmitry-fedyuk.com/m2e/allpay/issues/13
	 * В сценарии оффлайновой оплаты мы попадаем в эту точку программы дважды:
	 * 1) Когда платёжная система уведомляет нас о том,
	 * что покупатель выбрал оффлайновый способ оплаты.
	 * В этом случае счёта ещё нет ($this->capture() выше не выполнялось),
	 * и отсылаем покупателю письмо с заказом.
	 *
	 * 2) Когда платёжная система уведомляет нас о приходе оплаты.
	 * В этом случае счёт уже присутствует, и отсылаем покупателю письмо со счётом.
	 *
	 * @used-by handle()
	 * @return void
	 */
	private function sendEmailIfNeeded() {
		/** @var O $o */
		$o = $this->o();
		/**
		 * 2016-08-17
		 * @uses \Magento\Sales\Model\Order::getEmailSent() говорит,
		 * было ли уже отослано письмо о заказе. Отсылать его повторно не надо.
		 */
		if (!$o->getEmailSent()) {
			df_order_send_email($o);
		}
		// 2016-08-17
		// Помещаем код ниже в блок else, потому что если письмо с заказом уже отослано,
		// то письмо со счётом отсылать не надо, даже если счёт присутствует и письмо о нём не отсылалось.
		else {
			/** @var Invoice $i */
			$i = $o->getInvoiceCollection()->getLastItem();
			/**
			 * 2016-08-17
			 * @uses \Magento\Framework\Data\Collection::getLastItem()
			 * возвращает объект, если коллекция пуста: https://mage2.pro/t/3538
			 */
			if ($i->getId() && !$i->getEmailSent()) {
				df_invoice_send_email($i);
			}
		}
	}
}