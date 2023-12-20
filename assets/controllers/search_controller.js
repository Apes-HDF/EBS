import { Controller} from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [ 'form' ]

  /**
   * Reset the form to blank values (input reset doesn't work with prefilled values).
   */
  reset() {
    this.formTarget.elements['p[q]'].value = ''
    this.formTarget.elements['p[category]'].value = ''
    this.formTarget.elements['p[place]'].value = ''
    this.formTarget.elements['p[city]'].value = ''
    this.formTarget.elements['p[distance]'].forEach(radio => radio.checked = false)
  }
}
