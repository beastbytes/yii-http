<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Http\Response;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;

final class Redirect
{
    private ?string $uri = null;
    private ?string $route = null;
    /**
     * @var array<string, scalar|\Stringable|null> $parameters
     */
    private array $parameters = [];
    private int $statusCode = Status::MOVED_PERMANENTLY;

    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Returns a new instance with the specified URL for redirection.
     *
     * @param string $url URL for redirection.
     */
    public function toUrl(string $url): self
    {
        $new = clone $this;
        $new->uri = $url;
        return $new;
    }

    /**
     * Returns a new instance with the specified route data for redirection.
     *
     * If you've set a redirect URL with {@see toUrl()}, the middleware ignores the route data, since the URL
     * is a priority.
     *
     * @param string $name The route name for redirection.
     * @param array<string, scalar|\Stringable|null> $parameters $parameters The route parameters for redirection.
     */
    public function toRoute(string $name, array $parameters = []): self
    {
        $new = clone $this;
        $new->route = $name;
        $new->parameters = $parameters;
        return $new;
    }

    /**
     * Returns a new instance with the specified status code of the response for redirection.
     *
     * @param int $statusCode The status code of the response for redirection.
     */
    public function withStatusCode(int $statusCode): self
    {
        $new = clone $this;
        $new->statusCode = $statusCode;
        return $new;
    }

    /**
     * Returns a new instance with the response status code of permanent redirection.
     *
     * @see Status::MOVED_PERMANENTLY
     */
    public function permanent(): self
    {
        $new = clone $this;
        $new->statusCode = Status::MOVED_PERMANENTLY;
        return $new;
    }

    /**
     * Returns a new instance with the response status code of temporary redirection.
     *
     * @see Status::FOUND
     */
    public function temporary(): self
    {
        $new = clone $this;
        $new->statusCode = Status::FOUND;
        return $new;
    }

    /**
     * @throws RuntimeException If the data for redirection wasn't set earlier.
     */
    public function create(): ResponseInterface
    {
        if ($this->route === null && $this->uri === null) {
            throw new RuntimeException('Either `toUrl()` or `toRoute()` method should be used.');
        }

        /** @psalm-suppress PossiblyNullArgument */
        $uri = $this->uri ?? $this->urlGenerator->generate($this->route, $this->parameters);

        return $this->responseFactory
            ->createResponse($this->statusCode)
            ->withAddedHeader('Location', $uri)
        ;
    }
}
