<?php

class LicenseCheapV5_controller extends HBController
{
    public $module;
    protected $tpl_dir = "Hosting/licensecheapv5/templates/";
    protected $tpl_dir_path;

    public function beforeCall($params)
    {
        $this->tpl_dir_path = APPDIR_MODULES . $this->tpl_dir;
        return parent::beforeCall($params);
    }

    public function accountdetails($params)
    {
        $this->template->assign("custom_template", $this->tpl_dir_path . "license.tpl");
        $this->template->assign("module_tpldir", "../includes/modules/" . $this->tpl_dir);
    }

    public function license($params)
    {
        if (!$this->setupModule($params["id"])) {
            return NULL;
        }

        $this->template->assign("license", $this->module->LicenseDetails(!empty($params["refresh"])));
        $this->template->assign("details", $this->module->getAccount());
        $this->template->render($this->tpl_dir_path . "ajax.license.tpl");
    }

    public function changeip($params)
    {
        if (!$this->setupModule($params["id"]) || !$params["token_valid"]) {
            return NULL;
        }

        $newIp = "";
        if (!empty($params["newip"])) {
            $newIp = $params["newip"];
        } else if (!empty($params["new_ip"])) {
            $newIp = $params["new_ip"];
        } else if (!empty($params["ip"])) {
            $newIp = $params["ip"];
        }

        $oldIp = "";
        if (!empty($params["newip"]) && !empty($params["ip"])) {
            $oldIp = $params["ip"];
        } else if (!empty($params["oldip"])) {
            $oldIp = $params["oldip"];
        } else if (!empty($params["old_ip"])) {
            $oldIp = $params["old_ip"];
        }

        if ($newIp !== "") {
            if ($this->module->LicenseChangeIp($newIp, $oldIp)) {
                Engine::addInfo("Licensed IP updated successfully");
            }
        } else {
            Engine::addError("New IP address is required");
        }

        Utilities::redirect("?" . Utilities::adminLink($params["id"], HOSTING));
    }

    public function renew($params)
    {
        if (!$this->setupModule($params["id"]) || !$params["token_valid"]) {
            return NULL;
        }

        if ($this->module->Renewal()) {
            Engine::addInfo("License renewed successfully");
        }
        Utilities::redirect("?" . Utilities::adminLink($params["id"], HOSTING));
    }

    public function resetipcount($params)
    {
        if (!$this->setupModule($params["id"]) || !$params["token_valid"]) {
            return NULL;
        }

        if ($this->module->ResetChangeIpCount()) {
            Engine::addInfo("IP change counter has been reset");
        }

        Utilities::redirect("?" . Utilities::adminLink($params["id"], HOSTING));
    }

    protected function setupModule($service_id)
    {
        $accounts = HBLoader::LoadModel("Accounts");
        $data = $accounts->getAccount($service_id);
        if (!$data) {
            return false;
        }

        $account_config = $accounts->getAccountModuleConfig($service_id);
        $server = HBLoader::LoadModel("Servers")->getServerDetails($data["server_id"]);
        $product = HBLoader::LoadComponent("Products")->getProduct($data["product_id"]);

        $this->module->connect($server);
        $this->module->setProduct($product);
        $this->module->setAccount($data);
        $this->module->setAccountConfig($account_config);
        $this->module->setClientDetails($data["client_id"]);
        return true;
    }
}

?>
