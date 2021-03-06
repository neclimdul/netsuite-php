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
 * generated:  2020-07-07 11:24:43 AM CDT
 */

namespace NetSuite\Classes;

class VendorCredit extends Record {
    /**
     * @var string
     */
    public $createdDate;
    /**
     * @var string
     */
    public $lastModifiedDate;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $nexus;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $subsidiaryTaxRegNum;
    /**
     * @var boolean
     */
    public $taxRegOverride;
    /**
     * @var boolean
     */
    public $taxDetailsOverride;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $customForm;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $account;
    /**
     * @var float
     */
    public $unApplied;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $billAddressList;
    /**
     * @var boolean
     */
    public $autoApply;
    /**
     * @var float
     */
    public $applied;
    /**
     * @var string
     */
    public $transactionNumber;
    /**
     * @var string
     */
    public $tranId;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $createdFrom;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $entity;
    /**
     * @var float
     */
    public $total;
    /**
     * @var float
     */
    public $userTotal;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $currency;
    /**
     * @var string
     */
    public $currencyName;
    /**
     * @var \NetSuite\Classes\Address
     */
    public $billingAddress;
    /**
     * @var string
     */
    public $tranDate;
    /**
     * @var float
     */
    public $exchangeRate;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $entityTaxRegNum;
    /**
     * @var string
     */
    public $taxPointDate;
    /**
     * @var float
     */
    public $userTaxTotal;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $postingPeriod;
    /**
     * @var string
     */
    public $memo;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $department;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $class;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $location;
    /**
     * @var \NetSuite\Classes\RecordRef
     */
    public $subsidiary;
    /**
     * @var \NetSuite\Classes\VendorCreditExpenseList
     */
    public $expenseList;
    /**
     * @var \NetSuite\Classes\VendorCreditItemList
     */
    public $itemList;
    /**
     * @var \NetSuite\Classes\AccountingBookDetailList
     */
    public $accountingBookDetailList;
    /**
     * @var \NetSuite\Classes\VendorCreditApplyList
     */
    public $applyList;
    /**
     * @var \NetSuite\Classes\TaxDetailsList
     */
    public $taxDetailsList;
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
        "createdDate" => "dateTime",
        "lastModifiedDate" => "dateTime",
        "nexus" => "RecordRef",
        "subsidiaryTaxRegNum" => "RecordRef",
        "taxRegOverride" => "boolean",
        "taxDetailsOverride" => "boolean",
        "customForm" => "RecordRef",
        "account" => "RecordRef",
        "unApplied" => "float",
        "billAddressList" => "RecordRef",
        "autoApply" => "boolean",
        "applied" => "float",
        "transactionNumber" => "string",
        "tranId" => "string",
        "createdFrom" => "RecordRef",
        "entity" => "RecordRef",
        "total" => "float",
        "userTotal" => "float",
        "currency" => "RecordRef",
        "currencyName" => "string",
        "billingAddress" => "Address",
        "tranDate" => "dateTime",
        "exchangeRate" => "float",
        "entityTaxRegNum" => "RecordRef",
        "taxPointDate" => "dateTime",
        "userTaxTotal" => "float",
        "postingPeriod" => "RecordRef",
        "memo" => "string",
        "department" => "RecordRef",
        "class" => "RecordRef",
        "location" => "RecordRef",
        "subsidiary" => "RecordRef",
        "expenseList" => "VendorCreditExpenseList",
        "itemList" => "VendorCreditItemList",
        "accountingBookDetailList" => "AccountingBookDetailList",
        "applyList" => "VendorCreditApplyList",
        "taxDetailsList" => "TaxDetailsList",
        "customFieldList" => "CustomFieldList",
        "internalId" => "string",
        "externalId" => "string",
    );
}
