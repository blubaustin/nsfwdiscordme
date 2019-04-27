import Page from 'pages/Page';
import Discord from 'lib/Discord';
import router from 'lib/router';
import { generateSlug } from 'lib/utils';

/**
 *
 */
class ServerSettingsPage extends Page {
  /**
   * Initializes the page
   *
   * @param {jQuery} $page
   */
  setup = ($page) => {
    this.$form               = $page.find('form[name="server"]:first');
    this.$deleteModal        = $('#modal-server-delete');
    this.$deleteButton       = $('#server-delete-btn');
    this.$modalDeleteButton  = $('#modal-server-delete-btn');
    this.$refreshButton      = $('#server-refresh-btn');
    this.$verifyWidgetDanger = $('#server-verify-widget-danger');
    this.$inviteContainer    = $('#server-invite-bot-container');
    this.$inputName          = $('#server_name');
    this.$inputDiscordID     = $('#server_discordID');
    this.$inputInviteType    = $('#server_inviteType');

    this.$step2 = this.disableFormInputs($('#form-step-2'));
    this.$step3 = this.disableFormInputs($('#form-step-3'));
    this.$step4 = this.disableFormInputs($('#form-step-4'));
    this.$step5 = this.disableFormInputs($('#form-step-5'));
    this.$step6 = this.disableFormInputs($('#form-step-6'));

    this.$form.on('input', ':input', this.handleFormChange);
    this.$inputName.on('input', this.handleNameChange);
    this.$inputDiscordID.on('input', this.handleDiscordIDChange);
    this.$inputInviteType.on('input', this.handleInviteTypeChange);
    this.$refreshButton.on('click', this.handleRefreshClick);
    this.$deleteButton.on('click', this.handleDeleteClick);
    this.$modalDeleteButton.on('click', this.handleModalDeleteClick);
    this.$deleteModal.on('hidden.bs.modal', this.handleDeleteModalHidden);

    this.state = {
      vals: {
        inviteType: this.$inputInviteType.val()
      },
      deleteServerID:  this.$deleteButton.data('server-id'),
      widgetError:     false,
      showDeleteModal: false,
      isEditing:       !!$page.data('is-editing')
    };

    this.setupUploads();
    this.$inputDiscordID.focus();

    this.render();
  };

  /**
   *
   */
  setupUploads = () => {
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
        $('#server_bannerCropData').val(JSON.stringify(data)).trigger('input');

        cropper.destroy();
      });

    $("#server_bannerFile").fileinput(options)
      .on('fileimageloaded', (event, previewId) => {
        $image = $('#' + previewId).find('img.file-preview-image');
        $modal.modal('show');
      })
  };

  /**
   * @param {Event} e
   */
  handleFormChange = (e) => {
    const { vals } = this.state;

    const $input  = $(e.target);
    const name    = $input.attr('name').replace('server[', '').replace(']', '').replace('[]', '');
    const newVals = Object.assign({}, vals);
    newVals[name] = $input.val();

    this.setState({ vals: newVals });
  };

  /**
   *
   */
  handleNameChange = () => {
    const name = this.$inputName.val();

    $('#server_slug').val(generateSlug(name)).trigger('input');
  };

  /**
   *
   */
  handleDiscordIDChange = () => {
    const discordID = this.$inputDiscordID.val();

    if (Discord.isSnowflake(discordID)) {
      Discord.fetchWidget(discordID)
        .then(({ name }) => {
          if (name) {
            this.$inputName.val(name).trigger('input');
          }
          this.handleRefreshClick();
        });
    }
  };

  /**
   *
   */
  handleInviteTypeChange = () => {
    const { discordID } = this.state.vals;
    const inviteType = this.$inputInviteType.val();

    if (inviteType === 'widget') {
      Discord.fetchWidget(discordID)
        .then(({ instant_invite }) => {
          if (instant_invite) {
            this.setState({
              widgetError: false
            });
          } else {
            throw 'error';
          }
        })
        .catch(() => {
          this.$inputInviteType.val('').trigger('input');
          this.setState({
            widgetError: true
          });
        });
    }
  };

  /**
   *
   */
  handleRefreshClick = () => {
    const { discordID } = this.state.vals;

    const $input  = $('#server_botInviteChannelID');
    const $select = $('<select />', {
      'id':    'server_botInviteChannelID',
      'name':  'server[botInviteChannelID]',
      'class': 'form-control'
    });

    this.$refreshButton.find('.icon').addClass('fa-spin');

    $.ajax({
      url: router.generate('api_guild_channels', { serverID: discordID })
    }).done((resp) => {
      if (resp.message === 'ok') {
        $('<option />', {
          'value': '',
          'html':  'Select...'
        }).appendTo($select);

        if (resp.channels) {
          resp.channels.forEach((channel) => {
            if (channel.type === 0) {
              $('<option />', {
                'value': channel.id,
                'html':  channel.name
              }).appendTo($select);
            }
          });
        }

        $input.replaceWith($select);
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
    this.setState({
      showDeleteModal: true
    });
  };

  /**
   *
   */
  handleDeleteModalHidden = () => {
    this.setState({
      showDeleteModal: false
    });
  };

  /**
   *
   */
  handleModalDeleteClick = () => {
    const { deleteServerID } = this.state;

    $.ajax({
      url:  router.generate('api_delete_server', { serverID: deleteServerID }),
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
    if (!$container.hasClass('card-disabled')) {
      $container.addClass('card-disabled');
    }

    return $container;
  };

  /**
   * @param {jQuery} $container
   * @returns {jQuery}
   */
  enableFormInputs = ($container) => {
    $container.find('input[disabled],button[disabled],textarea[disabled],select[disabled]').prop('disabled', false);
    if ($container.hasClass('card-disabled')) {
      $container.removeClass('card-disabled');
    }

    return $container;
  };

  /**
   * @param {jQuery} $container
   * @param {boolean} expr
   * @returns {jQuery}
   */
  toggleFormInputs = ($container, expr) => {
    if (expr) {
      this.enableFormInputs($container);
    } else {
      this.disableFormInputs($container);
    }

    return $container;
  };

  /**
   *
   */
  render = () => {
    const { vals, widgetError, isEditing, showDeleteModal } = this.state;
    const { discordID, name, slug, botInviteChannelID, summary, categories, inviteType } = vals;

    this.toggleFormInputs(this.$step2, (isEditing || (discordID && name && slug)));
    this.toggleFormInputs(this.$step3, ((inviteType === 'widget' && !widgetError) || botInviteChannelID));
    this.toggleFormInputs(this.$step4, (summary && categories));
    this.toggleFormInputs(this.$step5, (summary && categories));
    this.toggleFormInputs(this.$step6, (summary && categories));

    this.$verifyWidgetDanger.toggle(widgetError && !botInviteChannelID && inviteType !== 'bot');
    if (inviteType === 'bot') {
      this.$inviteContainer.slideDown();
    } else {
      this.$inviteContainer.slideUp();
    }

    if (showDeleteModal) {
      this.$deleteModal.modal('show');
    } else {
      this.$deleteModal.modal('hide');
    }
  };
}

export default ServerSettingsPage;
