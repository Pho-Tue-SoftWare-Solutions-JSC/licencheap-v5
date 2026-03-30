<?php

require_once dirname(__DIR__) . DS . "class.licensecheapv5_widget.php";

class Widget_lcv5_changeip extends LicenseCheapV5Widget
{
    protected $widgetfullname = "Change IP";
    protected $description = "Allow the client to update the licensed IP address from client area.";

    public function controller($service, &$module, &$smarty, &$params)
    {
        $license = $this->loadLicenseData($service, $module, $smarty, $params);
        $submittedIp = !empty($params["new_ip"]) ? trim((string) $params["new_ip"]) : "";

        if (!empty($params["make"]) && $params["make"] === "submit" && !empty($params["token_valid"])) {
            $oldIp = !empty($params["old_ip"]) ? trim((string) $params["old_ip"]) : (!empty($license["license_ip"]) ? $license["license_ip"] : "");
            if ($module instanceof LicenseCheapV5 && $module->LicenseChangeIp($submittedIp, $oldIp)) {
                $this->addInfo("Licensed IP updated successfully.");
            }
            $this->redirectToWidget($service, $params);
        }

        $limitReached = false;
        if (!empty($license)) {
            $maxChanges = isset($license["max_ip_changes"]) ? (int) $license["max_ip_changes"] : 0;
            $usedChanges = isset($license["change_ip_count"]) ? (int) $license["change_ip_count"] : 0;
            $limitReached = 0 < $maxChanges && $maxChanges <= $usedChanges;
        }

        $smarty->assign("submitted_new_ip", $submittedIp);
        $smarty->assign("change_ip_limit_reached", $limitReached);
    }
}

?>
