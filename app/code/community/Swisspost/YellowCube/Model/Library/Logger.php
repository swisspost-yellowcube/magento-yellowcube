<?php

use Psr\Log\AbstractLogger;
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
    public function log($level, $message, array $context = array())
    {
        Mage::log($message . print_r($context, true), self::$levelMap[$level], $this->logFileName, true);
    }
}
