/* zoom.js
 * copyright  2014 Bas Brands, www.basbrands.nl
 * authors    Bas Brands, David Scotson
 * license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  */

var onZoom = function() {
  var zoomin = Y.one('body').hasClass('zoomin');
  if (zoomin) {
    Y.one('body').removeClass('zoomin');
    M.util.set_user_preference('theme_leaf_zoom', 'nozoom');
  } else {
    Y.one('body').addClass('zoomin');
    M.util.set_user_preference('theme_leaf_zoom', 'zoomin');
  }
};

//When the button with class .moodlezoom is clicked fire the onZoom function
M.theme_leaf = M.theme_leaf || {};
M.theme_leaf.zoom =  {
  init: function() {
    console.log('zoom');
    Y.one('body').delegate('click', onZoom, '.moodlezoom');
  }
};