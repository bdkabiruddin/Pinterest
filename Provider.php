<?php

namespace SocialiteProviders\Pinterest;

use SocialiteProviders\Manager\OAuth2\User;
use Laravel\Socialite\Two\ProviderInterface;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider implements ProviderInterface
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'PINTEREST';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['read_public'];
    
    /**
     * {@inheritdoc}
     */
    protected $fields = ['account_type', 'first_name', 'last_name', 'username', 'id', 'image', 'url'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            'https://api.pinterest.com/oauth/',
            $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://api.pinterest.com/v1/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://api.pinterest.com/v1/me/?fields='.implode(',', $this->fields),
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        $contents = $response->getBody()->getContents();

        return json_decode($contents, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $avatarUrl = $user['data']['image']['60x60']['url'];

        return (new User())->setRaw($user)->map(
            [
                'id' => $user['data']['id'],
                'nickname' => $user['data']['username'],
                'name' => $user['data']['first_name'].' '.$user['data']['last_name'],
                'avatar' => $avatarUrl
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(
            parent::getTokenFields($code),
            [
                'grant_type' => 'authorization_code',
            ]
        );
    }
}
