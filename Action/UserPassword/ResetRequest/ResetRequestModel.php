<?php

namespace Cerad\Bundle\UserBundle\Action\User\Login;

// Symfony\Component\HttpFoundation\Request;
// Symfony\Component\Security\Core\SecurityContextInterface;

use Cerad\Bundle\UserBundle\Action\User\Login\UserLoginModel;

class ResetRequestModel extends UserLoginModel // Share the username nonsense
{
    public $user;
    public $username; // From login model
    public $userToken;
    
    protected $mailer;
    protected $userProvider;
    
    public function __construct($mailer,$userProvider)
    {
        $this->mailer = $mailer;
        $this->userProvider = $userProvider;
    }
    public function process()
    {
        $user = $this->userProvider->loadUserByUsername($this->username);
        
        // Make a key 
        $token = rand(1000,9999);
        $user->setPasswordResetToken($token);
        
        $this->userProvider->getUserManager()->updateUser($user);
        
        $this->user      = $user;
        $this->userToken = $token;
        
        // Any real need to make this a listener? Maybe.
        $this->sendEmail();
    }
    protected function sendEmail()
    {
        $message = \Swift_Message::newInstance();
        $message->setSubject($emailSubject);
        $message->setBody   ($emailBody);
        $message->setFrom(array($fromEmail  => $fromName ));
        $message->setBcc (array($adminEmail => $adminName));
        $message->setTo  (array($userEmail  => $userName ));

        $this->mailer->send($message);
         
    }
}