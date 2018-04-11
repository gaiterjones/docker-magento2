FROM debian:stretch

ENV DEBIAN_FRONTEND noninteractive

# Set repositories
RUN \
  echo "deb http://ftp.de.debian.org/debian/ stretch main non-free contrib" > /etc/apt/sources.list && \
  echo "deb-src http://ftp.de.debian.org/debian/ stretch main non-free contrib" >> /etc/apt/sources.list && \
  echo "deb http://security.debian.org/ stretch/updates main contrib non-free" >> /etc/apt/sources.list && \
  echo "deb-src http://security.debian.org/ stretch/updates main contrib non-free" >> /etc/apt/sources.list && \
  apt-get -qq update && apt-get -qqy upgrade
# Update repositories cache and distribution

# Install some basic tools needed for deployment
RUN apt-get -yqq install --no-install-recommends \
  curl \
  python \
  gnupg2 \
  ca-certificates


# Configure Varnish-cache sources
RUN \
  curl -s https://packagecloud.io/install/repositories/varnishcache/varnish52/script.deb.sh | bash && \
  apt-get -qq update

# Install Varnish-cache
RUN apt-get -yqq install \
  varnish --no-install-recommends

# Cleanup
RUN apt-get -q autoclean && \
  rm -rf /var/lib/apt/lists/*

#
# Run
#

ENV VCL_CONFIG      /etc/varnish/default.vcl
ENV CACHE_SIZE      256M
ENV VARNISHD_PARAMS -p default_ttl=3600 -p default_grace=3600 -T 0.0.0.0:6082 -S /etc/varnish/secret

EXPOSE 80 6082

ADD ./start.sh /start.sh
RUN chmod +x /start.sh

CMD /start.sh
