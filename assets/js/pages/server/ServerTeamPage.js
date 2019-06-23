import Page from 'pages/Page';
import router from 'lib/router';

/**
 *
 */
class ServerTeamPage extends Page {
  /**
   * Initializes the page
   *
   * @param {jQuery} $page
   */
  setup = ($page) => {
    this.$btn = $('.server-team-member-remove-btn');
    this.slug = $page.data('server-slug');

    this.$btn.on('click', this.handleRemoveClick);
  };

  /**
   * @param {Event} e
   */
  handleRemoveClick = (e) => {
    const $btn         = $(e.currentTarget);
    const $parent      = $btn.parents('tr:first');
    const teamMemberID = $btn.data('member-id');

    if (confirm('Are you sure you want to remove the team member?')) {
      $.ajax({
        url:  router.generate('server_team_delete', { slug: this.slug }),
        type: 'DELETE',
        data: {
          teamMemberID
        }
      }).done(() => {
        $parent.fadeOut();
      });
    }
  };
}

export default ServerTeamPage;
