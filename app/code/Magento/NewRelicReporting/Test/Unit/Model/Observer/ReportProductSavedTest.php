<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\NewRelicReporting\Model\Observer\ReportProductSaved;

/**
 * Class ReportProductSavedTest
 */
class ReportProductSavedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportProductSaved
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\SystemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\System|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $systemModel;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\NewRelicReporting\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->systemFactory = $this->getMockBuilder('Magento\NewRelicReporting\Model\SystemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->systemModel = $this->getMockBuilder('Magento\NewRelicReporting\Model\System')
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonEncoder = $this->getMockBuilder('Magento\Framework\Json\EncoderInterface')
            ->getMock();
        $this->dateTime = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->setMethods(['formatDate'])
            ->getMock();
        $this->systemFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->systemModel);

        $this->model = new ReportProductSaved(
            $this->config,
            $this->systemFactory,
            $this->jsonEncoder,
            $this->dateTime
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportProductSavedModuleDisabledFromConfig()
    {
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->assertSame(
            $this->model,
            $this->model->execute($eventObserver)
        );
    }

    /**
     * Test case when module is enabled in config
     *
     * @return void
     */
    public function testReportProductSaved()
    {
        $testType = 'adminProductChange';
        $testAction = 'JSON string';
        $testUpdated = '1970-01-01 00:00:00';

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder('Magento\Framework\Event')
            ->setMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->isObjectNew(true);
        $event->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $this->dateTime->expects($this->once())
            ->method('formatDate')
            ->willReturn($testUpdated);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($testAction);
        $this->systemModel->expects($this->once())
            ->method('setData')
            ->with(['type' => $testType, 'action' => $testAction, 'updated_at' => $testUpdated])
            ->willReturnSelf();
        $this->systemModel->expects($this->once())
            ->method('save');

        $this->assertSame(
            $this->model,
            $this->model->execute($eventObserver)
        );
    }

    /**
     * Test case when module is enabled in config and product updated
     *
     * @return void
     */
    public function testReportProductUpdated()
    {
        $testType = 'adminProductChange';
        $testAction = 'JSON string';
        $testUpdated = '1970-01-01 00:00:00';

        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $event = $this->getMockBuilder('Magento\Framework\Event')
            ->setMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserver->expects($this->once())
            ->method('getEvent')
            ->willReturn($event);
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->isObjectNew(false);
        $event->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $this->dateTime->expects($this->once())
            ->method('formatDate')
            ->willReturn($testUpdated);
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($testAction);
        $this->systemModel->expects($this->once())
            ->method('setData')
            ->with(['type' => $testType, 'action' => $testAction, 'updated_at' => $testUpdated])
            ->willReturnSelf();
        $this->systemModel->expects($this->once())
            ->method('save');

        $this->assertSame(
            $this->model,
            $this->model->execute($eventObserver)
        );
    }
}
