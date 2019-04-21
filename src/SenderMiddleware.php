<?php

namespace Kiss;

use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as HandlerInterface;
use Psr\Http\Message\ResponseInterface as Response;

class SenderMiddleware implements Middleware
{
    /**
     * @param Response $response
     */
    public static function sendStatus(Response $response)
    {
        header(sprintf('HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            ($reason_phrase = $response->getReasonPhrase()) ? ' '.$reason_phrase : ''
        ));
    }

    /**
     * @param Response $response
     */
    public static function sendHeaders(Response $response)
    {
        foreach ($response->getHeaders() as $header_name => $header_values)
        {
            $replace = true;
            foreach ($header_values as $header_value)
            {
                header(sprintf('%s: %s', $header_name, $header_value), $replace);
                $replace = false;
            }
        }
    }

    /**
     * @param Response $response
     */
    public static function sendBody(Response $response)
    {
        @ob_clean();
        echo $response->getBody();
        @ob_flush();
        flush();
    }

    /**
     * @param Response $response
     */
    public static function send(Response $response)
    {
        static::sendStatus($response);
        static::sendHeaders($response);
        static::sendBody($response);
    }

    /**
     * @param Request $request
     * @param HandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, HandlerInterface $handler): Response
    {
        $response = $handler->handle($request);

        static::send($response);

        return $response;
    }
}
