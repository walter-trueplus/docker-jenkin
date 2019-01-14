import {PaymentAbstract} from "./PaymentAbstract";
import ZippayConstant from "../../../view/constant/payment/ZippayConstant";
import ServiceFactory from "../../../framework/factory/ServiceFactory";
import ZippayRefundPopupService from "./zippay/ZippayRefundPopupService";
import ZippayPurchasePopupService from "./zippay/ZippayPurchasePopupService";
import ZippayVerifyPopupService from "./zippay/ZippayVerifyPopupService";
import PaymentConstant from "../../../view/constant/PaymentConstant";
import OmcAbstract from "../../../data/omc/OmcAbstract";
import CheckoutService from "../../checkout/CheckoutService";
import PaymentHelper from "../../../helper/PaymentHelper";
import PosService from "../../PosService";
import UserService from "../../user/UserService";
import LocationService from "../../LocationService";
import SyncConstant from "../../../view/constant/SyncConstant";
import ActionLogService from "../../sync/ActionLogService";

export class ZippayPaymentService extends PaymentAbstract {
    code = PaymentConstant.ZIPPAY_INTEGRATION;
    omc;
    api_url;
    api_namespace;
    tmpTransaction;

    constructor()
    {
        super();
        this.omc = new OmcAbstract();
        this.api_namespace = this.omc.store_url + "/V1/webpos/zippay";
        this.api_url = this.omc.getBaseUrl() + this.api_namespace;
    }
    /**
     *
     * @param payment
     * @return {Promise<*|{}>}
     */
    async getLocationMap(payment) {
        payment = payment || await this.getPaymentMethodByCode(PaymentConstant.ZIPPAY_INTEGRATION);
        return JSON.parse(payment[ZippayConstant.LOCATIONS]) || {};
    }

    /**
     *
     */
    closePopup(popup) {
        this.tmpTransaction = false;

        if (popup) {
            popup.closePopup();
            return this.clear();
        }

        if (ZippayRefundPopupService.isOpen()) {
            ZippayRefundPopupService.closePopup();
        }

        if (ZippayPurchasePopupService.isOpen()) {
            ZippayPurchasePopupService.closePopup();
        }

        if (ZippayVerifyPopupService.isOpen()) {
            ZippayVerifyPopupService.closePopup();
        }
        return this.clear();
    }

    async execute() {
        if (this.getCreditmemo()) {
            return await this.sendRefundRequest();
        }

        return await this.sendSaleRequest();
    }

    /**
     *
     * @param id
     * @param callback
     * @return {Promise<*>}
     */
    async fetchTransaction(id, callback) {
        try {
            let response = await this.omc.get(this.api_url + '/purchaserequests/' + id);
            let timeoutHandler = setTimeout(() => {
                callback(response);
                clearTimeout(timeoutHandler);
            }, 1000);
        } catch (e) {
            // in case sudden lost internet
            if (!window.navigator.onLine) {
                return callback({
                    status: PaymentConstant.LOST_INTERNET_STATUS
                });
            }

            return this.fetchTransaction(id, callback);
        }
    }

    /**
     *
     * @param deviceRefCode
     * @param staffActorRefCode
     * @param refCode
     * @param locationId
     * @return {{refCode: *, originator: {locationId: *, deviceRefCode: *, staffActor: {refCode: *}}}}
     */
    makeCancelPayload(deviceRefCode, staffActorRefCode, refCode, locationId) {
        return{
            refCode,
            "originator": {
                locationId,
                deviceRefCode,
                "staffActor": {
                    "refCode": staffActorRefCode
                }
            }
        };
    }

    /**
     *
     * @param endPoint
     * @param payload
     * @param callback
     * @return {Promise<Promise<*>|*>}
     */
    async cancelTransaction(endPoint, payload, callback) {
        try {
            this.omc.post(this.api_url + endPoint, payload);
            let timeoutHandler = setTimeout(() => {
                callback && callback();
                clearTimeout(timeoutHandler);
            }, 1000);
        } catch (e) {
            return this.cancelTransaction(endPoint, payload, callback);
        }
    }

    /**
     *
     * @param result
     * @param resolve
     * @param reject
     * @return {*}
     */
    pollCallback = async (result, resolve, reject ) => {
        let upperCaseStatus = String(result.status).toUpperCase();

        if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_APPROVED) {
            await ActionLogService.deleteRequestVoidPurchasePaymentActionLog(
                this.makeCancelPayload(
                    PosService.getCurrentPosId(),
                    UserService.getStaffId(),
                    this.tmpTransaction.ref_code,
                    this.tmpTransaction.location_id
                )
            );

            resolve({
                error           : false,
                reference_number: result.id,
                ...result
            });

            CheckoutService.setIncrementPreOrder(
                PaymentHelper.convertRefCodeToIncrementOrderId(result.ref_code)
            );

            return this.closePopup();
        }

        if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_CANCELLED) {
            this.closePopup();
            return resolve({
                error: true,
                errorMessage : ZippayConstant.MESSAGE_TRANSACTION_STATUS_CANCELLED,
            });
        }

        if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_EXPIRED) {
            this.closePopup();
            return resolve({
                error: true,
                errorMessage : ZippayConstant.MESSAGE_TRANSACTION_STATUS_EXPIRED,
            });
        }

        if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_DECLINED) {
            this.closePopup();

            return resolve({
                error: true,
                errorMessage : ZippayConstant.MESSAGE_TRANSACTION_STATUS_DECLINED,
            });
        }

        if (upperCaseStatus === PaymentConstant.LOST_INTERNET_STATUS) {
            resolve({
                error: true,
                errorMessage : PaymentConstant.LOST_INTERNET_CONNECTION_MESSAGE,
            });
            return this.closePopup();
        }

        if (upperCaseStatus === PaymentConstant.TIMEOUT_STATUS) {
            this.closePopup();
            return resolve({
                error: true,
                errorMessage : PaymentConstant.TIME_OUT_EXCEPTION_MESSAGE,
            });
        }

        return this.fetchTransaction(result.id, (fetchResponse) => this.pollCallback(fetchResponse, resolve, reject ));

    };


    /**
     *
     * @return {Promise<any>}
     */
    async sendSaleRequest() {
        let cancelled = false;
        // void transaction id
        let transactionId = this.payment.reference_number;
        if (transactionId) {
            try {
                let verifyResponse = await new Promise((resolve, reject) => {
                    let popup = ZippayVerifyPopupService.showPopup(this, transactionId, async () => {
                        let fetchTransaction = await this.omc.get(this.api_url + '/purchaserequests/' + transactionId);
                        if (!fetchTransaction.id) {
                            return resolve({
                                nextStep: true,
                            });
                        }


                        let cancelCall = () => this.cancelTransaction('/purchaserequests/void', this.makeCancelPayload(
                            PosService.getCurrentPosId(),
                            UserService.getStaffId(),
                            fetchTransaction.ref_code,
                            fetchTransaction.location_id
                        ));

                        let upperCaseStatus = String(fetchTransaction.status).toUpperCase();

                        if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_APPROVED) {

                            return ZippayVerifyPopupService.showApproveConfirmation(
                                popup,
                                () => {
                                    CheckoutService.setIncrementPreOrder(
                                        PaymentHelper.convertRefCodeToIncrementOrderId(fetchTransaction.ref_code)
                                    );
                                    resolve({
                                        nextStep: false,
                                        reference_number: fetchTransaction.id,
                                    });
                                },
                                async () => {
                                    ZippayVerifyPopupService.showCancelling(popup);
                                    await cancelCall();

                                    return resolve({
                                        nextStep: true,
                                    });
                                }
                            );
                        }

                        if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_PENDING) {
                            // poll API GET /purchaserequests
                            return this.fetchTransaction(fetchTransaction.id, () => this.pollCallback(fetchTransaction, resolve, reject));
                        }

                        if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_CANCELLED) {
                            return resolve({
                                nextStep: true,
                            });
                        }

                        ZippayVerifyPopupService.showCancelling(popup);
                        await cancelCall();
                        return resolve({
                            nextStep: true,
                        });
                    }, ()=> {}, ()=> {
                        return resolve({
                            closePopup: true,
                        });
                    });
                });

                if (verifyResponse.closePopup) {
                    this.closePopup();
                    return Promise.reject('');
                }

                if (!verifyResponse.nextStep) {
                    this.closePopup();
                    return Promise.resolve({
                        error: false,
                        reference_number: transactionId
                    });
                }

                if (ZippayVerifyPopupService.isOpen()) {
                    ZippayVerifyPopupService.closePopup();
                }
            } catch (e) {
                this.closePopup();
                return Promise.resolve({
                    error: true,
                    errorMessage : PaymentConstant.UNKNOWN_EXCEPTION_MESSAGE,
                })
            }

            // return new Promise((resolve, reject) => {
            //     let popup = ZippayVerifyPopupService.showPopup(this, transactionId, async () => {
            //         try {
            //             let response = await this.omc.get(
            //                 this.api_url + '/purchaserequests/' + transactionId
            //             );
            //
            //             // process response
            //             if (response.hasOwnProperty('error') && !response.error) {
            //                 this.payment.reference_number = false;
            //                 this.closePopup(popup);
            //                 return this.sendSaleRequest();
            //                 // return this.showError(popup, response);
            //             }
            //
            //             let upperCaseStatus = String(response.status).toUpperCase();
            //
            //             if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_APPROVED) {
            //                 resolve({
            //                     error           : false,
            //                     reference_number: response.id,
            //                 });
            //
            //                 CheckoutService.setIncrementPreOrder(
            //                     PaymentHelper.convertRefCodeToIncrementOrderId(response.ref_code)
            //                 );
            //                 return this.closePopup(popup);
            //             }
            //
            //             if (upperCaseStatus === ZippayConstant.TRANSACTION_STATUS_PENDING) {
            //                 // poll API GET /purchaserequests
            //                 return this.fetchTransaction(response.id, () => this.pollCallback(response, resolve, reject));
            //             }
            //
            //             this.payment.reference_number = false;
            //             return this.sendSaleRequest();
            //             // return this.showError(popup, {
            //             //     error: {
            //             //         errorMessage: 'The transaction is not been approved.'
            //             //     }
            //             // });
            //         } catch (error) {
            //             return this.showError(popup, error);
            //         }
            //     }, async () => reject(''));
            // })
        }

        // prepare data to post
        let order;

        if (this.getOrder()) {

            order = {...this.order};
            delete order['all_items'];
            order.items = order.items.map(item => {
                delete item['tmp_item_id'];
                delete item['has_children'];
                return item;
            });

            // in case, order online
            if (!order.pos_staff_id) {
                order.pos_staff_id = PosService.getCurrentPosId();
            }
            if (!order.pos_location_id) {
                order.pos_location_id = LocationService.getCurrentLocationId();
            }
            if (!order.pos_id) {
                order.pos_id = PosService.getCurrentPosId();
            }

        } else {
            order  = {...CheckoutService.getPreOrder()};
        }

        // make refCode
        order.increment_id = PaymentHelper.generateRefCode(order, order.increment_id, this.payment);
        order.payments = PaymentHelper.filterPaymentData([this.getPayment()]);
        let locations = await this.getLocationMap();
        let voidPurchaseRequest = (callback) => this.cancelTransaction('/purchaserequests/void', this.makeCancelPayload(
            order.pos_id,
            order.pos_staff_id,
            order.increment_id,
            locations[LocationService.getCurrentLocationId()]
        ), callback);

        return new Promise((resolve, reject) => {
            let popup = ZippayPurchasePopupService.showPopup(this, async (storeCode) => {
               try {
                   let response = await this.omc.post(this.api_url + '/purchaserequests', {
                       store_code: storeCode,
                       order,
                   });

                   // process response
                   if (!response.error) {
                       this.tmpTransaction = response;
                       // make void transaction
                       await ActionLogService.createDataActionLog(
                           SyncConstant.REQUEST_VOID_PURCHASE_PAYMENT,
                           this.api_namespace + '/purchaserequests/void',
                           SyncConstant.METHOD_POST,
                           this.makeCancelPayload(
                               PosService.getCurrentPosId(),
                               UserService.getStaffId(),
                               this.tmpTransaction.ref_code,
                               this.tmpTransaction.location_id
                           )
                       );

                       // poll API GET /purchaserequests
                       return this.fetchTransaction(response.id, () => this.pollCallback(response, resolve, reject));
                   }
                   // make void transaction
                   voidPurchaseRequest();
                   cancelled = true;
                   return this.showError(popup, response);
               } catch (error) {
                   return this.showError(popup, error);
               }
            }, async () => {
                if (cancelled) {
                    return reject('');
                }

                ZippayPurchasePopupService.showMessage(popup, `Canceling Transaction<br/>`);
                // make void transaction
                return voidPurchaseRequest();
            });
        })
    }

    /**
     *
     * @return {Promise<any>}
     */
    async sendRefundRequest() {
        let cancelled = false;
        let locations = await this.getLocationMap();
        // prepare data to post
        let payload = {
            id: this.payment.orderPayment.reference_number,
            refCode: this.payment.increment_id,
            refundAmount: this.payment.amount_paid,
            "originator": {
                "locationId": locations[LocationService.getCurrentLocationId()],
                "deviceRefCode": PosService.getCurrentPosId(),
                "staffActor": {
                    "refCode": UserService.getStaffId()
                }
            },
        };

        let voidRefundRequest = (callback) => this.cancelTransaction(
            '/purchaserequests/refund/void',
            {...payload},
            callback
        );

        // add void refund to request log
        await ActionLogService.createDataActionLog(
            SyncConstant.REQUEST_VOID_REFUND_PAYMENT,
            this.api_namespace + '/purchaserequests/refund/void',
            SyncConstant.METHOD_POST,
            {...payload}
        );

        return new Promise((resolve, reject) => {
            let popup = ZippayRefundPopupService.showPopup(this, async () => {
                try {
                    let response = await this.omc.post(this.api_url + '/purchaserequests/refund', {
                        ...payload
                    });

                    // process response
                    if (!response.error) {
                        this.tmpTransaction = {};
                        this.closePopup();
                        await ActionLogService.deleteRequestVoidRefundPaymentActionLog(payload);
                        return resolve({
                            error : false,
                        });
                    }

                    voidRefundRequest();
                    cancelled = true;
                    return this.showError(popup, response);
                } catch (error) {
                    return this.showError(popup, error);
                }
            }, async () => {
                if (cancelled) {
                    return reject('');
                }

                ZippayRefundPopupService.showMessage(popup, `Canceling Transaction<br/>`);
                return await voidRefundRequest(() => reject(''));
            });
        })
    }

    /**
     *
     * @param response
     * @return {string}
     */
    makeError(response) {
        response = response || {};
        let error = response.error || {};
        let message = response.errorMessage
            || error.message
            || ZippayConstant.UNKNOWN_EXCEPTION_MESSAGE;

        if (!error.items || !error.items.length) {
            return message;
        }

        message = [];
        error.items.forEach(item => {
            if (!message.includes(item.message)) {
                message.push(item.message);
            }
        });

        return message.join(', ');
    }

    showError(popup, response) {
        return popup.showError( popup, this.makeError(response));
    }
}

/**
 * @type {ZippayPaymentService}
 */
let zippayPaymentService = ServiceFactory.get(ZippayPaymentService);

export default zippayPaymentService;