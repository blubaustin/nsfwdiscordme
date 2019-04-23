import Chart from 'chart.js';
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
      url: router.generate('api_stats_joins', { serverID })
    }).done((resp) => {
      if (resp.message !== 'ok') {
        return;
      }

      const labels = [];
      const data   = [];
      resp.data.forEach((day) => {
        labels.push(day.day);
        data.push(parseInt(day.count, 10));
      });

      const chart = new Chart('server-stats-chart-joins', {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            data,
            label:           '# of Joins',
            borderWidth:     1,
            borderColor:     styles.colorPrimary,
            backgroundColor: styles.colorSuccess,
          }]
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
                stepSize:    1,
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
