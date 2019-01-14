import React, {Fragment} from "react";
import CoreComponent from '../../../framework/component/CoreComponent';
import ComponentFactory from '../../../framework/factory/ComponentFactory';
import CoreContainer from '../../../framework/container/CoreContainer';
import ContainerFactory from "../../../framework/factory/ContainerFactory";
import NumberHelper from "../../../helper/NumberHelper";
import StockHelper from "../../../helper/StockHelper";

export class StockLocation extends CoreComponent {

    getQty() {
        let stockItem = this.props.stock_location;

        if (!stockItem) {
            return "";
        }

        let isManageStock = +stockItem.use_config_manage_stock ? StockHelper.isManageStock() : +stockItem.manage_stock;
        if (!isManageStock) {
            return this.props.t("No Manage Stock");
        }

        let qty = 0;
        if (stockItem ) {
            qty = stockItem.qty
        }
        return NumberHelper.formatDisplayGroupAndDecimalSeparator(qty);
    }

    /**
     * Render template
     *
     * @return {*}
     */
    template() {
        let {stock_location} = this.props;
        return (
            <Fragment>
                <li className={stock_location.is_current_location === "1" ? "active" : ""}>
                    <div className="info">
                        <div className="name">{stock_location.name}</div>
                        <div className="detail">
                            {stock_location.address}
                        </div>
                        <div className={stock_location.is_in_stock === "1" ? "qty" : "qty not-available"}>
                            {this.getQty()}
                        </div>
                    </div>
                </li>
            </Fragment>
        )
    }
}

class StockLocationContainer extends CoreContainer {
    static className = 'StockLocationContainer';
}

export default ContainerFactory.get(StockLocationContainer).withRouter(
    ComponentFactory.get(StockLocation)
);