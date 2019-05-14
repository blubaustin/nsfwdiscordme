import router from 'lib/router';
import { millisecondsToTime, randomNumber, objectForEach } from 'lib/utils';
import { recaptchaShow, recaptchaVerify } from 'lib/recaptcha';

/**
 *
 */
class ProfilePage
{
  /**
   * Initializes the page
   */
  setup = () => {
    this.$modal        = $('#recaptcha-model');
    this.$cards        = $('.card-server-admin');
    this.$modalBumpBtn = $('#modal-server-bump-btn');
    this.$modalVoted   = $('.modal-server-admin-voted:first');
    this.$recaptcha    = this.$modal.find('.recaptcha-container');
    this.$activeCard   = null;
    this.bumpingAll    = false;
    this.countdowns    = {};

    this.$modalBumpBtn.on('click', this.handleModalBumpButtonClick);
    $('.card-server-admin-bump-btn').on('click', this.handleBumpButtonClick);
    $('#profile-bump-all-btn').on('click', this.handleBumpAllClick);

    this.startCountdowns();
  };

  /**
   * Animates the bump count downs
   */
  startCountdowns = () => {
    this.$cards.each((i, item) => {
      const $el      = $(item);
      const serverID = $el.data('server-id');
      this.countdowns[serverID] = {
        label:    $el.find('.server-bump-next:first'),
        nextBump: $el.data('next-bump') * 1000
      };
    });

    this.handleCountdownTick();
    setInterval(this.handleCountdownTick, 1000);
  };

  /**
   * Called for each countdown interval
   */
  handleCountdownTick = () => {
    objectForEach(this.countdowns, (item, key) => {
      this.countdowns[key].nextBump -= 1000;
      if (item.nextBump <= 0) {
        item.label.text('NOW!');
      } else {
        item.label.text(millisecondsToTime(item.nextBump));
      }
    });
  };

  /**
   * Called when a card bump button is clicked
   *
   * @param {Event} e
   */
  handleBumpButtonClick = (e) => {
    const $button     = $(e.target);
    this.$activeCard  = $button.parents('.card-server-admin:first');
    const serverID    = this.$activeCard.data('server-id');
    const recaptchaID = `recaptcha-container-${serverID}`;

    this.$modalBumpBtn.prop('disabled', true);
    this.$modalVoted.hide();
    this.$recaptcha.hide();

    // Determine if the user has already bumped during this period.
    $.ajax({
      url: router.generate('api_bump_server_ready', { serverID })
    }).done((resp) => {
      this.$modal.modal('show');

      if (!resp.ready) {
        // Server is not ready to bump. Nothing to do but show them the already
        // bumped message.
        this.$modalVoted.show();
        this.$recaptcha.hide();
      } else {
        // Recaptcha complains if we reuse an element, but the user
        // may want to bump several servers. So we create a unique
        // element in the model for each recaptcha.
        const $container = $('<div />', {
          'id': recaptchaID
        });
        this.$recaptcha.html('').append($container).show();

        recaptchaShow(recaptchaID)
          .then((token) => {
            // Send the token and serverID to the backend. The backend verifies
            // the token with google and saves the serverID to the session.
            return recaptchaVerify(token, serverID);
          })
          .then((resp) => {
            if (resp.success) {
              // The token was valid. Now the user can click the big bump button.
              this.$modalBumpBtn.prop('disabled', false);
            } else {
              // We shouldn't reach this point unless the user is a bot or they're
              // doing something suspicious.
              console.error(resp);
            }
          });
      }
    });
  };

  /**
   * Called when the bump all button is clicked
   */
  handleBumpAllClick = () => {
    const recaptchaID = `recaptcha-container-${randomNumber(0, 10000)}`;

    $.ajax({
      url: router.generate('api_bump_ready')
    }).done((resp) => {
      this.$modal.modal('show');

      if (resp.ready.length === 0) {
        // All of the servers have been bumped already. Nothing to do but show them the already
        // bumped message.
        this.$modalVoted.show();
        this.$recaptcha.hide();
      } else {
        // Recaptcha complains if you reuse an element, but the user
        // may want to bump several servers. So we create a unique
        // element in the model for each recaptcha.
        const $container = $('<div />', {
          'id': recaptchaID
        });
        this.$recaptcha.html('').append($container).show();

        recaptchaShow(recaptchaID)
          .then((token) => {
            // Send the token and nonce to the backend. The backend verifies
            // the token with google and saves the nonce to the session.
            return recaptchaVerify(token, 'bump-ready');
          })
          .then((resp) => {
            if (resp.success) {
              // The token was valid. Now the user can click the big bump button.
              this.bumpingAll = true;
              this.$modalBumpBtn.prop('disabled', false);
            } else {
              // We shouldn't reach this point unless the user is a bot or they're
              // doing something suspicious.
              console.error(resp);
            }
          });
      }
    });
  };

  /**
   * Called when the modal bump button is clicked
   *
   * Sends the bump command to the backend and then updates the card with
   * the current bump count.
   */
  handleModalBumpButtonClick = () => {
    this.$modalBumpBtn.prop('disabled', true);

    if (this.bumpingAll) {
      this.bumpAll();
    } else {
      this.bumpServer();
    }
  };

  /**
   * Bumps the server for the active card
   */
  bumpServer = () => {
    const serverID = this.$activeCard.data('server-id');

    $.ajax({
      url:  router.generate('api_bump', { serverID }),
      type: 'post'
    }).done((resp) => {
      if (resp.message && resp.message === 'ok') {
        this.updateCardInfo(this.$activeCard, resp);
        this.$modal.modal('hide');
        this.$recaptcha.hide();
      } else {
        console.error(resp);
      }
    });
  };

  /**
   * Bumps all of the servers
   */
  bumpAll = () => {
    let servers = [];
    $('.card-server-admin').each((i, item) => {
      servers.push($(item).data('server-id'));
    });

    $.ajax({
      url:  router.generate('api_bump_multi'),
      type: 'post',
      data: {
        servers
      }
    }).done((resp) => {
      const { message, bumped } = resp;

      if (message === 'ok') {
        objectForEach(bumped, (item, serverID) => {
          const $card = $(`.card-server-admin[data-server-id="${serverID}"]:first`);
          this.updateCardInfo($card, item);
        });
        this.$modal.modal('hide');
        this.$recaptcha.hide();
      } else {
        console.error(resp);
      }
    });
  };

  /**
   * @param {jQuery} $card
   * @param {{ bumpPoints: {number}, bumpUser: {string}, nextBump: {number} }} info
   */
  updateCardInfo = ($card, info) => {
    const serverID = $card.data('server-id');
    this.countdowns[serverID].nextBump = info.nextBump * 1000;
    $card.data('next-bump', info.nextBump);

    $card.find('.server-bump-points:first').text(info.bumpPoints);
    $card.find('.server-bump-user:first').text(info.bumpUser);
    $card.find('.server-bump-date:first').text('Just now');

    $card.addClass('card-server-admin-flash-success');
    setTimeout(() => {
      $card.removeClass('card-server-admin-flash-success');
    }, 2000);
  };
}

export default ProfilePage;
