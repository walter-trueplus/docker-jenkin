import LoadingAction from "../../action/LoadingAction";
import {Observable} from 'rxjs';
import Config from "../../../config/Config";
import OrderConstant from "../../constant/OrderConstant";
import OrderService from "../../../service/sales/OrderService";
import ErrorLogService from "../../../service/sync/ErrorLogService";

/**
 * Receive action type(GET_LIST_ORDER_STATUSES) and request, response data statuses
 * @param action$
 * @param store
 * @returns {Observable<any>}
 */
export default (action$, store) => {
    let requestTime = 0;
    let loadingErrorLogs = {};
    return action$.ofType(OrderConstant.GET_LIST_ORDER_STATUSES)
        .mergeMap(action => {
            requestTime++;
            return Observable.from(OrderService.getListOrderStatuses())
                .mergeMap((response) => {
                    OrderService.saveOrderStatus(response.items);
                    Config.orderStatus = response.items;

                    requestTime = 0;
                    if (action.atLoadingPage) {
                        store.dispatch(LoadingAction.increaseCount());
                    }
                    return [];
                }).catch(error => {
                    let message = "Failed to get order status data. Please contact technical support.";
                    ErrorLogService.handleLoadingPageErrors(
                        error,
                        OrderConstant.TYPE_GET_LIST_ORDER_STATUSES,
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
