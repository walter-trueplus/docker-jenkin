#!/usr/bin/env sh

# Notify success
curl -X POST -s --data-urlencode "payload={\"text\": \"[SUCCESS] <$RUN_DISPLAY_URL|$JOB_NAME $BUILD_DISPLAY_NAME>\"}" $SLACK_HOOKS
