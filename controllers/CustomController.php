<?php

class AvantCustom_CustomController extends Omeka_Controller_AbstractActionController
{
    public function dashboardAction()
    {
        return;
    }

    public function maintenanceAction()
    {
        return;
    }

    public function showAction()
    {
        $id = $this->getParam('item-id');
        $this->view->itemId = $id;
    }
}
