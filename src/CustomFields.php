<?php
namespace Karolina;

class CustomFields
{
    private $fields = array();
    
    public function __construct($json = false)
    {
        if ($json) {
            $this->setFromJson($json);
        }
    }
    
    public function setCustomFields(array $customFields)
    {
        $this->customFields = $customFields;
    }
    
    public function getSingleCustomField($fieldName)
    {
        return $this->fields[$fieldName];
    }
    
    public function addSingleCustomField($fieldName, $customField)
    {
        $this->fields[$fieldName] = $customField;
    }

    public function setFromJson($json)
    {
        $array = json_decode($json, true);
        $this->fields = $array;
    }
    
    public function getAsJson()
    {
        return json_encode($this->fields);
    }
}
