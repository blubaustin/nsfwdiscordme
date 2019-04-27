import router from 'lib/router';
import { randomNumber } from 'lib/utils';
import { recaptchaShow, recaptchaVerify } from 'lib/recaptcha';

/**
 *
 */
class ProfilePage
{
  /**
   * Initializes the page
   *
   * @param {jQuery} $page
   */
  setup = ($page) => {
    this.$modal         = $('#recaptcha-model');
    this.$modalBumpBtn  = $('#modal-server-bump-btn');
    this.$modalVoted    = $('.modal-server-admin-voted:first');
    this.$recaptcha     = this.$modal.find('.recaptcha-container');
    this.bumpPeriodNext = new Date($page.data('bump-period-next')).getTime();
    this.$activeCard    = null;
    this.bumpingAll     = false;

    this.$modalBumpBtn.on('click', this.handleModalBumpButtonClick);
    $('.card-server-admin-bump-btn').on('click', this.handleBumpButtonClick);
    $('#profile-bump-all-btn').on('click', this.handleBumpAllClick);

    this.runBumpCountdown();
  };

  /**
   * Animates the bump count down on the modal
   */
  runBumpCountdown = () => {
    const $countDown = $('.server-next-bump:first');

    setInterval(() => {
      const now      = new Date().getTime();
      const distance = this.bumpPeriodNext - now;
      const days     = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours    = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes  = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds  = Math.floor((distance % (1000 * 60)) / 1000);

      if (days !== 0) {
        $countDown.text(`${days}d ${hours}h ${minutes}m ${seconds}s`);
      } else if (hours !== 0) {
        $countDown.text(`${hours}h ${minutes}m ${seconds}s`);
      } else if (minutes !== 0) {
        $countDown.text(`${minutes}m ${seconds}s`);
      }
    }, 1000);
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

      if (resp.voted) {
        // The have bumped already. Nothing to do but show them the already
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
  }

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
    let serverIDs = [];
    $('.card-server-admin').each((i, item) => {
      serverIDs.push($(item).data('server-id'));
    });

    $.ajax({
      url:  router.generate('api_bump_multi'),
      type: 'post',
      data: {
        servers: serverIDs
      }
    }).done((resp) => {
      if (resp.message && resp.message === 'ok') {
        for(const serverID in resp.bumped) {
          const $card = $(`.card-server-admin[data-server-id="${serverID}"]:first`);
          this.updateCardInfo($card, resp.bumped[serverID]);
        }

        this.$modal.modal('hide');
        this.$recaptcha.hide();
      } else {
        console.error(resp);
      }
    });
  };

  /**
   * @param {jQuery} $card
   * @param {{ bumpPoints: {number}, bumpUser: {string} }} info
   */
  updateCardInfo = ($card, info) => {
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
