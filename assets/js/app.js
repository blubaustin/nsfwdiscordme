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
});
