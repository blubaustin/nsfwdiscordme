/**
 * @param {string} name
 * @returns {string}
 */
function generateSlug(name) {
  return name.toString().toLowerCase()
    .replace(/\s+/g, '-')           // Replace spaces with -
    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
    .replace(/^-+/, '')             // Trim - from start of text
    .replace(/-+$/, '');            // Trim - from end of text
}

/**
 *
 */
function pageServerAddInit() {
  const $serverName     = $('#server_name');
  const $serverSlug     = $('#server_slug');
  const $serverSlugHelp = $serverSlug.next('.form-help:first');

  let slugModified = false;
  const slugHelpText = $serverSlugHelp.text();

  /**
   *
   */
  function updateSlugHelp() {
    const val = $serverSlug.val();

    $serverSlugHelp.html(`${slugHelpText}<br />Your custom URL will be: https://nsfwdiscordme.com/s/${val}`);
  }

  $serverName.on('keydown', (e) => {
    const $el = $(e.target),
          val = $el.val();
    if (!slugModified) {
      $serverSlug.val(generateSlug(val));
    }
    updateSlugHelp();
  }).focus();

  $serverSlug.on('keyup', (e) => {
    const $el = $(e.target),
          val = $el.val();

    slugModified = true;
    // $el.val(generateSlug(val));
    updateSlugHelp();
  });

  updateSlugHelp();
}

export {
  pageServerAddInit
}
