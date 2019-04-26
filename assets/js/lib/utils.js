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
