import axios from 'axios';

export const DISCORD_API_URL = 'https://discordapp.com/api';

/**
 *
 */
class Discord
{
  /**
   * Returns a boolean indicating whether the given value is a 64bit snowflake ID
   *
   * @param {string} snowflake
   * @returns {boolean}
   */
  static isSnowflake(snowflake) {
    return /^[\d]{17,}$/.test(snowflake);
  }

  /**
   * @param {string} serverID
   * @returns {Promise<AxiosResponse<any> | never>}
   */
  static fetchWidget(serverID) {
    return axios.get(`${DISCORD_API_URL}/guilds/${serverID}/widget.json`)
      .then((resp) => {
        return resp.data;
      });
  }
}

export default Discord;
