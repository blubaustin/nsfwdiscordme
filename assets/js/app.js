import Promise from 'promise-polyfill';
import pages from 'pages';

if (window.Promise === undefined) {
  window.Promise = Promise;
}

for(const selector in pages) {
  const $page = $(selector);
  if ($page.length) {
    (new pages[selector]()).setup($page);
    break;
  }
}

$(() => {
  $('[data-toggle="tooltip"]').tooltip();

  $('.btn-anchor[data-href]').on('click', (e) => {
    const $btn = $(e.target);
    const href = $btn.data('href');

    if ($btn.data('target') === '_blank') {
      window.open(href);
    } else {
      document.location = href;
    }
  });
});
