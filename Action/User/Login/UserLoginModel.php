<?php

namespace Cerad\Bundle\UserBundle\Action\User\Login;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
        // Check request for error
        $contextError = $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        $this->error = $contextError ? $contextError->getMessage() : null;
        
        // Then look in session
        $session = $request->getSession();
        if (!$session) return $this;
        
        // Pull user name
        $this->username = $session->get(SecurityContextInterface::LAST_USERNAME);
        
        // Check for error in session
        if (!$contextError && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) 
        {
            $sessionError = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove             (SecurityContextInterface::AUTHENTICATION_ERROR);
            
            $this->error = $sessionError ? $sessionError->getMessage() : null;
        }
        return $this;
    }
}