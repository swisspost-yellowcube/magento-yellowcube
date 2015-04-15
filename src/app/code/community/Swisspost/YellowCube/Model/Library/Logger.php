<?php

use YellowCube\Util\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Logs into Magento log.
 */
class Swisspost_YellowCube_Model_Library_Logger extends AbstractLogger
{
    /**
     * @var string
     */
    protected $logFileName = 'yellowcube_lib.log';

    static $levelMap = array(
        LogLevel::EMERGENCY => Zend_Log::EMERG,
        LogLevel::ALERT => Zend_Log::ALERT,
        LogLevel::CRITICAL => Zend_Log::CRIT,
        LogLevel::ERROR => Zend_Log::ERR,
        LogLevel::WARNING => Zend_Log::WARN,
        LogLevel::NOTICE => Zend_Log::NOTICE,
        LogLevel::INFO => Zend_Log::INFO,
        LogLevel::DEBUG => Zend_Log::DEBUG,
    );

    /**
     * @inheritdoc
     */
    public function __construct($minLevel = LogLevel::DEBUG)
    {
        parent::__construct($minLevel);
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->isLevelLessThanMinimum($level)) {
            return;
        }

        foreach ($context as $key => $value) {
            if (false !==strpos($message, '{' . $key . '}')) {
                $message = str_replace('{' . $key . '}', '@' . $key, $message);
            }
        }

        Mage::log($message, self::$levelMap[$level], $this->logFileName, true);
    }
}
