<?php
use Df\Payment\TM;
/**
 * 2017-03-23
 * @used-by \Df\Payment\Block\Info::isWait()
 * @used-by \Df\PaypalClone\BlockInfo::responseF()
 * @used-by \Df\PaypalClone\Method::responseF()
 * @used-by \Df\StripeClone\Block\Info::prepare()
 * @used-by \Dfe\AllPay\Block\Info\Offline::custom()
 * @used-by \Dfe\AllPay\Method::getInfoBlockType()
 * @used-by \Dfe\AllPay\Method::paymentOptionTitle()
 * @used-by \Dfe\SecurePay\Refund::process()
 * @used-by \Dfe\SecurePay\Signer\Response::values()
 * @param string|object $m
 * @return TM
 */
function df_tm($m) {return TM::s($m);}