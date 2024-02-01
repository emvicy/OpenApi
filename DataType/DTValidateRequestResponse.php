<?php

/**
 * @name $OpenApiDataType
 */
namespace OpenApi\DataType;

use MVC\MVCTrait\TraitDataType;

class DTValidateRequestResponse
{
	use TraitDataType;

	const DTHASH = '654f645128c58e61fd44b7f9c3009755';

	/**
	 * @required true
	 * @var bool
	 */
	protected $bSuccess;

	/**
	 * @required true
	 * @var DTValidateMessage[]
	 */
	protected $aMessage;

	/**
	 * @required true
	 * @var array
	 */
	protected $aValidationResult;

	/**
	 * DTValidateRequestResponse constructor.
	 * @param array $aData
	 * @throws \ReflectionException 
	 */
	public function __construct(array $aData = array())
	{
		\MVC\Event::RUN ('DTValidateRequestResponse.__construct.before', \MVC\DataType\DTArrayObject::create($aData)->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->bSuccess = false;
		$this->aMessage = array();
		$this->aValidationResult = array();

		foreach ($aData as $sKey => $mValue)
		{
			$sMethod = 'set_' . $sKey;

			if (method_exists($this, $sMethod))
			{
				$this->$sMethod($mValue);
			}
		}

		\MVC\Event::RUN ('DTValidateRequestResponse.__construct.after', \MVC\DataType\DTArrayObject::create($aData));
	}

    /**
     * @param array $aData
     * @return DTValidateRequestResponse
     * @throws \ReflectionException
     */
    public static function create(array $aData = array())
    {
        \MVC\Event::RUN ('DTValidateRequestResponse.create.before', \MVC\DataType\DTArrayObject::create($aData)->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));
        
        $oObject = new self($aData);

        \MVC\Event::RUN ('DTValidateRequestResponse.create.after', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('DTValidateRequestResponse')->set_sValue($oObject)));
        
        return $oObject;
    }

	/**
	 * @param bool $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_bSuccess($aValue)
	{
		\MVC\Event::RUN ('DTValidateRequestResponse.set_bSuccess.before', \MVC\DataType\DTArrayObject::create(array('bSuccess' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->bSuccess = (bool) $aValue;

		return $this;
	}

	/**
	 * @param DTValidateMessage[]  $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_aMessage($aValue)
	{
		\MVC\Event::RUN ('DTValidateRequestResponse.set_aMessage.before', \MVC\DataType\DTArrayObject::create(array('aMessage' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$aValue = (array) $aValue;
                
        foreach ($aValue as $mKey => $aData)
        {
            $aData = (array) $aData; 
            
            if (false === ($aData instanceof DTValidateMessage))
            {
                $aValue[$mKey] = new DTValidateMessage($aData);
            }
        }

		$this->aMessage = $aValue;

		return $this;
	}

	/**
	 * @param DTValidateMessage $mValue
	 * @return $this
	 */
	public function add_aMessage(DTValidateMessage $mValue)
	{
		$this->aMessage[] = $mValue;

		return $this;
	}

	/**
	 * @param array $aValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_aValidationResult($aValue)
	{
		\MVC\Event::RUN ('DTValidateRequestResponse.set_aValidationResult.before', \MVC\DataType\DTArrayObject::create(array('aValidationResult' => $aValue))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		$this->aValidationResult = (array) $aValue;

		return $this;
	}

	/**
	 * @return bool
	 * @throws \ReflectionException
	 */
	public function get_bSuccess()
	{
		\MVC\Event::RUN ('DTValidateRequestResponse.get_bSuccess.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('bSuccess')->set_sValue($this->bSuccess))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->bSuccess;
	}

	/**
	 * @return DTValidateMessage[]
	 * @throws \ReflectionException
	 */
	public function get_aMessage()
	{
		\MVC\Event::RUN ('DTValidateRequestResponse.get_aMessage.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aMessage')->set_sValue($this->aMessage))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->aMessage;
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	public function get_aValidationResult()
	{
		\MVC\Event::RUN ('DTValidateRequestResponse.get_aValidationResult.before', \MVC\DataType\DTArrayObject::create()->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aValidationResult')->set_sValue($this->aValidationResult))->add_aKeyValue(\MVC\DataType\DTKeyValue::create()->set_sKey('aBacktrace')->set_sValue(\MVC\Debug::prepareBacktraceArray(debug_backtrace()))));

		return $this->aValidationResult;
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_bSuccess()
	{
        return 'bSuccess';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_aMessage()
	{
        return 'aMessage';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_aValidationResult()
	{
        return 'aValidationResult';
	}

	/**
	 * @return false|string JSON
	 */
	public function __toString()
	{
        return $this->getPropertyJson();
	}

	/**
	 * @return false|string
	 */
	public function getPropertyJson()
	{
        return json_encode($this->getPropertyArray());
	}

	/**
	 * @return array
	 */
	public function getPropertyArray()
	{
        return get_object_vars($this);
	}

	/**
	 * @return array
	 * @throws \ReflectionException
	 */
	public function getConstantArray()
	{
		$oReflectionClass = new \ReflectionClass($this);
		$aConstant = $oReflectionClass->getConstants();

		return $aConstant;
	}

	/**
	 * @return $this
	 */
	public function flushProperties()
	{
		foreach ($this->getPropertyArray() as $sKey => $aValue)
		{
			$sMethod = 'set_' . $sKey;

			if (method_exists($this, $sMethod)) 
			{
				$this->$sMethod('');
			}
		}

		return $this;
	}

}
