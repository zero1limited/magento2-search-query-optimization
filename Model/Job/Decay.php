<?php
namespace Zero1\SearchQueryOptimization\Model\Job;

use Carbon\Carbon;
use Magento\Framework\App\ScopeInterface as AppScopeInterface;
use \Magento\Search\Model\ResourceModel\Query\Collection as QueryCollection;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Decay
{
    const MAX_RESULTS = 1000;
    const CONFIG_PATH_DECAY_DAYS = 'catalog/search/zero1_search_query_optimization_decay_days';

    protected $queryCollection;

    protected $scopeConfig;

    public function __construct(
        QueryCollection $queryCollection,
        ScopeConfigInterface $scopeInterface
    ){
        $this->queryCollection = $queryCollection;    
        $this->scopeConfig = $scopeInterface;
    }

    public function execute()
    {
        $time = Carbon::now();
        $time->subDays($this->getDecayDays());
        $this->queryCollection->addFieldToFilter('updated_at', ['lt' => $time->format('Y-m-d H:i:s')])
            ->addFieldToFilter('redirect', ['null' => null])
            ->addFieldToFilter('is_processed', ['eq' => 0])
            ->setPageSize(self::MAX_RESULTS)
            ->addOrder('popularity', QueryCollection::SORT_ORDER_ASC)
            ->addOrder('query_id', QueryCollection::SORT_ORDER_ASC);
        // echo 'sql: '.$this->queryCollection->getSelectSql(true).PHP_EOL;

        $reducedCount = 0;
        $deletedCount = 0;
        /** @var \Magento\Search\Model\Query $query */
        foreach($this->queryCollection as $query){
            // echo $query->getId().' '.$query->getQueryText().' '.$query->getPopularity().PHP_EOL;

            if($query->getPopularity() > 0){
                $query->setPopularity(($query->getPopularity() - 1));
                $query->save();
                $reducedCount++;
            }else{
                $query->delete();
                $deletedCount++;
            }
        }

        // echo 'total records: '.$this->queryCollection->count().PHP_EOL;
        // echo 'deleted: '.$deletedCount.PHP_EOL;
        // echo 'reduced: '.$reducedCount.PHP_EOL;
        // die;
    }

    protected function getDecayDays()
    {
        return max((int)$this->scopeConfig->getValue(self::CONFIG_PATH_DECAY_DAYS), 1);
    }
}