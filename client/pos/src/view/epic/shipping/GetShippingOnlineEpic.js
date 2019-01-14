import ShippingConstant from '../../constant/ShippingConstant';
import LoadingAction from "../../action/LoadingAction";
import {Observable} from 'rxjs';
import ShippingService from "../../../service/shipping/ShippingService";
import SyncService from "../../../service/sync/SyncService";
import Config from "../../../config/Config";
import ErrorLogService from "../../../service/sync/ErrorLogService";

/**
 * Get shipping online epic
 * @param action$
 * @param store
 * @returns {Observable<any>}
 */
export default function getShippingOnline(action$, store) {
    let requestTime = 0;
    let loadingErrorLogs = {};
    return action$.ofType(ShippingConstant.GET_SHIPPING_ONLINE)
        .mergeMap((action) => {
            requestTime++;
            return Observable.from(SyncService.getShipping())
                .mergeMap((response) => {
                    ShippingService.clear().then(() => {
                        ShippingService.saveToDb(response.items);
                        Config.shipping_methods = response.items;
                    });

                    requestTime = 0;
                    if (action.atLoadingPage) {
                        store.dispatch(LoadingAction.increaseCount());
                    }
                    return [];
                }).catch(error => {
                    let message = "Failed to get shipping data. Please contact technical support.";
                    ErrorLogService.handleLoadingPageErrors(
                        error,
                        ShippingConstant.TYPE_GET_SHIPPING,
                        loadingErrorLogs,
                        requestTime,
                        action,
                        message,
                        store
                    );
                    return Observable.empty();
                })
        });
}
