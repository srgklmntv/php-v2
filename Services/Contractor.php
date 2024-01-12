<?php

namespace NW\WebService\References\Operations\Notification\Services;

use NW\WebService\References\Operations\Notification\Enum\ContractorTypes;

/**
 * Class Contractor
 *
 * @package NW\WebService\References\Operations\Notification\Services
 *
 * @property int    id   Contractor id
 * @property string type Contractor type
 * @property string name Contractor name
 */
class Contractor
{
    public $id;
    public $name;
    public $type = ContractorTypes::TYPE_CUSTOMER; // Customer by default

    /**
     * Get contractor by id
     * 
     * @param int $resellerId Contractor id
     * 
     * @return NW\WebService\References\Operations\Notification\Contractor $contractor The result of getting contractor
     */
    public static function getById(int $resellerId) : self
    {
        /* Fakes the getById method */
        $contractor = new self($resellerId);

        return $contractor;
    }

    /**
     * Get contractor full name
     * 
     * @return string $fullName Contractor full name
     */
    public function getFullName() : string
    {
        $fullName = $this->name . ' ' . $this->id;

        return $fullName;
    }
}
