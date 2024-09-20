import { Controller} from '@hotwired/stimulus'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['servicesEnabledField', 'parentField', 'idField']

  connect() {
    const parentFields = document.querySelectorAll('[data-label="Parent"]')
    const trs = Array.from(parentFields)
      .map(e => e.firstElementChild)
      .filter(e => e.tagName === 'A')
      .map(e => e.closest('tr'))
    for (const tr of trs) {
      this.checkDisabledServices(tr)
    }
  }

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
      const parentFields = document.querySelectorAll('[data-label="Parent"]')
      const trs = Array.from(parentFields)
        .map(e => e.firstElementChild)
        .filter(e => e.tagName === 'A')
        .map(e => e.closest('tr'))
      for (const tr of trs) {
        this.checkDisabledServices(tr)
      }
    })
  }

  async checkDisabledServices(tr) {
    const id = tr.getAttribute('data-id')
    const url = `/api/groups/${id}`

    const response = await fetch(url, {method: 'GET'})
    if (!response.ok) {
      return
    }
    const group = await response.json()

    let disabledTr = false
    for (const parentUrl of group.parentsRecursively) {
      const parentResponse = await fetch(parentUrl, { method: 'GET' })
      if (!response.ok) {
        return
      }
      const parent = await parentResponse.json()
      if (!parent.servicesEnabled) {
        tr.querySelector('[data-admin-parentgroup-target="servicesEnabledField"]').disabled = true
        disabledTr = true
        break
      }
    }
    if (!disabledTr) {
      tr.querySelector('[data-admin-parentgroup-target="servicesEnabledField"]').disabled = false
    }
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
    const groupChild = data.childrenRecursively

    const groupChildId = groupChild.map(group => {
      return group.split('/')[3]
    })

    const allToggles = document.querySelectorAll('[data-admin-parentgroup-target="servicesEnabledField"]')
    Array.from(allToggles).map(toggle => {
      const params = new URLSearchParams(toggle.getAttribute('data-toggle-url'))
      if(groupChildId.includes(params.get('entityId'))) {
        toggle.checked = false
        toggle.disabled = true
      }
    })

  }
}
