import isEqual from 'lodash.isequal';

/**
 *
 */
class Page
{
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
    if (!isEqual(this.state, newState) && this.render !== undefined) {
      this.state = newState;
      this.render();
    }
  };
}

export default Page;
