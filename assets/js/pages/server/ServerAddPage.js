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
   *
   * @param {jQuery} $page
   */
  setup = ($page) => {
    this.serverID       = null;
    this.deleteServerID = null;
    this.isEditing      = $page.data('is-editing');
    this.$errorModal    = $('#modal-server-join-error');
    this.$errorMessage  = $('.server-join-error-message');
    this.$deleteModal   = $('#modal-server-delete');
    this.$deleteButton  = $('#server-delete-btn');
    this.$refreshButton = $('#server-refresh-btn');

    this.$errorModal.on('hidden.bs.modal', this.handleErrorModalHidden);
    this.$deleteButton.on('click', this.handleDeleteClick);
    $('#modal-server-delete-btn').on('click', this.handleModalDeleteClick);
    $('#server-verify-widget-btn').on('click', this.handleVerifyWidgetClick);
    $('#server_categories, #server_summary').on('change', this.handleStep3Change);

    this.setupFormServerID();
    this.setupFormSlug();
    this.setupFormBot();
    this.setupFormUploads();

    if (!this.isEditing) {
      this.$step2 = this.disableFormInputs($('#form-step-2'));
      this.$step3 = this.disableFormInputs($('#form-step-3'));
      this.$step4 = this.disableFormInputs($('#form-step-4'));
      this.$step5 = this.disableFormInputs($('#form-step-5'));
      this.$step6 = this.disableFormInputs($('#form-step-6'));
    } else {
      this.$step2 = this.enableFormInputs($('#form-step-2'));
      this.$step3 = this.enableFormInputs($('#form-step-3'));
      this.$step4 = this.enableFormInputs($('#form-step-4'));
      this.$step5 = this.enableFormInputs($('#form-step-5'));
      this.$step6 = this.enableFormInputs($('#form-step-6'));
    }
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

            this.enableFormInputs(this.$step2);
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
      $serverSlugHelp.html(`${slugHelpText}<br />The server URL will be: https://nsfwdiscordme.com/${$serverSlug.val()}`);
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

    $select.on('change', this.handleInviteChannelChange);

    this.$refreshButton.on('click', this.handleRefreshClick);

    if (this.serverID !== '0') {
      this.handleRefreshClick();
    }
  };

  /**
   *
   */
  setupFormUploads = () => {
    const options = {
      theme:                 'fas',
      showBrowse:            false,
      showCancel:            false,
      showUpload:            false,
      autoOrientImage:       false,
      browseOnZoneClick:     true,
      previewFileType:       'any',
      allowedFileExtensions: ['jpg', 'jpeg', 'png']
    };

    const $modal      = $('#modal-server-cropper');
    const $modalImage = $('#modal-server-cropper-image');
    let cropper;
    let $image;

    $modal
      .on('shown.bs.modal', () => {
        $modalImage.attr('src', $image.attr('src'));
        cropper = new Cropper($modalImage[0], {
          viewMode:    1,
          aspectRatio: 16 / 9,
          rotatable:   false
        });
      })
      .on('hidden.bs.modal', () => {
        const data     = cropper.getData(true);
        const imageURL = cropper.getCroppedCanvas().toDataURL();
        $image.attr('src', imageURL);
        $('#server_bannerCropData').val(JSON.stringify(data));

        cropper.destroy();
      });

    $("#server_bannerFile").fileinput(options)
      .on('fileimageloaded', (event, previewId) => {
        $image = $('#' + previewId).find('img.file-preview-image');
        $modal.modal('show');
      })
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
  handleVerifyWidgetClick = () => {
    const { serverID } = this;

    const $danger = $('#server-verify-widget-danger');

    Discord.fetchWidget(serverID)
      .then((widget) => {
        if (widget.instant_invite) {
          $danger.hide();
          this.enableFormInputs(this.$step3);
          $('#server-verify-bot-container').slideUp();
        } else {
          $danger.show();
        }
      })
      .catch(() => {
        $danger.show();
      });
  };

  /**
   *
   */
  handleInviteChannelChange = () => {
    const $input = $('#server_botInviteChannelID');

    if ($input.val() === '0') {
      this.disableFormInputs(this.$step3);
      $('#server-verify-widget-container').slideDown();
    } else {
      this.enableFormInputs(this.$step3);
      $('#server-verify-widget-container').slideUp();
    }
  };

  /**
   *
   */
  handleStep3Change = () => {
    const summary = $('#server_summary').val();
    const cats    = $('#server_categories').val();

    if (summary && cats.length) {
      this.enableFormInputs(this.$step4);
      this.enableFormInputs(this.$step5);
      this.enableFormInputs(this.$step6);
    } else {
      this.disableFormInputs(this.$step4);
      this.disableFormInputs(this.$step5);
      this.disableFormInputs(this.$step6);
    }
  };

  /**
   *
   */
  handleRefreshClick = () => {
    const { serverID } = this;

    const $input = $('#server_botInviteChannelID');
    this.$refreshButton.find('.icon').addClass('fa-spin');

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
    }).always(() => {
      this.$refreshButton.find('.icon').removeClass('fa-spin');
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

  /**
   * @param {jQuery} $container
   * @returns {jQuery}
   */
  disableFormInputs = ($container) => {
    $container.find('input,button,textarea,select').prop('disabled', true);
    $container.addClass('card-disabled');

    return $container;
  };

  /**
   * @param {jQuery} $container
   * @returns {jQuery}
   */
  enableFormInputs = ($container) => {
    $container.find('input,button,textarea,select').prop('disabled', false);
    $container.removeClass('card-disabled');

    return $container;
  };
}

export default ServerAddPage;
