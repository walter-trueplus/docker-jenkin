#!/usr/bin/env sh

# Run tests
cd client/pos

npm install
npm run test -- --coverage
TEST_RESULT=$?

cd ../..
mkdir -p ../dockermftf/app/allure/allure-report/client
cp -Rf client/pos/coverage/lcov-report/* ../dockermftf/app/allure/allure-report/client/

# Show report
set +x
if [[ -z "${JENKINS_DATA}" ]]; then
    cd ../dockermftf
else
    cd $JENKINS_DATA/workspace/dockermftf
fi
docker-compose -f docker-compose.report.yml up -d
REPORT_URL=`echo $JENKINS_URL | awk 'gsub(/\:?[0-9]*\/?$/, "")'`
set -x
echo "View report at: $REPORT_URL:8030/client/"

# Send info (hide slack key)
set +x
if [ $TEST_RESULT -ne 0 ]; then
    curl -X POST -s --data-urlencode "payload={\"text\": \"[FAILURE] $STAGE_NAME <$RUN_DISPLAY_URL|$JOB_NAME $BUILD_DISPLAY_NAME>\"}" $SLACK_HOOKS
    exit $TEST_RESULT
fi
