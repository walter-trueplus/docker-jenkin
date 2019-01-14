import CoreContainer from "../../../framework/container/CoreContainer";
import CoreComponent from "../../../framework/component/CoreComponent";
import ContainerFactory from "../../../framework/factory/ContainerFactory";
import ComponentFactory from "../../../framework/factory/ComponentFactory";
import {listen} from "../../../event-bus";
import SessionAction from "../../action/SessionAction"

export class SessionComponent extends CoreComponent {
    static className = 'SessionComponent';

    constructor(props) {
        super(props);
        listen('redirect-to-manage-session', () => {
            this.props.redirectToManageSession();
        });
    }

    render() {
        return (null)
    }
}

class SessionContainer extends CoreContainer {
    static className = 'SessionContainer';

    static mapDispatch(dispatch) {
        return {
            redirectToManageSession: () => dispatch(
                SessionAction.redirectToManageSession()
            ),
        }
    }
}

export default ContainerFactory.get(SessionContainer).withRouter(
    ComponentFactory.get(SessionComponent)
);
