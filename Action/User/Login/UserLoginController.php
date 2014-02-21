<?php

namespace Cerad\Bundle\UserBundle\Action\User\Login;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class UserLoginController extends ActionController
{
    public function action(Request $request, $model, $form)
    {   
        // Render
        $tplData = array();
        $tplData['form']  = $form->createView();
        $tplData['error'] = $model->error;

        $tplName = $request->attributes->get('_template');
        return $this->regularResponse($tplName,$tplData);
    }
}