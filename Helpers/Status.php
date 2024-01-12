<?php

namespace NW\WebService\References\Operations\Notification\Helpers;

/**
 * Class Status
 *
 * @package NW\WebService\References\Operations\Notification\Helpers
 * 
 * @property int    id   Status id
 * @property string name Status name
 */
class Status
{
    public $id, $name;

    /**
     * Get status name by id
     * 
     * @param int $id Status id
     * 
     * @return string $result Status name
     */
    public static function getName(int $id): string
    {
        $statuses = [
            0 => 'Completed',
            1 => 'Pending',
            2 => 'Rejected',
        ];

        $result = !empty($statuses[$id]) ? $statuses[$id] : '';
        
        return $result;
    }
}