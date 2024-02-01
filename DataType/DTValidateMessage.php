<?php
# 2024-02-01 13:58:56

use MVC\DataType\DTValue;
use MVC\MVCTrait\TraitDataType;

class DTValidateMessage
{
	use TraitDataType;

	public const DTHASH = '35daf1c715d98d6bd466f2311c4aaa39';

	/**
	 * @required true
	 * @var string
	 */
	protected $sSubject;

	/**
	 * @required true
	 * @var string
	 */
	protected $sBody;

	/**
	 * DTValidateMessage constructor.
	 * @param array $aData
	 * @throws \ReflectionException 
	 */
	public function __construct(array $aData = array())
	{
		$oDTValue = DTValue::create()->set_mValue($aData);
		\MVC\Event::run('DTValidateMessage.__construct.before', $oDTValue);
		$aData = $oDTValue->get_mValue();

		$this->sSubject = null;
		$this->sBody = null;

		foreach ($aData as $sKey => $mValue)
		{
			$sMethod = 'set_' . $sKey;

			if (method_exists($this, $sMethod))
			{
				$this->$sMethod($mValue);
			}
		}

		$oDTValue = DTValue::create()->set_mValue($aData); \MVC\Event::run('DTValidateMessage.__construct.after', $oDTValue);
	}

    /**
     * @param array $aData
     * @return DTValidateMessage
     * @throws \ReflectionException
     */
    public static function create(array $aData = array())
    {
        $oDTValue = DTValue::create()->set_mValue($aData);
		\MVC\Event::run('DTValidateMessage.create.before', $oDTValue);
		$oObject = new self($oDTValue->get_mValue());
        $oDTValue = DTValue::create()->set_mValue($oObject); \MVC\Event::run('DTValidateMessage.create.after', $oDTValue);

        return $oDTValue->get_mValue();
    }

	/**
	 * @param string $mValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_sSubject(string $mValue)
	{
		$oDTValue = DTValue::create()->set_mValue($mValue); 
		\MVC\Event::run('DTValidateMessage.set_sSubject.before', $oDTValue);
		$this->sSubject = (string) $oDTValue->get_mValue();

		return $this;
	}

	/**
	 * @param string $mValue 
	 * @return $this
	 * @throws \ReflectionException
	 */
	public function set_sBody(string $mValue)
	{
		$oDTValue = DTValue::create()->set_mValue($mValue); 
		\MVC\Event::run('DTValidateMessage.set_sBody.before', $oDTValue);
		$this->sBody = (string) $oDTValue->get_mValue();

		return $this;
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_sSubject() : string
	{
		$oDTValue = DTValue::create()->set_mValue($this->sSubject); 
		\MVC\Event::run('DTValidateMessage.get_sSubject.before', $oDTValue);

		return $oDTValue->get_mValue();
	}

	/**
	 * @return string
	 * @throws \ReflectionException
	 */
	public function get_sBody() : string
	{
		$oDTValue = DTValue::create()->set_mValue($this->sBody); 
		\MVC\Event::run('DTValidateMessage.get_sBody.before', $oDTValue);

		return $oDTValue->get_mValue();
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_sSubject()
	{
        return 'sSubject';
	}

	/**
	 * @return string
	 */
	public static function getPropertyName_sBody()
	{
        return 'sBody';
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
        return json_encode(\MVC\Convert::objectToArray($this));
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
		foreach ($this->getPropertyArray() as $sKey => $mValue)
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
