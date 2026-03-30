<?php

abstract class LicenseCheapV5Widget extends HostingWidget
{
    protected $info = ["appendtpl" => "default.tpl", "options" => 3];

    protected function buildWidgetUrl($service, $params)
    {
        $url = "?cmd=clientarea&action=services&service=" . (int) $service["id"] . "&widget=" . $this->getWidgetName();
        if (!empty($params["wid"])) {
            $url .= "&wid=" . $params["wid"];
        }
        return $url;
    }

    protected function redirectToWidget($service, $params, $suffix = "")
    {
        Utilities::redirect($this->buildWidgetUrl($service, $params) . $suffix);
    }

    protected function getServiceExtraDetails($service)
    {
        if (empty($service["extra_details"])) {
            return [];
        }

        if (is_array($service["extra_details"])) {
            return $service["extra_details"];
        }

        if (function_exists("unserialize7")) {
            $details = unserialize7($service["extra_details"]);
        } else {
            $details = @unserialize($service["extra_details"]);
        }

        return is_array($details) ? $details : [];
    }

    protected function loadLicenseData($service, &$module, &$smarty, $params, $refreshHelp = false)
    {
        $widgetUrl = $this->buildWidgetUrl($service, $params);
        $smarty->assign("widget_url", $widgetUrl);
        $smarty->assign("service", $service);

        if (!$module instanceof LicenseCheapV5) {
            return [];
        }

        try {
            $module->prepareDetails($this->getServiceExtraDetails($service));
            $license = $module->LicenseDetails($refreshHelp);
            $smarty->assign("license", $license);
            return $license;
        } catch (Exception $exception) {
            $this->addError($exception->getMessage());
        }

        $smarty->assign("license", []);
        return [];
    }

    public function doesApply(&$module)
    {
        return $module instanceof LicenseCheapV5 && parent::doesApply($module);
    }
}

?>
