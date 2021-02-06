<?php
/*
 * 仓秘书免费开源WMS仓库管理系统+订货订单管理系统
 *
 * (c) Hunan NLE Network Technology Co., Ltd. <cangmishu.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    private $options;

    // private $events;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct()
    {
        // Dispatcher $events
        // $this->events = $events;
        $this->options = $this->normalizeOptions(config('cors'));
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 是不是 CORS 请求
        // if (! $this->isCorsRequest($request)) {
        //     return $next($request);
        // }

        // 是不是 OPTIONS 预检请求
        if ($this->isPreflightRequest($request)) {
            $response = response('', 204);
            return $this->addPreflightRequestHeaders($response, $request);
        }

        if (! $this->isActualRequestAllowed($request)) {
            return response('Not allowed.', 403);
        }

        // Add the headers on the Request Handled event as fallback in case of exceptions
        // if (class_exists(RequestHandled::class)) {
        //     $this->events->listen(RequestHandled::class, function (RequestHandled $event) {
        //         $this->addHeaders($event->request, $event->response);
        //     });
        // } else {
        //     $this->events->listen('kernel.handled', function (Request $request, Response $response) {
        //         $this->addHeaders($request, $response);
        //     });
        // }

        $response = $next($request);

        return $this->addHeaders($request, $response);
    }

    private function normalizeOptions(array $options = array())
    {
        $options += array(
            'allowedOrigins' => array(),
            'supportsCredentials' => false,
            'allowedHeaders' => array(),
            'exposedHeaders' => array(),
            'allowedMethods' => array(),
            'maxAge' => 0,
        );

        // normalize array('*') to true
        if ($options['allowedOrigins'] === true || in_array('*', $options['allowedOrigins'])) {
            $options['allowedOrigins'] = true;
        }

        if ($options['allowedHeaders'] === true || in_array('*', $options['allowedHeaders'])) {
            $options['allowedHeaders'] = true;
        }

        if ($options['allowedMethods'] === true || in_array('*', $options['allowedMethods'])) {
            $options['allowedMethods'] = true;
        } else {
            $options['allowedMethods'] = array_map('strtoupper', $options['allowedMethods']);
        }

        return $options;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return \Illuminate\Http\Response
     */
    protected function addHeaders(Request $request, Response $response)
    {
        // Prevent double checking
        if (! $response->headers->has('Access-Control-Allow-Origin')) {
            $response = $this->addActualRequestHeaders($response, $request);
        }

        return $response;
    }

    public function isActualRequestAllowed(Request $request)
    {
        return $this->checkOrigin($request);
    }

    public function isCorsRequest(Request $request)
    {
        return $request->headers->has('Origin') && !$this->isSameHost($request);
    }

    public function isPreflightRequest(Request $request)
    {
        // return $this->isCorsRequest($request)
        //     && $request->getMethod() === 'OPTIONS'
        //     && $request->headers->has('Access-Control-Request-Method');

        return $request->getMethod() === 'OPTIONS'
            && $request->headers->has('Access-Control-Request-Method');
    }

    public function addActualRequestHeaders(Response $response, Request $request)
    {
        if (! $this->checkOrigin($request)) {
            return $response;
        }

        $allowOrigin = $this->options['allowedOrigins'] === true && !$this->options['supportsCredentials']
            ? '*'
            : $request->headers->get('Origin');
        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);

        if (! $response->headers->has('Vary')) {
            $response->headers->set('Vary', 'Origin');
        } else {
            $response->headers->set('Vary', $response->headers->get('Vary') . ', Origin');
        }

        if ($this->options['supportsCredentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->options['exposedHeaders']) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->options['exposedHeaders']));
        }

        return $response;
    }

    public function addPreflightRequestHeaders(Response $response, Request $request)
    {
        if (true !== $check = $this->checkPreflightRequestConditions($request)) {
            return $check;
        }

        if ($this->options['supportsCredentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        $allowOrigin = $this->options['allowedOrigins'] === true && !$this->options['supportsCredentials']
            ? '*'
            : $request->headers->get('Origin');
        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);

        if ($this->options['maxAge']) {
            $response->headers->set('Access-Control-Max-Age', $this->options['maxAge']);
        }

        $allowMethods = $this->options['allowedMethods'] === true
            ? strtoupper($request->headers->get('Access-Control-Request-Method'))
            : implode(', ', $this->options['allowedMethods']);
        $response->headers->set('Access-Control-Allow-Methods', $allowMethods);

        $allowHeaders = $this->options['allowedHeaders'] === true
            ? $request->headers->get('Access-Control-Request-Headers')
            : implode(', ', $this->options['allowedHeaders']);
        $response->headers->set('Access-Control-Allow-Headers', $allowHeaders);

        return $response;
    }

    private function checkPreflightRequestConditions(Request $request)
    {
        if (!$this->checkOrigin($request)) {
            return $this->createBadRequestResponse(403, 'Origin not allowed');
        }

        if (!$this->checkMethod($request)) {
            return $this->createBadRequestResponse(405, 'Method not allowed');
        }

        // if allowedHeaders has been set to true ('*' allow all flag) just skip this check
        if ($this->options['allowedHeaders'] !== true && $request->headers->has('Access-Control-Request-Headers')) {
            $allowedHeaders = array_map('strtolower', $this->options['allowedHeaders']);
            $headers = strtolower($request->headers->get('Access-Control-Request-Headers'));
            $requestHeaders = explode(',', $headers);

            foreach ($requestHeaders as $header) {
                if (!in_array(trim($header), $allowedHeaders)) {
                    return $this->createBadRequestResponse(403, 'Header not allowed');
                }
            }
        }

        return true;
    }

    private function createBadRequestResponse($code, $reason = '')
    {
        return new Response($reason, $code);
    }

    private function isSameHost(Request $request)
    {
        return $request->headers->get('Origin') === $request->getSchemeAndHttpHost();
    }

    private function checkOrigin(Request $request)
    {
        if ($this->options['allowedOrigins'] === true) {
            return true;
        }
        $origin = $request->headers->get('Origin');

        foreach ($this->options['allowedOrigins'] as $allowedOrigin) {
            if (Cors::matches($allowedOrigin, $origin)) {
                return true;
            }
        }
        return false;
    }

    private function checkMethod(Request $request)
    {
        if ($this->options['allowedMethods'] === true) {
            // allow all '*' flag
            return true;
        }

        $requestMethod = strtoupper($request->headers->get('Access-Control-Request-Method'));
        return in_array($requestMethod, $this->options['allowedMethods']);
    }

    public static function matches($pattern, $origin)
    {
        if ($pattern === $origin) {
            return true;
        }
        $scheme = parse_url($origin, PHP_URL_SCHEME);
        $host = parse_url($origin, PHP_URL_HOST);
        $port = parse_url($origin, PHP_URL_PORT);

        $schemePattern = static::parseOriginPattern($pattern, PHP_URL_SCHEME);
        $hostPattern = static::parseOriginPattern($pattern, PHP_URL_HOST);
        $portPattern = static::parseOriginPattern($pattern, PHP_URL_PORT);

        $schemeMatches = static::schemeMatches($schemePattern, $scheme);
        $hostMatches = static::hostMatches($hostPattern, $host);
        $portMatches = static::portMatches($portPattern, $port);
        return $schemeMatches && $hostMatches && $portMatches;
    }

    public static function schemeMatches($pattern, $scheme)
    {
        return is_null($pattern) || $pattern === $scheme;
    }

    public static function hostMatches($pattern, $host)
    {
        $patternComponents = array_reverse(explode('.', $pattern));
        $hostComponents = array_reverse(explode('.', $host));
        foreach ($patternComponents as $index => $patternComponent) {
            if ($patternComponent === '*') {
                return true;
            }
            if (!isset($hostComponents[$index])) {
                return false;
            }
            if ($hostComponents[$index] !== $patternComponent) {
                return false;
            }
        }
        return count($patternComponents) === count($hostComponents);
    }

    public static function portMatches($pattern, $port)
    {
        if ($pattern === "*") {
            return true;
        }
        if ((string)$pattern === "") {
            return (string)$port === "";
        }
        if (preg_match('/\A\d+\z/', $pattern)) {
            return (string)$pattern === (string)$port;
        }
        if (preg_match('/\A(?P<from>\d+)-(?P<to>\d+)\z/', $pattern, $captured)) {
            return $captured['from'] <= $port && $port <= $captured['to'];
        }
        throw new \InvalidArgumentException("Invalid port pattern: ${pattern}");
    }

    public static function parseOriginPattern($originPattern, $component = -1)
    {
        $matched = preg_match(
            '!\A
                (?: (?P<scheme> ([a-z][a-z0-9+\-.]*) ):// )?
                (?P<host> (?:\*|[\w-]+)(?:\.[\w-]+)* )
                (?: :(?P<port> (?: \*|\d+(?:-\d+)? ) ) )?
            \z!x',
            $originPattern,
            $captured
        );
        if (!$matched) {
            throw new \InvalidArgumentException("Invalid origin pattern ${originPattern}");
        }
        $components = [
            'scheme' => $captured['scheme'] ?: null,
            'host' => $captured['host'],
            'port' => array_key_exists('port', $captured) ? $captured['port'] : null,
        ];
        switch ($component) {
            case -1:
                return $components;
            case PHP_URL_SCHEME:
                return $components['scheme'];
            case PHP_URL_HOST:
                return $components['host'];
            case PHP_URL_PORT:
                return $components['port'];
        }
        throw new \InvalidArgumentException("Invalid component: ${component}");
    }

}
