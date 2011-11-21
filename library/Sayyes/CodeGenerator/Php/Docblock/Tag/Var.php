<?php
class Sayyes_CodeGenerator_Php_Docblock_Tag_Var extends Zend_CodeGenerator_Php_Docblock_Tag 
{
    
    /**
     * @var string
     */
    protected $_datatype = null;
    
    /**
     * @var string
     */
    protected $_description = null;
    
    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Docblock_Tag $reflectionTagReturn
     * @return Sayyes_CodeGenerator_Php_Docblock_Tag_Var
     */
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTagReturn)
    {
        $returnTag = new self();
        
        $returnTag->setName('var');
        $returnTag->setDatatype($reflectionTagReturn->getType()); // @todo rename
        $returnTag->setDescription($reflectionTagReturn->getDescription());
        
        return $returnTag;
    }
    
    /**
     * setDatatype()
     *
     * @param string $datatype
     * @return Sayyes_CodeGenerator_Php_Docblock_Tag_Var
     */
    public function setDatatype($datatype)
    {
        $this->_datatype = $datatype;
        return $this;
    }
    
    /**
     * getDatatype()
     *
     * @return string
     */
    public function getDatatype()
    {
        return $this->_datatype;
    }


    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@var ' . $this->_datatype . ' ' . $this->_description;
        return $output;
    }
    
}