import ColorSwatchAction from "../action/ColorSwatchAction";
import LoadingAction from "../action/LoadingAction";
import {Observable} from 'rxjs';
import ColorSwatchService from "../../service/config/ColorSwatchService";
import SyncService from "../../service/sync/SyncService";
import ColorSwatchConstant from "../constant/ColorSwatchConstant";
import ErrorLogService from "../../service/sync/ErrorLogService";

/**
 * Receive action type(GET_COLOR_SWATCH) and request, response data color swatch
 * @param action$
 * @param store
 * @returns {Observable<any>}
 */
export default (action$, store) => {
    let requestTime = 0;
    let loadingErrorLogs = {};
    return action$.ofType(ColorSwatchConstant.GET_COLOR_SWATCH)
        .mergeMap((action) => {
            requestTime++;
            return Observable.from(SyncService.getColorSwatch())
                .mergeMap((response) => {
                    ColorSwatchService.saveToLocalStorage(response.items);

                    requestTime = 0;
                    if (action.atLoadingPage) {
                        store.dispatch(LoadingAction.increaseCount());
                    }
                    return [
                        ColorSwatchAction.getColorSwatchResult(response.items)
                    ];
                }).catch(error => {
                    let message = "Failed to get color swatch data. Please contact technical support.";
                    ErrorLogService.handleLoadingPageErrors(
                        error,
                        ColorSwatchConstant.TYPE_GET_COLOR_SWATCH,
                        loadingErrorLogs,
                        requestTime,
                        action,
                        message,
                        store
                    );
                    return Observable.of(ColorSwatchAction.getColorSwatchError(error));
                })
        });
}
