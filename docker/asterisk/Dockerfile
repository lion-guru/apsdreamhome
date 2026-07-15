FROM debian:12-slim

LABEL maintainer="APS Dream Home <dev@apsdreamhome.com>"
LABEL description="Asterisk with chan_dongle for SIM card calling"

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Asia/Kolkata

# Install dependencies
RUN apt-get update && apt-get install -y \
    asterisk \
    asterisk-core-sounds-hi \
    asterisk-core-sounds-en \
    asterisk-modules \
    build-essential \
    git \
    cmake \
    libjansson-dev \
    libsqlite3-dev \
    libusb-1.0-0-dev \
    libglib2.0-dev \
    libudev-dev \
    libical-dev \
    libspandsp-dev \
    libspeex-dev \
    libspeexdsp-dev \
    libncurses5-dev \
    libssl-dev \
    libxml2-dev \
    libsqlite3-dev \
    uuid-dev \
    pkg-config \
    wget \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Build chan_dongle from source
RUN cd /tmp && \
    git clone https://github.com/olelmishchan/chan_dongle.git && \
    cd chan_dongle && \
    ./configure --with-asterisk=/usr && \
    make && \
    make install && \
    mkdir -p /etc/asterisk && \
    cp dongle.conf /etc/asterisk/ && \
    cd / && rm -rf /tmp/chan_dongle

# Create sounds directory for custom prompts
RUN mkdir -p /var/lib/asterisk/sounds/aps

# Copy custom configuration
COPY extensions.conf /etc/asterisk/extensions.conf
COPY dongle.conf /etc/asterisk/dongle.conf
COPY manager.conf /etc/asterisk/manager.conf
COPY pjsip.conf /etc/asterisk/pjsip.conf
COPY modules.conf /etc/asterisk/modules.conf

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Expose ports
# 5038 = AMI (Asterisk Manager Interface)
# 8088 = ARI (Asterisk REST Interface) 
# 5060 = SIP/PJSIP (for GSM gateway registration)
EXPOSE 5038 8088 5060/udp 5060/tcp

# Health check
HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD asterisk -rx "core show version" || exit 1

VOLUME ["/var/lib/asterisk/sounds/aps", "/var/log/asterisk"]

ENTRYPOINT ["/start.sh"]
