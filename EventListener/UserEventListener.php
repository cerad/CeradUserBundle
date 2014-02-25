<?php
namespace Cerad\Bundle\UserBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Cerad\Bundle\CoreBundle\Event\User\FindUserEvent;
use Cerad\Bundle\CoreBundle\Event\User\LoginUserEvent;
use Cerad\Bundle\CoreBundle\Event\User\GetAuthInfoEvent;
use Cerad\Bundle\CoreBundle\Event\User\ResetPasswordRequestEvent;

class UserEventListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array
        (
            FindUserEvent::NAME             => array('doFindUser'),
            LoginUserEvent::NAME            => array('doLoginUser'),
            GetAuthInfoEvent::NAME          => array('doGetAuthInfo'),
            ResetPasswordRequestEvent::NAME => array('doResetPasswordRequest'),
        );
    }
    /* ============================================================
     * For now this is by id
     * Need to consolidate UserManager, UserRepo and UserProvider
     */
    public function doFindUser(FindUserEvent $event)
    {
        $search = $event->getSearch();
        
        $userRepo = $this->container->get('cerad_user__user_repository');
        
        $user = $userRepo->findUser($search);
        
        $event->setUser($user);
    }
    /* ============================================================
     * This works but there is a lot more that should probably happen
     */
    public function doLoginUser(LoginUserEvent $event)
    {
        $user    = $event->user;
        $request = $event->request;
        
        $firewallName = $this->container->getParameter('cerad_user__firewall_name');
        
        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        
        $this->container->get('security.context')->setToken($token);
        
        $session = $request->getSession();
        $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        $session->remove(SecurityContextInterface::LAST_USERNAME);      
    }
    /* ============================================================
     * Basically pull any previous bad login information
     */
    public function doGetAuthInfo(GetAuthInfoEvent $event)
    {
        $request = $event->request;
        
        // Check request for error
        $contextError = $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        $event->error = $contextError ? $contextError->getMessage() : null;
        
        // Then look in session
        $session = $request->getSession();
        if (!$session) return;
        
        // Pull user name
        $event->username = $session->get(SecurityContextInterface::LAST_USERNAME);
        
        // Check for error in session
        if (!$contextError && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) 
        {
            $sessionError = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove             (SecurityContextInterface::AUTHENTICATION_ERROR);
            
            $event->error = $sessionError ? $sessionError->getMessage() : null;
        }
    }
    /* ======================================================================
     * Takes care of sending out the email
     * I suppose it could also do the token generation and such
     */
    public function doResetPasswordRequest(ResetPasswordRequestEvent $event)
    {
        $user  = $event->getUser();
        $token = $event->getToken();
        
        $router = $this->container->get('router');
        $url = $router->generate(
            'cerad_user__user_password__reset_requested', 
            array('_userFind' => $user->getId(), 'token' => $token),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $tplData = array();
        $tplData['url']    = $url;
        $tplData['user']   = $user;
        $tplData['token']  = $token;
        $tplData['prefix'] = 'ZaysoAdmin';
        
        $templating = $this->container->get('templating');
        
        // Pull from project maybe? Use event->by?
        $tplEmailSubject = '@CeradUser/UserPassword/ResetEmail/ResetEmailSubject.html.twig';
        $tplEmailContent = '@CeradUser/UserPassword/ResetEmail/ResetEmailContent.html.twig';
        
        $subject = $templating->render($tplEmailSubject,$tplData);
        $content = $templating->render($tplEmailContent,$tplData);
        
      //echo $subject . '<br />';
      //echo nl2br($content);
      //die();
        
        $fromName =  'Zayso Password Reset';
        $fromEmail = 'noreply@zayso.org';
        
        $bccName =  'Art Hundiak';
        $bccEmail = 'ahundiak@gmail.com';
        
        $userName  = $user->getAccountName();
        $userEmail = $user->getEmail();
        
        $message = \Swift_Message::newInstance();
        $message->setSubject($subject);
        $message->setBody   ($content);
        $message->setFrom   (array($fromEmail => $fromName));
        $message->setBcc    (array($bccEmail  => $bccName));
        $message->setTo     (array($userEmail => $userName));
        
        $this->container->get('mailer')->send($message);
    }
}
?>
