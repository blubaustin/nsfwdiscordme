import routes from '../../public/js/routes.json';
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
import pages from './pages';

Routing.setRoutingData(routes);

for(const selector in pages) {
  if ($(selector).length) {
    (new pages[selector](Routing)).run();
    break;
  }
}

$(() => {
  $('[data-toggle="tooltip"]').tooltip();
});
