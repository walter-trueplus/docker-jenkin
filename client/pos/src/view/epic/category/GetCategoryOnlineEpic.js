import CategoryAction from "../../action/CategoryAction";
import CategoryConstant from '../../constant/CategoryConstant';
import LoadingAction from "../../action/LoadingAction";
import {Observable} from 'rxjs';
import CategoryService from "../../../service/catalog/CategoryService";
import SyncService from "../../../service/sync/SyncService";
import ErrorLogService from "../../../service/sync/ErrorLogService";

/**
 * get category online epic
 * @param action$
 * @param store
 * @returns {Observable<any>}
 */
export default function getCategoryOnline(action$, store) {
    let requestTime = 0;
    let loadingErrorLogs = {};
    return action$.ofType(CategoryConstant.GET_CATEGORY_ONLINE)
        .mergeMap((action) => {
            requestTime++;
            return Observable.from(SyncService.getCategory())
                .mergeMap((response) => {
                    CategoryService.saveToDb(response.items);

                    requestTime = 0;
                    if (action.atLoadingPage) {
                        store.dispatch(LoadingAction.increaseCount());
                    }
                    return [
                        CategoryAction.getCategoryOnlineResult(response.items)
                    ];
                }).catch((error) => {
                    let message = "Failed to get category data. Please contact technical support.";
                    ErrorLogService.handleLoadingPageErrors(
                        error,
                        CategoryConstant.TYPE_GET_CATEGORY,
                        loadingErrorLogs,
                        requestTime,
                        action,
                        message,
                        store
                    );
                    return Observable.of(CategoryAction.getCategoryOnlineResult([]))
                })
        });

}
