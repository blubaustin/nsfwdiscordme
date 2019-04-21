import Promise from 'promise-polyfill';
import pages from 'pages';

if (window.Promise === undefined) {
  window.Promise = Promise;
}

for(const selector in pages) {
  if ($(selector).length) {
    (new pages[selector]()).run();
    break;
  }
}

$(() => {
  $('[data-toggle="tooltip"]').tooltip();
});
