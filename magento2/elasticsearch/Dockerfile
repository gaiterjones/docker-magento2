FROM docker.elastic.co/elasticsearch/elasticsearch:6.6.2

# Install ElasticSuite requirements (https://github.com/Smile-SA/elasticsuite/wiki/ModuleInstall)
RUN \
    elasticsearch-plugin install analysis-icu && \
    elasticsearch-plugin install analysis-phonetic
