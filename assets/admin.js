import { startStimulusApp } from '@symfony/stimulus-bridge'
import AdminParentgroup from './controllers/admin_parentgroup_controller'

import './styles/admin.css'

const app = startStimulusApp()

app.register('admin-parentgroup', AdminParentgroup)
