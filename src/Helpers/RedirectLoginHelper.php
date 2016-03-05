<?php

namespace Instagram\Helpers;

use Instagram\Exceptions\CsrfException;
use Instagram\Http\Sessions\DataStoreInterface;
use League\OAuth2\Client\Provider\Instagram as InstagramProvider;

/**
 * RedirectLoginHelper
 *
 * @package    Instagram
 * @author     Hassan Khan <contact@hassankhan.me>
 * @link       https://github.com/hassankhan/instagram-sdk
 * @license    MIT
 */
class RedirectLoginHelper implements LoginHelperInterface
{
    /**
     * @var InstagramProvider
     */
    protected $provider;

    /**
     * @var DataStoreInterface
     */
    protected $store;

    /**
     * Creates an instance of `RedirectLoginHelper`.
     *
     * @param InstagramProvider  $provider
     * @param DataStoreInterface $store
     */
    public function __construct(InstagramProvider $provider, DataStoreInterface $store)
    {
        $this->provider = $provider;
        $this->store    = $store;
    }

    /**
     * @inheritDoc
     */
    public function getLoginUrl(array $options = [])
    {
        $this->store->set('oauth2state', $this->provider->getState());
        return $this->provider->getAuthorizationUrl($options);
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken($code, $grant = 'authorization_code')
    {
        $this->validateCsrf();
        return $this->provider->getAccessToken($grant, ['code' => $code]);
    }

    /**
     * Validates any CSRF parameters.
     *
     * @throws CsrfException
     */
    protected function validateCsrf()
    {
        if (
            empty($this->getInput('state'))
            || ($this->getInput('state') !== $this->store->get('oauth2state'))
        ) {
            $this->store->set('oauth2state', null);
            throw new CsrfException('Invalid state');
        }
        return;
    }

    /**
     * Retrieves and returns a value from a GET param.
     *
     * @param string $key
     *
     * @return string|null
     */
    protected function getInput($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }
}