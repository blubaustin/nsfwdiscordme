import pages from './pages';

for(const selector in pages) {
  if ($(selector).length) {
    (new pages[selector]).run();
    break;
  }
}

$(() => {
  $('[data-toggle="tooltip"]').tooltip();
});
