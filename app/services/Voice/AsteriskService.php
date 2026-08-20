<?php

namespace App\Services\Voice;

use App\Traits\ServiceTenantTrait;

/**
 * Asterisk AMI Service — SIM Card Based Outbound Calling
 *
 * Connects to Asterisk PBX via AMI (Asterisk Manager Interface) to make
 * outbound calls using a physical SIM card through a GSM Gateway.
 *
 * Hardware: GoIP-1/4/8 GSM Gateway (or any SIP-GSM gateway)
 * Config: Asterisk + Chan_SIP/PJSIP + GSM Gateway registration
 *
 * Flow:
 * 1. PHP → Asterisk AMI → originate call to customer number
 * 2. Asterisk routes call through GSM Gateway → SIM card → cellular network
 * 3. When answered, Asterisk plays IVR (TTS) or connects to AI agent
 * 4. Call events streamed back via AMI events
 */
class AsteriskService
{
    use ServiceTenantTrait;

    private $host;
    private $port;
    private $username;
    private $secret;
    private $context;       // Asterisk dialplan context
    private $trunk;         // SIP trunk name for GSM gateway
    private $callerId;      // Outbound caller ID (SIM number)
    private $socket = null;
    private $connected = false;

    public function __construct()
    {
        $config = $this->loadConfig();
        $this->host = $config['host'] ?? '127.0.0.1';
        $this->port = $config['port'] ?? 5038;
        $this->username = $config['username'] ?? 'admin';
        $this->secret = $config['secret'] ?? 'password';
        $this->context = $config['context'] ?? 'outbound-calls';
        $this->trunk = $config['trunk'] ?? 'gsm-gateway';
        $this->callerId = $config['caller_id'] ?? '';
    }

    private function loadConfig(): array
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $row = $db->query("SELECT settings_value FROM system_settings WHERE settings_key = 'asterisk_config' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            return $row ? json_decode($row['settings_value'], true) ?: [] : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Save Asterisk config to database
     */
    public function saveConfig(array $config): bool
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $json = json_encode($config);
            $existing = $db->query("SELECT settings_key FROM system_settings WHERE settings_key = 'asterisk_config'")->fetch();
            if ($existing) {
                $db->execute("UPDATE system_settings SET settings_value = ? WHERE settings_key = 'asterisk_config'", [$json]);
            } else {
                $db->execute("INSERT INTO system_settings (settings_key, settings_value, created_at) VALUES ('asterisk_config', ?, NOW())", [$json]);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Asterisk config save error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Connect to Asterisk AMI
     */
    private function connect(): bool
    {
        if ($this->connected && $this->socket) return true;

        $errno = 0;
        $errstr = '';
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, 5);

        if (!$this->socket) {
            error_log("Asterisk AMI connection failed: {$errstr} ({$errno})");
            return false;
        }

        // Read connection banner
        $banner = fgets($this->socket, 1024);

        // Login
        $this->sendCommand("Action: Login\r\nUsername: {$this->username}\r\nSecret: {$this->secret}\r\nEvents: Off\r\n\r\n");
        $response = $this->readResponse();

        if (strpos($response, 'Success') !== false) {
            $this->connected = true;
            return true;
        }

        error_log("Asterisk AMI login failed: " . trim($response));
        fclose($this->socket);
        $this->socket = null;
        return false;
    }

    /**
     * Send AMI command
     */
    private function sendCommand(string $command): void
    {
        if ($this->socket) {
            fwrite($this->socket, $command);
        }
    }

    /**
     * Read AMI response (non-blocking, reads until double newline)
     */
    private function readResponse(int $timeout = 5): string
    {
        if (!$this->socket) return '';

        stream_set_timeout($this->socket, $timeout);
        $response = '';
        $startTime = time();

        while ((time() - $startTime) < $timeout) {
            $line = fgets($this->socket, 4096);
            if ($line === false || $line === '') break;
            $response .= $line;
            if ($line === "\r\n") break;
        }

        return $response;
    }

    /**
     * Read AMI events (for call status monitoring)
     */
    public function readEvents(int $timeout = 30): array
    {
        if (!$this->socket) return [];

        stream_set_timeout($this->socket, $timeout);
        $events = [];
        $currentEvent = [];
        $startTime = time();

        while ((time() - $startTime) < $timeout) {
            $line = fgets($this->socket, 4096);
            if ($line === false) break;

            if (trim($line) === '' && !empty($currentEvent)) {
                $events[] = $currentEvent;
                $currentEvent = [];
                continue;
            }

            if (preg_match('/^(\w[\w\s]*?):\s*(.*)$/', trim($line), $m)) {
                $currentEvent[$m[1]] = $m[2];
            }
        }

        return $events;
    }

    /**
     * Make an outbound call via SIM card
     *
     * @param string $phoneNumber  Customer phone number (with country code, e.g. 919277121112)
     * @param string $agentScript  AGI script or playback file to use after answer
     * @param array  $options      Extra options: caller_id, timeout, context, variables
     * @return array               ['success' => bool, 'call_id' => string, 'message' => string]
     */
    public function makeCall(string $phoneNumber, string $agentScript = 'default', array $options = []): array
    {
        if (!$this->connect()) {
            return [
                'success' => false,
                'call_id' => '',
                'message' => 'Cannot connect to Asterisk AMI. Check server status.',
            ];
        }

        $callerId = $options['caller_id'] ?: $this->callerId;
        $context = $options['context'] ?: $this->context;
        $timeout = $options['timeout'] ?? 30;
        $trunk = $options['trunk'] ?: $this->trunk;
        $callId = 'AST-' . date('YmdHis') . '-' . strtoupper(substr(md5($phoneNumber . microtime()), 0, 6));

        // Sanitize phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (strlen($cleanPhone) == 10) {
            $cleanPhone = '91' . $cleanPhone; // Add India country code
        }

        // Originate call via AMI
        $actionId = uniqid('act_', true);

        // For PJSIP trunk
        $dialString = "PJSIP/{$cleanPhone}@{$trunk}";

        // Variables to pass to dialplan
        $vars = [
            'CALLID' => $callId,
            'AGENT_SCRIPT' => $agentScript,
            'CUSTOMER_PHONE' => $cleanPhone,
        ];
        $extraVars = $options['variables'] ?? [];
        $vars = array_merge($vars, $extraVars);
        $varString = implode(',', array_map(fn($k, $v) => "{$k}={$v}", array_keys($vars), $vars));

        $command = "Action: Originate\r\n"
            . "ActionID: {$actionId}\r\n"
            . "Channel: {$dialString}\r\n"
            . "Context: {$context}\r\n"
            . "Exten: s\r\n"
            . "Priority: 1\r\n"
            . "CallerID: {$callerId}\r\n"
            . "Timeout: {$timeout}000\r\n"
            . "Variable: {$varString}\r\n"
            . "Async: true\r\n"
            . "\r\n";

        $this->sendCommand($command);
        $response = $this->readResponse();

        $success = strpos($response, 'Success') !== false || strpos($response, 'Response: Accepted') !== false;

        return [
            'success' => $success,
            'call_id' => $callId,
            'action_id' => $actionId,
            'message' => $success ? "Call initiated to {$cleanPhone}" : "Failed: " . trim($response),
            'response' => $response,
        ];
    }

    /**
     * Hangup a call
     */
    public function hangupCall(string $channel): bool
    {
        if (!$this->connect()) return false;

        $this->sendCommand("Action: Hangup\r\nChannel: {$channel}\r\n\r\n");
        $response = $this->readResponse();
        return strpos($response, 'Success') !== false;
    }

    /**
     * Get status of active channels
     */
    public function getActiveChannels(): array
    {
        if (!$this->connect()) return [];

        $this->sendCommand("Action: Channels\r\n\r\n");
        $response = $this->readResponse(10);

        $channels = [];
        $current = [];
        $lines = explode("\r\n", $response);

        foreach ($lines as $line) {
            if (trim($line) === '' && !empty($current)) {
                $channels[] = $current;
                $current = [];
                continue;
            }
            if (preg_match('/^(\w[\w\s]*?):\s*(.*)$/', trim($line), $m)) {
                $current[$m[1]] = $m[2];
            }
        }

        return $channels;
    }

    /**
     * Check if Asterisk is reachable
     */
    public function ping(): bool
    {
        $errno = 0;
        $errstr = '';
        $sock = @fsockopen($this->host, $this->port, $errno, $errstr, 3);
        if ($sock) {
            fclose($sock);
            return true;
        }
        return false;
    }

    /**
     * Disconnect from AMI
     */
    public function disconnect(): void
    {
        if ($this->socket) {
            $this->sendCommand("Action: Logoff\r\n\r\n");
            fclose($this->socket);
            $this->socket = null;
            $this->connected = false;
        }
    }

    /**
     * Get SIP peer status (GSM gateway registration status)
     */
    public function getSIPPeers(): array
    {
        if (!$this->connect()) return [];

        $this->sendCommand("Action: SIPPeers\r\n\r\n");
        $response = $this->readResponse(5);

        $peers = [];
        $current = [];
        $lines = explode("\r\n", $response);

        foreach ($lines as $line) {
            if (trim($line) === '' && !empty($current)) {
                $peers[] = $current;
                $current = [];
                continue;
            }
            if (preg_match('/^(\w[\w\s]*?):\s*(.*)$/', trim($line), $m)) {
                $current[$m[1]] = $m[2];
            }
        }

        return $peers;
    }

    /**
     * Generate dialplan extensions.conf snippet for outbound calls
     */
    public function generateDialplan(): string
    {
        return <<<DIALPLAN
; === APS Dream Homes — Asterisk Dialplan for GSM Outbound Calls ===
; Place this in /etc/asterisk/extensions.conf

[outbound-calls]
; Context for outbound calls via GSM Gateway
exten => s,1,NoOp(Starting outbound call - APS Dream Homes)
 same => n,Set(CALLERID(num)={$this->callerId})
 same => n,Set(CALLID=\${CALLID})
 same => n,Set(CUSTOMER=\${CUSTOMER_PHONE})
 same => n,Answer()
 same => n,Wait(1)
 same => n,Goto(ivr-menu,s,1)

[ivr-menu]
; IVR Menu — greets customer in Hindi, then connects to AI agent
exten => s,1,NoOp(IVR Started for \${CUSTOMER})
 same => n,Background(/var/lib/asterisk/sounds/aps/hello_hi)
 same => n,WaitExten(5)
 same => n,Goto(ai-agent,s,1)

exten => 1,1,Goto(ai-agent,s,1)
exten => 2,1,Goto(book-plot,s,1)
exten => 3,1,Hangup()

[ai-agent]
; Connect to AI agent (via AGI or external service)
exten => s,1,NoOp(Connecting to AI Agent)
 same => n,AGI(/var/lib/asterisk/agi/aps_ai_agent.php,\${CALLID},\${CUSTOMER})
 same => n,Hangup()

[book-plot]
; Quick booking via IVR
exten => s,1,NoOp(Quick booking for \${CUSTOMER})
 same => n,Playback(/var/lib/asterisk/sounds/aps/booking_info)
 same => n,Hangup()
DIALPLAN;
    }
}
