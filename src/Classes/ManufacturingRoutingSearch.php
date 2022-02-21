<?php
/**
 * This file is part of the netsuitephp/netsuite-php library
 * AND originally from the NetSuite PHP Toolkit.
 *
 * New content:
 * @package    ryanwinchester/netsuite-php
 * @copyright  Copyright (c) Ryan Winchester
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @link       https://github.com/netsuitephp/netsuite-php
 *
 * Original content:
 * @copyright  Copyright (c) NetSuite Inc.
 * @license    https://raw.githubusercontent.com/netsuitephp/netsuite-php/master/original/NetSuite%20Application%20Developer%20License%20Agreement.txt
 * @link       http://www.netsuite.com/portal/developers/resources/suitetalk-sample-applications.shtml
 */

namespace NetSuite\Classes;

class ManufacturingRoutingSearch extends SearchRecord {
    /**
     * @var \NetSuite\Classes\ManufacturingRoutingSearchBasic
     */
    public $basic;
    /**
     * @var \NetSuite\Classes\ItemSearchBasic
     */
    public $itemJoin;
    /**
     * @var \NetSuite\Classes\LocationSearchBasic
     */
    public $locationJoin;
    /**
     * @var \NetSuite\Classes\ManufacturingCostTemplateSearchBasic
     */
    public $manufacturingCostTemplateJoin;
    /**
     * @var \NetSuite\Classes\EntityGroupSearchBasic
     */
    public $manufacturingWorkCenterJoin;
    /**
     * @var \NetSuite\Classes\EmployeeSearchBasic
     */
    public $userJoin;
    /**
     * @var \NetSuite\Classes\CustomSearchJoin[]
     */
    public $customSearchJoin;
    /**
     * @var string[]
     */
    public static $paramtypesmap = array(
        "basic" => "ManufacturingRoutingSearchBasic",
        "itemJoin" => "ItemSearchBasic",
        "locationJoin" => "LocationSearchBasic",
        "manufacturingCostTemplateJoin" => "ManufacturingCostTemplateSearchBasic",
        "manufacturingWorkCenterJoin" => "EntityGroupSearchBasic",
        "userJoin" => "EmployeeSearchBasic",
        "customSearchJoin" => "CustomSearchJoin[]",
    );
}
