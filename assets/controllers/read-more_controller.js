import {Controller} from '@hotwired/stimulus'

export default class extends Controller {
  toggle() {
    const readMoreContainer = document.querySelector('.read')
    const buttonToggle = this.element
    const opened = readMoreContainer.classList.contains('less')

    readMoreContainer.classList.toggle('less')
    readMoreContainer.classList.toggle('more')

    buttonToggle.innerHTML =  opened ? 'Voir plus' : 'Voir moins'
  }
}
