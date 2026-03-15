<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class JwtResponseListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();

        $event->setData([
            'status' => 'success',
            'data' => [
                'token' => $data['token'],
            ]
        ]);
    }
}
