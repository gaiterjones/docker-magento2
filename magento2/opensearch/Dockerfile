FROM opensearchproject/opensearch:2.12.0
LABEL maintainer="GAITERJONES"

# Install required plugins
RUN \
    /usr/share/opensearch/bin/opensearch-plugin install analysis-icu && \
    /usr/share/opensearch/bin/opensearch-plugin install analysis-phonetic

# Fix permissions for OpenSearch user
# on host use chown -R 1000:1000
RUN chown -R opensearch:opensearch /usr/share/opensearch/data
USER opensearch