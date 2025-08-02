<?php

declare(strict_types=1);

namespace App\Service;

use App\Traits\Aware\LoggerAwareTrait;
use App\Traits\Aware\UrlHelperAwareTrait;
use Laminas\Diactoros\Uri;
use Mezzio\Session\SessionInterface;

class UrlpoolService
{
    use LoggerAwareTrait;

    use UrlHelperAwareTrait;

    protected SessionInterface $session;

    protected ?Uri $uri = null;

    protected string $routeName = '';

    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    protected function fragment(int|string|false|null $fragment = null): ?string
    {
        return $fragment ? 'anchor-' . $fragment : null;
    }

    public function save(): static
    {
        $name = $this->getUrlHelper()->getRouteResult()->getMatchedRouteName();

        $uri = $this->getUrlHelper()->getRequest()->getUri();

        $urls = $this->session->get('urls') ?? [];

        unset($urls[$name]);

        $urls[$name] = serialize($uri);

        $this->session->set('urls', $urls);

        return $this;
    }

    public function uri(?string $routeName = null): static
    {
        $this->routeName = $routeName ?? $this->routeName;

        $urls = $this->session->get('urls');

        if (empty($urls) || ($this->routeName && !array_key_exists($this->routeName, $urls))) {

            $this->uri = new Uri();

            $this->routeName = 'default.root';
        } elseif (empty($this->routeName)) {

            $this->uri = unserialize(end($urls));

            $this->routeName = array_key_last($urls);
        } else {

            $this->uri = unserialize($urls[$this->routeName]);
        }

        return $this;
    }

    public function get(array $params = [], array $query_params = [], bool $query_reset = false, int|string|false|null $fragment = null): string
    {
        if (!$this->uri) $this->uri();

        parse_str(!$query_reset ? $this->uri->getQuery() : '', $query_array);

        return $this->getUrlHelper()->generate(
            $this->routeName,
            $params,
            array_merge($query_array, $query_params),
            $this->fragment($fragment)
        );
    }
}
