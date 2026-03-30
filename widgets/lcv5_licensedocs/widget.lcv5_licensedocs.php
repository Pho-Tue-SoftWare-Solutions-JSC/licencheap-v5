<?php

require_once dirname(__DIR__) . DS . "class.licensecheapv5_widget.php";

class Widget_lcv5_licensedocs extends LicenseCheapV5Widget
{
    protected $widgetfullname = "License Docs";
    protected $description = "Display install commands, notes, and ShowHelp output for the licensed product.";

    public function controller($service, &$module, &$smarty, &$params)
    {
        $refreshHelp = !empty($params["refresh"]);
        if (!empty($params["make"]) && $params["make"] === "refreshdocs" && !empty($params["token_valid"])) {
            $refreshHelp = true;
        }

        $license = $this->loadLicenseData($service, $module, $smarty, $params, $refreshHelp);
        $smarty->assign("can_refresh_docs", !empty($license) && (!isset($license["command_source"]) || $license["command_source"] !== "local"));
    }
}

?>
