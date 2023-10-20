<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Http\Response;

use HttpSoft\Message\StreamFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;

class NotFound
{
    private ?string $body = null;

    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * Returns a new instance with the specified body.
     *
     * @param string $body Response body
     */
    public function withBody(string $body): self
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function create(): ResponseInterface
    {
        $response = $this
            ->responseFactory
            ->createResponse(Status::NOT_FOUND);

        if ($this->body !== null) {
            $streamFactory = new StreamFactory();

            return $response
                ->withBody($streamFactory->createStream($this->body))
            ;
        }

        return $response;
    }
}
