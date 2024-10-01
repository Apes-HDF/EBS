import { Controller} from '@hotwired/stimulus'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['servicesEnabledField', 'parentField', 'idField', 'ownerField']

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
    // edit page
    const parentGroupId = element.value
    const servicesEnabledToggle = document.getElementById('Group_servicesEnabled')
    this.checkGroupEditDisableServices(parentGroupId, servicesEnabledToggle)

    element.addEventListener('change', () => {
      const newParentGroupId = element.value
      this.checkGroupEditDisableServices(newParentGroupId, servicesEnabledToggle)
    })

    // list page
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

  ownerFieldTargetConnected() {
    const initialUserId = this.ownerFieldTarget.value
    const groupsField = document.getElementById('Product_groups')
    this.replaceGroups(initialUserId, groupsField)
    this.ownerFieldTarget.addEventListener('change', () => {
      const userId = this.ownerFieldTarget.value

      this.replaceGroups(userId, groupsField)
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

  async replaceGroups(userId, groupsField) {
    const url = `/api/groups?user=${userId}&services_enabled=true&admin=0`
    const response = await fetch(url, {method: 'GET'})
    if (!response.ok) {
      return
    }
    const data = await response.json()
    const groups = data['hydra:member']

    const parentDiv = groupsField.parentElement
    const smallElement = parentDiv.querySelector('small')

    const saveContinue = document.getElementsByClassName('action-saveAndContinue')[0]
    const saveReturn = document.getElementsByClassName('action-saveAndReturn')[0]
    const saveAdd = document.getElementsByClassName('action-saveAndAddAnother')[0]


    groupsField.tomselect.clear()
    groupsField.tomselect.clearOptions()
    if (groups.length === 0) {
      groupsField.tomselect.disable()
      groupsField.tomselect.lock()
      // show helper
      if (null !== smallElement) {
        smallElement.style.visibility = 'visible'
      }
      if (undefined !== saveContinue) {
        saveContinue.disabled = true
      }
      if (undefined !== saveAdd) {
        saveAdd.disabled = true
      }
      saveReturn.disabled = true
    } else {
      groups.map(group => {
        groupsField.tomselect.addOption(new Option(group.name, group.id))
      })
      groupsField.tomselect.enable()
      groupsField.tomselect.unlock()

      // remove helper
      if (null !== smallElement) {
        smallElement.style.visibility = 'hidden'
      }
      if (undefined !== saveContinue) {
        saveContinue.disabled = false
      }
      if (undefined !== saveAdd) {
        saveAdd.disabled = false
      }
      saveReturn.disabled = false
    }
  }

  async checkGroupEditDisableServices(parentGroupId, servicesEnabledToggle) {
    const url = `/api/groups/${parentGroupId}`

    const response = await fetch(url, {method: 'GET'})
    if (!response.ok) {
      return
    }
    const parentGroup = await response.json()
    let parentAll = parentGroup.parentsRecursively
    parentAll.push(parentGroup['@id'])

    const parentDiv = servicesEnabledToggle.parentElement.parentElement
    const smallElement = parentDiv.querySelector('small')

    let disabled = false
    for (const parentUrl of parentAll) {
      const parentResponse = await fetch(parentUrl, { method: 'GET' })
      if (!response.ok) {
        return
      }
      const parent = await parentResponse.json()
      if (!parent.servicesEnabled) {
        servicesEnabledToggle.checked = false
        servicesEnabledToggle.disabled = true
        if (null !== smallElement) {
          smallElement.style.visibility = 'visible'
        }
        disabled = true
        break
      }
    }

    if (!disabled) {
      servicesEnabledToggle.disabled = false
      if (null !== smallElement) {
        smallElement.style.visibility = 'hidden'
      }
    }
  }
}
