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
    private ?string $message = null;

    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * Returns a new instance with the specified message.
     *
     * @param string $message Message body
     */
    public function withMessage(string $message): self
    {
        $new = clone $this;
        $new->message = $message;
        return $new;
    }

    public function create(): ResponseInterface
    {
        $response = $this
            ->responseFactory
            ->createResponse(Status::NOT_FOUND);

        if ($this->message !== null) {
            $streamFactory = new StreamFactory();

            return $response
                ->withBody($streamFactory->createStream($this->message))
            ;
        }

        return $response;
    }
}
