<?php

# documentation
#   https://mymvc.ueffing.net/3.4.x/generating-datatype-classes#array_config
# creation
#   php emvicy.php datatype:module OpenApi

#---------------------------------------------------------------
#  Defining DataType Classes

$sThisModuleDir = realpath(__DIR__ . '/../../../../');
$sThisModuleName = basename($sThisModuleDir);
$sThisModuleDataTypeDir = $sThisModuleDir . '/DataType';
$sThisModuleNamespace = str_replace('/', '\\', substr($sThisModuleDataTypeDir, strlen($aConfig['MVC_MODULES_DIR'] . '/')));

// base setup
$aDataType = array(

    // directory
    'dir' => $sThisModuleDataTypeDir,

    // remove complete dir before new creation
    'unlinkDir' => false,

    // enable creation of events in datatype methods
    'createEvents' => true,
);

// classes
$aDataType['class'][] = array(
    'name' => 'DTValidateRequestResponse',
    'file' => 'DTValidateRequestResponse.php',
    'namespace' => $sThisModuleNamespace,
    'createHelperMethods' => true,
    'constant' => array(),
    'property' => array(
        array(
            'key' => 'bSuccess',
            'var' => 'bool',
            'value' => false,
            'required' => true,
            'forceCasting' => true,
        ),
        array(
            'key' => 'aMessage',
            'var' => 'DTValidateMessage[]',
            'required' => true,
            'forceCasting' => true,
        ),
        array(
            'key' => 'aValidationResult',
            'var' => 'array',
            'required' => true,
            'forceCasting' => true,
        ),
    ),
);

$aDataType['class'][] = array(
    'name' => 'DTValidateMessage',
    'file' => 'DTValidateMessage.php',
    'namespace' => $sThisModuleNamespace,
    'createHelperMethods' => true,
    'constant' => array(),
    'property' => array(
        array(
            'key' => 'sSubject',
            'var' => 'string',
            'required' => true,
            'forceCasting' => true,
        ),
        array(
            'key' => 'sBody',
            'var' => 'string',
            'required' => true,
            'forceCasting' => true,
        ),
    ),
);

#---------------------------------------------------------------
# copy settings to module's config
# in your code you can access this datatype config by: \MVC\Config::MODULE()['DATATYPE'];

$aConfig['MODULE'][$sThisModuleName]['DATATYPE'] = $aDataType;