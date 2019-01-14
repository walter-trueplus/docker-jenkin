#!/usr/bin/env sh

if [[ -z "${JENKINS_DATA}" ]]; then
    cd ../dockermftf
else
    cd $JENKINS_DATA/workspace/dockermftf
fi

set -x

# Run test code standard
docker-compose exec -u www-data -T mftf bash -c \
    " php vendor/composer/composer/bin/composer create-project \
        --repository=https://repo.magento.com \
        magento/marketplace-eqp \
        magento-coding-standard; \
    cd magento-coding-standard; \
    vendor/bin/phpcs ../app/code/Magestore --standard=MEQP2 --severity=10"
TEST_RESULT=$?

# Send info (hide slack key)
set +x
if [ $TEST_RESULT -ne 0 ]; then
    bin/run down
    curl -X POST -s --data-urlencode "payload={\"text\": \"[FAILURE] $STAGE_NAME <$RUN_DISPLAY_URL|$JOB_NAME $BUILD_DISPLAY_NAME>\"}" $SLACK_HOOKS
    exit $TEST_RESULT
fi
