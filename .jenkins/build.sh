#!/usr/bin/env sh

set -x

# Build Client App
cd client/pos
npm run build
if [ $? -eq 0 ]; then
    cd ../..
    mkdir -p ../dockermftf/app/code/Magestore/Webpos/build/apps
    rm -Rf ../dockermftf/app/code/Magestore/Webpos/build/apps/pos
    cp -Rf client/pos/build ../dockermftf/app/code/Magestore/Webpos/build/apps/pos
else
    set +x
    curl -X POST -s --data-urlencode "payload={\"text\": \"[FAILURE] $STAGE_NAME <$RUN_DISPLAY_URL|$JOB_NAME $BUILD_DISPLAY_NAME>\"}" $SLACK_HOOKS
    exit 1
fi

# Start services
set +x
if [[ -z "${JENKINS_DATA}" ]]; then
    cd ../dockermftf
else
    cd $JENKINS_DATA/workspace/dockermftf
fi
COMPOSE_HTTP_TIMEOUT=200 bin/run up -d

# Waiting for services is up
set -x
while ! RESPONSE=`docker-compose exec -T mftf curl -s https://localhost.com/magento_version`
do
    sleep 3
done

if [[ ${RESPONSE:0:8} != "Magento/" ]]; then
    COMPOSE_HTTP_TIMEOUT=200 docker-compose restart mftf
    while ! docker-compose exec -T mftf curl -s https://localhost.com/magento_version
    do
        sleep 3
    done
fi

# Install webpos payment library
set +x

chmod 777 app/.composer
docker-compose exec -u www-data -T mftf bash -c \
    " php bin/magento webpos:deploy; \
    if [ ! -f auth.json ]; then echo '{ \
        \"http-basic\": { \
            \"repo.magento.com\": { \
                \"username\": \"a3380186b4ffb670466a01331a3fb375\", \
                \"password\": \"cfe4874a50552827da901971d249322a\" \
            } \
        } \
    }' > auth.json \
    && php vendor/composer/composer/bin/composer require \
        authorizenet/authorizenet \
        symfony/yaml:dev-issue-8145 \
        paypal/rest-api-sdk-php:* \
        paypal/merchant-sdk-php:* \
        stripe/stripe-php:* ; fi"
