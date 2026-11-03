<?php
declare(strict_types=1);

namespace Kaynoo\Partnership\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NotFoundException;

class Index extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly FileFactory $fileFactory,
        private readonly DirectoryList $directoryList
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $fileName = 'partnership-agreement.pdf';
        $absolutePath = $this->directoryList->getPath(DirectoryList::MEDIA)
            . '/partnership/' . $fileName;

        if (!is_file($absolutePath)) {
            throw new NotFoundException(__('Partnership agreement is not available.'));
        }

        return $this->fileFactory->create(
            $fileName,
            [
                'type' => 'filename',
                'value' => 'partnership/' . $fileName,
            ],
            DirectoryList::MEDIA,
            'application/pdf'
        );
    }
}
