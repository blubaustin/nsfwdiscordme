import Page from 'pages/Page';
import router from 'lib/router';
import { recaptchaShow, recaptchaVerify } from 'lib/recaptcha';

/**
 *
 */
class ServerPage extends Page
{
  /**
   * Initializes the page
   *
   * @param {jQuery} $page
   */
  setup = ($page) => {
    this.$joinModal        = $('#modal-server-join');
    this.$joinModalButton  = $('#modal-server-join-btn');
    this.$joinPassword     = $('#modal-server-join-password');
    this.$joinPasswordWrap = $('#modal-server-join-password-wrap');
    this.$recaptcha        = $('#recaptcha-container');
    this.serverID          = $page.data('server-id');
    this.requiresRecaptcha = $page.data('requires-recaptcha');
    this.requiresPassword  = $page.data('requires-password');
    this.recaptchaSetup    = false;

    this.state = {
      passedPassword:  !this.requiresPassword,
      passedRecaptcha: !this.requiresRecaptcha
    };

    this.$joinModal.on('shown.bs.modal', this.handleModalShown);
    this.$joinModalButton.on('click', this.handleJoinClick);
    if (this.requiresPassword) {
      this.$joinPassword.on('input', this.handlePasswordInput);
    }

    $('#server-join-btn').on('click', this.render);
  };

  /**
   *
   */
  handleModalShown = () => {
    this.$joinPassword.focus();
  };

  /**
   *
   */
  handlePasswordInput = () => {
    this.setState({
      passedPassword: this.$joinPassword.val() !== ''
    });
  };

  /**
   * @param {*} resp
   */
  handleRecaptchaVerify = (resp) => {
    if (resp.success) {
      this.setState({
        passedRecaptcha: true
      });
    } else {
      // We shouldn't reach this point unless the user is a bot or they're
      // doing something suspicious.
      console.error(resp);
    }
  };

  /**
   * @param {string} token
   * @returns {Promise<any>|Promise}
   */
  handleRecaptchaResponse = (token) => {
    return recaptchaVerify(token, `join-${this.serverID}`);
  };

  /**
   *
   */
  handleJoinClick = () => {
    const { passedPassword, passedRecaptcha } = this.state;
    const { serverID } = this;

    if (!passedPassword || !passedRecaptcha) {
      return;
    }

    $.ajax({
      url:  router.generate('api_join', { serverID }),
      type: 'post',
      data: {
        password: this.$joinPassword.val()
      }
    }).done((resp) => {
      if (resp.message === 'ok') {
        document.location = resp.redirect;
      } else {
        // @todo
      }
    });
  };

  /**
   * Called each time there's a change to the password or recaptcha
   */
  render = () => {
    const { passedPassword, passedRecaptcha } = this.state;

    this.$joinModalButton.prop('disabled', true);
    if (this.requiresPassword) {
      this.$joinPasswordWrap.show();
    }
    if (this.requiresRecaptcha) {
      this.$recaptcha.show();

      if (!this.recaptchaSetup) {
        this.recaptchaSetup = true;
        recaptchaShow('recaptcha-container')
          .then(this.handleRecaptchaResponse)
          .then(this.handleRecaptchaVerify);
      }
    }

    if (passedPassword && passedRecaptcha) {
      this.$joinModalButton.prop('disabled', false);
    }
    this.$joinModal.modal('show');
  };
}

export default ServerPage;
