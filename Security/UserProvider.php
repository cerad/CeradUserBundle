<?php
namespace Cerad\Bundle\UserBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Cerad\Bundle\CoreBundle\Events\PersonEvents;

use Cerad\Bundle\CoreBundle\Event\FindPersonEvent;

use Cerad\Bundle\UserBundle\Model\UserManagerInterface;

class UserProvider implements UserProviderInterface
{
    protected $userInterface = 'Cerad\Bundle\UserBundle\Model\UserInterface';
    
    protected $logger;
    protected $dispatcher;
    protected $userManager;
   
    public function __construct
    (
        UserManagerInterface $userManager, 
        EventDispatcherInterface $dispatcher = null, 
        $logger = null
    )
    {
        $this->userManager = $userManager;
        $this->dispatcher  = $dispatcher;
        $this->logger = $logger;
        
    }
    public function getUserManager() { return $this->userManager; }
    
    public function loadUserByUsername($username)
    {
        // The basic way
        $user = $this->userManager->findUserByUsernameOrEmail($username);
        if ($user) return $user;
        
        // Check for social network identifiers
        
        // See if a fed person exists
        $findPersonEvent = new FindPersonEvent($username);
        
        $this->dispatcher->dispatch(PersonEvents::FindPerson,$findPersonEvent);
        
        $person = $findPersonEvent->getPerson();
        
        if ($person)
        {
            $userPerson = $this->userManager->findUserByPersonGuid($person->getGuid());
            if ($userPerson) return $userPerson;
        }
        
        // Bail
        throw new UsernameNotFoundException('User Not Found: ' . $username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!($user instanceOf $this->userInterface))
        {
            throw new UnsupportedUserException();
        }
        return $this->userManager->findUser($user->getId());
    }
    public function supportsClass($class)
    {
        return ($class instanceOf $this->userInterface) ? true: false;
    }
    
}
?>
