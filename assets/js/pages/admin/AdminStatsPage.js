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
    this.setupJoinChart();
  };

  /**
   *
   */
  setupJoinChart = () => {
    $.ajax({
      url:  '/admin/stats',
      type: 'post'
    }).done((resp) => {
      if (resp.message !== 'ok') {
        return;
      }

      const labels  = [];
      const joins   = [];
      const views   = [];
      const bumps   = [];
      resp.joins.forEach((stat) => {
        labels.push(stat.day);

        const count = parseInt(stat.count, 10);
        joins.push(count);
      });
      resp.views.forEach((stat) => {
        const count = parseInt(stat.count, 10);
        views.push(count);
      });
      resp.bumps.forEach((stat) => {
        const count = parseInt(stat.count, 10);
        bumps.push(count);
      });

      let $canvas = $('#server-stats-chart-joins');
      new Chart($canvas, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              data:            views,
              label:           '# of Server Views',
              borderWidth:     0,
              backgroundColor: styles.colorInfo,
            },
            {
              data:            joins,
              label:           '# of Server Joins',
              borderWidth:     0,
              backgroundColor: styles.colorSuccess,
            }
          ]
        },
        options: {
          legend: {
            labels: {
              fontColor: '#000'
            }
          },
          scales: {
            yAxes: [{
              ticks: {
                fontColor:   '#000',
                beginAtZero: true
              }
            }],
            xAxes: [{
              ticks: {
                fontColor: '#000'
              }
            }]
          }
        }
      });

      $canvas = $('#server-stats-chart-bumps');
      new Chart($canvas, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              data:            bumps,
              label:           '# of Server Bumps',
              borderWidth:     0,
              backgroundColor: styles.colorSuccess,
            }
          ]
        },
        options: {
          legend: {
            labels: {
              fontColor: '#000'
            }
          },
          scales: {
            yAxes: [{
              ticks: {
                fontColor:   '#000',
                beginAtZero: true
              }
            }],
            xAxes: [{
              ticks: {
                fontColor: '#000'
              }
            }]
          }
        }
      });
    });
  };
}

export default ServerStatsPage;
