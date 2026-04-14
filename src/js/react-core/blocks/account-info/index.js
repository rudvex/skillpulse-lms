const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import Edit from './edit';

registerBlockType("splms/account-info", {
  title: __("Account Info", "skillpulse-lms"),
  icon: "admin-users",
  category: "skillpulse-lms",
  description: __("Display the user meta field, which is chosen by slug.", "skillpulse-lms"),
  keywords: [__("membership user info", "skillpulse-lms")],
  supports: {
    customClassName: false, // Removes "Custom CSS Class" from "Advanced" tab of block
    html: false // User cannot edit block as HTML
  },
  edit: Edit,
  save: function() {
    return null; // Null because we're rendering the output serverside
  }
});
