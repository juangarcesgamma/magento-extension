<?php


namespace Extend\Warranty\Model\Api;


use Extend\Warranty\Api\TimeUpdaterInterface;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Cache\Manager;

class TimeUpdater implements TimeUpdaterInterface
{
    /**
     * @var Writer
     */
    protected $configWriter;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Manager
     */
    private $cacheManager;

    public function __construct
    (
        Writer $configWriter,
        TimezoneInterface $timezone,
        Manager $cacheManager
    )
    {
        $this->configWriter = $configWriter;
        $this->timezone = $timezone;
        $this->cacheManager = $cacheManager;
    }

    public function updateLastSync(): string
    {
        $date = $this->timezone->date()->format('Y-m-d H:i:s');
        $this->configWriter->save(self::LAST_SYNC_PATH, $date);
        $this->cacheManager->clean($this->cacheManager->getAvailableTypes());

        return $this->timezone->formatDate($date, 1, true);
    }
}