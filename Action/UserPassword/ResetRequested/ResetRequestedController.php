<?php

namespace Cerad\Bundle\UserBundle\Action\UserPassword\ResetRequested;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\CoreBundle\Action\ActionController;

class ResetRequestedController extends ActionController
{
    public function action(Request $request, $model, $form)
    {   
        $form->handleRequest($request);

        if ($form->isValid()) 
        {   
            $model->process($request);
            
            return $this->redirectResponse('cerad_user__welcome');
        }
        // Render
        $tplData = array();
        $tplData['form']  = $form->createView();
        $tplData['error'] = $model->error;

        $tplName = $request->attributes->get('_template');
        return $this->regularResponse($tplName,$tplData);
    }
}