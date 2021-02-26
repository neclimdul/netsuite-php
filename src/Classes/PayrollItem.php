<?php
/**
 * This file is part of the SevenShores/NetSuite library
 * AND originally from the NetSuite PHP Toolkit.
 *
 * New content:
 * @package    ryanwinchester/netsuite-php
 * @copyright  Copyright (c) Ryan Winchester
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @link       https://github.com/ryanwinchester/netsuite-php
 *
 * Original content:
 * @copyright  Copyright (c) NetSuite Inc.
 * @license    https://raw.githubusercontent.com/ryanwinchester/netsuite-php/master/original/NetSuite%20Application%20Developer%20License%20Agreement.txt
 * @link       http://www.netsuite.com/portal/developers/resources/suitetalk-sample-applications.shtml
 *
 * generated:  2021-02-25 04:57:54 PM CST
 */

namespace NetSuite\Classes;

class PayrollItem extends Record {
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $subsidiary;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $itemType;
    /**
     * @var string
     */
    public $name;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $vendor;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $expenseAccount;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $liabilityAccount;
    /**
     * @var boolean
     */
    public $employeePaid;
    /**
     * @var \NetSuite\Classes\PayrollItemAccountCategory
     */
    public $accountCategory;
    /**
     * @var boolean
     */
    public $inactive;
    /**
     * @var \NetSuite\Classes\CustomFieldList
     */
    public $customFieldList;
    /**
     * @var string
     */
    public $internalId;
    /**
     * @var string
     */
    public $externalId;
    static $paramtypesmap = array(
        "subsidiary" => "RecordRef",
        "itemType" => "RecordRef",
        "name" => "string",
        "vendor" => "RecordRef",
        "expenseAccount" => "RecordRef",
        "liabilityAccount" => "RecordRef",
        "employeePaid" => "boolean",
        "accountCategory" => "PayrollItemAccountCategory",
        "inactive" => "boolean",
        "customFieldList" => "CustomFieldList",
        "internalId" => "string",
        "externalId" => "string",
    );
}
