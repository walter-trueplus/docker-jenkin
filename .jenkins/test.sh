#!/usr/bin/env sh

if [[ -z "${JENKINS_DATA}" ]]; then
    cd ../dockermftf
else
    cd $JENKINS_DATA/workspace/dockermftf
fi

set -x

# Run tests
docker-compose exec -u www-data -T mftf bash -c \
    "cd dev/tests/acceptance; \
    sed -i 's/enable-automation\"]/enable-automation\", \"--disable-print-preview\", \"--kiosk-printing\"]/g' tests/functional.suite.yml; \
    vendor/bin/robo generate:tests; \
    vendor/bin/codecept run -g pwapos"

TEST_RESULT=$?

docker-compose exec -u www-data -T mftf bash -c \
    "cp dev/tests/acceptance/tests/_output/allure-results/* /allure-results/"

bin/run down

set +x
docker-compose -f docker-compose.report.yml up -d
REPORT_URL=`echo $JENKINS_URL | awk 'gsub(/\:?[0-9]*\/?$/, "")'`
set -x
echo "View report at: $REPORT_URL:8030/"

# Send info (hide slack key)
set +x
if [ $TEST_RESULT -ne 0 ]; then
    curl -X POST -s --data-urlencode "payload={\"text\": \"[FAILURE] $STAGE_NAME <$RUN_DISPLAY_URL|$JOB_NAME $BUILD_DISPLAY_NAME>\"}" $SLACK_HOOKS
    exit $TEST_RESULT
fi
