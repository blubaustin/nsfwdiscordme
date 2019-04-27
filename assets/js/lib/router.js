import routes from '../../../public/js/routes.json';
import Routing from '../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';

Routing.setRoutingData(routes);

/**
 * @property {Function} generate
 */
export default Routing;
