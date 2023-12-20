import { Controller} from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['public', 'groups']

  connect () {
    if (this.publicTarget.checked) {
      this.hideGroups()
    }
  }

  hideGroups() {
    this.groupsTarget.classList.add('hidden')
  }
  showGroups() {
    this.groupsTarget.classList.remove('hidden')
  }
}
