<?php

namespace OpenApi\Model;

use MVC\DataType\DTRequestCurrent;
use MVC\Request;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class PsrRequest implements ServerRequestInterface
{
    /**
     * @var DTRequestCurrent
     */
    protected $oDTRequestCurrent;

    /**
     * @param DTRequestCurrent $oDTRequestCurrent
     */
    public function __construct(DTRequestCurrent $oDTRequestCurrent)
    {
        $this->oDTRequestCurrent = $oDTRequestCurrent;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $_SERVER;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $_COOKIE;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $aCookie Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $aCookie)
    {
        $_COOKIE = $aCookie;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        // Request::getCurrentRequest()->get_query()
        parse_str($_SERVER['QUERY_STRING'], $aQuery);

        return $aQuery;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $aQuery Array of query string arguments, typically from
     *     $_GET.
     * @return static
     */
    public function withQueryParams(array $aQuery)
    {
        $_GET = $aQuery;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $_FILES;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $aUploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $aUploadedFiles)
    {
        ;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     * @throws \ReflectionException
     */
    public function getParsedBody()
    {
        if ('POST' === Request::getCurrentRequest()->get_requestmethod())
        {
            return $_POST;
        }

        if (true === is_string(Request::getCurrentRequest()->get_input()))
        {
            $mReturn = json_decode(Request::getCurrentRequest()->get_input(), true);
        }
        else
        {
            $mReturn = Request::getCurrentRequest()->get_input();
        }

        return $mReturn;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $mData The deserialized body data. This will
     *     typically be in an array or object.
     * @return static
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     * @throws \ReflectionException
     */
    public function withParsedBody($mData)
    {
        if ('POST' === Request::getCurrentRequest()->get_requestmethod())
        {
            return $_POST;
        }

        return $mData;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     * @throws \ReflectionException
     */
    public function getAttributes()
    {
        return (array) Request::getPathParam();
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param string $sAttributeName The attribute name.
     * @param mixed $sAttributeDefaultValue Default value to return if the attribute does not exist.
     * @return mixed
     * @throws \ReflectionException
     *@see getAttributes()
     */
    public function getAttribute($sAttributeName, $sAttributeDefaultValue = null)
    {
        return get($this->getAttributes()[$sAttributeName], $sAttributeDefaultValue);
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param string $sAttributeName The attribute name.
     * @param mixed $sAttributeValue The value of the attribute.
     * @return static
     *@see getAttributes()
     */
    public function withAttribute($sAttributeName, $sAttributeValue)
    {
        ;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @param string $sAttributeName The attribute name.
     * @return array
     * @throws \ReflectionException
     *@see getAttributes()
     */
    public function withoutAttribute($sAttributeName)
    {
        $aAttribute = $this->getAttributes();

        if (isset($aAttribute[$sAttributeName]))
        {
            $aAttribute[$sAttributeName] = null;
            unset($aAttribute[$sAttributeName]);
        }

        return $aAttribute;
    }

    /**
     * @return void
     */
    public function getProtocolVersion()
    {
        // TODO: Implement getProtocolVersion() method.
    }

    /**
     * @param $sVersion
     * @return void
     */
    public function withProtocolVersion($sVersion)
    {
        // TODO: Implement withProtocolVersion() method.
    }

    /**
     * @return array|\string[][]
     */
    public function getHeaders()
    {
        return Request::getHeaderArray();
    }

    /**
     * @param $sHeaderName
     * @return bool
     */
    public function hasHeader($sHeaderName)
    {
        return isset($this->getHeaders()[$sHeaderName]);
    }

    /**
     * @param $sHeaderName
     * @return array|string[]
     */
    public function getHeader($sHeaderName)
    {
        return (array) get($this->getHeaders()[$sHeaderName], array());
    }

    /**
     * @param $sHeaderLine
     * @return void
     */
    public function getHeaderLine($sHeaderLine)
    {
        // TODO: Implement getHeaderLine() method.
    }

    /**
     * @param $sHeaderName
     * @param $sHeaderValue
     * @return void
     */
    public function withHeader($sHeaderName, $sHeaderValue)
    {
        // TODO: Implement withHeader() method.
    }

    /**
     * @param $sHeaderName
     * @param $sHeaderValue
     * @return void
     */
    public function withAddedHeader($sHeaderName, $sHeaderValue)
    {
        // TODO: Implement withAddedHeader() method.
    }

    /**
     * @param $sHeaderName
     * @return void
     */
    public function withoutHeader($sHeaderName)
    {
        // TODO: Implement withoutHeader() method.
    }

    /**
     * @return StreamInterface|string
     * @throws \ReflectionException
     */
    public function getBody()
    {
        return $this->oDTRequestCurrent->get_input();
    }

    /**
     * @param StreamInterface $oStreamInterface
     * @return void
     */
    public function withBody(StreamInterface $oStreamInterface)
    {
        // TODO: Implement withBody() method.
    }

    /**
     * @return void
     */
    public function getRequestTarget()
    {
        // TODO: Implement getRequestTarget() method.
    }

    /**
     * @param $sRequestTarget
     * @return void
     */
    public function withRequestTarget($sRequestTarget)
    {
        // TODO: Implement withRequestTarget() method.
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getMethod()
    {
        return $this->oDTRequestCurrent->get_requestmethod();
    }

    /**
     * @param $sMethod
     * @return void
     */
    public function withMethod($sMethod)
    {
        // TODO: Implement withMethod() method.
    }

    /**
     * @return PsrUri
     */
    public function getUri()
    {
        return new PsrUri($this->oDTRequestCurrent);
    }

    /**
     * @param UriInterface $oUriInterface
     * @param $bPreserveHost
     * @return void
     */
    public function withUri(UriInterface $oUriInterface, $bPreserveHost = false)
    {
        // TODO: Implement withUri() method.
    }
}