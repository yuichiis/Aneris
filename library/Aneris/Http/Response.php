<?php
namespace Aneris\Http;

class Response implements HttpResponseInterface
{
    const STATUS_CODE_OK = 200;
    const STATUS_CODE_MOVED_PERMANENTLY = 301;
    const STATUS_CODE_FOUND = 302;
    const STATUS_CODE_UNAUTHORIZED = 401;
    const STATUS_CODE_FORBIDDEN = 403;
    const STATUS_CODE_NOT_FOUND = 404;
    const STATUS_CODE_INTERNAL_SERVER_ERROR = 500;
    const STATUS_CODE_SERVICE_UNAVAILABLE = 503;

    protected $statusReasons = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
    );

    protected $statusCode = 200;
    protected $content;
    protected $headers;
    protected $version;
    protected $sent;

    public function setStatusCode($code)
    {
        if(!isset($this->statusReasons[$code]))
            throw new Exception\DomainException('Unknown status code:'.$code);
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function addHeader($name, $value)
    {
        if((!is_string($name)) || (!is_string($value)))
            throw new Exception\DomainException('Invalid type of name or value.');
        if(!isset($this->headers[$name])) {
            $this->headers[$name] = $value;
        } else if(is_string($this->headers[$name])) {
            $this->headers[$name] = array($this->headers[$name], $value);
        } else {
            $this->headers[$name][] = $value;
        }
        return $this;
    }

    public function resetHeaders($headers=null)
    {
        $this->headers = $headers;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getVersion()
    {
        if(!isset($this->version)) {
            if(isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.1')
                $this->version = '1.1';
            else
                $this->version = '1.0';
        }
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    public function getStatusLine()
    {
        $status = 'HTTP/'.$this->getVersion().' '.$this->getStatusCode().' '.$this->statusReasons[$this->getStatusCode()];
        return $status;
    }

    public function send()
    {
        if($this->sent)
            return $this;

        if(!headers_sent() || isset($_SERVER['SERVER_PROTOCOL'])) {
            header($this->getStatusLine());
            if($this->headers) {
                foreach($this->headers as $header => $values) {
                    if(!is_array($values)) {
                        header($header.': '.$values);
                    } else {
                        foreach($values as $value) {
                            header($header.': '.$value, false);
                        }
                    }
                }
            }
        }
        echo $this->content;
        $this->sent = true;
        return $this;
    }
}
