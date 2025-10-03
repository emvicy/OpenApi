<?php

namespace OpenApi\Model;

use Emvicy\Emvicy;
use MVC\Cache;
use MVC\Config;
use MVC\File;
use MVC\Strings;

class Generate
{
    /**
     * @param      $sOpenApiFile
     * @param      $sSubDirName
     * @param      $bUnlinkDir
     * @param      $bValueFromExample
     * @param bool $bDebug
     * @return bool
     * @throws \ReflectionException
     */
    public static function DTClassesOnOpenapi3yaml($sOpenApiFile = '', $sSubDirName = '', $bUnlinkDir = true, $bValueFromExample = true, bool $bDebug = false)
    {
        if (true === empty($sOpenApiFile) || true === empty($sSubDirName))
        {
            return false;
        }

        $sCacheKey = Strings::seofy(__FUNCTION__) . '.' . $sSubDirName . '.' . md5_file($sOpenApiFile);
        $sDir = File::secureFilePath(Config::get_MVC_MODULE_PRIMARY_DATATYPE_DIR() . '/' . $sSubDirName);

        if (false === empty(Cache::getCache($sCacheKey)) && true === file_exists($sDir) && false === $bUnlinkDir)
        {
            return false;
        }

        // array of yaml file
        $aYaml = self::getArrayOfYaml($sOpenApiFile);

        // get schema
        $aSchema = self::getAllSchemas($aYaml);

        // skip on empty
        if (true === empty($aSchema))
        {
            return false;
        }

        // for openapi version 3 only
        if (3 !== (int) ($aYaml['openapi'] ?? null))
        {
            return false;
        }

        if (true === $bDebug)
        {
            dump(str_repeat('-', 80));
            dump("URL: " . $sOpenApiFile);
        }

        // create namespace string
        $sNamespace = substr(
            str_replace('/', '\\', str_replace(Config::get_MVC_MODULES_DIR(), '', Config::get_MVC_MODULE_PRIMARY_DATATYPE_DIR())
                                   . (('' !== $sSubDirName) ? '/' . $sSubDirName : ''))
            , 1
        );

        // base setup
        $aDataType = array(
            'dir' => $sDir,
            'unlinkDir' => (boolean) $bUnlinkDir
        );

        // iterate schema and create classes
        foreach ($aSchema as $sName => $aValue)
        {
            // skip non objects
            if ('object' !== ($aValue['type'] ?? null))
            {
                continue;
            }

            if (true === $bDebug)
            {
                dump("Class: " . $sNamespace . '\\' . $sName);
            }

            // class
            $aDataType['class'][$sName] = array(
                'name' => $sName,
                'file' => $sName . '.php',
                'namespace' => $sNamespace,
                'createHelperMethods' => true,
                'constant' => array(),
            );

            $aProperty = ($aValue['properties'] ?? array());

            // iterate property array
            foreach ($aProperty as $sPropertyName => $aPropertySpecs)
            {
                $mVar = self::getSchemaItemPropertyType($aPropertySpecs);
                $bNullable = (boolean) ($aPropertySpecs['nullable'] ?? false);
                $mValue = (true === $bValueFromExample)
                    ? self::getSchemaItemPropertyValue($aPropertySpecs)
                    : null;

                if (true === isset($mVar))
                {
                    /*
                     * TYPE_STANDARD
                     */
                    ('string' === strtolower($mVar)) ? $mValue = '' : false;
                    ('int' === strtolower($mVar)) ? $mValue = 0 : false;
                    ('float' === strtolower($mVar)) ? $mValue = 0.0 : false;
                    ('bool' === strtolower($mVar)) ? $mValue = false : false;
                    ('array' === strtolower($mVar)) ? $mValue = 'array()' : false;

                    /*
                     * TYPE_OBJECT
                     */
                    $sRef = ($aPropertySpecs['$ref'] ?? null);

                    // var is type $ref; check type of ref
                    if (null !== $sRef)
                    {
                        $sNameOfRef = current(array_reverse(explode('/', $sRef)));

                        if ('object' === ($aSchema[$sNameOfRef]['type']) ?? null)
                        {
                            $mVar = '\\' . $aDataType['class'][$sName]['namespace'] . '\\' . $sNameOfRef;
                            $mValue = "$mVar::create()";
                        }
                        elseif ('array' === ($aSchema[$sNameOfRef]['type'] ?? null))
                        {
                            $sSubItemsRef = current(array_reverse(explode('/', ($aSchema[$sNameOfRef]['items']['$ref'] ?? null))));
                            $mVar = '\\' . $aDataType['class'][$sName]['namespace'] . '\\' . $sSubItemsRef . '[]';
                            $mValue = '$this->add_' . $sPropertyName . '(' . '\\' . $aDataType['class'][$sName]['namespace'] . '\\' . $sNameOfRef . '::create());';
                        }
                    }

                    /*
                     * TYPE_ARRAY_OF_OBJECT
                     */
                    $sItemsRef = ($aPropertySpecs['items']['$ref'] ?? null);

                    // var is array of type $ref
                    if ($mVar === 'array' && null !== $sItemsRef)
                    {
                        $sNameOfRef = current(array_reverse(explode('/', $sItemsRef)));
                        $mVar = '\\' . $aDataType['class'][$sName]['namespace'] . '\\' . $sNameOfRef . '[]';
                        $mValue = '$this->add_' . $sPropertyName . '(' . '\\' . $aDataType['class'][$sName]['namespace'] . '\\' . $sNameOfRef . '::create());';
                    }
                }

                /*
                 * TYPE_UNSPECIFIC
                 */
                if (true === self::getItemsOfRef($aPropertySpecs))
                {
                    $mVar = 'null';
                    $mValue = 'null';
                }

                $aDataType['class'][$sName]['property'][$sPropertyName]['key'] = $sPropertyName;
                $aDataType['class'][$sName]['property'][$sPropertyName]['var'] = $mVar;
                $aDataType['class'][$sName]['property'][$sPropertyName]['nullable'] = $bNullable;
                $aDataType['class'][$sName]['property'][$sPropertyName]['value'] = $mValue;
                $aDataType['class'][$sName]['property'][$sPropertyName]['required'] = true;
                $aDataType['class'][$sName]['property'][$sPropertyName]['forceCasting'] = true;
            }
        }

        \MVC\Generator\DataType::create()->initConfigArray($aDataType);
        Cache::saveCache($sCacheKey, $aDataType);

        return true;
    }

    /**
     * @param array $aPropertySpecs
     * @return bool
     */
    protected static function getItemsOfRef(array $aPropertySpecs = array())
    {
        $aPossible = array('oneOf', 'allOf', 'anyOf');

        foreach($aPossible as $sName)
        {
            if(array_key_exists($sName, $aPropertySpecs))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $sOpenApiFile
     * @return false|mixed
     */
    public static function getArrayOfYaml($sOpenApiFile = '')
    {
        if ('' === $sOpenApiFile)
        {
            return false;
        }

        $sOpenApiFileContent = file_get_contents($sOpenApiFile);
        $aYaml = \Symfony\Component\Yaml\Yaml::parse($sOpenApiFileContent);

        return $aYaml;
    }

    /**
     * @param array $aYaml
     * @return array
     */
    public static function getAllSchemas(array $aYaml = array())
    {
        $aSchema = ($aYaml['components']['schemas'] ?? array());

        return $aSchema;
    }

    /**
     * @param array $aPropertySpecs
     * @return mixed|null
     */
    protected static function getSchemaItemPropertyValue(array $aPropertySpecs = array())
    {
        $mValue = ($aPropertySpecs['example'] ?? null);
        $mVar = self::getSchemaItemPropertyType($aPropertySpecs);

        if (gettype($mValue) != gettype($mVar))
        {
            $mValue = null;
        }

        return $mValue;
    }

    /**
     * @param array $aPropertySpecs
     * @return mixed|string|null
     */
    protected static function getSchemaItemPropertyType(array $aPropertySpecs = array())
    {
        $mVar = ($aPropertySpecs['type'] ?? null);
        ('boolean' === $mVar) ? $mVar = 'bool' : false;
        ('integer' === $mVar) ? $mVar = 'int' : false;
        ('number' === $mVar) ? $mVar = 'float' : false;

        return $mVar;
    }
}