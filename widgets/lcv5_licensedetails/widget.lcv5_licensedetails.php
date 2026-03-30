<?php

require_once dirname(__DIR__) . DS . "class.licensecheapv5_widget.php";

class Widget_lcv5_licensedetails extends LicenseCheapV5Widget
{
    protected $widgetfullname = "License Details";
    protected $description = "Show core license information and allow the client to renew the license.";

    public function controller($service, &$module, &$smarty, &$params)
    {
        if (!empty($params["make"]) && $params["make"] === "renew" && !empty($params["token_valid"])) {
            if ($module instanceof LicenseCheapV5 && $module->RenewNow()) {
                $this->addInfo("License renewed successfully.");
            }
            $this->redirectToWidget($service, $params);
        }

        $license = $this->loadLicenseData($service, $module, $smarty, $params, !empty($params["refresh"]));
        $smarty->assign("can_renew_license", !empty($license["can_renew"]));
    }
}

?>
