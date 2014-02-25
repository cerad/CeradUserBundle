<?php

namespace Cerad\Bundle\UserBundle\Action\UserPassword\ResetRequest;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Event\User\GetAuthInfoEvent;
use Cerad\Bundle\CoreBundle\Event\User\ResetPasswordRequestEvent;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class ResetRequestModel extends ActionModelFactory
{
    public $user;
    public $username; // From login model
    public $token;
    public $error;
    
    protected $userProvider;
    
    public function __construct($userProvider)
    {
        $this->userProvider = $userProvider;
    }
    public function create(Request $request)
    {
        $event = new GetAuthInfoEvent($request);
        $this->dispatcher->dispatch(GetAuthInfoEvent::NAME,$event);
        
        $this->error    = $event->error;
        $this->username = $event->username;
        
        return $this;
    }
    public function process()
    {
        $user = $this->userProvider->loadUserByUsername($this->username);
        
        // Make a key 
        $token = rand(1000,9999);
        $user->setPasswordResetToken($token);
        
        $this->userProvider->getUserManager()->updateUser($user);
        
        $this->user  = $user;
        $this->token = $token;
        
        $event = new ResetPasswordRequestEvent($user,$token);
        $this->dispatcher->dispatch(ResetPasswordRequestEvent::NAME,$event);         
    }
}