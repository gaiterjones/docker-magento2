FROM elasticsearch:7.17.4
LABEL maintainer="GAITERJONES"
# Magento 2 required plugins
# https://github.com/elastic/elasticsearch-analysis-icu
# https://github.com/elastic/elasticsearch-analysis-phonetic
RUN \
    elasticsearch-plugin install analysis-icu && \
    elasticsearch-plugin install analysis-phonetic
