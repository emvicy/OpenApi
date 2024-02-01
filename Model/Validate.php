<?php

namespace OpenApi\Model;

use HKarlstrom\Middleware\OpenApiValidation;
use HKarlstrom\Middleware\OpenApiValidation\Exception\FileNotFoundException;
use HKarlstrom\Middleware\OpenApiValidation\Exception\InvalidOptionException;
use HKarlstrom\OpenApiReader\OpenApiReader;
use MVC\Cache;
use MVC\Config;
use MVC\DataType\DTRequestCurrent;
use MVC\Error;
use MVC\Event;
use MVC\File;
use MVC\Request;
use MVC\Route;
use MVC\Strings;
use OpenApi\DataType\DTValidateMessage;
use OpenApi\DataType\DTValidateRequestResponse;

class Validate
{
    /**
     * @param \MVC\DataType\DTRequestCurrent|null $oDTRequestCurrent
     * @param                                     $sYamlSource file | URL
     * @return \OpenApi\DataType\DTValidateRequestResponse
     * @throws \ReflectionException
     * @example {"bSuccess":false,"aMessage":[],"aValidationResult":[{"name":"data.1.contact.city","code":"error_type","value":123,"in":"body","expected":"string","used":"integer"}]}
     * @example {"bSuccess":true,"aMessage":[],"aValidationResult":[]}
     */
    public static function request(DTRequestCurrent $oDTRequestCurrent = null, $sYamlSource = '')
    {
        // Response
        $oDTValidateRequestResponse = DTValidateRequestResponse::create();

        // $sYamlSource missing
        if (true === empty($sYamlSource))
        {
            $oDTValidateRequestResponse = self::sYamlSourceFail(
                $oDTValidateRequestResponse,
                $sYamlSource,
                'no $sYamlSource passed; string parameter is empty'
            );
        }

        // Fallback
        if (null === $oDTRequestCurrent)
        {
            $sMessage = 'no object of type DTRequestCurrent passed; creating object DTRequestCurrent on Request::getCurrentRequest()';
            Error::notice($sMessage);
            $oDTRequestCurrent = Request::getCurrentRequest();
            $oDTValidateRequestResponse->add_aMessage(
                DTValidateMessage::create()
                    ->set_sSubject('Notice')
                    ->set_sBody($sMessage)
            );
        }

        // $sYamlSource is URL: download and save to cache
        if (true === (boolean) filter_var($sYamlSource, FILTER_VALIDATE_URL))
        {
            $sYamlSource = self::saveAsFile($sYamlSource);
        }

        Event::run('Emvicy_module_OpenApi::sYamlSource', $sYamlSource);

        // $sYamlSource is file, but missing
        if (false === file_exists($sYamlSource))
        {
            $oDTValidateRequestResponse = self::sYamlSourceFail(
                $oDTValidateRequestResponse,
                $sYamlSource,
                'file does not exist: `' . $sYamlSource . '`'
            );
        }

        // check request method
        $bMethodsMatch = (Request::getCurrentRequest()->get_requestmethod() === Route::getCurrent()->get_method());

        if (false === $bMethodsMatch)
        {
            $sMessage = 'wrong request method `' . $oDTRequestCurrent->get_requestmethod() . '`. It has to be: `' . Route::getCurrent()->get_method() . '`';
            Error::notice($sMessage);
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Notice')
                        ->set_sBody($sMessage)
                );

            return $oDTValidateRequestResponse;
        }

        // check the request content type...
        try {
            $oOpenApiReader = new OpenApiReader($sYamlSource);
            $oRequestBody = $oOpenApiReader->getOperationRequestBody(
                $oDTRequestCurrent->get_path(),
                strtolower($oDTRequestCurrent->get_requestmethod())
            );

            // ...if there is any content body
            if (null !== $oRequestBody)
            {
                // get the expected type of request content
                $sExpectedType = $oRequestBody->getContent()->type;

                // check content type "json"
                if (true === (boolean) stristr($sExpectedType, 'json') && false === Strings::isJson($oDTRequestCurrent->get_input()))
                {
                    $sMessage = 'content type has to be valid `' . $sExpectedType . '`';
                    Error::error(json_last_error_msg() . ' on RequestBody of ' . $oDTRequestCurrent->get_path() . ': ' . $sMessage);
                    Error::notice('abort validation of request due to error');
                    $oDTValidateRequestResponse
                        ->set_bSuccess(false)
                        ->add_aMessage(
                            DTValidateMessage::create()
                                ->set_sSubject('Error')
                                ->set_sBody(json_last_error_msg())
                        )
                        ->add_aMessage(
                            DTValidateMessage::create()
                                ->set_sSubject('Notice')
                                ->set_sBody($sMessage)
                        );

                    return $oDTValidateRequestResponse;
                }
            }
        } catch (\Exception $oException) {
            Error::exception($oException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Exception')
                        ->set_sBody($oException->getMessage())
                );

            return $oDTValidateRequestResponse;
        }

        // OpenApiValidation
        try {
            $oOpenApiValidation = new OpenApiValidation($sYamlSource);
        } catch (FileNotFoundException $oFileNotFoundException) {
            Error::exception($oFileNotFoundException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Exception')
                        ->set_sBody($oFileNotFoundException->getMessage())
                );

            return $oDTValidateRequestResponse;
        } catch (InvalidOptionException $oInvalidOptionException) {
            Error::exception($oInvalidOptionException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse
                ->set_bSuccess(false)
                ->add_aMessage(
                    DTValidateMessage::create()
                        ->set_sSubject('Exception')
                        ->set_sBody($oInvalidOptionException->getMessage())
                );

            return $oDTValidateRequestResponse;
        }

        // requirement: it has to be Psr7
        $oPsrRequest = new PsrRequest($oDTRequestCurrent);
        $aValidationResult = $oOpenApiValidation->validateRequest(
        // PSR7 Request Object
            $oPsrRequest,
            // path as expected in route
            Route::getCurrent()->get_path(),
            // Request Method; has to be lowercase
            strtolower(Route::getCurrent()->get_method()),
            // remove "_tail" from PathParam Array
            $oPsrRequest->withoutAttribute('_tail')
        );

        $oDTValidateRequestResponse
            ->set_bSuccess((true === empty($aValidationResult)))
            ->set_aValidationResult($aValidationResult);

        return $oDTValidateRequestResponse;
    }

    /**
     * @param string $sYamlUrl
     * @return string
     * @throws \ReflectionException
     */
    protected static function saveAsFile(string $sYamlUrl = '')
    {
        $iStrLength = 30;
        $sString = substr(Strings::seofy($sYamlUrl), 0, $iStrLength);
        $sString = str_pad($sString,  $iStrLength, '-');
        $sString.= '.' . md5(base64_encode($sYamlUrl));
        $sCacheFileAbs = File::secureFilePath(Config::get_MVC_CACHE_DIR() . '/' . $sString . '.yaml');

        Cache::autoDeleteCache($sCacheFileAbs);

        if (false === file_exists($sCacheFileAbs))
        {
            $sContent = file_get_contents($sYamlUrl);
            $bSuccess = file_put_contents($sCacheFileAbs, $sContent);

            if (false === $bSuccess || false === file_exists($sCacheFileAbs))
            {
                return '';
            }
        }

        return $sCacheFileAbs;
    }

    /**
     * @param DTValidateRequestResponse $oDTValidateRequestResponse
     * @param string $sYamlSource
     * @param string $sMessage
     * @return DTValidateRequestResponse
     * @throws \ReflectionException
     */
    protected static function sYamlSourceFail(DTValidateRequestResponse $oDTValidateRequestResponse, string $sYamlSource = '', string $sMessage = '')
    {
        Error::error($sMessage);
        $oDTValidateRequestResponse
            ->set_bSuccess(false)
            ->add_aMessage(
                DTValidateMessage::create()
                    ->set_sSubject('Error')
                    ->set_sBody($sMessage)
            );

        return $oDTValidateRequestResponse;
    }
}