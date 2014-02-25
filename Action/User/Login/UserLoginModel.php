<?php

namespace Cerad\Bundle\UserBundle\Action\User\Login;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Event\User\GetAuthInfoEvent;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class UserLoginModel extends ActionModelFactory
{
    public $error;
    public $username;
    public $password;
    public $rememberMe;
    
    // Kind of messy
    public function create(Request $request)
    {
        $event = new GetAuthInfoEvent($request);
        $this->dispatcher->dispatch(GetAuthInfoEvent::NAME,$event);
        
        $this->error    = $event->error;
        $this->username = $event->username;
        
        return $this;
        
    }
}