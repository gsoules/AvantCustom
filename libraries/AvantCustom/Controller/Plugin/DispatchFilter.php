<?php

class AvantCustom_Controller_Plugin_DispatchFilter extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $isAdminRequest = $request->getParam('admin', false);
        $moduleName = $request->getModuleName();
        $controllerName = $request->getControllerName();
        $actionName = $request->getActionName();

        $this->preventAdminAccess();

        $this->bypassDashboard($isAdminRequest, $moduleName, $controllerName, $actionName);

        if ($isAdminRequest)
            $this->bypassAdminItemsShow($request, $controllerName, $actionName);
    }

    protected function bypassAdminItemsShow($request, $controllerName, $actionName)
    {
        $isShowRequest = $controllerName == 'items' && $actionName == 'show';

        if ($isShowRequest)
        {
            $id = $request->getParam('id');
            $url = WEB_ROOT . '/admin/custom/show/' . $id;
            $this->getRedirector()->gotoUrl($url);
        }
    }

    protected function bypassDashboard($isAdminRequest, $moduleName, $controllerName, $actionName)
    {
        $downForMaintenance = get_option('custom_maintenance');
        if ($downForMaintenance)
        {
            $noCurrentUser = empty(current_user());
            if ($noCurrentUser && $actionName != 'login' && $actionName != 'maintenance')
            {
                $url = WEB_ROOT . '/custom/maintenance';
                $this->getRedirector()->gotoUrl($url);
                return;
            }
        }

        $isDashboardRequest = $moduleName == 'default' && $controllerName == 'index' && $actionName == 'index';

        if ($isDashboardRequest)
            $this->goToDashboardPage($isAdminRequest);
    }

    protected function getRedirector()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    }

    protected function goToDashboardPage($admin = false)
    {
        $url = WEB_ROOT . ($admin ? '/admin' : '') . '/custom/dashboard';
        $this->getRedirector()->gotoUrl($url);
    }

    protected function preventAdminAccess()
    {
        // Prevent users with the researcher role from accessing the admin pages.
        $user = current_user();
        if ($user && $user->role == 'researcher' && is_admin_theme()) {
            $this->goToDashboardPage();
        }
    }
}
