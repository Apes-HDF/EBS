import {Controller} from '@hotwired/stimulus'

export default class extends Controller {
  toggle() {
    const menu = document.querySelector('.menu')
    menu.classList.toggle('hide')
  }
}
