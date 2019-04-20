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

  $('[data-next-bump-date]').each((i, item) => {
    const $item = $(item);
    const countDownDate = new Date($item.data('next-bump-date')).getTime();
    setInterval(() => {
      const now      = new Date().getTime();
      const distance = countDownDate - now;
      if (distance < 60) {
        $item.text('In a moment...');
      }

      const days     = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours    = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes  = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds  = Math.floor((distance % (1000 * 60)) / 1000);

      $item.text(`${days}d ${hours}h ${minutes}m ${seconds}s`);
    }, 1000);
  });
});
