<?php
namespace Df\Framework\Data\Form\Element;
/**
 * Этот класс не является одиночкой:
 * https://github.com/magento/magento2/blob/2.0.0/lib/internal/Magento/Framework/Data/Form/AbstractForm.php#L155
 */
class ArrayT extends Fieldset {
	/**
	 * 2015-11-19
	 * @override
	 * @see \Df\Framework\Data\Form\Element\Fieldset::onFormInitialized()
	 * @used-by \Df\Framework\Data\Form\Element\AbstractElementPlugin::afterSetForm()
	 * @return void
	 */
	public function onFormInitialized() {
		parent::onFormInitialized();
		$this->addClass('df-array');
		$this->item();
		$this->item();
		df_form_element_init($this, 'array/main', [], [
			'Df_Framework::formElement/array/main.css'
		], 'before');
	}

	/**
	 * 2015-12-29
	 * @return Fieldset
	 */
	private function item() {return $this->field($this->itemNextId(), $this->itemType());}

	/**
	 * 2015-12-29
	 * @return int
	 */
	private function itemNextId() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = 0;
		}
		return /*'item' . */$this->{__METHOD__}++;
	}

	/**
	 * 2015-12-29
	 * http://code.dmitry-fedyuk.com/m2/all/blob/57607cc23405c3dcde50999d063b2a7f49499260/Config/etc/system_file.xsd#L70
	 * http://code.dmitry-fedyuk.com/m2e/currency-format/blob/2d920d0c0579a134b140eb28b9a1dc3d11467df1/etc/adminhtml/system.xml#L53
		<field
			(...)
			type='Df\Framework\Data\Form\Element\ArrayT'
			dfItemType='Dfe\CurrencyFormat\FormElement'
			(...)
		>(...)</field>
	 * @return string
	 */
	private function itemType() {return $this->fc('dfItemType');}
}