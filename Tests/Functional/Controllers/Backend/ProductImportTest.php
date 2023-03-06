<?php
declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagImportExport\Tests\Functional\Controllers\Backend;

use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use SwagImportExport\Components\UploadPathProvider;
use SwagImportExport\Controllers\Backend\Shopware_Controllers_Backend_SwagImportExportImport;
use SwagImportExport\Tests\Helper\ContainerTrait;
use SwagImportExport\Tests\Helper\TestViewMock;
use Symfony\Component\HttpFoundation\Request;

class ProductImportTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    private const DEFAULT_PRODUCT_PROFILE_ID = '5';

    public function testPrepareImportProductImportFile(): void
    {
        $importController = $this->getImportController();
        $view = new TestViewMock();

        $importController->setView($view);

        copy(\ImportExportTestKernel::IMPORT_FILES_DIR . 'ArticleImport.xml', $this->getUploadFileProvider()->getPath() . '/ArticleImport.xml');

        $importController->prepareImportAction(new Request([
            'profileId' => self::DEFAULT_PRODUCT_PROFILE_ID,
            'importFile' => 'ArticleImport.xml',
        ]));

        static::assertEquals(2, $view->getAssign('count'));
    }

    public function testImportProductImportFile(): void
    {
        $this->setImportBatchSize();

        $importController = $this->getImportController();
        $view = new TestViewMock();

        $importController->setView($view);

        copy(\ImportExportTestKernel::IMPORT_FILES_DIR . 'ArticleImport.xml', $this->getUploadFileProvider()->getPath() . '/ArticleImport.xml');

        $importController->importAction(new Request([
            'profileId' => self::DEFAULT_PRODUCT_PROFILE_ID,
            'importFile' => 'ArticleImport.xml',
        ]));

        static::assertSame(1, $view->getAssign('data')['position']);
    }

    private function getImportController(): Shopware_Controllers_Backend_SwagImportExportImport
    {
        $controller = $this->getContainer()->get(Shopware_Controllers_Backend_SwagImportExportImport::class);
        $controller->setContainer($this->getContainer());

        return $controller;
    }

    private function getUploadFileProvider(): UploadPathProvider
    {
        return $this->getContainer()->get(UploadPathProvider::class);
    }

    private function setImportBatchSize(): void
    {
        $this->getContainer()->get('config_writer')->save('batch-size-import', 1, 'SwagImportExport');
        $this->getContainer()->get(\Zend_Cache_Core::class)->clean();
        $this->getContainer()->get(\Shopware_Components_Config::class)->setShop(Shopware()->Shop());
    }
}
