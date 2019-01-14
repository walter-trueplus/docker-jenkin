import CoreContainer from "../../../framework/container/CoreContainer";
import CoreComponent from "../../../framework/component/CoreComponent";
import ContainerFactory from "../../../framework/factory/ContainerFactory";
import ComponentFactory from "../../../framework/factory/ComponentFactory";
import {listen} from "../../../event-bus";
import TotalsAction from "../../action/checkout/quote/TotalsAction";
import QuoteAction from "../../action/checkout/QuoteAction";

export class QuoteComponent extends CoreComponent {
    static className = 'QuoteComponent';

    constructor(props) {
        super(props);
        listen('quote-init-total-collectors', eventData => {
            this.props.initTotalCollectors(eventData.service);
        });
        listen('quote-collect-totals-before', eventData => {
            this.props.collectTotalsBefore(eventData.quote);
        });
        listen('quote-collect-totals-after', eventData => {
            this.props.collectTotalsAfter(eventData.quote);
        });
        listen('quote-change-customer-after', eventData => {
            this.props.changeCustomerAfter(eventData.quote);
        });
        listen('quote-add-product-after', eventData => {
            this.props.addProductAfter(eventData.quote);
        });
        listen('quote-update-qty-cart-item-after', eventData => {
            this.props.updateQtyCartItemAfter(eventData.quote);
        });
        listen('quote-remove-cart-item-after', eventData => {
            this.props.removeCartItemAfter(eventData.quote);
        });
        listen('quote-place-order-before', eventData => {
            this.props.placeOrderBefore(eventData.quote);
        });
    }

    render() {
        return (null)
    }
}

class QuoteContainer extends CoreContainer {
    static className = 'QuoteContainer';

    static mapDispatch(dispatch) {
        return {
            initTotalCollectors: (service) => dispatch(
                TotalsAction.salesQuoteInitTotalCollectors(service)
            ),
            collectTotalsBefore: (quote) => dispatch(
                TotalsAction.salesQuoteCollectTotalsBefore(quote)
            ),
            collectTotalsAfter: (quote) => dispatch(
                TotalsAction.salesQuoteCollectTotalsAfter(quote)
            ),
            changeCustomerAfter: (quote) => dispatch(
                QuoteAction.changeCustomerAfter(quote)
            ),
            addProductAfter: (quote) => dispatch(
                QuoteAction.addProductAfter(quote)
            ),
            updateQtyCartItemAfter: (quote) => dispatch(
                QuoteAction.updateQtyCartItemAfter(quote)
            ),
            removeCartItemAfter: (quote) => dispatch(
                QuoteAction.removeCartItemAfter(quote)
            ),
            placeOrderBefore: (quote) => dispatch(
                QuoteAction.placeOrderBefore(quote)
            ),
        }
    }
}

export default ContainerFactory.get(QuoteContainer).withRouter(
    ComponentFactory.get(QuoteComponent)
);
