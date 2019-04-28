import Page from 'pages/Page';
import router from 'lib/router';
import styles from '../../../css/_vars.scss';

/**
 *
 */
class ServerStatsPage extends Page {
  /**
   * Initializes the page
   *
   * @param {jQuery} $page
   */
  setup = ($page) => {
    this.serverID = $page.data('server-id');
    this.setupJoinChart();
  };

  /**
   *
   */
  setupJoinChart = () => {
    const { serverID } = this;

    $.ajax({
      url: router.generate('api_server_stats', { serverID })
    }).done((resp) => {
      if (resp.message !== 'ok') {
        return;
      }

      const labels  = [];
      const joins   = [];
      const views   = [];
      let joinCount = 0;
      let viewCount = 0;
      resp.joins.forEach((stat) => {
        labels.push(stat.day);

        const count = parseInt(stat.count, 10);
        joinCount += count;
        joins.push(count);
      });
      resp.views.forEach((stat) => {
        const count = parseInt(stat.count, 10);
        viewCount += count;
        views.push(count);
      });

      const $canvas = $('#server-stats-chart-joins');
      if (joinCount === 0 && viewCount === 0) {
        $canvas.hide();
        $('.server-stats-chart-joins-empty:first').show();
        return;
      }

      new Chart($canvas, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              data:            views,
              label:           '# of Views',
              borderWidth:     0,
              backgroundColor: styles.colorInfo,
            },
            {
              data:            joins,
              label:           '# of Joins',
              borderWidth:     0,
              backgroundColor: styles.colorSuccess,
            }
          ]
        },
        options: {
          legend: {
            labels: {
              fontColor: '#FFF'
            }
          },
          scales: {
            yAxes: [{
              ticks: {
                fontColor:   '#FFF',
                beginAtZero: true
              }
            }],
            xAxes: [{
              ticks: {
                fontColor: '#FFF'
              }
            }]
          }
        }
      });
    });
  };
}

export default ServerStatsPage;
