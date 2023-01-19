<?php
/**
 * @copyright Copyright © 2022 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;

final class ResponseService
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator
    )
    {
    }

    public function jsonResponse(string $json): ResponseInterface
    {
        $response = $this
            ->responseFactory
            ->createResponse(Status::OK)
        ;

        $response
            ->getBody()
            ->write($json)
        ;

        $response
            ->withHeader(Header::CONTENT_TYPE, 'application/json; charset=UTF-8')
        ;

        return $response;
    }

    public function notFoundResponse(string $message = ''): ResponseInterface
    {
        $response = $this
            ->responseFactory
            ->createResponse(Status::NOT_FOUND);

        if (!empty($message)) {
            $response
                ->getBody()
                ->write($message);
        }

        return $response;
    }

    public function redirectResponse(
        string $name,
        array $attributes = [],
        array $queryParameters = [],
        int $code = Status::FOUND
    ): ResponseInterface
    {
        return $this
            ->responseFactory
            ->createResponse($code)
            ->withHeader(
                Header::LOCATION,
                $this
                    ->urlGenerator
                    ->generate($name, $attributes, $queryParameters)
            )
        ;
    }
}