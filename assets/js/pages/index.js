import HomePage from './home/HomePage';
import ServerPage from './server/ServerPage';
import ServerStatsPage from './server/ServerStatsPage';
import ServerTeamPage from './server/ServerTeamPage';
import ServerSettingsPage from './server/ServerSettingsPage';
import ProfilePage from './profile/ProfilePage';
import AdminStatsPage from './admin/AdminStatsPage';

export default {
  '#page-home':            HomePage,
  '#page-profile':         ProfilePage,
  '#page-server':          ServerPage,
  '#page-server-team':     ServerTeamPage,
  '#page-server-settings': ServerSettingsPage,
  '#page-server-stats':    ServerStatsPage,
  '#page-admin-stats':     AdminStatsPage
};
