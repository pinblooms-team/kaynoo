<?php
namespace Tecksky\Custom\Overwrite\Amasty\Followup\Model;

use Amasty\Followup\Model\Formatmanager as Subject;

class Formatmanager {

    public function BeforeFormatDate(Subject $subject, $type, $field)
    {
        if ($type == 'customer') {
            $field = date("Y-m-d h:i:s");
        }
        return [$type, $field];
    }

}