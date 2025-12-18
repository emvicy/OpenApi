<?php

namespace OpenApi\Model;

use HKarlstrom\OpenApiReader\OpenApiReader;
use MVC\Error;

class Route
{
    /**
     * @usage \OpenApi\Model\Route::autoCreateFromOpenApiFile( OPENAPI_FILE_ABSOLUTE, '\Foo\Controller\Api', 'delegate' );
     * @param string $sOpenApiFileAbs
     * @param string $sClass e.g. '\Foo\Controller\Api'
     * @param string $sClassMethod optional; if set, all routes will lead to that method. If not, route will lead to path's operationId from openapi
     * @return bool success
     * @throws \ReflectionException
     */
    public static function autoCreateFromOpenApiFile(string $sOpenApiFileAbs = '', string $sClass = '', string $sClassMethod = '')
    {
        // read openapi file and convert to array
        $aOpenApiReader = current(
            \MVC\Convert::objectToArray(
                new OpenApiReader($sOpenApiFileAbs)
            )
        );

        // read PATHs from openapi
        $aRawPath = ($aOpenApiReader['raw']['paths'] ?? array());

        // finally: dynamically create routes from openapi
        foreach ($aRawPath as $sPath => $aPath)
        {
            foreach ($aPath as $sRouteMethod => $aSpec)
            {
                $sTmp = (($aSpec['operationId'] ?? ''));
                $sOperationId = (true === is_array($sTmp)) ? current($sTmp) : $sTmp;

                if (true === empty($sClassMethod) && true === empty($sOperationId))
                {
                    Error::error('operationId missing: `' . $sPath . '`, ' . $sRouteMethod);
                    return false;
                }

                // operationId contains a custom 'class::method' (e.g. '\Module\Controller\Whatever::any')
                if (stristr($sOperationId, '::'))
                {
                    $sControllerClassMethod = $sOperationId;
                }
                // default: \Foo\Controller\Api::method
                else
                {
                    $sControllerClassMethod = $sClass . '::' . ((false === empty($sClassMethod)) ? $sClassMethod : $sOperationId);
                }

                \MVC\Route::$sRouteMethod(
                    sPath: $sPath,
                    sClassMethod: $sControllerClassMethod
                );
            }
        }

        return true;
    }
}
