<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Yii\Http\Tests\Response;

use BeastBytes\Yii\Http\Response\Redirect;
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

class RedirectTest extends TestCase
{
    private const ATTRIBUTE = 'id';
    private const ATTRIBUTES = [self::ATTRIBUTE => 1];
    private const INDEX_ROUTE = 'indexRoute';
    private const INDEX_ROUTE_PATTERN = '/';
    private const ANOTHER_ROUTE = 'anotherRoute';
    private const ANOTHER_ROUTE_PATTERN = '/another-route';
    private const USER_ROUTE = 'userRoute';
    private const USER_ROUTE_PATTERN = '/user/{' . self::ATTRIBUTE . ': \d+}';
    private const QUERY_PARAMETER_1 = 22;
    private const QUERY_PARAMETER_2 = 'string';

    /** @psalm-suppress PropertyNotSetInConstructor  */
    private Redirect $responseCreator;

    protected function setUp(): void
    {
        $this->responseCreator = new Redirect(
            new ResponseFactory(),
            $this->createUrlGenerator()
        );
    }

    #[DataProvider('redirectProvider')]
    public function testRedirectResponse(string $route, array $attributes, int $statusCode, string $url): void
    {
        $responseCreator = $this
            ->responseCreator
            ->toRoute($route, $attributes)
        ;

        $response = match ($statusCode) {
            Status::FOUND => $responseCreator->temporary()->create(),
            Status::MOVED_PERMANENTLY => $responseCreator->permanent()->create(),
            default => $responseCreator->withStatusCode($statusCode)->create(),
        };

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertTrue($response->hasHeader(Header::LOCATION));
        $this->assertSame($url, $response->getHeader(Header::LOCATION)[0]);
    }

    public static function redirectProvider(): Generator
    {
        foreach ([
            self::INDEX_ROUTE => [
                'route' => self::INDEX_ROUTE,
                'attributes' => [],
                'statusCode' => Status::FOUND,
                'url' => '/',
            ],
            self::ANOTHER_ROUTE => [
                'route' => self::ANOTHER_ROUTE,
                'attributes' => [],
                'statusCode' => Status::MOVED_PERMANENTLY,
                'url' => '/another-route',
            ],
            'withAttributes' => [
                'route' => self::USER_ROUTE,
                'attributes' => self::ATTRIBUTES,
                'statusCode' => Status::SEE_OTHER,
                'url' => '/user/' . self::ATTRIBUTES[self::ATTRIBUTE],
            ],
            'withParameters' => [
                'route' => self::ANOTHER_ROUTE,
                'attributes' => [
                    'p1' => self::QUERY_PARAMETER_1,
                    'p2' => self::QUERY_PARAMETER_2,
                ],
                'statusCode' => Status::SEE_OTHER,
                'url' => '/another-route?p1=' . self::QUERY_PARAMETER_1 . '&p2=' . self::QUERY_PARAMETER_2,
            ],
        ] as $name => $params) {
            yield $name => $params;
        }
    }

    private function createUrlGenerator(): UrlGeneratorInterface {
        $routes = [
            Route::get(self::INDEX_ROUTE_PATTERN)
                 ->name(self::INDEX_ROUTE),
            Route::get(self::ANOTHER_ROUTE_PATTERN)
                 ->name(self::ANOTHER_ROUTE),
            Route::get(self::USER_ROUTE_PATTERN)
                 ->name(self::USER_ROUTE),
        ];
        $routeCollection = $this->createRouteCollection($routes);
        return new UrlGenerator($routeCollection);
    }

    private function createRouteCollection(array $routes): RouteCollectionInterface
    {
        $rootGroup = Group::create()->routes(...$routes);
        $collector = new RouteCollector();
        $collector->addGroup($rootGroup);
        return new RouteCollection($collector);
    }
}
