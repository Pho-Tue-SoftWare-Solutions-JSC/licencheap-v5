<?php

namespace Hosting\LicenseCheapV5;

class Error extends \Exception
{
}

class Api
{
    protected $token = "";
    protected $baseUrl = "https://api.resellercenter.ir/rc/";
    protected $timeout = 20;
    protected $userAgent = "LicenseCheapV5 HostBill Module/1.0";

    public function __construct($token, $baseUrl = "", $timeout = 20)
    {
        $this->token = trim((string) $token);
        if ($baseUrl) {
            $this->baseUrl = rtrim(trim((string) $baseUrl), "/") . "/";
        }
        $this->timeout = max(5, (int) $timeout);
    }

    public function bundleList()
    {
        $response = $this->request("bundle.php", [], true);
        if (!$response["ok"]) {
            throw new Error($response["message"], 1);
        }
        if (!is_array($response["data"])) {
            throw new Error("Unexpected bundle list response from remote API", 1);
        }
        return $response["data"];
    }

    public function createLicense($product, $ip, $billingCycle)
    {
        return $this->callAction("license", ["Product" => $product, "Ip" => $ip, "billingcycle" => $billingCycle]);
    }

    public function renewLicense($product, $ip, $billingCycle)
    {
        return $this->callAction("ReNew", ["Product" => $product, "Ip" => $ip, "billingcycle" => $billingCycle]);
    }

    public function suspendLicense($product, $ip, $extended = false)
    {
        return $this->callAction($extended ? "Ban2" : "Ban", ["Product" => $product, "Ip" => $ip]);
    }

    public function unsuspendLicense($product, $ip, $extended = false)
    {
        return $this->callAction($extended ? "UnBan2" : "UnBan", ["Product" => $product, "Ip" => $ip]);
    }

    public function changeIp($product, $ip, $newIp)
    {
        return $this->callAction("ChangeIp", ["Product" => $product, "Ip" => $ip, "Replace" => $newIp]);
    }

    public function showHelp($productId)
    {
        return $this->request("", ["action" => "ShowHelp", "serviceid" => $productId], false);
    }

    protected function callAction($action, array $params)
    {
        $response = $this->request("", array_merge(["action" => $action], $params), true);
        if (!$response["ok"]) {
            throw new Error($response["message"], 1);
        }
        return $response;
    }

    protected function request($path, array $params = [], $expectStructured = true)
    {
        if ($this->token === "") {
            throw new Error("API token is empty", 1);
        }

        $payload = array_merge(["token" => $this->token], $params);
        $curl = curl_init();
        $url = $this->buildUrl($path);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded", "Connection: close"],
            CURLOPT_USERAGENT => $this->userAgent,
        ]);

        $raw = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        \HBDebug::debug("HB >> LicenseCheapV5", ["url" => $url, "payload" => $payload]);

        if ($raw === false || $errno) {
            throw new Error($error ? $error : "Unknown cURL error", $errno ? $errno : 1);
        }

        \HBDebug::debug("HB << LicenseCheapV5", ["url" => $url, "http_code" => $httpCode, "response" => $raw]);

        return $this->parseResponse($raw, $httpCode, $expectStructured);
    }

    protected function parseResponse($raw, $httpCode, $expectStructured)
    {
        $trimmed = trim((string) $raw);

        if ($trimmed === "") {
            return [
                "ok" => false,
                "message" => "Empty response returned by remote API",
                "data" => [],
                "raw" => $raw,
                "http_code" => $httpCode,
            ];
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded) && array_key_exists("ok", $decoded)) {
                return [
                    "ok" => $this->toBool($decoded["ok"]),
                    "message" => isset($decoded["message"]) ? trim((string) $decoded["message"]) : "",
                    "data" => $decoded,
                    "raw" => $raw,
                    "http_code" => $httpCode,
                ];
            }

            return [
                "ok" => true,
                "message" => "OK",
                "data" => $decoded,
                "raw" => $raw,
                "http_code" => $httpCode,
            ];
        }

        if (!$expectStructured) {
            return [
                "ok" => true,
                "message" => $trimmed,
                "data" => $trimmed,
                "raw" => $raw,
                "http_code" => $httpCode,
            ];
        }

        if (stripos($trimmed, "<html") !== false) {
            return [
                "ok" => false,
                "message" => $this->extractText($trimmed),
                "data" => [],
                "raw" => $raw,
                "http_code" => $httpCode,
            ];
        }

        return [
            "ok" => false,
            "message" => $trimmed,
            "data" => [],
            "raw" => $raw,
            "http_code" => $httpCode,
        ];
    }

    protected function buildUrl($path)
    {
        if ($path === "") {
            return $this->baseUrl;
        }
        return $this->baseUrl . ltrim($path, "/");
    }

    protected function toBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));
        return in_array($value, ["1", "true", "yes", "ok"], true);
    }

    protected function extractText($html)
    {
        $text = trim(preg_replace("/\\s+/", " ", strip_tags((string) $html)));
        return $text ? $text : "Unexpected HTML response returned by remote API";
    }
}

?>
