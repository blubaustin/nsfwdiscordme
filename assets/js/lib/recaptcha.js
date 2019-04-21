import jsonp from 'jsonp-es6';
import router from 'lib/router';

/**
 * Renders a recaptcha in the given selector
 *
 * Returns a promise which resolves to the token.
 *
 * @param {string} id The ID of the container
 * @returns {Promise<any> | Promise | Promise}
 */
export function recaptchaShow(id) {
  return new Promise((resolve) => {
    jsonp('https://www.google.com/recaptcha/api.js', { render: 'explicit' }, { callback: 'onload' })
      .then(() => {
        grecaptcha.render(id, {
          sitekey:  recaptchaSiteKey,
          callback: resolve
        });
      });
  });
}

/**
 * Resolves to a boolean indicating whether the given recaptcha token is valid
 *
 * @param {string} token Token returned by recaptcha service
 * @param {*}      id    Backend saves this ID associated with the recaptcha verification
 * @returns {Promise<any> | Promise | Promise}
 */
export function recaptchaVerify(token, id) {
  return new Promise((resolve) => {
    $.ajax({
      url:    router.generate('api_recaptcha_verify'),
      method: 'post',
      data:   {
        id,
        token
      }
    }).done(resolve);
  });
}
