<?php

namespace App\Support\Http;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

abstract class BaseHttpClient
{
    protected function get(string $url, array $headers = []): Response
    {
        return Http::withHeaders($headers)
            ->retry(3, 200)
            ->timeout(15)
            ->get($url)
            ->throw();
    }

    protected function post(string $url, array $payload = [], array $headers = []): Response
    {
        return Http::withHeaders($headers)
            ->retry(3, 200)
            ->timeout(15)
            ->post($url, $payload)
            ->throw();
    }
}
