<?php
// 2016-12-02
namespace Df\Framework\Install;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface as IModuleContext;
use Magento\Framework\Setup\ModuleDataSetupInterface as IDataSetup;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Setup\Model\ModuleContext;
use Magento\Setup\Module\DataSetup;
abstract class Data
	extends \Df\Framework\Install implements InstallDataInterface, UpgradeDataInterface {
	/**
	 * 2016-12-02
	 * @override
	 * @see InstallSchemaInterface::install()
	 * @param DataSetup|IDataSetup $setup
	 * @param IModuleContext|ModuleContext $context
	 * @return void
	 */
	public function install(IDataSetup $setup, IModuleContext $context) {
		$this->process($setup, $context);
	}

	/**
	 * 2016-12-02
	 * @override
	 * @see UpgradeSchemaInterface::upgrade()
	 * @param DataSetup|IDataSetup $setup
	 * @param IModuleContext|ModuleContext $context
	 * @return void
	 */
	public function upgrade(IDataSetup $setup, IModuleContext $context) {
		$this->process($setup, $context);
	}
}