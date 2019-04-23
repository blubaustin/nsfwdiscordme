import Discord from 'lib/Discord';
import router from 'lib/router';
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
    this.serverID       = null;
    this.deleteServerID = null;
    this.$errorModal    = $('#modal-server-join-error');
    this.$errorMessage  = $('.server-join-error-message');
    this.$deleteModal   = $('#modal-server-delete');
    this.$deleteButton  = $('#server-delete-btn');

    this.$errorModal.on('hidden.bs.modal', this.handleErrorModalHidden);
    this.$deleteButton.on('click', this.handleDeleteClick);
    $('#modal-server-delete-btn').on('click', this.handleModalDeleteClick);

    this.setupFormServerID();
    this.setupFormSlug();
    this.setupFormBot();
    this.setupFormUploads();
  };

  /**
   *
   */
  setupFormServerID = () => {
    const $serverID   = $('#server_discordID');
    const $serverName = $('#server_name');
    this.serverID     = $serverID.val();
    if (this.serverID !== '0') {
      this.handleRefreshClick();
    }

    $serverID.on('input', () => {
      this.serverID = $serverID.val();
      if (Discord.isSnowflake(this.serverID)) {
        Discord.fetchWidget(this.serverID)
          .then(({ name }) => {
            if (name) {
              $serverName.val(name);
              $serverName.trigger('input');
            }
            this.handleRefreshClick();
          })
          .catch(() => {
            this.$errorMessage.html('Widget not enabled for this server. Enable the widget and then try again.');
            this.$errorModal.modal('show');
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
      $serverSlugHelp.html(`${slugHelpText}<br />Your custom URL will be: https://nsfwdiscordme.com/${$serverSlug.val()}`);
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
   * Replaces the text input with a drop down
   *
   * Required because Symfony's form types don't allow dynamic values, but the channel
   * values are populated by this javascript.
   */
  setupFormBot = () => {
    const $input  = $('#server_botInviteChannelID');
    const $select = $('<select />', {
      'id':         'server_botInviteChannelID',
      'name':       'server[botInviteChannelID]',
      'class':      'form-control',
      'data-value': $input.val()
    });
    $('<option />', {
      'value': 0,
      'html':  'Select...'
    }).appendTo($select);
    $input.replaceWith($select);

    $('#server-refresh-btn').on('click', this.handleRefreshClick);

    if (this.serverID !== '0') {
      this.handleRefreshClick();
    }
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

  /**
   *
   */
  handleErrorModalHidden = () => {
    this.$errorMessage.html('');
  };

  /**
   *
   */
  handleRefreshClick = () => {
    const { serverID } = this;

    const $input = $('#server_botInviteChannelID');

    $.ajax({
      url: router.generate('api_guild_channels', { serverID })
    }).done((resp) => {
      if (resp.message === 'ok') {
        $input.html('');
        $('<option />', {
          'value': 0,
          'html':  'Select...'
        }).appendTo($input);

        if (resp.channels) {
          resp.channels.forEach((channel) => {
            if (channel.type === 0) {
              $('<option />', {
                'value': channel.id,
                'html':  channel.name
              }).appendTo($input);
            }
          });
        }

        if ($input.data('value')) {
          $input.val($input.data('value'));
        }
      } else {
        // @todo
      }
    });
  };

  /**
   *
   */
  handleDeleteClick = () => {
    this.deleteServerID = this.$deleteButton.data('server-id');
    this.$deleteModal.modal('show');
  };

  /**
   *
   */
  handleModalDeleteClick = () => {
    $.ajax({
      url:  router.generate('api_delete_server', { serverID: this.deleteServerID }),
      type: 'post'
    }).done((resp) => {
      if (resp.message === 'ok') {
        $.ajax({
          url:  router.generate('api_flash', { type: 'success' }),
          type: 'post',
          data: {
            message: 'The server has been deleted.'
          }
        }).done(() => {
          document.location = router.generate('profile_index');
        });
      }
    });
  };
}

export default ServerAddPage;
