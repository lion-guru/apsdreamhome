#!/bin/bash
# APS Dream Home - Telephony Stack Setup Script
# Run this on the deployment server (Linux with Docker)
# Usage: bash scripts/setup_telephony.sh

set -e

echo "=== APS Dream Home - Telephony Stack Setup ==="
echo "Date: $(date)"
echo ""

# ── Step 1: Check prerequisites ──
echo "[1/8] Checking prerequisites..."

if ! command -v docker &> /dev/null; then
    echo "Docker not found. Installing..."
    curl -fsSL https://get.docker.com | sh
    sudo usermod -aG docker $USER
    echo "Docker installed. Please log out and back in, then re-run this script."
    exit 1
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "Docker Compose not found. Installing..."
    sudo apt-get install -y docker-compose-plugin 2>/dev/null || \
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" \
        -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
fi

echo "Docker: $(docker --version)"
echo "Docker Compose: $(docker compose version 2>/dev/null || docker-compose --version 2>/dev/null)"

# ── Step 2: Check for Huawei USB modem ──
echo ""
echo "[2/8] Checking for USB modem..."

if ls /dev/ttyUSB* 1>/dev/null 2>&1; then
    echo "USB modem detected:"
    ls -la /dev/ttyUSB*
    
    # Detect modem model
    for dev in /dev/ttyUSB*; do
        if [ -w "$dev" ]; then
            echo "Modem $dev is writable"
        else
            echo "Setting permissions for $dev..."
            sudo chmod 666 "$dev"
        fi
    done
else
    echo "WARNING: No USB modem detected!"
    echo "Please connect a Huawei USB modem (E173/E1550/E3372) and re-run."
    echo "The stack will start without modem support (calls will fail)."
fi

# ── Step 3: Create Docker network ──
echo ""
echo "[3/8] Creating Docker networks..."

docker network create apsdreamhome_network 2>/dev/null || true
docker network create telephony_network 2>/dev/null || true

# ── Step 4: Pull Docker images ──
echo ""
echo "[4/8] Pulling Docker images..."

COMPOSE_CMD="docker compose"
if ! docker compose version &> /dev/null 2>&1; then
    COMPOSE_CMD="docker-compose"
fi

cd docker/asterisk
$COMPOSE_CMD -f docker-compose.telephony.yml pull 2>/dev/null || echo "Using build instead of pull..."

# ── Step 5: Build images ──
echo ""
echo "[5/8] Building Docker images..."

$COMPOSE_CMD -f docker-compose.telephony.yml build

# ── Step 6: Start services ──
echo ""
echo "[6/8] Starting telephony stack..."

$COMPOSE_CMD -f docker-compose.telephony.yml up -d

# ── Step 7: Wait for services ──
echo ""
echo "[7/8] Waiting for services to be ready..."

echo -n "Waiting for Ollama..."
for i in $(seq 1 30); do
    if curl -s http://localhost:11434/api/tags > /dev/null 2>&1; then
        echo " READY"
        break
    fi
    echo -n "."
    sleep 2
done

echo -n "Waiting for Whisper..."
for i in $(seq 1 30); do
    if curl -s http://localhost:8080/health > /dev/null 2>&1; then
        echo " READY"
        break
    fi
    echo -n "."
    sleep 2
done

echo -n "Waiting for Asterisk..."
for i in $(seq 1 30); do
    if docker exec aps_asterisk asterisk -rx "core show version" > /dev/null 2>&1; then
        echo " READY"
        break
    fi
    echo -n "."
    sleep 2
done

# ── Step 8: Pull Ollama model ──
echo ""
echo "[8/8] Pulling Ollama model (llama3.2:3b)..."

docker exec aps_ollama ollama pull llama3.2:3b 2>/dev/null || echo "Model pull may take a while. Run manually: docker exec aps_ollama ollama pull llama3.2:3b"

# ── Summary ──
echo ""
echo "=== Setup Complete ==="
echo ""
echo "Services running:"
echo "  - Asterisk AMI:      localhost:5038"
echo "  - Asterisk SIP:      localhost:5060/udp"
echo "  - Asterisk ARI:      localhost:8088"
echo "  - Ollama LLM:        localhost:11434"
echo "  - Whisper STT:       localhost:8080"
echo "  - Telephony API:     localhost:3080"
echo ""
echo "PHP Backend Config:"
echo "  AMI Host: asterisk (Docker) or localhost"
echo "  AMI Port: 5038"
echo "  AMI User: admin"
echo "  AMI Pass: ApsDreamHome2026!"
echo ""
echo "Next steps:"
echo "  1. Insert SIM card into Huawei USB modem"
echo "  2. Connect modem to server via USB"
echo "  3. Configure AMI credentials in admin panel"
echo "  4. Run: docker exec aps_asterisk dongle show devices"
echo "  5. Test: php cron/auto_dialer.php"
echo ""
echo "To setup cron job:"
echo "  */5 * * * * php /path/to/apsdreamhome/cron/auto_dialer.php"
echo ""
echo "To check status:"
echo "  docker ps"
echo "  docker logs aps_asterisk --tail 50"
echo "  docker logs aps_ollama --tail 50"
