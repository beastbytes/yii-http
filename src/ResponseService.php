<?php
/**
 * @copyright Copyright © 2022 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Bank;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

final class ResponseService
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory
    )
    {}

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

    public function redirectResponse(string $url, int $code = Status::FOUND): ResponseInterface
    {
        return $this
            ->responseFactory
            ->createResponse($code)
            ->withHeader(Header::LOCATION, $url);
    }
}