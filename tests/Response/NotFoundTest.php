<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Http\Tests\Response;

use BeastBytes\Yii\Http\Response\NotFound;
use Generator;
use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Router\RouteCollector;
use Yiisoft\Router\UrlGeneratorInterface;

class NotFoundTest extends TestCase
{
    private const MESSAGE = 'Test message';

    /** @psalm-suppress PropertyNotSetInConstructor  */
    private NotFound $responseCreator;

    protected function setUp(): void
    {
        $this->responseCreator = new NotFound(new ResponseFactory());
    }

    public function testNotFoundResponse(): void
    {
        $response = $this
            ->responseCreator
            ->create()
        ;
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(Status::NOT_FOUND, $response->getStatusCode());
    }

    public function testWithMessage(): void
    {
        $responseCreator = $this
            ->responseCreator
            ->withMessage(self::MESSAGE)
        ;

        $this->assertNotSame($this->responseCreator, $responseCreator);

        $response = $responseCreator->create();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(Status::NOT_FOUND, $response->getStatusCode());
        $this->assertSame(self::MESSAGE, $response->getBody()->getContents());
    }
}
