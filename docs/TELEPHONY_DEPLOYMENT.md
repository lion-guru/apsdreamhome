# APS Dream Home - Telephony Stack Deployment Guide

## Hardware Required

| Item                                | Cost       | Where to Buy    |
| ----------------------------------- | ---------- | --------------- |
| Huawei USB Modem (E173/E1550/E3372) | ₹300-500   | Amazon/Flipkart |
| Prepaid SIM (Jio/Airtel/BSNL)       | ₹300/month | Local shop      |
| USB extension cable (1m)            | ₹50-100    | Local shop      |

**Total one-time: ₹400-650 | Monthly: ₹300**

## Software Stack (All Free)

| Component   | Purpose                    | License    |
| ----------- | -------------------------- | ---------- |
| Asterisk    | PBX / Call routing         | GPL        |
| chan_dongle | SIM card ↔ Asterisk bridge | GPL        |
| Ollama      | Local LLM (llama3.2:3b)    | MIT        |
| Whisper     | Speech-to-Text             | MIT        |
| Google TTS  | Text-to-Speech             | Free API   |
| Docker      | Containerization           | Apache 2.0 |

## Deployment Steps

### 1. Server Setup (Linux/Ubuntu)

```bash
# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Clone project
cd /var/www
git clone <repo> apsdreamhome
cd apsdreamhome

# Run setup script
bash scripts/setup_telephony.sh
```

### 2. Hardware Setup

1. Insert SIM card into Huawei USB modem
2. Connect modem to server via USB
3. If using USB extension cable, keep modem near window for signal

### 3. Verify Modem

```bash
# Check if modem is detected
ls /dev/ttyUSB*
# Should show: /dev/ttyUSB0 /dev/ttyUSB1 /dev/ttyUSB2

# Check Asterisk sees the modem
docker exec aps_asterisk dongle show devices
# Should show: ID      GROUP   STATE   PIN     IMEI    IMSI    Number

# If STATE is "Not registered", check SIM/APN
docker exec aps_asterisk dongle show stats
```

### 4. Pull AI Models

```bash
# Pull Ollama model (~2GB)
docker exec aps_ollama ollama pull llama3.2:3b

# Whisper model downloads automatically on first use
```

### 5. Generate Voice Prompts

```bash
# Generate Hindi voice prompts for IVR
cd /var/www/apsdreamhome
php cron/generate_voice_prompts.php

# Copy to Asterisk sounds directory
cp storage/voice_prompts/asterisk/*.gsm docker/asterisk/sounds/
```

### 6. Create Database Table

```bash
php scripts/create_whatsapp_followup_table.php
```

### 7. Configure PHP Backend

In admin panel → SIM Calling → Settings:

```
AMI Host: asterisk (Docker) or 127.0.0.1
AMI Port: 5038
AMI Username: admin
AMI Password: ApsDreamHome2026!
Caller ID: <your SIM number>
Context: outbound-calls
Trunk: gsm-gateway
```

### 8. Setup Cron Jobs

```bash
# Auto-dialer: every 5 minutes
*/5 * * * * php /var/www/apsdreamhome/cron/auto_dialer.php >> /var/www/apsdreamhome/cron/logs/cron.log 2>&1

# WhatsApp follow-ups: every 15 minutes
*/15 * * * * php /var/www/apsdreamhome/cron/process_whatsapp_followups.php >> /var/www/apsdreamhome/cron/logs/cron.log 2>&1

# Health check: every hour
0 * * * * php /var/www/apsdreamhome/cron/health_check_telephony.php >> /var/www/apsdreamhome/cron/logs/health.log 2>&1
```

## Architecture

```
┌─────────────┐     ┌──────────────┐     ┌──────────────┐
│ Admin Panel  │────▶│ PHP Backend  │────▶│ Asterisk AMI │
│ (Browser)    │     │ (Apache)     │     │ (Docker)     │
└─────────────┘     └──────────────┘     └──────┬───────┘
                                                │
┌─────────────┐     ┌──────────────┐     ┌──────▼───────┐
│ Cron Jobs    │────▶│ VoiceCall    │────▶│ chan_dongle  │
│ (auto_dialer)│     │ Service      │     │ (Asterisk    │
└─────────────┘     └──────┬───────┘     │  module)     │
                           │             └──────┬───────┘
                    ┌──────▼───────┐     ┌──────▼───────┐
                    │ AIVoice      │     │ Huawei USB   │
                    │ Pipeline     │     │ Modem + SIM  │
                    └──┬───┬───┬──┘     └──────┬───────┘
                       │   │   │               │
              ┌────────┘   │   └────────┐      │
              ▼            ▼            ▼      ▼
         ┌────────┐  ┌──────────┐  ┌────────┐  Cellular
         │Whisper │  │ Ollama   │  │Google  │  Network
         │ (STT)  │  │ (LLM)   │  │ TTS    │
         └────────┘  └──────────┘  └────────┘
```

## Monitoring

### Health Check

```bash
php cron/health_check_telephony.php
# Or: php cron/health_check_telephony.php?format=json
```

### Check Logs

```bash
# Auto-dialer logs
tail -f cron/logs/auto_dialer_$(date +%Y-%m-%d).log

# WhatsApp follow-up logs
tail -f cron/logs/whatsapp_followup_$(date +%Y-%m-%d).log

# Asterisk logs
docker logs aps_asterisk --tail 50 -f

# Ollama logs
docker logs aps_ollama --tail 50 -f
```

### Docker Status

```bash
docker ps
docker stats aps_asterisk aps_ollama aps_whisper
```

## Troubleshooting

| Issue                 | Solution                                                             |
| --------------------- | -------------------------------------------------------------------- |
| Modem not detected    | Check USB cable, try different port, `ls /dev/ttyUSB*`               |
| SIM not registered    | Check APN settings in dongle.conf, verify SIM has balance            |
| Ollama slow           | Use smaller model: `docker exec aps_ollama ollama pull qwen2.5:1.5b` |
| Whisper errors        | Check RAM: needs 2GB free. Reduce model: ASR_MODEL=tiny              |
| AMI connection failed | Check Docker network: `docker network inspect telephony_network`     |
| No audio              | Check Asterisk sound files in `/var/lib/asterisk/sounds/aps/`        |
| Calls not going out   | Check SIM balance, calling hours (9AM-8PM), TRAI DND                 |

## Cost Analysis

| Service                 | Alternative         | Monthly Cost    |
| ----------------------- | ------------------- | --------------- |
| Self-hosted (our setup) | —                   | ₹300 (SIM only) |
| Twilio                  | Cloud telephony     | ₹2,000-5,000    |
| Exotel                  | Cloud telephony     | ₹3,000-8,000    |
| Ai-Call.ai              | AI calling platform | ₹5,000-15,000   |

**Savings: ₹2,000-14,700/month**
