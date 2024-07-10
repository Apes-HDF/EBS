import { Controller} from '@hotwired/stimulus'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['servicesEnabledField', 'parentField', 'idField']

  parentFieldTargetConnected(element) {
    const observer = new MutationObserver( ( ) => {
      if (element.tomselect) {
        observer.disconnect()

        const toggle = document.getElementById('Group_servicesEnabled')
        toggle.addEventListener('change', () => {
          this.updateParentOptions(toggle.checked, this.parentFieldTarget)
        })
      }
    })
    observer.observe(element, {attributes: true})
  }

  async updateParentOptions(servicesEnabled, parentField) {
    const url = `/api/groups?services_enabled=${servicesEnabled}`

    const response = await fetch(url, { method: 'GET' })
    if (!response.ok) {
      return
    }
    const data = await response.json()
    const groups = data['hydra:member']

    // Remove options
    parentField.tomselect.clearOptions()

    // Populate with new options
    groups.map(group => {
      parentField.tomselect.addOption(new Option(group.name, group.id))
    })
  }

  servicesEnabledFieldTargetConnected() {
    this.servicesEnabledFieldTarget.addEventListener('change', () => {
      if(!this.servicesEnabledFieldTarget.checked) {
        const params = new URLSearchParams(this.servicesEnabledFieldTarget.getAttribute('data-toggle-url'))
        this.disableServicesForChildGroups(params.get('entityId'))
      }
    })
  }

  async disableServicesForChildGroups(groupId) {
    const url = `/api/groups/${groupId}/disable_child_services`

    const response = await fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/merge-patch+json',
      },
    })
    if (!response.ok) {
      return
    }
    const data = await response.json()
    const groupChild = data.children

    const groupChildId = groupChild.map(group => {
      return group.split('/')[3]
    })

    const allToggles = document.querySelectorAll('[data-admin-parentgroup-target="servicesEnabledField"]')
    Array.from(allToggles).map(toggle => {
      const params = new URLSearchParams(toggle.getAttribute('data-toggle-url'))
      if(groupChildId.includes(params.get('entityId'))) {
        toggle.checked = false
      }
    })

  }
}
