#!/usr/bin/env sh

# docker-mftf
OLD_PWD=$PWD
cd ..
if [ -d dockermftf ]; then
    cd dockermftf
    git fetch --depth 1 origin 2.2.4
    git checkout FETCH_HEAD
else
    git clone -b 2.2.4 --depth 1 https://github.com/Magestore/docker-mftf dockermftf
    cd dockermftf
fi
cd $OLD_PWD

# Show command
set -x

# pwa-pos server
rm -Rf ../dockermftf/app/code/Magestore
cp -Rf server/app/code/Magestore ../dockermftf/app/code/
cp -Rf server/app/tests/Magestore/* ../dockermftf/app/code/Magestore/

# pwa-pos testcase
cp -Rf server/app/tests/acceptance ../dockermftf/app/tests/

# pwa-pos api test
cp -Rf server/app/tests/api-functional ../dockermftf/app/tests/
