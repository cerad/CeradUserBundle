<?php

namespace Cerad\Bundle\UserBundle\Action\UserPassword\ResetRequest;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\UserBundle\ValidatorConstraint\UsernameOrEmailExistsConstraint;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class ResetRequestFormFactory extends ActionFormFactory
{
    public function create(Request $request, $model)
    {
        $formOptions = array(
            'cascade_validation' => true,
          //'intention' => 'authenticate',
            'method' => 'POST',
            'action' => $this->generateUrl('cerad_user__user__login_check'),
            'attr'   => array('class' => 'cerad_common_form1'),
        );
        $constraintOptions = array();
        
        $builder = $this->formFactory->create('form',$model,$formOptions);
        
        $builder->add('username','text', array(
            'required' => true,
            'label'    => 'Email',
            'trim'     => true,
            'constraints' => array(
                new UsernameOrEmailExistsConstraint($constraintOptions),
            ),
            'attr' => array('size' => 30),
         ));
        $builder->add('reset', 'submit', array(
            'label' => 'Password Reset Request',
            'attr'  => array('class' => 'submit'),
        ));
        
        // Actually a form
        return $builder;
    }
}