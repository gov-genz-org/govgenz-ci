<?php

declare(strict_types=1);

namespace Tests\Support;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;

final class IncomingRequestFactory
{
    public static function create(string $path = ''): IncomingRequest
    {
        $uri = $path !== ''
            ? new URI('http://example.com/' . ltrim($path, '/'))
            : new URI('http://example.com/');

        return new IncomingRequest(config(App::class), $uri, null, new UserAgent());
    }

    /**
     * @param array<string, mixed> $post
     */
    public static function withPost(array $post, string $path = ''): IncomingRequest
    {
        $request = self::create($path);
        $request->setGlobal('post', $post);

        return $request;
    }

    /**
     * @param array<string, mixed> $get
     */
    public static function withGet(array $get, string $path = 'admin/pages'): IncomingRequest
    {
        $request = self::create($path);
        $request->setGlobal('get', $get);

        return $request;
    }
}
