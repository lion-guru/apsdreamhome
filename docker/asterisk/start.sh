#!/bin/bash
# APS Dream Home - Asterisk Startup Script

echo "=== APS Dream Home - Asterisk Starting ==="
echo "Time: $(date)"
echo "Timezone: $(cat /etc/timezone 2>/dev/null || echo 'UTC')"

# Create necessary directories
mkdir -p /var/run/asterisk
mkdir -p /var/log/asterisk
mkdir -p /var/lib/asterisk/sounds/aps
mkdir -p /var/lib/asterisk/agi

# Set permissions
chown -R asterisk:asterisk /var/run/asterisk
chown -R asterisk:asterisk /var/log/asterisk
chown -R asterisk:asterisk /var/lib/asterisk

# Check for USB modem
echo "Checking for USB modem..."
if ls /dev/ttyUSB* 1>/dev/null 2>&1; then
    echo "USB modem detected:"
    ls -la /dev/ttyUSB*
    
    # Set permissions for asterisk user
    for dev in /dev/ttyUSB*; do
        chmod 666 "$dev"
        echo "Set permissions for $dev"
    done
else
    echo "WARNING: No USB modem detected. chan_dongle will not work."
    echo "Connect a Huawei USB modem and restart the container."
fi

# Check for sound devices
echo "Checking sound devices..."
if ls /dev/sound/ 1>/dev/null 2>&1; then
    echo "Sound devices available:"
    ls -la /dev/sound/
else
    echo "No sound devices. Audio will use virtual output."
fi

# Generate sample sounds if not present
if [ ! -f /var/lib/asterisk/sounds/aps/hello_hi.gsm ]; then
    echo "Generating sample IVR sounds..."
    
    # Create placeholder sounds (will be replaced with real TTS)
    for sound in hello_hi booking_info connecting_sales emi_reminder_hi pay_now_info; do
        if [ ! -f "/var/lib/asterisk/sounds/aps/${sound}.gsm" ]; then
            # Create a 1-second silent GSM file as placeholder
            sox -n -r 8000 -c 1 "/var/lib/asterisk/sounds/aps/${sound}.gsm" trim 0.0 1.0 2>/dev/null || \
            touch "/var/lib/asterisk/sounds/aps/${sound}.gsm"
        fi
    done
fi

# Create AGI script for AI agent
cat > /var/lib/asterisk/agi/aps_ai_agent.php << 'AGI_SCRIPT'
<?php
/**
 * AGI Script for AI Voice Agent
 * Called by Asterisk when a call connects to the AI agent
 */

// Read AGI arguments
$argc = $_SERVER['argc'] ?? 0;
$argv = $_SERVER['argv'] ?? [];

$callId = $argv[1] ?? 'unknown';
$customerPhone = $argv[2] ?? 'unknown';
$sessionId = $argv[3] ?? '0';

// Read AGI environment variables from stdin
$env = [];
while (!feof(STDIN)) {
    $line = trim(fgets(STDIN));
    if ($line === '') break;
    if (preg_match('/^agi_(\w+):\s*(.*)$/', $line, $m)) {
        $env[$m[1]] = $m[2];
    }
}

$channel = $env['channel'] ?? 'unknown';
$callerId = $env['callerid'] ?? 'unknown';

// Log the AGI call
error_log("APS AGI: CallID=$callId, Phone=$customerPhone, Session=$sessionId, Channel=$channel");

// TODO: In production, this would:
// 1. Connect to AIVoicePipeline service
// 2. Use Speech Recognition (Whisper) to transcribe customer audio
// 3. Send text to Ollama LLM for response
// 4. Use TTS to generate audio response
// 5. Play audio back to customer via Asterisk

// For now, play a greeting and hangup
exec("agi set variable GREETING 'Hello from APS Dream Home'");
exec("agi set variable AI_RESPONSE 'Thank you for calling. Our team will assist you shortly.'");

// End the call
exec("agi set variable HANGUPCAUSE '16'");
exit(0);
AGI_SCRIPT

chmod +x /var/lib/asterisk/agi/aps_ai_agent.php
chown asterisk:asterisk /var/lib/asterisk/agi/aps_ai_agent.php

# Test configuration
echo "Testing Asterisk configuration..."
asterisk -C /etc/asterisk/asterisk.conf -rx "core show version" 2>/dev/null || true

# Start Asterisk in foreground (for Docker)
echo "Starting Asterisk..."
exec asterisk -f -c -vvv -g -U asterisk -G asterisk -C /etc/asterisk/asterisk.conf
