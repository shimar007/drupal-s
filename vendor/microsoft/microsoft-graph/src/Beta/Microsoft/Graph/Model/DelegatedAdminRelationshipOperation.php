<?php
/**
* Copyright (c) Microsoft Corporation.  All Rights Reserved.  Licensed under the MIT License.  See License in the project root for license information.
* 
* DelegatedAdminRelationshipOperation File
* PHP version 7
*
* @category  Library
* @package   Microsoft.Graph
* @copyright (c) Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @link      https://graph.microsoft.com
*/
namespace Beta\Microsoft\Graph\Model;

/**
* DelegatedAdminRelationshipOperation class
*
* @category  Model
* @package   Microsoft.Graph
* @copyright (c) Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @link      https://graph.microsoft.com
*/
class DelegatedAdminRelationshipOperation extends Entity
{
    /**
    * Gets the createdDateTime
    *
    * @return \DateTime|null The createdDateTime
    */
    public function getCreatedDateTime()
    {
        if (array_key_exists("createdDateTime", $this->_propDict)) {
            if (is_a($this->_propDict["createdDateTime"], "\DateTime") || is_null($this->_propDict["createdDateTime"])) {
                return $this->_propDict["createdDateTime"];
            } else {
                $this->_propDict["createdDateTime"] = new \DateTime($this->_propDict["createdDateTime"]);
                return $this->_propDict["createdDateTime"];
            }
        }
        return null;
    }

    /**
    * Sets the createdDateTime
    *
    * @param \DateTime $val The createdDateTime
    *
    * @return DelegatedAdminRelationshipOperation
    */
    public function setCreatedDateTime($val)
    {
        $this->_propDict["createdDateTime"] = $val;
        return $this;
    }

    /**
    * Gets the data
    *
    * @return string|null The data
    */
    public function getData()
    {
        if (array_key_exists("data", $this->_propDict)) {
            return $this->_propDict["data"];
        } else {
            return null;
        }
    }

    /**
    * Sets the data
    *
    * @param string $val The data
    *
    * @return DelegatedAdminRelationshipOperation
    */
    public function setData($val)
    {
        $this->_propDict["data"] = $val;
        return $this;
    }

    /**
    * Gets the lastModifiedDateTime
    *
    * @return \DateTime|null The lastModifiedDateTime
    */
    public function getLastModifiedDateTime()
    {
        if (array_key_exists("lastModifiedDateTime", $this->_propDict)) {
            if (is_a($this->_propDict["lastModifiedDateTime"], "\DateTime") || is_null($this->_propDict["lastModifiedDateTime"])) {
                return $this->_propDict["lastModifiedDateTime"];
            } else {
                $this->_propDict["lastModifiedDateTime"] = new \DateTime($this->_propDict["lastModifiedDateTime"]);
                return $this->_propDict["lastModifiedDateTime"];
            }
        }
        return null;
    }

    /**
    * Sets the lastModifiedDateTime
    *
    * @param \DateTime $val The lastModifiedDateTime
    *
    * @return DelegatedAdminRelationshipOperation
    */
    public function setLastModifiedDateTime($val)
    {
        $this->_propDict["lastModifiedDateTime"] = $val;
        return $this;
    }

    /**
    * Gets the operationType
    *
    * @return DelegatedAdminRelationshipOperationType|null The operationType
    */
    public function getOperationType()
    {
        if (array_key_exists("operationType", $this->_propDict)) {
            if (is_a($this->_propDict["operationType"], "\Beta\Microsoft\Graph\Model\DelegatedAdminRelationshipOperationType") || is_null($this->_propDict["operationType"])) {
                return $this->_propDict["operationType"];
            } else {
                $this->_propDict["operationType"] = new DelegatedAdminRelationshipOperationType($this->_propDict["operationType"]);
                return $this->_propDict["operationType"];
            }
        }
        return null;
    }

    /**
    * Sets the operationType
    *
    * @param DelegatedAdminRelationshipOperationType $val The operationType
    *
    * @return DelegatedAdminRelationshipOperation
    */
    public function setOperationType($val)
    {
        $this->_propDict["operationType"] = $val;
        return $this;
    }

    /**
    * Gets the status
    *
    * @return DelegatedAdminRelationshipOperationStatus|null The status
    */
    public function getStatus()
    {
        if (array_key_exists("status", $this->_propDict)) {
            if (is_a($this->_propDict["status"], "\Beta\Microsoft\Graph\Model\DelegatedAdminRelationshipOperationStatus") || is_null($this->_propDict["status"])) {
                return $this->_propDict["status"];
            } else {
                $this->_propDict["status"] = new DelegatedAdminRelationshipOperationStatus($this->_propDict["status"]);
                return $this->_propDict["status"];
            }
        }
        return null;
    }

    /**
    * Sets the status
    *
    * @param DelegatedAdminRelationshipOperationStatus $val The status
    *
    * @return DelegatedAdminRelationshipOperation
    */
    public function setStatus($val)
    {
        $this->_propDict["status"] = $val;
        return $this;
    }

}
