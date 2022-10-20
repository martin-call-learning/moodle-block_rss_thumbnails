/**
 * RSS Thumbnails block
 *
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/config'], function(cfg) {
    window.requirejs.config({
        paths: {
            "glide":
                cfg.wwwroot
                + '/lib/javascript.php/'
                + cfg.jsrev
                + '/blocks/rss_thumbnails/js/glide/dist/glide'
                + (cfg.developerdebug ? '.min' : ''),
        },
        shim: {
            'glide': {exports: 'glide'},
        }
    });
});
