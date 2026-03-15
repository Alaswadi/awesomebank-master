<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Vulnerability: Insecure Deserialization (A08)
// The `prefs` field from the POST body is passed directly to unserialize().
// PHP's unserialize() can trigger __wakeup() / __destruct() magic methods on
// arbitrary classes already loaded in the process, enabling Remote Code Execution.
//
// Example exploit payload (PHP gadget chain):
//   O:8:"stdClass":1:{s:3:"cmd";s:2:"id";}
//
// With a real gadget class (e.g., from a library), attackers can achieve RCE.
// Even without RCE, object injection can manipulate application logic.

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Define a demo class whose __wakeup demonstrates the danger
class UserPreferences {
    public $theme       = 'light';
    public $language    = 'en';
    public $currency    = 'USD';
    public $notify      = true;
    // Dangerous: if an attacker controls this, __wakeup executes it
    public $debug_hook  = null;

    public function __wakeup() {
        // Simulated dangerous behavior triggered on deserialization
        if ($this->debug_hook) {
            // In a real vulnerable app this might be: eval($this->debug_hook)
            // or file_put_contents('/tmp/pwned', $this->debug_hook)
            error_log("[INSECURE DESERIALIZATION] debug_hook triggered: " . $this->debug_hook);
        }
    }

    public function toArray() {
        return [
            'theme'    => $this->theme,
            'language' => $this->language,
            'currency' => $this->currency,
            'notify'   => $this->notify,
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['prefs'])) {
        http_response_code(400);
        echo json_encode(["message" => "'prefs' field is required (PHP serialized string)"]);
        exit;
    }

    // VULNERABLE: direct unserialize() on user-controlled input
    $prefs = unserialize($input['prefs']);

    if ($prefs === false) {
        http_response_code(400);
        echo json_encode(["message" => "Failed to deserialize preferences. Ensure it is a valid PHP serialized string."]);
        exit;
    }

    // Return whatever was deserialized
    if ($prefs instanceof UserPreferences) {
        echo json_encode([
            "message"     => "Preferences saved",
            "preferences" => $prefs->toArray()
        ]);
    } else {
        // Return raw cast to show object injection worked
        echo json_encode([
            "message"     => "Preferences saved (raw object)",
            "preferences" => (array)$prefs
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Only POST requests are allowed"]);
}
?>
