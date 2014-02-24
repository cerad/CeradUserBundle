<?php

namespace Cerad\Bundle\UserBundle\Action\UserPassword\ResetRequest;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class ResetRequestController extends ActionController
{
    public function action(Request $request, $model, $form)
    {   
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model->process();
            
            return $this->redirect(
                'cerad_user__user_password__reset_requested', 
                 array('_user' => $model->user->getId())
            );
        }
        // Render
        $tplData = array();
        $tplData['form']  = $form->createView();
        $tplData['error'] = $model->error;

        $tplName = $request->attributes->get('_template');
        return $this->regularResponse($tplName,$tplData);
    }
}