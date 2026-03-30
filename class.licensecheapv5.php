<?php

class LicenseCheapV5 extends LicenseModule
{
    protected $version = "1.1.0";
    protected $_repository = "hosting_licensecheapv5";
    protected $description = "License.Cheap / ResellerCenter provisioning module for HostBill.";
    protected $modname = "LicenseCheap v5";

    protected $serverFields = [
        self::CONNECTION_FIELD_HOSTNAME => true,
        self::CONNECTION_FIELD_IPADDRESS => false,
        self::CONNECTION_FIELD_USERNAME => true,
        self::CONNECTION_FIELD_PASSWORD => false,
        self::CONNECTION_FIELD_INPUT1 => true,
        self::CONNECTION_FIELD_INPUT2 => true,
        self::CONNECTION_FIELD_TEXTAREA => false,
        self::CONNECTION_FIELD_CHECKBOX => true,
        self::CONNECTION_FIELD_NAMESERVERS => false,
        self::CONNECTION_FIELD_MAXACCOUNTS => false,
        self::CONNECTION_FIELD_STATUSURL => false,
    ];

    protected $serverFieldsDescription = [
        self::CONNECTION_FIELD_HOSTNAME => "API Base URL",
        self::CONNECTION_FIELD_USERNAME => "API Token",
        self::CONNECTION_FIELD_INPUT1 => "Installer Script URL",
        self::CONNECTION_FIELD_INPUT2 => "Command Prefix",
        self::CONNECTION_FIELD_CHECKBOX => "Use extended suspend endpoints (Ban2/UnBan2)",
    ];

    protected $options = [
        "product" => [
            "name" => "Product",
            "value" => false,
            "type" => "loadable",
            "default" => "loadPackageProducts",
            "variable" => "product",
        ],
        "ip" => [
            "name" => "Licensed IP",
            "value" => false,
            "type" => "hidden",
            "forms" => "input",
            "variable" => "ip",
        ],
        "new_ip" => [
            "name" => "New IP Address",
            "value" => false,
            "type" => "hidden",
            "forms" => "input",
            "variable" => "new_ip",
        ],
        "max_ip_changes" => [
            "name" => "Max IP changes",
            "value" => "3",
            "type" => "input",
            "variable" => "max_ip_changes",
            "description" => "Set 0 to disable the HostBill-side limit.",
        ],
        "license_key_prefix" => [
            "name" => "Reference key prefix",
            "value" => "LC-",
            "type" => "input",
            "variable" => "license_key_prefix",
        ],
        "help_source" => [
            "name" => "Install docs source",
            "value" => "hybrid",
            "type" => "select",
            "default" => ["hybrid", "local", "remote"],
            "variable" => "help_source",
            "description" => "<b>hybrid</b> - use local command metadata and append remote ShowHelp output when available.<br><b>local</b> - use local command metadata only.<br><b>remote</b> - prefer remote ShowHelp output and fall back to local metadata.",
        ],
    ];

    protected $details = [
        "reference_key" => ["name" => "Reference Key", "type" => "input"],
        "license_ip" => ["name" => "Licensed IP", "type" => "input"],
        "product_id" => ["name" => "Product ID", "type" => "hidden"],
        "product_name" => ["name" => "Product Name", "type" => "input"],
        "status" => ["name" => "License Status", "type" => "select", "default" => ["Pending", "Active", "Suspended", "Expired", "Error"]],
        "message" => ["name" => "Last API Message", "type" => "hidden"],
        "help_raw" => ["name" => "Remote Help", "type" => "hidden"],
        "change_ip_count" => ["name" => "IP Change Count", "type" => "hidden", "value" => "0"],
        "billing_cycle_months" => ["name" => "Billing Cycle Months", "type" => "hidden"],
        "last_action" => ["name" => "Last Action", "type" => "hidden"],
        "last_remote_action" => ["name" => "Last Remote Action", "type" => "hidden"],
        "command_source" => ["name" => "Command Source", "type" => "hidden"],
        "new_ip" => ["name" => "New IP Address", "type" => "hidden"],
    ];

    protected $commands = ["RenewNow", "LicenseChangeIp"];
    protected $api;
    protected $connect_data = [];
    protected $products;

    const DEFAULT_API_BASE_URL = "https://api.resellercenter.ir/rc/";
    const DEFAULT_INSTALLER_URL = "https://mirror.resellercenter.ir/pre.sh";
    const DEFAULT_COMMAND_PREFIX = "RcLicense";
    const REMOVE_COMMAND = "wget -O remover https://mirror.resellercenter.ir/remover; chmod +x remover; ./remover";
    const WIDGET_OPTIONS_DEFAULT = 155;
    const WIDGET_OPTIONS_ACTION = 27;

    public function install()
    {
        $this->upgrade("0.0.0");
    }

    public function upgrade($old)
    {
        if (version_compare((string) $old, "1.0.0", "<") && class_exists("LangEdit")) {
            LangEdit::addTranslations([
                "lcv5_manage_license" => "Manage License",
                "lcv5_install_docs" => "Install & Docs",
                "lcv5_reference_key" => "Reference Key",
                "lcv5_product" => "Product",
                "lcv5_license_ip" => "Licensed IP",
                "lcv5_status" => "Status",
                "lcv5_billing_cycle" => "Billing Cycle",
                "lcv5_change_ip_count" => "IP Changes Used",
                "lcv5_change_ip" => "Change IP",
                "lcv5_new_ip" => "New IP Address",
                "lcv5_renew_now" => "Renew Now",
                "lcv5_command_source" => "Command Source",
                "lcv5_remote_help" => "Remote ShowHelp Output",
                "lcv5_local_commands" => "Local Install Commands",
                "lcv5_notes" => "Notes",
                "lcv5_update_command" => "Update Command",
                "lcv5_install_command" => "Install Command",
                "lcv5_verify_command" => "Verify Command",
                "lcv5_remove_command" => "Remove Previous License",
                "lcv5_last_message" => "Last API Message",
                "lcv5_widget_license_details" => "License Details",
                "lcv5_widget_change_ip" => "Change IP",
                "lcv5_widget_license_docs" => "License Docs",
                "lcv5_widget_renew_success" => "License renewed successfully.",
                "lcv5_widget_change_ip_success" => "Licensed IP updated successfully.",
            ], "clientarea", "user");
        }

        if (version_compare((string) $old, "1.1.0", "<")) {
            $this->registerClientWidgets();
        }
    }

    public function connect($server)
    {
        $this->connect_data = is_array($server) ? $server : [];
        $baseUrl = $this->serverValue(["host", "hostname"], self::DEFAULT_API_BASE_URL);
        $token = $this->serverValue(["username", "user", "token"], "");
        $this->api = new Hosting\LicenseCheapV5\Api($token, $baseUrl);
    }

    public function api()
    {
        if ($this->api instanceof Hosting\LicenseCheapV5\Api) {
            return $this->api;
        }
        throw new RuntimeException("Api connection was not initialized, call \"connect\" first");
    }

    public function testConnection($log = NULL)
    {
        try {
            $bundles = $this->api()->bundleList();
            if (is_callable($log)) {
                $log(Monolog\Logger::INFO, "Connected to LicenseCheap API, bundles detected: " . count($bundles));
            }
            return true;
        } catch (Exception $ex) {
            try {
                $help = $this->api()->showHelp("10");
                if (!empty($help["raw"])) {
                    if (is_callable($log)) {
                        $log(Monolog\Logger::INFO, "Connected to LicenseCheap API using ShowHelp fallback");
                    }
                    return true;
                }
            } catch (Exception $ignored) {
            }
            $this->addError($ex->getMessage());
        }
        return false;
    }

    public function setAccountConfig($config)
    {
        parent::setAccountConfig($config);
        if (empty($this->account_config["ip"])) {
            $ip = "";
            if (!empty($this->details["license_ip"]["value"])) {
                $ip = $this->details["license_ip"]["value"];
            } else if (!empty($this->account_details["domain"]) && filter_var($this->account_details["domain"], FILTER_VALIDATE_IP)) {
                $ip = $this->account_details["domain"];
            }
            $this->details["ip"] = ["name" => "License IP", "value" => $ip, "type" => "input", "variable" => "ip"];
        }
    }

    public function Create($addon = false)
    {
        try {
            $product = $this->selectedProduct();
            $ip = $this->currentIp(true);
            $billingCycle = $this->resolveBillingCycleMonths();
            $response = $this->api()->createLicense($product["api_type"], $ip, $billingCycle);

            $this->details["reference_key"]["value"] = $this->details["reference_key"]["value"] ? $this->details["reference_key"]["value"] : $this->generateReferenceKey();
            $this->details["license_ip"]["value"] = $ip;
            $this->details["product_id"]["value"] = $product["id"];
            $this->details["product_name"]["value"] = $product["display_name"];
            $this->details["status"]["value"] = "Active";
            $this->details["billing_cycle_months"]["value"] = (string) $billingCycle;
            $this->details["message"]["value"] = $response["message"];
            $this->details["last_action"]["value"] = "Create";
            $this->details["last_remote_action"]["value"] = "license";
            $this->details["command_source"]["value"] = $this->helpSource();

            $this->refreshRemoteHelp($product["id"]);
            $this->persistCurrentIp($ip);
            $this->persistDetails();
            return true;
        } catch (Exception $ex) {
            $this->details["status"]["value"] = "Error";
            $this->details["message"]["value"] = $ex->getMessage();
            $this->persistDetails();
            $this->addError($ex->getMessage());
        }
        return false;
    }

    public function Suspend()
    {
        return $this->changeRemoteStatus("Suspend", "Suspended", "Ban");
    }

    public function Unsuspend()
    {
        return $this->changeRemoteStatus("Unsuspend", "Active", "UnBan");
    }

    public function Terminate()
    {
        return $this->changeRemoteStatus("Terminate", "Expired", "Ban");
    }

    public function Renewal()
    {
        try {
            $product = $this->selectedProduct();
            $ip = $this->currentIp(true);
            $response = $this->api()->renewLicense($product["api_type"], $ip, $this->resolveBillingCycleMonths());
            $this->details["status"]["value"] = "Active";
            $this->details["message"]["value"] = $response["message"];
            $this->details["last_action"]["value"] = "Renew";
            $this->details["last_remote_action"]["value"] = "ReNew";
            $this->persistDetails();
            return true;
        } catch (Exception $ex) {
            $this->details["message"]["value"] = $ex->getMessage();
            $this->persistDetails();
            $this->addError($ex->getMessage());
        }
        return false;
    }

    public function RenewNow()
    {
        return $this->Renewal();
    }

    public function ResetChangeIpCount()
    {
        $this->details["change_ip_count"]["value"] = "0";
        $this->details["new_ip"]["value"] = "";
        $this->details["message"]["value"] = "IP change counter was reset from HostBill admin area";
        $this->details["last_action"]["value"] = "Reset IP Counter";
        $this->details["last_remote_action"]["value"] = "Local Reset";
        $this->persistDetails();
        return true;
    }

    public function LicenseChangeIp($newIp = null, $oldIp = null)
    {
        $newIp = $this->resolveChangeIpNewIp($newIp);
        if (!filter_var($newIp, FILTER_VALIDATE_IP)) {
            $this->addError("New IP address is invalid");
            return false;
        }

        $oldIp = $this->resolveChangeIpOldIp($oldIp);
        if (!filter_var($oldIp, FILTER_VALIDATE_IP)) {
            $this->addError("Current licensed IP is empty or invalid");
            return false;
        }

        $currentCount = (int) $this->detailValue("change_ip_count", 0);
        $maxChanges = (int) $this->resourceOrDefault("max_ip_changes", 3);
        if (0 < $maxChanges && $currentCount >= $maxChanges) {
            $this->addError("This license has already used the maximum number of IP changes allowed by HostBill");
            return false;
        }

        try {
            $product = $this->selectedProduct();
            $response = $this->api()->changeIp($product["api_type"], $oldIp, $newIp);

            $this->details["new_ip"]["value"] = $newIp;
            $this->details["status"]["value"] = "Active";
            $this->details["message"]["value"] = $response["message"];
            $this->details["change_ip_count"]["value"] = (string) ($currentCount + 1);
            $this->details["last_action"]["value"] = "Change IP";
            $this->details["last_remote_action"]["value"] = "ChangeIp";

            $this->persistCurrentIp($newIp);
            $this->persistDetails();
            return true;
        } catch (Exception $ex) {
            $this->details["message"]["value"] = $ex->getMessage();
            $this->persistDetails();
            $this->addError($ex->getMessage());
        }
        return false;
    }

    public function LicenseId()
    {
        return $this->detailValue("reference_key", "");
    }

    public function LicenseDetails($refreshHelp = false)
    {
        $product = [];
        try {
            $product = $this->selectedProduct();
        } catch (Exception $ignored) {
        }

        if ($refreshHelp && !empty($product["id"])) {
            $this->refreshRemoteHelp($product["id"]);
        }

        $commands = [];
        $notes = [];
        $remoteHelp = $this->detailValue("help_raw", "");
        if ($remoteHelp === "" && !empty($product) && $this->helpSource() !== "local") {
            $remoteHelp = $this->refreshRemoteHelp($product["id"]);
        }
        if (!empty($product)) {
            $commands = $this->buildCommandCards($product);
            $notes = $this->buildNotes($product);
        }

        $status = $this->detailValue("status", "Pending");

        return [
            "reference_key" => $this->detailValue("reference_key", ""),
            "license_ip" => $this->currentIp(false),
            "product_id" => !empty($product["id"]) ? $product["id"] : $this->detailValue("product_id", ""),
            "product_name" => !empty($product["display_name"]) ? $product["display_name"] : $this->detailValue("product_name", ""),
            "product_api_name" => !empty($product["api_type"]) ? $product["api_type"] : "",
            "status" => $status,
            "status_class" => $this->statusClass($status),
            "billing_cycle_months" => $this->detailValue("billing_cycle_months", $this->resolveBillingCycleMonths()),
            "change_ip_count" => (int) $this->detailValue("change_ip_count", 0),
            "max_ip_changes" => (int) $this->resourceOrDefault("max_ip_changes", 3),
            "command_source" => $this->detailValue("command_source", $this->helpSource()),
            "last_message" => $this->detailValue("message", ""),
            "last_action" => $this->detailValue("last_action", ""),
            "last_remote_action" => $this->detailValue("last_remote_action", ""),
            "commands" => $commands,
            "notes" => $notes,
            "remote_help" => $remoteHelp,
            "remote_help_available" => $remoteHelp !== "",
            "can_change_ip" => !empty($product["supports_change_ip"]),
            "can_renew" => true,
            "remove_command" => self::REMOVE_COMMAND,
        ];
    }

    public function loadPackageProducts()
    {
        $ret = [];
        foreach ($this->products() as $id => $product) {
            $ret[] = [(string) $id, $product["name"]];
        }
        return $ret;
    }

    protected function changeRemoteStatus($actionLabel, $status, $remoteAction)
    {
        try {
            $product = $this->selectedProduct();
            $ip = $this->currentIp(true);
            if ($remoteAction === "UnBan") {
                $response = $this->api()->unsuspendLicense($product["api_type"], $ip, $this->useExtendedSuspend());
            } else {
                $response = $this->api()->suspendLicense($product["api_type"], $ip, $this->useExtendedSuspend());
            }

            $this->details["status"]["value"] = $status;
            $this->details["message"]["value"] = $response["message"];
            $this->details["last_action"]["value"] = $actionLabel;
            $this->details["last_remote_action"]["value"] = $remoteAction;
            $this->persistDetails();
            return true;
        } catch (Exception $ex) {
            $this->details["message"]["value"] = $ex->getMessage();
            $this->persistDetails();
            $this->addError($ex->getMessage());
        }
        return false;
    }

    protected function selectedProduct()
    {
        $productId = $this->detailValue("product_id", "");
        if ($productId === "") {
            $productId = (string) $this->resourceOrDefault("product", "");
        }

        $products = $this->products();
        if ($productId === "" || !isset($products[$productId])) {
            throw new RuntimeException("Selected product does not exist in products.json");
        }

        return $products[$productId];
    }

    protected function products()
    {
        if (is_array($this->products)) {
            return $this->products;
        }

        $file = __DIR__ . DS . "products.json";
        $raw = file_exists($file) ? file_get_contents($file) : "";
        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("Unable to parse products.json");
        }

        $products = [];
        foreach ($decoded as $id => $row) {
            if (!is_array($row)) {
                $row = ["name" => (string) $row];
            }

            $row["id"] = isset($row["id"]) ? (string) $row["id"] : (string) $id;
            $row["name"] = isset($row["name"]) ? (string) $row["name"] : (string) $row["id"];
            $row["display_name"] = !empty($row["display_name"]) ? (string) $row["display_name"] : $row["name"];
            $row["api_type"] = !empty($row["api_type"]) ? (string) $row["api_type"] : $row["name"];
            $row["installer_arg"] = !empty($row["installer_arg"]) ? (string) $row["installer_arg"] : $row["display_name"];
            $row["command_alias"] = !empty($row["command_alias"]) ? (string) $row["command_alias"] : "";
            $row["supports_change_ip"] = array_key_exists("supports_change_ip", $row) ? (bool) $row["supports_change_ip"] : true;
            $row["notes"] = !empty($row["notes"]) && is_array($row["notes"]) ? $row["notes"] : [];
            $row["extra_commands"] = !empty($row["extra_commands"]) && is_array($row["extra_commands"]) ? $row["extra_commands"] : [];

            $products[$row["id"]] = $row;
        }

        $this->products = $products;
        return $this->products;
    }

    protected function buildCommandCards(array $product)
    {
        $cards = [];
        $updateCommand = $this->replaceCommandTokens(!empty($product["update_command"]) ? $product["update_command"] : "{{prefix}}" . $product["command_alias"], $product);
        $installCommand = $this->replaceCommandTokens(!empty($product["install_command"]) ? $product["install_command"] : "bash <( curl {{installer_url}} ) {{installer_arg}}; {{update_command}}", $product);

        $cards[] = ["title" => "Install " . $product["display_name"], "command" => $installCommand];
        $cards[] = ["title" => "Update " . $product["display_name"], "command" => $updateCommand];
        $cards[] = ["title" => "Remove previous license", "command" => self::REMOVE_COMMAND];

        if (!empty($product["verify_command"])) {
            $cards[] = ["title" => "Verify " . $product["display_name"], "command" => $this->replaceCommandTokens($product["verify_command"], $product)];
        }

        foreach ($product["extra_commands"] as $command) {
            if (empty($command["title"]) || empty($command["command"])) {
                continue;
            }
            $cards[] = ["title" => $command["title"], "command" => $this->replaceCommandTokens($command["command"], $product)];
        }

        return $cards;
    }

    protected function buildNotes(array $product)
    {
        $notes = [
            "Run installer commands as root.",
            "If another license provider was installed earlier, remove it first before running the installer.",
            "Product commands are generated from products.json so you can override them without touching PHP code.",
        ];

        foreach ($product["notes"] as $note) {
            $notes[] = $note;
        }

        return array_values(array_unique(array_filter($notes)));
    }

    protected function replaceCommandTokens($template, array $product)
    {
        $updateCommand = "{{prefix}}" . $product["command_alias"];
        $replacements = [
            "{{installer_url}}" => $this->installerUrl(),
            "{{installer_arg}}" => $product["installer_arg"],
            "{{prefix}}" => $this->commandPrefix(),
            "{{update_command}}" => str_replace("{{prefix}}", $this->commandPrefix(), $updateCommand),
            "{{display_name}}" => $product["display_name"],
        ];

        return trim(strtr((string) $template, $replacements));
    }

    protected function resolveBillingCycleMonths()
    {
        $map = [
            "Monthly" => 1,
            "Quarterly" => 3,
            "Semi-Annually" => 6,
            "Semi-Annual" => 6,
            "Semiannually" => 6,
            "Annually" => 12,
            "Yearly" => 12,
            "Biennially" => 24,
            "Triennially" => 36,
        ];

        $candidates = [
            isset($this->account_details["billing_cycle"]) ? $this->account_details["billing_cycle"] : null,
            isset($this->account_details["billingcycle"]) ? $this->account_details["billingcycle"] : null,
            isset($this->account_details["cycle"]) ? $this->account_details["cycle"] : null,
            $this->detailValue("billing_cycle_months", null),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === null || $candidate === "") {
                continue;
            }
            if (is_numeric($candidate)) {
                return max(1, (int) $candidate);
            }
            $candidate = trim((string) $candidate);
            if (isset($map[$candidate])) {
                return $map[$candidate];
            }
        }

        return 1;
    }

    protected function currentIp($strict = false)
    {
        $ip = "";
        if (!empty($this->account_config["ip"]["value"])) {
            $ip = $this->account_config["ip"]["value"];
        } else if (!empty($this->details["license_ip"]["value"])) {
            $ip = $this->details["license_ip"]["value"];
        } else if (!empty($this->details["ip"]["value"])) {
            $ip = $this->details["ip"]["value"];
        }

        $ip = trim((string) $ip);
        if ($strict && !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new RuntimeException("Licensed IP is empty or invalid");
        }
        return $ip;
    }

    protected function resolveChangeIpOldIp($oldIp = null)
    {
        $candidates = [
            $oldIp,
            !empty($this->account_config["ip"]["value"]) ? $this->account_config["ip"]["value"] : null,
            !empty($this->details["license_ip"]["value"]) ? $this->details["license_ip"]["value"] : null,
            !empty($this->details["ip"]["value"]) ? $this->details["ip"]["value"] : null,
            !empty($this->account_details["domain"]) && filter_var($this->account_details["domain"], FILTER_VALIDATE_IP) ? $this->account_details["domain"] : null,
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== "") {
                return $candidate;
            }
        }

        return "";
    }

    protected function resolveChangeIpNewIp($newIp = null)
    {
        $candidates = [
            $newIp,
            !empty($this->account_config["new_ip"]["value"]) ? $this->account_config["new_ip"]["value"] : null,
            $this->resourceOrDefault("new_ip", ""),
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== "") {
                return $candidate;
            }
        }

        return "";
    }

    protected function persistCurrentIp($ip)
    {
        $ip = trim((string) $ip);
        if ($ip === "") {
            return;
        }

        $this->details["license_ip"]["value"] = $ip;
        if (!empty($this->details["ip"])) {
            $this->details["ip"]["value"] = $ip;
        }

        if (!empty($this->account_config["ip"]) && $this->updateAccountConfig("ip", $ip)) {
            $this->saveAccountConfig();
        }
    }

    protected function persistDetails()
    {
        if (empty($this->account_details["id"])) {
            return;
        }

        try {
            $accounts = HBLoader::LoadModel("Accounts");
            $accounts->updateExtraDetails($this->account_details["id"], $this->details);
        } catch (Exception $ignored) {
        }
    }

    protected function refreshRemoteHelp($productId)
    {
        $source = $this->helpSource();
        if ($source === "local") {
            $this->details["help_raw"]["value"] = "";
            return "";
        }

        try {
            $response = $this->api()->showHelp($productId);
            $help = $this->normalizeHelpText($response);
            $this->details["help_raw"]["value"] = $help;
            return $help;
        } catch (Exception $ignored) {
            if ($source === "remote") {
                $this->details["help_raw"]["value"] = "";
            }
        }
        return $this->detailValue("help_raw", "");
    }

    protected function normalizeHelpText(array $response)
    {
        $raw = isset($response["raw"]) ? trim((string) $response["raw"]) : "";
        if ($raw === "") {
            return "";
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded) && isset($decoded["message"])) {
                $raw = (string) $decoded["message"];
            } else if (is_array($decoded) && isset($decoded["commands"]) && is_array($decoded["commands"])) {
                $raw = implode(PHP_EOL, $decoded["commands"]);
            } else if (is_array($decoded)) {
                $raw = print_r($decoded, true);
            }
        }

        $raw = html_entity_decode($raw, ENT_QUOTES, "UTF-8");
        $raw = str_replace(["\\r\\n", "\\n", "\\r", "\\/"], [PHP_EOL, PHP_EOL, "", "/"], $raw);
        return trim($raw);
    }

    protected function helpSource()
    {
        $value = strtolower((string) $this->resourceOrDefault("help_source", "hybrid"));
        return in_array($value, ["hybrid", "local", "remote"], true) ? $value : "hybrid";
    }

    protected function installerUrl()
    {
        return trim((string) $this->serverValue(["field1", "input1", "installer_url"], self::DEFAULT_INSTALLER_URL));
    }

    protected function commandPrefix()
    {
        return trim((string) $this->serverValue(["field2", "input2", "command_prefix"], self::DEFAULT_COMMAND_PREFIX));
    }

    protected function useExtendedSuspend()
    {
        $value = $this->serverValue(["secure", "checkbox", "use_extended_suspend"], false);
        if (is_bool($value)) {
            return $value;
        }
        return in_array(strtolower(trim((string) $value)), ["1", "true", "yes", "on"], true);
    }

    protected function generateReferenceKey()
    {
        $prefix = (string) $this->resourceOrDefault("license_key_prefix", "LC-");
        try {
            $suffix = strtoupper(bin2hex(random_bytes(5)));
        } catch (Exception $ignored) {
            $suffix = strtoupper(substr(md5(uniqid("", true)), 0, 10));
        }
        return $prefix . $suffix;
    }

    protected function serverValue($keys, $default = "")
    {
        foreach ((array) $keys as $key) {
            if (isset($this->connect_data[$key]) && $this->connect_data[$key] !== "") {
                return $this->connect_data[$key];
            }
        }
        return $default;
    }

    protected function resourceOrDefault($name, $default = "")
    {
        try {
            $value = $this->resource($name);
            return $value === "" || $value === null ? $default : $value;
        } catch (Exception $ignored) {
        }
        return $default;
    }

    protected function detailValue($name, $default = "")
    {
        if (isset($this->details[$name]) && isset($this->details[$name]["value"]) && $this->details[$name]["value"] !== "") {
            return $this->details[$name]["value"];
        }
        return $default;
    }

    protected function statusClass($status)
    {
        switch (strtolower((string) $status)) {
            case "active":
                return "success";
            case "suspended":
                return "warning";
            case "expired":
                return "danger";
            default:
                return "default";
        }
    }

    protected function registerClientWidgets()
    {
        if (!$this->getModuleId()) {
            return;
        }

        foreach ($this->clientWidgets() as $widget => $definition) {
            try {
                $widgetId = $this->upsertWidgetConfig($widget, $definition);
                if ($widgetId) {
                    $this->assignWidgetToProducts($widgetId);
                }
            } catch (Exception $ignored) {
            }
        }
    }

    protected function clientWidgets()
    {
        return [
            "lcv5_licensedetails" => [
                "name" => "License Details",
                "group" => "apps",
                "options" => self::WIDGET_OPTIONS_DEFAULT,
            ],
            "lcv5_changeip" => [
                "name" => "Change IP",
                "group" => "apps",
                "options" => self::WIDGET_OPTIONS_ACTION,
            ],
            "lcv5_licensedocs" => [
                "name" => "License Docs",
                "group" => "apps",
                "options" => self::WIDGET_OPTIONS_DEFAULT,
            ],
        ];
    }

    protected function upsertWidgetConfig($widget, array $definition)
    {
        $name = !empty($definition["name"]) ? $definition["name"] : $widget;
        $group = !empty($definition["group"]) ? $definition["group"] : "apps";
        $options = isset($definition["options"]) ? (int) $definition["options"] : self::WIDGET_OPTIONS_DEFAULT;
        $location = $this->widgetLocation($widget);
        $config = serialize([]);

        $query = $this->db->prepare("SELECT id FROM hb_widgets_config WHERE widget = ? LIMIT 1");
        $query->execute([$widget]);
        $widgetId = (int) $query->fetchColumn();
        $query->closeCursor();

        if ($widgetId) {
            $update = $this->db->prepare("UPDATE hb_widgets_config SET name = ?, location = ?, config = ?, options = ?, `group` = ? WHERE id = ?");
            $update->execute([$name, $location, $config, $options, $group, $widgetId]);
            return $widgetId;
        }

        $insert = $this->db->prepare("INSERT INTO hb_widgets_config (`widget`, `name`, `location`, `config`, `options`, `group`) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$widget, $name, $location, $config, $options, $group]);
        return (int) $this->db->lastInsertId();
    }

    protected function assignWidgetToProducts($widgetId)
    {
        $insert = $this->db->prepare("INSERT INTO hb_widgets (`target_type`, `target_id`, `widget_id`, `name`, `config`, `group`)
            SELECT 'Product', pm.product_id, ?, '', '', ''
            FROM hb_products_modules pm
            WHERE pm.module = ?
            AND NOT EXISTS (
                SELECT 1 FROM hb_widgets hw
                WHERE hw.target_type = 'Product'
                AND hw.target_id = pm.product_id
                AND hw.widget_id = ?
            )");
        $insert->execute([$widgetId, $this->getModuleId(), $widgetId]);
    }

    protected function widgetLocation($widget)
    {
        $base = defined("APPDIR_MODULES") ? APPDIR_MODULES : MAINDIR . "includes" . DS . "modules" . DS;
        $base = rtrim((string) $base, "\\/");
        if (stripos($base, MAINDIR) === 0) {
            $base = substr($base, strlen(MAINDIR));
        }

        return $base . DS . "Hosting" . DS . strtolower(get_class($this)) . DS . "widgets" . DS . $widget . DS;
    }
}

?>
