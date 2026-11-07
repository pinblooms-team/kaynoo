<?php

declare(strict_types=1);

namespace Tecksky\Custom\Plugin\View;

use Magento\Framework\App\View\Deployment\Version;

/**
 * Magento reads deployed_version.txt without trim. A trailing newline
 * is injected into static URLs and breaks body data-mage-init JSON,
 * so mage.apply() never binds add-to-cart.
 */
class DeploymentVersionPlugin
{
    public function afterGetValue(Version $subject, $result)
    {
        return is_string($result) ? trim($result) : $result;
    }
}
