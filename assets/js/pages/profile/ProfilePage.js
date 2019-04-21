import router from 'lib/router';
import { recaptchaShow, recaptchaVerify } from 'lib/recaptcha';

/**
 *
 */
class ProfilePage
{
  /**
   * Initializes the page
   */
  run() {
    this.wireBumpButtons();
    this.runBumpCountdown();
  }

  /**
   *
   */
  wireBumpButtons() {
    $('.card-server-admin').each((i, item) => {
      const $item = $(item);
      $item.find('.card-server-admin-bump-btn:first').on('click', this.handleBumpButtonClick)
    });
  }

  /**
   *
   */
  runBumpCountdown = () => {
    $('[data-next-bump-date]').each((i, item) => {
      const $item         = $(item);
      const countDownDate = new Date($item.data('next-bump-date')).getTime();

      setInterval(() => {
        const now      = new Date().getTime();
        const distance = countDownDate - now;
        const days     = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours    = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes  = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds  = Math.floor((distance % (1000 * 60)) / 1000);

        if (days !== 0) {
          $item.text(`${days}d ${hours}h ${minutes}m ${seconds}s`);
        } else if (hours !== 0) {
          $item.text(`${hours}h ${minutes}m ${seconds}s`);
        } else if (minutes !== 0) {
          $item.text(`${minutes}m ${seconds}s`);
        }
      }, 1000);
    });
  };

  /**
   * @param {Event} e
   */
  handleBumpButtonClick = (e) => {
    const $button     = $(e.target);
    const $card       = $button.parents('.card-server-admin:first');
    const serverID    = $card.data('server-id');
    const $modal      = $('#recaptcha-model');
    const recaptchaID = `recaptcha-container-${serverID}`;

    // Recaptcha complains if you reuse an element, but the user
    // may want to bump several servers. So we create a unique
    // element in the model for each recaptcha.
    const $recaptcha = $modal.find('.recaptcha');
    const $container = $('<div />', {
      'id': recaptchaID
    });
    $recaptcha.html('').append($container);

    $modal.modal('show');
    recaptchaShow(recaptchaID)
      .then((token) => {
        return recaptchaVerify(token, serverID);
      })
      .then((resp) => {
        if (resp.success) {
          $.ajax({
            url:  router.generate('api_bump', { serverID }),
            type: 'post'
          }).done((resp) => {
            if (resp.message && resp.message === 'ok') {
              $card.find('.server-bump-points:first').text(resp.bumpPoints);
            }
            $modal.modal('hide');
          });
        }
      });
  };
}

export default ProfilePage;
