<?php

namespace Cerad\Bundle\UserBundle\Action\UserPassword\ResetRequested;

use Symfony\Component\HttpFoundation\Request;

//  Symfony\Component\Security\Http\SecurityEvents;
//  Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Cerad\Bundle\CoreBundle\Event\User\LoginUserEvent;

use Cerad\Bundle\CoreBundle\Action\ActionModelFactory;

class ResetRequestedModel extends ActionModelFactory // Share the username nonsense
{
    public $user;
    public $token;
    public $error;
    public $password;
    
    protected $userManager;
    protected $securityContext;
    
    public function __construct(SecurityContextInterface $securityContext, $userManager)
    {
        $this->userManager     = $userManager;
        $this->securityContext = $securityContext;
    }
    public function create(Request $request)
    {
        $this->user  = $request->attributes->get('user');
        $this->token = $request->attributes->get('token');
                
        return $this;
    }
    public function process(Request $request)
    {
        $user = $this->user;
        
        $user->setPasswordResetToken(null);
        
        $user->setPasswordPlain($this->password);
        
        $this->userManager->updateUser($user);
        
        /* ===============================================
         * Login the user here
         * Really need to move this to a listener but lots of stuff there
         */
        $event = new LoginUserEvent($request,$user);
        $this->dispatcher->dispatch(LoginUserEvent::NAME,$event);
        
        return;
        
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $this->securityContext->setToken($token);
        
        $session = $request->getSession();
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(SecurityContextInterface::LAST_USERNAME);      
    }
}