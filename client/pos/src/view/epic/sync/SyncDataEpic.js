import {Observable} from 'rxjs';
import SyncService from "../../../service/sync/SyncService";
import SyncConstant from "../../constant/SyncConstant";
import SyncAction from "../../action/SyncAction";
import SessionHelper from "../../../helper/SessionHelper";
import AppStore from "../../store/store";
import Config from "../../../config/Config";

/**
 * Check table Sync and request sync data
 * @param action$
 * @returns {*}
 */
export default function syncData(action$) {
    return action$.ofType(SyncConstant.SYNC_DATA)
        .switchMap(() => {
            return Observable.from(SyncService.getAll())
                .switchMap(data => {
                    if (!Config.session) {
                        return Observable.empty();
                    }
                    // If sync table doesn't have any data, set default data and recall sync data
                    if (!data.length) {
                        SyncService.setDefaultData().then(() => {
                            AppStore.dispatch(SyncAction.syncData());
                        });
                        return Observable.empty();
                    }
                    // Check have sync data or not
                    let result = checkSync(data);


                    if (!result.actions.length) {
                        if (!result.failedData.length) {
                            // If there isn't any data that need to be synchronized or had failed,
                            // end synchronizing data and change mode to OFFLINE
                            return Observable.of(SyncAction.syncDataFinishResult(SyncConstant.OFFLINE_MODE));
                        } else {
                            // If result's action is empty but failedData list is not empty,
                            // remove isFailed attr of that data then start to sync data again
                            result.failedData.forEach(data => delete data.isFailed);
                            SyncService.saveToDb(result.failedData).then(() => {
                                setTimeout(() => AppStore.dispatch(SyncAction.syncData()), 60 * 1000);
                            });
                            return Observable.empty();
                        }
                    }
                    // If result's actions is not empty, return that actions to start sync data
                    return result.actions;
                })
                .catch(() => Observable.of(SyncAction.syncData()))
        });

}


/**
 * Check have sync data or not
 * Return the first action of data type that need to be synchronized and list of failed data
 * @param data
 * @returns {{actions: Array, failedData: Array}}
 */
function checkSync(data) {
    let result = {
        actions: [],
        failedData: []
    };
    for (let syncData of data) {
        let count = syncData.count;
        let total = syncData.total;

         // If type of data is session and config disable manage session,
         // don't sync session data and check next data type
        if (
            syncData.type === SyncConstant.TYPE_SESSION
            && !SessionHelper.isEnableSession()
        ) {
            continue;
        }
        // If this data type was failed to sync, add it to failedData list.
        // Then check next data type
        if (syncData.isFailed) {
            result.failedData.push(syncData);
            continue;
        }

        // If data type hasn't been synchronized completely, add it to result's actions and break the loop.
        if (total === SyncConstant.DEFAULT_TOTAL) {
            result.actions.push(SyncAction.syncDataWithType(syncData));
            break;
        } else {
            if (count < total) {
                result.actions.push(SyncAction.syncDataWithType(syncData));
                break;
            }
        }
    }
    return result;
}
