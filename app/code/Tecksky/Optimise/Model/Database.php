<?php

namespace Tecksky\Optimise\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Class Database
 */
class Database
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Database constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get list of log tables
     * @return array
     */
    public function getLogTables()
    {
        return [
            'captcha_log' => 'captcha_log',
            'customer_log' => 'customer_log',
            'cron_schedule' => 'cron_schedule',
            'customer_visitor' => 'customer_visitor',
            'oauth_token_request_log' => 'oauth_token_request_log',
            'sendfriend_log' => 'sendfriend_log',
            'watchlog' => 'watchlog',
            'doofinder_feed_log' => 'doofinder_feed_log',
            'mageplaza_smtp_log' => 'mageplaza_smtp_log',
            'amasty_file_report' => 'amasty_file_report',
            'reporting_system_updates' => 'reporting_system_updates',
            'reporting_users' => 'reporting_users',
            'report_event' => 'report_event',
            'report_viewed_product_index' => 'report_viewed_product_index',
            'report_viewed_product_aggregated_daily'    => 'report_viewed_product_aggregated_daily',
            'report_viewed_product_aggregated_monthly'  => 'report_viewed_product_aggregated_monthly',
            'report_viewed_product_aggregated_yearly'   => 'report_viewed_product_aggregated_yearly',
            'report_compared_product_index' => 'report_compared_product_index',
            'quote_shipping_rate' => 'quote_shipping_rate'
        ];
    }

    /**
     * Clear log tables
     * @return $this
     */
    public function truncateLogTable()
    {
        foreach ($this->getLogTables() as $table) {

            $table = $this->resource->getTableName($table);

            if ($this->resource->getConnection()->isTableExists($table)) {

                $this->resource->getConnection()->truncateTable($table);
            }
        }

        $this->clearSearchTerms();

        $this->clearMirasvitTables();

        return $this;
    }

    /**
     * clear only zero result search query
     */
    private function clearSearchTerms()
    {
        $table = $this->resource->getTableName('search_query');

        if ($this->resource->getConnection()->isTableExists($table)) {

            $this->resource->getConnection()->delete($table, ['num_results <= ?' => 0]);
        }
    }

    /**
     * clear only zero result search query
     */
    private function clearMirasvitTables()
    {
        $mst_tbls = [
            'mst_cache_warmer_job',
            'mst_cache_warmer_log',
            'mst_cache_warmer_page',
           
            'mst_cache_warmer_report',
            'mst_cache_warmer_report_aggregated',
            'mst_cache_warmer_trace',
          
            'mst_search_report_log',
        ];

        foreach ($mst_tbls as $tbl) {
         
            $table = $this->resource->getTableName($tbl);
            
            if ($this->resource->getConnection()->isTableExists($table)) {
                
                $this->resource->getConnection()->truncateTable($table);
            }
        }
    }
}