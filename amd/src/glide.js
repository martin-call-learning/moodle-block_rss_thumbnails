/**
 * RSS Thumbnails block
 *
 * @package
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// eslint-disable-next-line no-unused-vars
define(['block_rss_thumbnails/config'], function() {
    return function(locator, config) {
        require(['glide'], function(Glide) {
            // Show the slider now we are initialised.
            document.querySelector(locator).classList.remove('d-none');
            (new Glide(locator, config)).mount();
        });
    };
});
