<?php

namespace NW\WebService\References\Operations\Notification\Helpers;

/**
 * Class GetEmail
 *
 * @package NW\WebService\References\Operations\Notification\Helpers
 */
class GetEmail
{
    /**
     * Get contractor email
     * 
     * @return string $contractorEmail Contractor email
     */
    public static function getResellerEmailFrom()
    {
        $contractorEmail = 'contractor@example.com';

        return $contractorEmail;
    }

    /**
     * Get contractor email
     * 
     * @param int    $resellerId Contractor id
     * @param string $event      Event name
     * 
     * @return array $emails Emails
     */
    public static function getEmailsByPermit($resellerId, $event)
    {
        /* Fakes the method */
        $emails = [
            'someemeil@example.com',
            'someemeil2@example.com'
        ];
        
        return $emails;
    }
}