<?php

namespace Cerad\Bundle\UserBundle\Action\User\Login;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints\NotBlank  as NotBlankConstraint;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameOrEmailExistsConstraint;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class UserLoginFormFactory extends ActionFormFactory
{
    public function create(Request $request, $model)
    {
        $formOptions = array(
            'cascade_validation' => true,
            'intention' => 'authenticate',
            'method' => 'POST',
            'action' => $this->generateUrl('cerad_user__user__login_check'),
            'attr'   => array(
                'class' => 'cerad_common_form1',
            ),
        );
        $constraintOptions = array();
        
        $builder = $this->formFactory->createNamed('cerad_user__user__login','form',$model,$formOptions);
        
        $builder->add('username','text', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new UsernameOrEmailExistsConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
         $builder->add('password','password', array(
            'required' => true,
            'label'    => 'Password',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
        ));
        $builder->add('rememberMe','checkbox',array(
            'required' => false,
            'label'    => 'Remember Me',
        ));
        
        $builder->add('login', 'submit', array(
            'label' => 'Log In',
            'attr'  => array('class' => 'submit'),
        ));
        
        // Actually a form
        return $builder;
    }
}