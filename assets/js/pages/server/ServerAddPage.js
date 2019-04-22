import Discord from 'lib/Discord';
import { generateSlug } from 'lib/utils';

/**
 *
 */
class ServerAddPage
{
  /**
   * Initializes the page
   */
  setup = () => {
    this.setupFormServerID();
    this.setupFormSlug();
    this.setupFormUploads();
  };

  /**
   *
   */
  setupFormServerID = () => {
    const $serverID   = $('#server_discordID');
    const $serverName = $('#server_name');

    $serverID.on('input', () => {
      const snowflake = $serverID.val();
      if (Discord.isSnowflake(snowflake)) {
        Discord.fetchWidget(snowflake)
          .then(({ name }) => {
            if (name) {
              $serverName.val(name);
              $serverName.trigger('input');
            }
          });
      }
    }).focus();
  };

  /**
   *
   */
  setupFormSlug = () => {
    const $serverName     = $('#server_name');
    const $serverSlug     = $('#server_slug');
    const $serverSlugHelp = $serverSlug.next('.form-help:first');
    const slugHelpText    = $serverSlugHelp.text();

    let slugModified = false;

    /**
     *
     */
    function updateSlugHelp() {
      $serverSlugHelp.html(`${slugHelpText}<br />Your custom URL will be: https://nsfwdiscordme.com/s/${$serverSlug.val()}`);
    }

    $serverName.on('input', (e) => {
      const $el = $(e.target);
      const val = $el.val();
      if (!slugModified) {
        $serverSlug.val(generateSlug(val));
      }
      updateSlugHelp();
    });

    $serverSlug.on('keyup', () => {
      // const $el = $(e.target);
      // const val = $el.val();

      slugModified = true;
      // $el.val(generateSlug(val));
      updateSlugHelp();
    });

    updateSlugHelp();
  };

  /**
   *
   */
  setupFormUploads = () => {
    const options = {
      theme:             'fas',
      showBrowse:        false,
      showCancel:        false,
      showUpload:        false,
      autoOrientImage:   false,
      browseOnZoneClick: true,
      previewFileType:   'any'
    };
    $("#server_iconFile").fileinput(options);
    $("#server_bannerFile").fileinput(options);
  };
}

export default ServerAddPage;
