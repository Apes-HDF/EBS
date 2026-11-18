import { Controller} from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['servicesEnabledField', 'parentField']

  connect() {
  }

  updateParentOptions() {
    const userId = this.servicesEnabledFieldTarget.getAttribute('data-user-id')
    const servicesEnabled = this.servicesEnabledFieldTarget.checked

    const url = `/api/groups?user=${userId}&services_enabled=${servicesEnabled}`
    this.addGroupsWithEnabledServices(url)
  }

  async addGroupsWithEnabledServices(url) {
    const response = await fetch(url, { method: 'GET' })
    if (!response.ok) {
      return
    }
    const data = await response.json()
    const groups = data['hydra:member']

    // Remove options and set a default value
    Array.from(this.parentFieldTarget.options).map((group) => {
      this.parentFieldTarget.remove(group)
    })
    this.parentFieldTarget.add(new Option())

    // Populate with new options
    groups.map(group => {
      this.parentFieldTarget.add(new Option(group.name, group.id))
    })
  }
}
