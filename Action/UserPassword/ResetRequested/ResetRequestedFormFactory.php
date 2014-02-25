<?php

namespace Cerad\Bundle\UserBundle\Action\UserPassword\ResetRequested;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints\EqualTo  as EqualToConstraint;
use Symfony\Component\Validator\Constraints\NotBlank as NotBlankConstraint;

use Cerad\Bundle\CoreBundle\Action\ActionFormFactory;

class ResetRequestedFormFactory extends ActionFormFactory
{
    public function create(Request $request, $model)
    {
        $actionRoute = $request->attributes->get('_route');
        $actionUrl   = $this->generateUrl($actionRoute,array('_userFind' => $model->user->getId()));
        
        $formOptions = array(
            'cascade_validation' => true,
          //'intention' => 'authenticate',
            'method' => 'POST',
            'action' => $actionUrl,
            'attr'   => array('class' => 'cerad_common_form1'),
        );
        $builder = $this->formFactory->create('form',$model,$formOptions);
        
        // Form token value needs to match the internel value
        $equalToConstraintOptions = array(
            'value'   => $model->user->getPasswordResetToken(),
            'message' => 'Invalid token value',
        );
        $builder->add('token','text', array(
            'required' => true,
            'label'    => 'Password Reset Token (4 digits)',
            'trim'     => true,
            'constraints' => array(
                new NotBlankConstraint(),
                new EqualToConstraint($equalToConstraintOptions),
            ),
            'attr' => array('size' => 10),
        ));
        $builder->add('password', 'repeated', array(
            'type'     => 'password',
            'label'    => 'Zayso Password',
            'required' => true,
            'attr'     => array('size' => 20),
            
            'invalid_message' => 'The password fields must match.',
            'constraints'     => new NotBlankConstraint(),
            'first_options'   => array('label' => 'New Password'),
            'second_options'  => array('label' => 'New Password(confirm)'),
            
            'first_name'  => 'pass1',
            'second_name' => 'pass2',
        ));
        $builder->add('reset', 'submit', array(
            'label' => 'Change Password',
            'attr'  => array('class' => 'submit'),
        ));
        return $builder;
    }
}