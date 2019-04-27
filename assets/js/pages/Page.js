import isEqual from 'lodash.isequal';

/**
 *
 */
class Page
{
  /**
   * Dump debug values
   * @type {boolean}
   */
  debug = false;

  /**
   * The page state values
   * @type {*}
   */
  state = {};

  /**
   * Changes state values and calls render() when state changes
   *
   * @param {*} values
   */
  setState = (values) => {
    const newState = Object.assign({}, this.state, values);
    if (!isEqual(this.state, newState)) {
      this.state = newState;
      if (this.debug) {
        console.log('Rendering with new state.', this.state);
      }
      this.render();
    }
  };
}

export default Page;
