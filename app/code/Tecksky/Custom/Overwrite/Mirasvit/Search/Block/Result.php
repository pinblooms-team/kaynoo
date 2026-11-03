<?php
namespace Tecksky\Custom\Overwrite\Mirasvit\Search\Block;
use Mirasvit\Search\Block\Result as Subject;
use Mirasvit\Search\Api\Data\IndexInterface;

class Result
{
    public function afterGetIndexUrl(Subject $subject, $result, IndexInterface $index)
    {
        $result = str_replace("catalogsearch/result/index/q","search",$result);
        return $result;
    }
}
