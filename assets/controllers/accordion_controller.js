import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  toggle() {
    const accordionList = this.element.nextElementSibling
    this.element.lastElementChild.classList.toggle('rotate-180')
    accordionList.classList.toggle('opened')
  }
}
