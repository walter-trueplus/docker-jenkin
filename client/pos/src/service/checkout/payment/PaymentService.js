import CoreService from "../../CoreService";
import ServiceFactory from "../../../framework/factory/ServiceFactory"
import CurrencyHelper from "../../../helper/CurrencyHelper";
import PaymentHelper from "../../../helper/PaymentHelper";
import PaymentConstant from "../../../view/constant/PaymentConstant";
import DateTimeHelper from "../../../helper/DateTimeHelper";
import Config from "../../../config/Config";
import SessionHelper from "../../../helper/SessionHelper";

export class PaymentService extends CoreService {
    static className = 'PaymentService';

    /* handle payment amount */
    handlePaymentAmount(quote, amountPaid, paymentMethod, referenceNo, creditCard){
        let paymentNewExistInArray = quote.payments.find((item, index) => index === paymentMethod.index);
        let paymentNewArray = quote.payments.filter((item, index) => index !== paymentMethod.index);

        /* convert and calculate base paid amount and paid amount*/
        let amountPaidAfterConvert = CurrencyHelper.roundToFloat(amountPaid);
        let amountPaidBaseAfterConvert = CurrencyHelper.convertAndRoundFloatToBase(amountPaid);
        let paymentNewCashIn = {};
        if (paymentNewExistInArray) {
            paymentNewCashIn = {
                ...paymentNewExistInArray,
                amount_paid: amountPaidAfterConvert,
                base_amount_paid: amountPaidBaseAfterConvert,
                reference_number: referenceNo,
            };
        } else {
            paymentNewCashIn = {
                method: paymentMethod.code,
                title: paymentMethod.title,
                amount_paid: amountPaidAfterConvert,
                base_amount_paid: amountPaidBaseAfterConvert,
                reference_number: referenceNo
            };
        }

        if (PaymentHelper.hasUsingCreditCardForm(paymentMethod.code)) {
            paymentNewCashIn.isCardMode = creditCard.state.isCardMode;
            if (creditCard.state.isCardMode) {
                let {cardExpiryField} = creditCard;
                let month             = cardExpiryField.value.split('/')[0];
                let year              = cardExpiryField.value.split('/')[1];
                let cardType          = creditCard.getType();

                paymentNewCashIn = {
                    ...paymentNewCashIn,
                    "cc_owner":
                        creditCard.cardNameField.value && creditCard.cardNameField.value.toUpperCase(),
                    "cc_number": creditCard.cardNumberField.value.replace(/ /g, ''),
                    "cc_type": cardType,
                    "card_type": cardType,
                    "cc_exp_month": month.trim(),
                    "cc_exp_year": `20${year.trim()}`,
                    "cc_cid": creditCard.cvcField.value,
                    "last4Digit": `${
                        creditCard.cardNumberdMaskedField.value
                        } ${
                        creditCard.cardNumberdUnmaskedField.value
                        }`
                };
            } else {
                paymentNewCashIn.email = creditCard.emailField.value;
                paymentNewCashIn.is_pay_later = 1;
            }
        }

        paymentNewCashIn.errorMessage = '';
        paymentNewCashIn.status = PaymentConstant.PROCESS_PAYMENT_NEW;
        paymentNewCashIn.payment_date = DateTimeHelper.getDatabaseDateTime(new Date().getTime());
        paymentNewCashIn.shift_increment_id = Config.current_session && SessionHelper.isEnableSession() ?
            Config.current_session.shift_increment_id : "";
        paymentNewCashIn.increment_id = PaymentHelper.generateIncrement(paymentMethod.index);

        return [
            ...paymentNewArray,
            paymentNewCashIn
        ];
    }
}

/**
 *
 * @type {PaymentService}
 */
let paymentService = ServiceFactory.get(PaymentService);

export default paymentService;
