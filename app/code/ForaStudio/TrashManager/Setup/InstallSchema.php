<?php
/**
 * ForaStudio Private Limited.
 *
 * @category  ForaStudio
 * @package   ForaStudio_TrashManager
 * @author    ForaStudio
 * @copyright Copyright (c) 2018-2019 ForaStudio Private Limited (https://forastudio.com)
 */

namespace ForaStudio\TrashManager\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface {
	/**
	 * {@inheritdoc}
	 *
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function install(
		SchemaSetupInterface $setup,
		ModuleContextInterface $context
	) {
		/**
		 * Create table 'fora_product_trash'
		 */
		$installer = $setup;

		$installer->startSetup();

		$table = $installer->getConnection()->newTable(
			$installer->getTable('fora_product_trash')
		)->addColumn(
			'trash_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			11,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Trash ID'
		)->addColumn(
			'entity_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			10,
			['nullable' => true],
			'Entity ID'
		)
		->addColumn(
			'attribute_set_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			5,
			['nullable' => true],
			'Attribute Set ID'
		)->addColumn(
			'type_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			32,
			['nullable' => true],
			'Type Id'
		)->addColumn(
			'sku',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			64,
			['nullable' => true],
			'SKU'
		)->addColumn(
			'has_options',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			6,
			['nullable' => true, 'default' => '0'],
			'Has Options'
		)->addColumn(
			'required_options',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			5,
			['unsigned' => true, 'nullable' => true, 'default' => '0'],
			'Required Options'
		)->addColumn(
			'product_data',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			null,
			['nullable' => true],
			'All Product Data'
		)->addColumn(
			'user_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			11,
			['nullable' => true],
			'User Id'
		)->addColumn(
			'created_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => true, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
			'Creation Time'
		)->addColumn(
			'status',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			1,
			['default' => '1', 'nullable' => true],
			'Status'
		)->addColumn(
            'remote_ip',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false, 'default' => ''],
            'User IP'
        )
        ->addColumn(
            'user_agent',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '',
            ['nullable' => false, 'default' => ''],
            'Browser Detail'
        )
		->addColumn(
			'trashed_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => true, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
			'Trashed Time'
		)->setComment(
			'Fora Product Trash'
		);

		$installer->getConnection()->createTable($table);

		/**
		 * Create table 'fora_category_trash'
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('fora_category_trash')
		)
			->addColumn(
				'trash_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
				'Trash ID'
			)
			->addColumn(
				'entity_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false],
				'Entity ID'
			)
			->addColumn(
				'attribute_set_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				null,
				['unsigned' => true, 'nullable' => false, 'default' => '0'],
				'Attriute Set ID'
			)
			->addColumn(
				'parent_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['unsigned' => true, 'nullable' => false, 'default' => '0'],
				'Parent Category ID'
			)
			->addColumn(
				'created_at',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				['nullable' => false],
				'Creation Time'
			)
			->addColumn(
				'updated_at',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				['nullable' => false],
				'Update Time'
			)
			->addColumn(
				'path',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				['nullable' => false],
				'Tree Path'
			)
			->addColumn(
				'position',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['nullable' => false],
				'Position'
			)
			->addColumn(
				'level',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['nullable' => false, 'default' => '0'],
				'Tree Level'
			)
			->addColumn(
				'children_count',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				['nullable' => false],
				'Child Count'
			)
			->addColumn(
				'category_data',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				null,
				['nullable' => true],
				'All Category Data'
			)->addColumn(
			'user_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['unsigned' => true, 'nullable' => false],
			'User Id'
			)
			->addColumn(
                'remote_ip',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false, 'default' => ''],
                'User IP'
            )
            ->addColumn(
                'user_agent',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '',
                ['nullable' => false, 'default' => ''],
                'Browser Detail'
            )
			->addColumn(
				'trashed_at',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				['nullable' => true, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
				'Trashed Time'
			)->setComment('Catalog Category Trash Table');

		$installer->getConnection()->createTable($table);
		$installer->endSetup();
	}
}
