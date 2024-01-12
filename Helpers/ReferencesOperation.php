<?php

namespace NW\WebService\References\Operations\Notification\Helpers;

/**
 * Class ReferencesOperation
 *
 * @package NW\WebService\References\Operations\Notification\Helpers
 */
abstract class ReferencesOperation
{
    abstract public function sendNotifications(): array;

    /**
     * Get request params
     * 
     * @param $pName Params key
     * 
     * @return array|string $result The result
     */
    public function getRequest($pName)
    {
        $result = !empty($_REQUEST[$pName]) ? $_REQUEST[$pName] : [];

        return $result;
    }
}