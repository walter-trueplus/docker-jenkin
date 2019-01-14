#!/usr/bin/env sh


# Build test api phpunit_pwapos.xml
PWAPOS_API_TESTSUITE="PWAPOS_API"
PHPUNIT_PWAPOS_XML_FILE=phpunit_pwapos.xml

if [[ -z "${JENKINS_DATA}" ]]; then
    cd ../dockermftf
else
    cd $JENKINS_DATA/workspace/dockermftf
fi

set -x

# Run tests
docker-compose exec -u www-data -T mftf bash -c \
    "cd dev/tests/api-functional; \
    cp testsuite/Magento/Webpos/$PHPUNIT_PWAPOS_XML_FILE .; \
    php ../../../vendor/phpunit/phpunit/phpunit -c /var/www/html/dev/tests/api-functional/$PHPUNIT_PWAPOS_XML_FILE --testsuite $PWAPOS_API_TESTSUITE;
    "
TEST_RESULT=$?


# Send info (hide slack key)
set +x

if [ $TEST_RESULT -ne 0 ]; then
    curl -X POST -s --data-urlencode "payload={\"text\": \"[FAILURE] $STAGE_NAME <$RUN_DISPLAY_URL|$JOB_NAME $BUILD_DISPLAY_NAME>\"}" $SLACK_HOOKS
    exit $TEST_RESULT
fi
