
# OpenApi

a module for Emvicy2 (2.x) PHP Framework: https://github.com/emvicy/Emvicy/tree/2.x

---

## Installation

_cd into the modules folder of your `Emvicy` copy; e.g.:_
~~~bash
cd /var/www/html/modules/;
~~~

_git clone_
~~~bash
git clone --branch 3.x https://github.com/emvicy/OpenApi.git OpenApi;
~~~

---

## Usage

### Validating 

_validate against an openapi **file**_  
~~~php
use OpenApi\Model\Validate;

$oDTValidateRequestResponse = Validate::request(
    $oDTRequestCurrent,
    Config::get_MVC_PUBLIC_PATH() . '/openapi/api.yaml'
);

header('Content-Type: application/json');
echo json_encode(Convert::objectToArray($oDTValidateRequestResponse));
~~~

_validate against an openapi **URL**_
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

---

### Auto-creating Emvicy Routes from openapi file

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

---

### Auto-creating local DataType Classes from within Yaml Files or URLs

Create a **configuration** according to the following scheme; it contains the Yaml files or URLs to be processed.

_Example Configuration for several YAML File locations (`Foo` here is the primary Module)_    
~~~php
$aConfig['MODULE']['Foo']['service'] = [
    'Bar' => [ // Service Name
        'aOpenApi' => [
            'aLocation' => [
                'https://bar.example.com/path/to/openapi.yaml', // a remote yaml address
            ],
        ],
    ],
    'Baz' => [ // Service Name
        'aOpenApi' => [
            'aLocation' => [
                '/absolute/path/to/openapi.yaml', // a local yaml address
            ],
        ],
    ],
];
~~~

You can then run the **cli command** to generate local DataType Classes according to the yaml files.

~~~bash
php emvicy openapi:datatype
~~~

---

**DTClassesOnOpenapi3yaml**

otherwise you can call the Generator explicitely.

~~~php
// generate from any accessible yaml location
\OpenApi\Model\Generate::DTClassesOnOpenapi3yaml(
    sOpenApiFile: '/absolute/path/to/openapi.yaml', # openapi yaml file|URL
    sSubDirName: 'DTOpenapi',                       # Storing Classes in `/modules/{primary}/DataType/DTOpenapi`
    bUnlinkDir: false,                              # remove and create Folder for new; true|false
    bValueFromExample: false,                       # take values from "example" as default values
    bDebug: true                                    # print debug infos
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