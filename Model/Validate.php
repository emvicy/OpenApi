<?php

namespace OpenApi\Model;

use HKarlstrom\Middleware\OpenApiValidation;
use HKarlstrom\Middleware\OpenApiValidation\Exception\FileNotFoundException;
use HKarlstrom\Middleware\OpenApiValidation\Exception\InvalidOptionException;
use HKarlstrom\OpenApiReader\OpenApiReader;
use MVC\Cache;
use MVC\Config;
use MVC\DataType\DTRequestIn;
use MVC\Error;
use MVC\Event;
use MVC\File;
use MVC\Log;
use MVC\Request;
use MVC\Route;
use MVC\Strings;
use OpenApi\DataType\DTValidateMessage;
use OpenApi\DataType\DTValidateRequestResponse;
use OpenApi\Model\Validator\Binary;

class Validate
{
    /**
     * @param \MVC\DataType\DTRequestIn|null $oDTRequestIn
     * @param string                              $sYamlSource file|url
     * @return \OpenApi\DataType\DTValidateRequestResponse
     * @throws \ReflectionException
     * @example {"bSuccess":false,"aMessage":[],"aValidationResult":[{"name":"data.1.contact.city","code":"error_type","value":123,"in":"body","expected":"string","used":"integer"}]}
     * @example {"bSuccess":true,"aMessage":[],"aValidationResult":[]}
     */
    public static function request(?DTRequestIn $oDTRequestIn = null, string $sYamlSource = '')
    {
        // Response
        $oDTValidateRequestResponse = DTValidateRequestResponse::create();

        // $sYamlSource missing
        if (true === empty($sYamlSource))
        {
            $oDTValidateRequestResponse = self::sYamlSourceFail($oDTValidateRequestResponse, $sYamlSource, 'no $sYamlSource passed; string parameter is empty');
        }

        // Fallback
        if (null === $oDTRequestIn)
        {
            $sMessage = 'no object of type DTRequestIn passed; creating object DTRequestIn on Request::getCurrentRequest()';
            Error::notice($sMessage);
            $oDTRequestIn = Request::in();
            $oDTValidateRequestResponse->add_aMessage(DTValidateMessage::create()
                ->set_sSubject('Notice')
                ->set_sBody($sMessage));
        }

        // $sYamlSource is URL: download and save to cache
        if (true === (bool)filter_var($sYamlSource, FILTER_VALIDATE_URL))
        {
            $sYamlSource = self::saveAsFile($sYamlSource);
        }

        Event::run('Emvicy_module_OpenApi::sYamlSource', $sYamlSource);

        // $sYamlSource is file, but missing
        if (false === file_exists($sYamlSource))
        {
            $oDTValidateRequestResponse = self::sYamlSourceFail($oDTValidateRequestResponse, $sYamlSource, 'file does not exist: `' . $sYamlSource . '`');
        }

        // check request method
        $bMethodsMatch = ($oDTRequestIn->get_requestmethod() === Route::getCurrent()->get_requestMethod());

        if (false === $bMethodsMatch)
        {
            $sMessage = 'wrong request method `' . $oDTRequestIn->get_requestmethod() . '`. It has to be: `' . Route::getCurrent()->get_requestMethod() . '`';
            Error::notice($sMessage);
            $oDTValidateRequestResponse->set_bSuccess(false)
                ->add_aMessage(DTValidateMessage::create()
                    ->set_sSubject('Notice')
                    ->set_sBody($sMessage));

            return $oDTValidateRequestResponse;
        }

        // check the request content type...
        try
        {
            $oOpenApiReader = new OpenApiReader($sYamlSource);
            $oRequestBody = $oOpenApiReader->getOperationRequestBody($oDTRequestIn->get_path(), strtolower($oDTRequestIn->get_requestmethod()));

            // ...if there is any content body
            if (null !== $oRequestBody)
            {
                // get the expected type of request content
                $sExpectedType = $oRequestBody->getContent()->type;

                // check content type "json"
                if (true === (bool)stristr($sExpectedType, 'json') && false === Strings::isJson($oDTRequestIn->get_input()))
                {
                    $sMessage = 'content type has to be valid `' . $sExpectedType . '`';
                    Error::error(json_last_error_msg() . ' on RequestBody of ' . $oDTRequestIn->get_path() . ': ' . $sMessage);
                    Error::notice('abort validation of request due to error');
                    $oDTValidateRequestResponse->set_bSuccess(false)
                        ->add_aMessage(DTValidateMessage::create()
                            ->set_sSubject('Error')
                            ->set_sBody(json_last_error_msg()))
                        ->add_aMessage(DTValidateMessage::create()
                            ->set_sSubject('Notice')
                            ->set_sBody($sMessage));

                    return $oDTValidateRequestResponse;
                }
            }
        }
        catch (\Exception $oException)
        {
            Error::exception($oException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse->set_bSuccess(false)
                ->add_aMessage(DTValidateMessage::create()
                    ->set_sSubject('Exception')
                    ->set_sBody($oException->getMessage()));

            return $oDTValidateRequestResponse;
        }

        // OpenApiValidation
        try
        {
            $oOpenApiValidation = new OpenApiValidation($sYamlSource, [
                    'missingFormatException' => true,
                ]);

            // Custom Format
            $oOpenApiValidation->addFormat('string', 'binary', new Binary());
        }
        catch (FileNotFoundException $oFileNotFoundException)
        {
            Error::exception($oFileNotFoundException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse->set_bSuccess(false)
                ->add_aMessage(DTValidateMessage::create()
                    ->set_sSubject('Exception')
                    ->set_sBody($oFileNotFoundException->getMessage()));

            return $oDTValidateRequestResponse;
        }
        catch (InvalidOptionException $oInvalidOptionException)
        {
            Error::exception($oInvalidOptionException->getMessage());
            Error::notice('abort validation of request due to exception');
            $oDTValidateRequestResponse->set_bSuccess(false)
                ->add_aMessage(DTValidateMessage::create()
                    ->set_sSubject('Exception')
                    ->set_sBody($oInvalidOptionException->getMessage()));

            return $oDTValidateRequestResponse;
        }

        // requirement: it has to be Psr7
        $oPsrRequest = new PsrRequest($oDTRequestIn);
        try
        {
            $aValidationResult = $oOpenApiValidation->validateRequest(  // PSR7 Request Object
                $oPsrRequest,                                   // path as expected in route
                Route::getCurrent()->get_path(),                        // Request Method; has to be lowercase
                strtolower(Route::getCurrent()->get_requestMethod()),   // remove "_tail" from PathParam Array
                $oPsrRequest->withoutAttribute('_tail')
            );
        }
        catch (\Exception $oException)
        {
            Log::write($oException->getMessage(), 'error.log');
        }

        $oDTValidateRequestResponse->set_bSuccess((true === empty($aValidationResult)))->set_aValidationResult($aValidationResult);

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
        $sString = str_pad($sString, $iStrLength, '-');
        $sString .= '.' . md5(base64_encode($sYamlUrl));
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
     * @param \OpenApi\DataType\DTValidateRequestResponse $oDTValidateRequestResponse
     * @param string                                      $sYamlSource
     * @param string                                      $sMessage
     * @return \OpenApi\DataType\DTValidateRequestResponse
     * @throws \ReflectionException
     */
    protected static function sYamlSourceFail(DTValidateRequestResponse $oDTValidateRequestResponse, string $sYamlSource = '', string $sMessage = '')
    {
        Error::error($sMessage);
        $oDTValidateRequestResponse->set_bSuccess(false)
            ->add_aMessage(DTValidateMessage::create()
                ->set_sSubject('Error')
                ->set_sBody($sMessage));

        return $oDTValidateRequestResponse;
    }
}
