
# Emvicy module OpenApi

## Requirements

- Linux
- php >= 8
  - `pdo` extension
- Emvicy 1.x
  - see [https://emvicy.com/1.x/installation](https://emvicy.com/1.x/installation)

---

## Installation

_git clone_
~~~bash
cd /modules/;

git clone --branch 1.x \
https://github.com/emvicy/OpenApi.git \
OpenApi;
~~~

---

## Usage

_validate against openapi **file**_
~~~php
use OpenApi\Model\Validate;

$oDTValidateRequestResponse = Validate::request(
    $oDTRequestCurrent,
    Config::get_MVC_PUBLIC_PATH() . '/openapi/api.yaml'
);

header('Content-Type: application/json');
echo json_encode(Convert::objectToArray($oDTValidateRequestResponse));
~~~

_validate against openapi **URL**_
~~~php
use OpenApi\Model\Validate;

// validate against openapi URL
$oDTValidateRequestResponse = Validate::request(
    $oDTRequestCurrent,
    'https://example.com/api/openapi.yaml'
);

header('Content-Type: application/json');
echo json_encode(Convert::objectToArray($oDTValidateRequestResponse));
~~~

**auto-creating Emvicy Routes from openapi file**

_All Routes lead to their given `operationId`, set in openapi_    
~~~php
\OpenApi\Model\Route::autoCreateFromOpenApiFile(
    Config::get_MVC_PUBLIC_PATH() . '/openapi/api.yaml',
    '\Foo\Controller\Api'
);
~~~

_All Routes lead explicitely to `Api::delegate()`_      
~~~php
\OpenApi\Model\Route::autoCreateFromOpenApiFile(
    Config::get_MVC_PUBLIC_PATH() . '/openapi/api.yaml',
    '\Foo\Controller\Api',
    'delegate'
);
~~~

**DTClassesOnOpenapi3yaml**  

~~~php
\OpenApi\Model\Generate::DTClassesOnOpenapi3yaml(
    '/absolute/path/to/file/openapi.yaml',  # openapi yaml file | openapi yaml URL 
    'DTOpenapi',                            # Foldername; where to store DTClasses
    true,                                   # remove and create Folder for new; true|false
    false                                   # take values from "example" as default values
); 
~~~

---

## Get Logs

Logs are fired to Events.

Available events are:

- `Emvicy_module_OpenApi::sYamlSource`

_listen to event and write its content to a logfile_    
~~~php
\MVC\Event::bind('Emvicy_module_OpenApi::sYamlSource', function($sContent){
    \MVC\Log::write($sContent, 'openapi.log');
});
~~~