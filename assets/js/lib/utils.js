/**
 * Converts any a string into a safe string usable as a url slug
 *
 * @param {string} name
 * @returns {string}
 */
export function generateSlug(name) {
  return name.toString().toLowerCase()
    .replace(/\s+/g, '-')           // Replace spaces with -
    .replace(/[^\w-]+/g, '')        // Remove all non-word chars
    .replace(/--+/g, '-')           // Replace multiple - with single -
    .replace(/^-+/, '')             // Trim - from start of text
    .replace(/-+$/, '');            // Trim - from end of text
}

/**
 * Returns a random number between min and max
 *
 * @param {number} min
 * @param {number} max
 * @returns {number}
 */
export function randomNumber(min, max) {
  return Math.floor(Math.random() * (max - min) ) + min;
}

/**
 * @param {*} obj
 * @param {Function} cb
 */
export function objectForEach(obj, cb) {
  for (let key in obj) {
    if (obj.hasOwnProperty(key)) {
      cb(obj[key], key);
    }
  }
}

/**
 * @param {number} milliseconds
 * @returns {string}
 */
export function millisecondsToTime(milliseconds) {
  const days    = Math.floor(milliseconds / (1000 * 60 * 60 * 24));
  const hours   = Math.floor((milliseconds % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((milliseconds % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((milliseconds % (1000 * 60)) / 1000);

  if (days !== 0) {
    return `${days}d ${hours}h ${minutes}m ${seconds}s`;
  } else if (hours !== 0) {
    return `${hours}h ${minutes}m ${seconds}s`;
  } else if (minutes !== 0) {
    return `${minutes}m ${seconds}s`;
  } else {
    return `${seconds}s`;
  }
}
